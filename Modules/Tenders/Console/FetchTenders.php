<?php

namespace Modules\Tenders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Tenders\Entities\Tender;
use Modules\Tenders\Entities\TenderSetting;
use Modules\Tenders\Entities\DeniedTender;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Log;

class FetchTenders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenders:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch tenders from the eTenders API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching tenders...');

        $companies = User::where('type', 'company')->get();

        foreach ($companies as $company) {
            $settings = TenderSetting::where('created_by', $company->id)->first();

            if (!$settings) {
                $this->info("No tender settings found for company {$company->name}. Skipping.");
                continue;
            }

            try {
                $response = Http::get('https://ocds-api.etenders.gov.za/api/OCDSReleases');

                if ($response->failed()) {
                    $this->error("Failed to fetch tenders from the API for company {$company->name}. Status: " . $response->status());
                    Log::error("Tenders API request failed for company {$company->id}: " . $response->body());
                    continue;
                }
            } catch (\Exception $e) {
                $this->error("Failed to fetch tenders from the API for company {$company->name}. Exception: " . $e->getMessage());
                Log::error("Tenders API request exception for company {$company->id}: " . $e->getMessage());
                continue;
            }

            $releases = $response->json()['releases'];
            $deniedTenders = DeniedTender::where('created_by', $company->id)->pluck('ocid')->toArray();
            $newTenders = 0;

            foreach ($releases as $release) {
                $tenderData = $release['tender'];

                // Skip if tender is denied
                if (in_array($release['ocid'], $deniedTenders)) {
                    continue;
                }

                // Filter by settings
                $passesFilters = $this->applyFilters($release, $settings);

                if ($passesFilters) {
                    $tender = Tender::updateOrCreate(
                        ['ocid' => $release['ocid']],
                        [
                            'title' => $tenderData['title'],
                            'description' => $tenderData['description'],
                            'status' => $tenderData['status'],
                            'main_procurement_category' => $tenderData['mainProcurementCategory'],
                            'additional_procurement_categories' => json_encode($tenderData['additionalProcurementCategories']),
                            'submission_method' => json_encode($tenderData['submissionMethod']),
                            'procuring_entity_name' => $tenderData['procuringEntity']['name'],
                            'procuring_entity_id' => $tenderData['procuringEntity']['id'],
                            'tender_period_start_date' => $tenderData['tenderPeriod']['startDate'],
                            'tender_period_end_date' => $tenderData['tenderPeriod']['endDate'],
                        ]
                    );

                    if ($tender->wasRecentlyCreated) {
                        $newTenders++;
                    }
                    $company->tenders()->syncWithoutDetaching($tender->id);
                }
            }

            if ($newTenders > 0) {
                $obj = [
                    'user_name' => $company->name,
                    'app_url' => env('APP_URL'),
                ];
                Utility::sendEmailTemplate('new_tender', [$company->email], $obj);
            }
        }

        $this->info('Tenders fetched successfully.');
    }

    private function applyFilters($release, $settings)
    {
        $tenderData = $release['tender'];

        // Category filter
        if (!empty($settings->categories)) {
            $settingCategories = json_decode($settings->categories, true);
            $tenderCategories = array_merge([$tenderData['mainProcurementCategory']], $tenderData['additionalProcurementCategories'] ?? []);
            if (count(array_intersect($settingCategories, $tenderCategories)) == 0) {
                return false;
            }
        }

        // Province filter
        if (!empty($settings->provinces)) {
            $settingProvinces = json_decode($settings->provinces, true);
            $procuringEntityId = $tenderData['procuringEntity']['id'];
            $tenderProvince = '';

            foreach ($release['parties'] as $party) {
                if ($party['id'] == $procuringEntityId && in_array('procuringEntity', $party['roles'])) {
                    $tenderProvince = $party['address']['region'] ?? '';
                    break;
                }
            }

            if (empty($tenderProvince) || !in_array($tenderProvince, $settingProvinces)) {
                return false;
            }
        }

        // Type filter
        if (!empty($settings->type) && $tenderData['procurementMethod'] != $settings->type) {
            return false;
        }

        // Submission type filter
        if (!empty($settings->submission_type)) {
            if (!in_array($settings->submission_type, $tenderData['submissionMethod'])) {
                return false;
            }
        }

        return true;
    }
}
