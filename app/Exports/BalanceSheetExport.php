<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Utility;

class BalanceSheetExport implements FromArray, WithEvents, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $data;
    public $companyName;
    public $startDate;
    public $endDate;
    public $settings;

    public function __construct($data, $startDate, $endDate, $companyName)
    {
        // Get currency settings
        $this->settings = Utility::settings();

        $formattedData = [];
        $liabilitiesOrEquityEncountered = false;
        $liabilitiesOrEquityEncountered1 = false;
        $liabilities = false;
        $equity = false;
        $assets = false;

        foreach ($data as $category => $subCategories) {
            $total = 0;
            $amountTotal = 0;
            if ($category == 'Liabilities') {
                if (!$liabilitiesOrEquityEncountered) {
                    $formattedData[] = [
                        'Account Name' => 'Liabilities & Equity',
                        'Account No' => '',
                        'Total' => '',
                    ];
                    $liabilitiesOrEquityEncountered = true;
                }

                if (!$liabilities) {

                    $formattedData[] = [
                        'Account Name' => '  ' . $category,
                        'Account No' => '',
                        'Total' => '',

                    ];
                    $liabilities = true;
                }
            } else if ($category == 'Equity'){
                if (!$equity) {

                    $formattedData[] = [
                        'Account Name' => '  ' . $category,
                        'Account No' => '',
                        'Total' => '',

                    ];
                    $equity = true;
                }                    
            } else {
                if (!$assets) {

                    $formattedData[] = [
                        'Account Name' => $category,
                        'Account No' => '',
                        'Total' => '',

                    ];
                    $assets = true;
                }
            }

            foreach ($subCategories as $subkey => $subCategory) {

                foreach ($subCategory['account'] as $account) {
                    if ($account != []) {
                        $sub_type = !empty($subCategory['subType']) ? $subCategory['subType'] : '';
                        $formattedData[] = [
                            'Account Name' => '    ' . $sub_type,
                            'Account No' => '',
                            'Total' => '',
                        ];
                        break;
                    }
                }

                foreach ($subCategory['account'] as $key => $account) {
                    foreach ($account as $key => $record) {
                    $sub_type = !empty($subCategory['subType']) ? $subCategory['subType'] : '';
                    if (($record['netAmount'] != null && $record['account_name'] == 'Total ' . $sub_type) || $record['account_name'] == 'Current Year Earnings') {
                        $amount = $category == 'Assets' ? -$record['netAmount'] : $record['netAmount'];
                        $formattedData[] = [
                            'Account Name' => '    ' . $record['account_name'],
                            'Account No' => $record['account_code'],
                            'Total' => $amount,
                        ];

                        $amountTotal += $amount;
                    } 
                    elseif ($record['account'] == 'parent' || $record['account'] == 'parentTotal') {
                        $amount = $category == 'Assets' ? -$record['netAmount'] : $record['netAmount'];
                        $formattedData[] = [
                            'Account Name' => '       ' . $record['account_name'],
                            'Account No' => $record['account_code'],
                            'Total' => $amount,
                        ];
                    }
                    elseif ($record['netAmount'] != null && !preg_match('/\bTotal\b/i', $record['account_name'])) {
                        $amount = $category == 'Assets' ? -$record['netAmount'] : $record['netAmount'];
                        $formattedData[] = [
                            'Account Name' => '         ' . $record['account_name'],
                            'Account No' => $record['account_code'],
                            'Total' => $amount,
                        ];

                    }
                }
                }

            }

            if ($category == 'Liabilities' || $category == 'Equity') {
                $formattedData[] = [
                    'Account Name' => '  Total ' . $category,
                    'Account No' => '',
                    'Total' => $amountTotal,
                ];
                if ($category == 'Liabilities') {
                    $formattedData[] = [
                        'Account Name' => '',
                        'Account No' => '',
                        'Total' => '',
                    ];
                }
            } else {
                $formattedData[] = [
                    'Account Name' => 'Total ' . $category,
                    'Account No' => '',
                    'Total' => $amountTotal,
                ];
                $formattedData[] = [
                    'Account Name' => '',
                    'Account No' => '',
                    'Total' => '',
                ];
            }
        }
        foreach ($formattedData as $a) {
            if ($a['Account Name'] == '  Total Liabilities' || $a['Account Name'] == '  Total Equity') {
                $total += $a['Total'];
            }
        }

        if (!$liabilitiesOrEquityEncountered1) {
            $formattedData[] = [
                'Account Name' => 'Total Liabilities & Equity',
                'Account No' => '',
                'Total' => $total,
            ];
            $liabilitiesOrEquityEncountered1 = true;

        }
        $this->data = $formattedData;
        $this->companyName = $companyName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function map($row): array
    {
        // Format currency for non-empty total values
        $formattedTotal = '';
        if ($row['Total'] !== '' && is_numeric($row['Total'])) {
            if ($row['Total'] == 0 || $row['Total'] == 0.0) {
                $formattedTotal = Utility::priceFormat($this->settings, 0);
            } else {
                $formattedTotal = Utility::priceFormat($this->settings, $row['Total']);
            }
        }

        return [
            $row['Account Name'],
            $row['Account No'],
            $formattedTotal,
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,  // Increased from 30 to 45 for account names
            'B' => 15,  // Account No
            'C' => 20,  // Total - increased for currency formatting
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('B5')->getFont()->setBold(true);
        $sheet->getStyle('C5')->getFont()->setBold(true);
        
        // Right align the Total column
        $sheet->getStyle('C:C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {

            },

            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:C1');
                $event->sheet->getDelegate()->mergeCells('A2:C2');
                $event->sheet->getDelegate()->mergeCells('A3:C3');

                $event->sheet->getDelegate()->setCellValue('A1', 'Balance Sheet - ' . $this->companyName)->getStyle('A1')->getFont()->setBold(true);
                $event->sheet->getDelegate()->setCellValue('A2', 'Print Out Date : ' . date('Y-m-d H:i'));
      		$event->sheet->getDelegate()->setCellValue('A3', ($this->startDate !== '1900-01-01' ? 'Period: ' . date('d M Y', strtotime($this->startDate)) . ' to ' : 'As of: ') . date('d F Y', strtotime($this->endDate)));

                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('A')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $data = $this->data;
                foreach ($data as $index => $row) {

                    if (isset($row['Account Name']) && ($row['Account Name'] == '  Total Liabilities' || $row['Account Name'] == 'Assets' ||
                        $row['Account Name'] == 'Total Assets' || $row['Account Name'] == '  Liabilities' || $row['Account Name'] == '  Equity' ||
                        $row['Account Name'] == '  Total Equity' || $row['Account Name'] == 'Liabilities & Equity' || $row['Account Name'] == 'Total Liabilities & Equity' ||
                        preg_match('/\bTotal\b/i', $row['Account Name']))) {
                        $rowIndex = $index + 6; // Adjust for 1-based indexing and header row
                        $event->sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                            ]);
                    }
                }
            },
        ];
    }

    public function headings(): array
    {
        return [
            "Account",
            "Account No",
            "Total",
        ];
    }
}