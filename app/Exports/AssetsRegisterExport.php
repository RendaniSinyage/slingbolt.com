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

class AssetsRegisterExport implements FromArray, WithEvents, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithMapping
{
    public $data;
    public $companyName;
    public $startDate;
    public $endDate;
    public $totalAssetValue;
    public $totalDepreciation;
    public $netAssetValue;

    public function __construct($exportData)
    {
        $this->companyName = $exportData['company_name'];
        $this->startDate = $exportData['start_date'];
        $this->endDate = $exportData['end_date'];
        $this->totalAssetValue = $exportData['totalAssetValue'];
        $this->totalDepreciation = $exportData['totalDepreciation'];
        $this->netAssetValue = $exportData['netAssetValue'];

        $formattedData = [];
        
        // Group assets by type for better organization
        $currentAssets = [];
        $nonCurrentAssets = [];
        
        foreach ($exportData['assetAccounts'] as $asset) {
            $currentValue = $asset['is_depreciation'] ? 0 : $asset['balance'];
            $depreciation = $asset['is_depreciation'] ? abs($asset['balance']) : 0;
            $netValue = $currentValue - $depreciation;
            
            $assetData = [
                'Account Code' => $asset['account_code'],
                'Account Name' => $asset['account_name'],
                'Asset Type' => $asset['sub_type'],
                'Purchase Date' => $asset['purchase_date'] ? date('Y-m-d', strtotime($asset['purchase_date'])) : '',
                'Current Value' => $currentValue,
                'Depreciation' => $depreciation,
                'Net Value' => $netValue,
            ];
            
            if ($asset['sub_type'] == 'Current Asset') {
                $currentAssets[] = $assetData;
            } else {
                $nonCurrentAssets[] = $assetData;
            }
        }
        
        // Build formatted data with proper grouping
        if (!empty($currentAssets)) {
            $formattedData[] = [
                'Account Code' => '',
                'Account Name' => 'Current Assets',
                'Asset Type' => '',
                'Purchase Date' => '',
                'Current Value' => '',
                'Depreciation' => '',
                'Net Value' => '',
            ];
            
            foreach ($currentAssets as $asset) {
                $formattedData[] = [
                    'Account Code' => '  ' . $asset['Account Code'],
                    'Account Name' => '  ' . $asset['Account Name'],
                    'Asset Type' => $asset['Asset Type'],
                    'Purchase Date' => $asset['Purchase Date'],
                    'Current Value' => $asset['Current Value'],
                    'Depreciation' => $asset['Depreciation'],
                    'Net Value' => $asset['Net Value'],
                ];
            }
            
            // Add blank row
            $formattedData[] = [
                'Account Code' => '',
                'Account Name' => '',
                'Asset Type' => '',
                'Purchase Date' => '',
                'Current Value' => '',
                'Depreciation' => '',
                'Net Value' => '',
            ];
        }
        
        if (!empty($nonCurrentAssets)) {
            $formattedData[] = [
                'Account Code' => '',
                'Account Name' => 'Non-Current Assets',
                'Asset Type' => '',
                'Purchase Date' => '',
                'Current Value' => '',
                'Depreciation' => '',
                'Net Value' => '',
            ];
            
            foreach ($nonCurrentAssets as $asset) {
                $formattedData[] = [
                    'Account Code' => '  ' . $asset['Account Code'],
                    'Account Name' => '  ' . $asset['Account Name'],
                    'Asset Type' => $asset['Asset Type'],
                    'Purchase Date' => $asset['Purchase Date'],
                    'Current Value' => $asset['Current Value'],
                    'Depreciation' => $asset['Depreciation'],
                    'Net Value' => $asset['Net Value'],
                ];
            }
            
            // Add blank row
            $formattedData[] = [
                'Account Code' => '',
                'Account Name' => '',
                'Asset Type' => '',
                'Purchase Date' => '',
                'Current Value' => '',
                'Depreciation' => '',
                'Net Value' => '',
            ];
        }
        
        // Add summary section
        $formattedData[] = [
            'Account Code' => '',
            'Account Name' => 'SUMMARY',
            'Asset Type' => '',
            'Purchase Date' => '',
            'Current Value' => '',
            'Depreciation' => '',
            'Net Value' => '',
        ];
        
        $formattedData[] = [
            'Account Code' => '',
            'Account Name' => 'Total Asset Value',
            'Asset Type' => '',
            'Purchase Date' => '',
            'Current Value' => $this->totalAssetValue,
            'Depreciation' => '',
            'Net Value' => '',
        ];
        
        $formattedData[] = [
            'Account Code' => '',
            'Account Name' => 'Total Depreciation',
            'Asset Type' => '',
            'Purchase Date' => '',
            'Current Value' => '',
            'Depreciation' => $this->totalDepreciation,
            'Net Value' => '',
        ];
        
        $formattedData[] = [
            'Account Code' => '',
            'Account Name' => 'Net Asset Value',
            'Asset Type' => '',
            'Purchase Date' => '',
            'Current Value' => '',
            'Depreciation' => '',
            'Net Value' => $this->netAssetValue,
        ];

        $this->data = $formattedData;
    }

