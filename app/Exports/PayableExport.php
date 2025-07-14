<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PayableExport implements FromArray, WithHeadings, WithStyles, WithCustomStartCell, WithColumnWidths, WithEvents
{
    protected $tabType;
    
    public function __construct($data, $startDate, $endDate, $companyName, $tabType = '#vendor_balance')
    {
        $this->tabType = $tabType;
        $formattedData = [];
        
        switch($tabType) {
            case '#vendor_balance':
                $formattedData = $this->formatVendorBalance($data);
                break;
            case '#payable_summary':
                $formattedData = $this->formatPayableSummary($data);
                break;
            case '#payable_details':
                $formattedData = $this->formatPayableDetails($data);
                break;
            default:
                $formattedData = $this->formatVendorBalance($data);
                break;
        }
        
        $this->data = $formattedData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
    }

    private function formatVendorBalance($data)
    {
        $formattedData = [];
        $total = 0;
        
        foreach($data as $vendor) {
            $vendorBalance = $vendor['price'] + $vendor['total_tax'] - $vendor['pay_price'];
            $balance = $vendorBalance - $vendor['debit_price'];
            $total += $balance;
            
            $formattedData[] = [
                'Vendor Name' => $vendor['name'],
                'Billed Amount' => $vendorBalance,
                'Available Debit' => !empty($vendor['debit_price']) ? $vendor['debit_price'] : 0,
                'Closing Balance' => $balance,
            ];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Vendor Name' => 'Total',
                'Billed Amount' => '',
                'Available Debit' => '',
                'Closing Balance' => $total,
            ];
        }
        
        return $formattedData;
    }

    private function formatPayableSummary($data)
    {
        $formattedData = [];
        $total = 0;
        $totalAmount = 0;
        
        foreach($data as $summary) {
            if ($summary['bill']) {
                $payableBalance = $summary['price'] + $summary['total_tax'];
                $transactionType = $summary['type']; // 'Bill' or 'Expense'
                if ($summary['type'] == 'Bill') {
                    $transaction = \Auth::user()->billNumberFormat($summary['bill']);
                } else {
                    $transaction = \Auth::user()->expenseNumberFormat($summary['bill']);
                }
            } else {
                $payableBalance = -$summary['price'];
                $transactionType = 'Debit Note';
                $transaction = 'Debit Note';
            }
            
            $pay_price = $summary['pay_price'] != null ? $summary['pay_price'] : 0;
            $balance = $payableBalance - $pay_price;
            $total += $balance;
            $totalAmount += $payableBalance;
            
            $statusText = $this->getStatusText($summary['status']);
            
            $formattedData[] = [
                'Vendor Name' => $summary['name'],
                'Date' => $summary['bill_date'],
                'Transaction' => $transaction,
                'Status' => $statusText,
                'Transaction Type' => $transactionType,
                'Total' => $payableBalance,
                'Balance' => $balance,
            ];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Vendor Name' => 'Total',
                'Date' => '',
                'Transaction' => '',
                'Status' => '',
                'Transaction Type' => '',
                'Total' => $totalAmount,
                'Balance' => $total,
            ];
        }
        
        return $formattedData;
    }

    private function formatPayableDetails($data)
    {
        $formattedData = [];
        $total = 0;
        $totalQuantity = 0;
        
        foreach($data as $detail) {
            if ($detail['bill']) {
                $receivableBalance = $detail['price'];
                $quantity = $detail['quantity'];
                $itemTotal = $receivableBalance * $detail['quantity'];
                $transactionType = $detail['type']; // 'Bill' or 'Expense'
                if ($detail['type'] == 'Bill') {
                    $transaction = \Auth::user()->billNumberFormat($detail['bill']);
                } else {
                    $transaction = \Auth::user()->expenseNumberFormat($detail['bill']);
                }
            } else {
                $receivableBalance = -$detail['price'];
                $quantity = 0;
                $itemTotal = -$detail['price'];
                $transactionType = 'Debit Note';
                $transaction = 'Debit Note';
            }
            
            $total += $itemTotal;
            $totalQuantity += $quantity;
            
            $statusText = $this->getStatusText($detail['status']);
            
            $formattedData[] = [
                'Vendor Name' => $detail['name'],
                'Date' => $detail['bill_date'],
                'Transaction' => $transaction,
                'Status' => $statusText,
                'Transaction Type' => $transactionType,
                'Item Name' => $detail['product_name'],
                'Quantity Ordered' => $quantity,
                'Item Price' => $receivableBalance,
                'Total' => $itemTotal,
            ];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Vendor Name' => 'Total',
                'Date' => '',
                'Transaction' => '',
                'Status' => '',
                'Transaction Type' => '',
                'Item Name' => '',
                'Quantity Ordered' => $totalQuantity,
                'Item Price' => '',
                'Total' => $total,
            ];
        }
        
        return $formattedData;
    }

    private function getStatusText($status)
    {
        switch($status) {
            case 0: return 'Draft';
            case 1: return 'Sent';
            case 2: return 'Unpaid';
            case 3: return 'Partially Paid';
            case 4: return 'Paid';
            default: return '-';
        }
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        switch($this->tabType) {
            case '#vendor_balance':
                return [
                    'A' => 30, 'B' => 20, 'C' => 20, 'D' => 20,
                ];
            case '#payable_summary':
                return [
                    'A' => 25, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 18, 'F' => 15, 'G' => 15,
                ];
            case '#payable_details':
                return [
                    'A' => 25, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 18, 'F' => 20, 'G' => 15, 'H' => 15, 'I' => 15,
                ];
            default:
                return ['A' => 30, 'B' => 20, 'C' => 20, 'D' => 20];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $headerRange = $this->getHeaderRange();
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
    }

    private function getHeaderRange()
    {
        switch($this->tabType) {
            case '#payable_summary': return 'A6:G6';
            case '#payable_details': return 'A6:I6';
            default: return 'A6:D6';
        }
    }

    public function array(): array
    {
        return $this->data;
    }
    
    public function headings(): array
    {
        switch($this->tabType) {
            case '#vendor_balance':
                return ["Vendor Name", "Billed Amount", "Available Debit", "Closing Balance"];
            case '#payable_summary':
                return ["Vendor Name", "Date", "Transaction", "Status", "Transaction Type", "Total", "Balance"];
            case '#payable_details':
                return ["Vendor Name", "Date", "Transaction", "Status", "Transaction Type", "Item Name", "Quantity Ordered", "Item Price", "Total"];
            default:
                return ["Vendor Name", "Billed Amount", "Available Debit", "Closing Balance"];
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headerRange = $this->getHeaderRange();
                $mergeRange = str_replace('6', '2', str_replace('A6:', 'A2:', $headerRange));
                $event->sheet->getDelegate()->mergeCells($mergeRange);
                $event->sheet->getDelegate()->mergeCells(str_replace('2', '3', $mergeRange));
                $event->sheet->getDelegate()->mergeCells(str_replace('2', '4', $mergeRange));

                $reportTitle = $this->getReportTitle();
                $event->sheet->getDelegate()->setCellValue('A2', $reportTitle . ' - ' . $this->companyName)->getStyle('A2')->getFont()->setBold(true);
                $event->sheet->getDelegate()->setCellValue('A3', 'Print Out Date : ' . date('Y-m-d H:i'));
                $event->sheet->getDelegate()->setCellValue('A4', ($this->startDate !== '1900-01-01' ? 'Period: ' . date('d M Y', strtotime($this->startDate)) . ' to ' : 'As of: ') . date('d F Y', strtotime($this->endDate)));

                $startRow = 2;
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A' . $startRow . ':Z' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Bold total rows
                $data = $this->data;
                foreach ($data as $index => $row) {
                    $firstColumn = array_values($row)[0] ?? '';
                    if ($firstColumn == 'Total') {
                        $rowIndex = $index + 7;
                        $event->sheet->getStyle('A' . $rowIndex . ':' . $this->getLastColumn() . $rowIndex)
                            ->applyFromArray(['font' => ['bold' => true]]);
                    }
                }
            },
        ];
    }

    private function getReportTitle()
    {
        switch($this->tabType) {
            case '#vendor_balance': return 'Payable Vendor Balance Report';
            case '#payable_summary': return 'Payable Summary Report';
            case '#payable_details': return 'Payable Details Report';
            default: return 'Payable Report';
        }
    }

    private function getLastColumn()
    {
        switch($this->tabType) {
            case '#payable_summary': return 'G';
            case '#payable_details': return 'I';
            default: return 'D';
        }
    }
}