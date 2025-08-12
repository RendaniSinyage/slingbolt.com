<?php

namespace Modules\Tenders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Tenders\Entities\Tender;
use Modules\Tenders\Entities\TenderSetting;
use Modules\Tenders\Entities\DeniedTender;
use App\Models\User;
use App\Models\Utility;

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
            $settings = TenderSetting::where('company_id', $company->id)->first();

            if (!$settings) {
                $this->info("No tender settings found for company {$company->name}. Skipping.");
                continue;
            }

            $response = Http::get('https://ocds-api.etenders.gov.za/api/OCDSReleases');

            if ($response->failed()) {
                $this->error("Failed to fetch tenders from the API for company {$company->name}.");
                continue;
            }

            $releases = $response->json()['releases'];
            $deniedTenders = DeniedTender::where('company_id', $company->id)->pluck('ocid')->toArray();
            $newTenders = 0;

            foreach ($releases as $release) {
                $tenderData = $release['tender'];

                // Skip if tender is denied
                if (in_array($release['ocid'], $deniedTenders)) {
                    continue;
                }

                // Filter by settings
                $passesFilters = $this->applyFilters($tenderData, $settings);

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

                    if($tender->wasRecentlyCreated) {
                        $newTenders++;
                    }
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

    private function applyFilters($tenderData, $settings)
    {
        // Category filter
        if (!empty($settings->categories)) {
            $settingCategories = json_decode($settings->categories, true);
            $tenderCategories = array_merge([$tenderData['mainProcurementCategory']], $tenderData['additionalProcurementCategories'] ?? []);
            if (count(array_intersect($settingCategories, $tenderCategories)) == 0) {
                return false;
            }
        }

        // Province filter (assuming province is in procuringEntity name)
        if (!empty($settings->provinces)) {
            $settingProvinces = json_decode($settings->provinces, true);
            $passesProvince = false;
            foreach ($settingProvinces as $province) {
                if (stripos($tenderData['procuringEntity']['name'], $province) !== false) {
                    $passesProvince = true;
                    break;
                }
            }
            if (!$passesProvince) {
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