    public function map($row): array
    {
        return [
            $row['Account Code'],
            $row['Account Name'],
            $row['Asset Type'],
            $row['Purchase Date'],
            is_numeric($row['Current Value']) ? number_format($row['Current Value'], 2) : $row['Current Value'],
            is_numeric($row['Depreciation']) ? number_format($row['Depreciation'], 2) : $row['Depreciation'],
            is_numeric($row['Net Value']) ? number_format($row['Net Value'], 2) : $row['Net Value'],
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Account Code
            'B' => 35,  // Account Name
            'C' => 20,  // Asset Type
            'D' => 15,  // Purchase Date
            'E' => 15,  // Current Value
            'F' => 15,  // Depreciation
            'G' => 15,  // Net Value
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('A5:G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A5:G5')->getFill()->getStartColor()->setARGB('FFE0E0E0');
    }

    public function array(): array
    {
        return $this->data;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                // Any pre-processing if needed
            },

            AfterSheet::class => function (AfterSheet $event) {
                // Merge cells for title rows
                $event->sheet->getDelegate()->mergeCells('A1:G1');
                $event->sheet->getDelegate()->mergeCells('A2:G2');
                $event->sheet->getDelegate()->mergeCells('A3:G3');

                // Set title content
                $event->sheet->getDelegate()->setCellValue('A1', 'Assets Register - ' . $this->companyName);
                $event->sheet->getDelegate()->setCellValue('A2', 'Print Out Date: ' . date('Y-m-d H:i'));
		$event->sheet->getDelegate()->setCellValue('A3', ($this->startDate !== '1900-01-01' ? 'Period: ' . date('d F Y', strtotime($this->startDate)) . ' to ' : 'As of: ') . date('d F Y', strtotime($this->endDate)));

                // Style the title rows
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A2')->getFont()->setSize(10);
                $event->sheet->getStyle('A3')->getFont()->setSize(10);

                // Center align titles
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Style group headers and summary rows
                $data = $this->data;
                foreach ($data as $index => $row) {
                    $rowIndex = $index + 6; // Adjust for header and title rows
                    
                    // Style group headers (Current Assets, Non-Current Assets, SUMMARY)
                    if (in_array($row['Account Name'], ['Current Assets', 'Non-Current Assets', 'SUMMARY'])) {
                        $event->sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getFont()->setBold(true);
                        $event->sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFD0D0D0');
                    }
                    
                    // Style summary total rows
                    if (in_array($row['Account Name'], ['Total Asset Value', 'Total Depreciation', 'Net Asset Value'])) {
                        $event->sheet->getStyle('A' . $rowIndex . ':G' . $rowIndex)->getFont()->setBold(true);
                    }
                }

                // Right align numeric columns
                $event->sheet->getStyle('E:G')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                // Add borders to data area
                $lastRow = count($this->data) + 5;
                $event->sheet->getStyle('A5:G' . $lastRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Account Code',
            'Account Name',
            'Asset Type',
            'Purchase Date',
            'Current Value',
            'Depreciation',
            'Net Value',
        ];
    }
}