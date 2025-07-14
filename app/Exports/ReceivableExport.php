<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Revenue;
use App\Models\BillProduct;
use App\Models\Customer;
use App\Models\BillAccount;
use App\Models\InvoiceProduct;
use App\Models\JournalItem;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ReceivableExport implements FromArray, WithHeadings, WithStyles, WithCustomStartCell, WithColumnWidths, WithEvents
{
    protected $tabType;
    
    public function __construct($data, $startDate, $endDate, $companyName, $tabType = '#customer_balance')
    {
        $this->tabType = $tabType;
        $formattedData = [];
        
        switch($tabType) {
            case '#customer_balance':
                $formattedData = $this->formatCustomerBalance($data);
                break;
            case '#receivable_summary':
                $formattedData = $this->formatReceivableSummary($data);
                break;
            case '#receivable_details':
                $formattedData = $this->formatReceivableDetails($data);
                break;
            case '#aging_summary':
                $formattedData = $this->formatAgingSummary($data);
                break;
            case '#aging_details':
                $formattedData = $this->formatAgingDetails($data);
                break;
            default:
                $formattedData = $this->formatCustomerBalance($data);
                break;
        }
        
        $this->data = $formattedData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
    }

    private function formatCustomerBalance($data)
    {
        $formattedData = [];
        $total = 0;
        
        foreach($data as $customer) {
            $customerBalance = $customer['price'] + $customer['total_tax'] - $customer['pay_price'];
            $balance = $customerBalance - $customer['credit_price'];
            $total += $balance;
            
            $formattedData[] = [
                'Customer Name' => $customer['name'],
                'Invoice Balance' => $customerBalance,
                'Available Credits' => !empty($customer['credit_price']) ? $customer['credit_price'] : 0,
                'Balance' => $balance,
            ];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Customer Name' => 'Total',
                'Invoice Balance' => '',
                'Available Credits' => '',
                'Balance' => $total,
            ];
        }
        
        return $formattedData;
    }

    private function formatReceivableSummary($data)
    {
        $formattedData = [];
        $total = 0;
        $totalAmount = 0;
        
        foreach($data as $summary) {
            if ($summary['invoice']) {
                $receivableBalance = $summary['price'] + $summary['total_tax'];
                $transactionType = 'Invoice';
                $transaction = \Auth::user()->invoiceNumberFormat($summary['invoice']);
            } else {
                $receivableBalance = -$summary['price'];
                $transactionType = 'Credit Note';
                $transaction = 'Credit Note';
            }
            
            $pay_price = $summary['pay_price'] != null ? $summary['pay_price'] : 0;
            $balance = $receivableBalance - $pay_price;
            $total += $balance;
            $totalAmount += $receivableBalance;
            
            $statusText = $this->getStatusText($summary['status']);
            
            $formattedData[] = [
                'Customer Name' => $summary['name'],
                'Date' => $summary['issue_date'],
                'Transaction' => $transaction,
                'Status' => $statusText,
                'Transaction Type' => $transactionType,
                'Total' => $receivableBalance,
                'Balance' => $balance,
            ];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Customer Name' => 'Total',
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

    private function formatReceivableDetails($data)
    {
        $formattedData = [];
        $total = 0;
        $totalQuantity = 0;
        
        foreach($data as $detail) {
            if ($detail['invoice']) {
                $receivableBalance = $detail['price'];
                $quantity = $detail['quantity'];
                $itemTotal = $receivableBalance * $detail['quantity'];
                $transactionType = 'Invoice';
                $transaction = \Auth::user()->invoiceNumberFormat($detail['invoice']);
            } else {
                $receivableBalance = -$detail['price'];
                $quantity = 0;
                $itemTotal = -$detail['price'];
                $transactionType = 'Credit Note';
                $transaction = 'Credit Note';
            }
            
            $total += $itemTotal;
            $totalQuantity += $quantity;
            
            $statusText = $this->getStatusText($detail['status']);
            
            $formattedData[] = [
                'Customer Name' => $detail['name'],
                'Date' => $detail['issue_date'],
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
                'Customer Name' => 'Total',
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

    private function formatAgingSummary($data)
    {
        $formattedData = [];
        $currentTotal = 0;
        $days15 = 0;
        $days30 = 0;
        $days45 = 0;
        $daysMore45 = 0;
        $total = 0;
        
        foreach($data as $key => $aging) {
            $formattedData[] = [
                'Customer Name' => $key,
                'Current' => $aging['current'],
                '1-15 DAYS' => $aging['1_15_days'],
                '16-30 DAYS' => $aging['16_30_days'],
                '31-45 DAYS' => $aging['31_45_days'],
                '> 45 DAYS' => $aging['greater_than_45_days'],
                'Total' => $aging['total_due'],
            ];
            
            $currentTotal += $aging['current'];
            $days15 += $aging['1_15_days'];
            $days30 += $aging['16_30_days'];
            $days45 += $aging['31_45_days'];
            $daysMore45 += $aging['greater_than_45_days'];
            $total += $aging['total_due'];
        }
        
        if($formattedData != []) {
            $formattedData[] = [
                'Customer Name' => 'Total',
                'Current' => $currentTotal,
                '1-15 DAYS' => $days15,
                '16-30 DAYS' => $days30,
                '31-45 DAYS' => $days45,
                '> 45 DAYS' => $daysMore45,
                'Total' => $total,
            ];
        }
        
        return $formattedData;
    }

    private function formatAgingDetails($data)
    {
        // This would handle the complex aging details format
        // with sections for different day ranges
        $formattedData = [];
        
        // Add sections for each aging range
        $sections = [
            'moreThan45' => '> 45 Days',
            'days31to45' => '31 to 45 Days',
            'days16to30' => '16 to 30 Days',
            'days1to15' => '1 to 15 Days',
            'currents' => 'Current'
        ];
        
        foreach($sections as $key => $sectionTitle) {
            if(isset($data[$key]) && !empty($data[$key])) {
                // Add section header
                $formattedData[] = [
                    'Section' => $sectionTitle,
                    'Date' => '',
                    'Transaction' => '',
                    'Type' => '',
                    'Status' => '',
                    'Customer Name' => '',
                    'Age' => '',
                    'Amount' => '',
                    'Balance Due' => '',
                ];
                
                foreach($data[$key] as $item) {
                    $statusText = $this->getStatusText($item['status']);
                    $age = $key === 'currents' ? '-' : $item['age'] . ' Days';
                    
                    $formattedData[] = [
                        'Section' => '',
                        'Date' => $item['due_date'],
                        'Transaction' => \Auth::user()->invoiceNumberFormat($item['invoice_id']),
                        'Type' => 'Invoice',
                        'Status' => $statusText,
                        'Customer Name' => $item['name'],
                        'Age' => $age,
                        'Amount' => $item['total_price'],
                        'Balance Due' => $item['balance_due'],
                    ];
                }
            }
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
            case '#customer_balance':
                return [
                    'A' => 30, 'B' => 20, 'C' => 20, 'D' => 20,
                ];
            case '#receivable_summary':
                return [
                    'A' => 25, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 18, 'F' => 15, 'G' => 15,
                ];
            case '#receivable_details':
                return [
                    'A' => 25, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 18, 'F' => 20, 'G' => 15, 'H' => 15, 'I' => 15,
                ];
            case '#aging_summary':
                return [
                    'A' => 25, 'B' => 15, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15, 'G' => 15,
                ];
            case '#aging_details':
                return [
                    'A' => 15, 'B' => 15, 'C' => 20, 'D' => 15, 'E' => 15, 'F' => 25, 'G' => 15, 'H' => 15, 'I' => 15,
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
            case '#receivable_summary': return 'A6:G6';
            case '#receivable_details': return 'A6:I6';
            case '#aging_summary': return 'A6:G6';
            case '#aging_details': return 'A6:I6';
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
            case '#customer_balance':
                return ["Customer Name", "Invoice Balance", "Available Credits", "Balance"];
            case '#receivable_summary':
                return ["Customer Name", "Date", "Transaction", "Status", "Transaction Type", "Total", "Balance"];
            case '#receivable_details':
                return ["Customer Name", "Date", "Transaction", "Status", "Transaction Type", "Item Name", "Quantity Ordered", "Item Price", "Total"];
            case '#aging_summary':
                return ["Customer Name", "Current", "1-15 DAYS", "16-30 DAYS", "31-45 DAYS", "> 45 DAYS", "Total"];
            case '#aging_details':
                return ["Section", "Date", "Transaction", "Type", "Status", "Customer Name", "Age", "Amount", "Balance Due"];
            default:
                return ["Customer Name", "Invoice Balance", "Available Credits", "Balance"];
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
            case '#customer_balance': return 'Receivable Customer Balance Report';
            case '#receivable_summary': return 'Receivable Summary Report';
            case '#receivable_details': return 'Receivable Details Report';
            case '#aging_summary': return 'Aging Summary Report';
            case '#aging_details': return 'Aging Details Report';
            default: return 'Receivable Report';
        }
    }

    private function getLastColumn()
    {
        switch($this->tabType) {
            case '#receivable_summary': return 'G';
            case '#receivable_details': return 'I';
            case '#aging_summary': return 'G';
            case '#aging_details': return 'I';
            default: return 'D';
        }
    }
}