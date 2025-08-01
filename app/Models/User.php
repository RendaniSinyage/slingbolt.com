<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use App\Services\CompanyClonerService;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles ,Impersonate;

    protected $appends = ['profile'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'storage_limit',
        'avatar',
        'lang',
        'mode',
        'delete_status',
        'plan',
        'email_verified_at',
        'plan_expire_date',
  	    'trial_plan',
    	'trial_expire_date',
        'requested_plan',
        'is_active',
        'referral_code',
        'used_referral_code',
        'commission_amount',
        'paid_amount',
        'is_enable_login',
        'last_login_at',
	    'last_login_ip',
   	    'registration_ip',
   	    'user_agent',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $settings;

    public function getProfileAttribute()
    {

        if (!empty($this->avatar) && \Storage::exists($this->avatar)) {
            return $this->attributes['avatar'] = asset(\Storage::url($this->avatar));
        } else {
            return $this->attributes['avatar'] = asset(\Storage::url('avatar.png'));
        }
    }

    public function authId()
    {
        return $this->id;
    }

    public function creatorId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerId()
    {
        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }

    public function ownerDetails()
    {

        if ($this->type == 'company' || $this->type == 'super admin') {
            return User::where('id', $this->id)->first();
        } else {
            return User::where('id', $this->created_by)->first();
        }
    }

    public function currentLanguage()
    {
        return $this->lang;
    }

    public function priceFormat($price)
    {
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));
        $float_number = Utility::getValByName('float_number') == 'dot' ? '.' : ',';

        if ($length > 3) {
            $decimal_separator = Utility::getValByName('decimal_separator') == 'dot' ? ',' : ',';
            $thousand_separator = Utility::getValByName('thousand_separator') == 'dot' ? '.' : ',';
        } else {
            $decimal_separator = Utility::getValByName('decimal_separator') == 'dot' ? '.' : ',';
            $thousand_separator = Utility::getValByName('thousand_separator') == 'dot' ? '.' : ',';
        }

        $currency = Utility::getValByName('currency_symbol') == 'withcurrencysymbol' ? Utility::getValByName('site_currency_symbol') : Utility::getValByName('site_currency');
        $settings = Utility::settings();
        $decimal_number = Utility::getValByName('decimal_number') ? Utility::getValByName('decimal_number') : 0;
        $currency_space = Utility::getValByName('currency_space');
        $price = number_format($price, $decimal_number, $decimal_separator, $thousand_separator);

        if ($float_number == 'dot') {
            $price = preg_replace('/' . preg_quote($thousand_separator, '/') . '([^' . preg_quote($thousand_separator, '/') . ']*)$/', $float_number . '$1', $price);
        } else {
            $price = preg_replace('/' . preg_quote($decimal_separator, '/') . '([^' . preg_quote($decimal_separator, '/') . ']*)$/', $float_number . '$1', $price);
        }

        return (($settings['site_currency_symbol_position'] == "pre") ? $currency : '') . ($currency_space == 'withspace' ? ' ' : '') . $price . ($currency_space == 'withspace' ? ' ' : '') . (($settings['site_currency_symbol_position'] == "post") ? $currency : '');
    }


    public function currencySymbol()
    {
        $settings = Utility::settings();

        return $settings['site_currency_symbol'];
    }

    public function dateFormat($date)
    {
        $settings = Utility::settings();

        return date($settings['site_date_format'], strtotime($date));
    }

    public function timeFormat($time)
    {
        $settings = Utility::settings();

        return date($settings['site_time_format'], strtotime($time));
    }
    public function DateTimeFormat($date)
    {
        $settings = Utility::settings();

        $date_formate = !empty($settings['site_date_format']) ? $settings['site_date_format'] : 'd-m-y';
        $time_formate = !empty($settings['site_time_format']) ? $settings['site_time_format'] : 'H:i';

        return date($date_formate . ' ' . $time_formate, strtotime($date));
    }
    public function purchaseNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["purchase_prefix"] . sprintf("%05d", $number);
    }
    public static function quotationNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["quotation_prefix"] . sprintf("%05d", $number);
    }
    public function posNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["pos_prefix"] . sprintf("%05d", $number);
    }

    public function invoiceNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public function proposalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public function contractNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["contract_prefix"] . sprintf("%05d", $number);
    }

    public function billNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }
    public function expenseNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["expense_prefix"] . sprintf("%05d", $number);
    }

    public function journalNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["journal_prefix"] . sprintf("%05d", $number);
    }

    public function getPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan');
    }

    public function assignPlan($planID, $company_id = 0)
    {
        $plan = Plan::find($planID);
        if ($plan) {
            $this->plan = $plan->id;
            if($this->trial_expire_date != null);
            {
                $this->trial_expire_date = null;
            }

            if ($plan->duration == 'month') {
                $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($plan->duration == 'year') {
                $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $this->plan_expire_date = null;
            }
            $this->save();

            if ($company_id != 0) {
                $user_id = $company_id;
            } else {
                $user_id = \Auth::user()->creatorId();
            }


            $users = User::where('created_by', '=', $user_id)->where('type', '!=', 'super admin')->where('type', '!=', 'company')->where('type', '!=', 'client')->get();
            $clients = User::where('created_by', '=', $user_id)->where('type', 'client')->get();
            $customers = Customer::where('created_by', '=', $user_id)->get();
            $venders = Vender::where('created_by', '=', $user_id)->get();

            if ($plan->max_users == -1) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $plan->max_users) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }

            if ($plan->max_clients == -1) {
                foreach ($clients as $client) {
                    $client->is_active = 1;
                    $client->save();
                }
            } else {
                $clientCount = 0;
                foreach ($clients as $client) {
                    $clientCount++;
                    if ($clientCount <= $plan->max_clients) {
                        $client->is_active = 1;
                        $client->save();
                    } else {
                        $client->is_active = 0;
                        $client->save();
                    }
                }
            }

            if ($plan->max_customers == -1) {
                foreach ($customers as $customer) {
                    $customer->is_active = 1;
                    $customer->save();
                }
            } else {
                $customerCount = 0;
                foreach ($customers as $customer) {
                    $customerCount++;
                    if ($customerCount <= $plan->max_customers) {
                        $customer->is_active = 1;
                        $customer->save();
                    } else {
                        $customer->is_active = 0;
                        $customer->save();
                    }
                }
            }

            if ($plan->max_venders == -1) {
                foreach ($venders as $vender) {
                    $vender->is_active = 1;
                    $vender->save();
                }
            } else {
                $venderCount = 0;
                foreach ($venders as $vender) {
                    $venderCount++;
                    if ($venderCount <= $plan->max_venders) {
                        $vender->is_active = 1;
                        $vender->save();
                    } else {
                        $vender->is_active = 0;
                        $vender->save();
                    }
                }
            }

            return ['is_success' => true];
        } else {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }

    public function customerNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["customer_prefix"] . sprintf("%05d", $number);
    }

    public function venderNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["vender_prefix"] . sprintf("%05d", $number);
    }

    public function countUsers()
    {
        return User::where('type', '!=', 'super admin')->where('type', '!=', 'company')->where('type', '!=', 'client')->where('created_by', '=', $this->creatorId())->count();
    }

    public function countCompany()
    {
        return User::where('type', '=', 'company')->where('created_by', '=', $this->creatorId())->count();
    }

    public function countOrder()
    {
        return Order::count();
    }

    public function countplan()
    {
        return Plan::count();
    }

    public function countPaidCompany()
    {
        return User::where('type', '=', 'company')->whereNotIn(
            'plan', [
                0,
                1,
            ]
        )->where('created_by', '=', \Auth::user()->id)->count();
    }

    public function countCustomers()
    {
        return Customer::where('created_by', '=', $this->creatorId())->count();
    }

    public function countVenders()
    {
        return Vender::where('created_by', '=', $this->creatorId())->count();
    }

    public function countInvoices()
    {
        return Invoice::where('created_by', '=', $this->creatorId())->count();
    }

    public function countBills()
    {
        return Bill::where('created_by', '=', $this->creatorId())->count();
    }

    public function todayIncome()
    {
        $revenue = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('Date(date) = CURDATE()')->where('created_by', \Auth::user()->creatorId())->sum('amount');
        $invoiceTotal = self::getInvoiceProductsData((date('y-m-d')));

        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceTotal) ? ($invoiceTotal) : 0);

        return $totalIncome;
    }

    public function todayExpense()
    {
        $payment = Payment::where('created_by', '=', $this->creatorId())->where('created_by', \Auth::user()->creatorId())->whereRaw('Date(date) = CURDATE()')->sum('amount');

        $billTotal = self::getBillProductsData(date('y-m-d'));

        $totalExpense = (!empty($payment) ? $payment : 0) + (!empty($billTotal) ? ($billTotal) : 0);

        return $totalExpense;
    }

    public function incomeCurrentMonth()
    {
        $currentMonth = date('m');
        $revenue = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');
        $invoiceTotal = self::getInvoiceProductsData('', $currentMonth);

        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceTotal) ? ($invoiceTotal) : 0);

        return $totalIncome;

    }
    public function incomecat()
    {

        $currentMonth = date('m');
        $revenue = Revenue::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');

        $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('product_service_categories', 'revenues.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 1);

        $invoices = Invoice::select('*')->where('created_by', \Auth::user()->creatorId())->whereRAW('MONTH(send_date) = ?', [$currentMonth])->get();

        $invoiceArray = array();
        foreach ($invoices as $invoice) {
            $invoiceArray[] = $invoice->getTotal();
        }
        $totalIncome = (!empty($revenue) ? $revenue : 0) + (!empty($invoiceArray) ? array_sum($invoiceArray) : 0);

        return $totalIncome;
    }

    public function expenseCurrentMonth()
    {
        $currentMonth = date('m');

        $payment = Payment::where('created_by', '=', $this->creatorId())->whereRaw('MONTH(date) = ?', [$currentMonth])->sum('amount');
        $billTotal = self::getBillProductsData('', $currentMonth);



        $totalExpense = (!empty($payment) ? $payment : 0) + (!empty($billTotal) ? ($billTotal) : 0);

        return $totalExpense;
    }


    public static function getInvoiceProductsData($date = '', $month = '')
    {
        if ($month != '' && $date != '') {
            $InvoiceProducts = \DB::table('invoice_products')
                ->select('invoice_products.invoice_id as invoice',
                    \DB::raw('SUM(quantity) as total_quantity'),
                    \DB::raw('SUM(discount) as total_discount'),
                    \DB::raw('SUM(price * quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
                    LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
                    WHERE invoice_products.invoice_id = invoices.id) as tax_values')
                ->leftJoin('invoices', 'invoice_products.invoice_id', 'invoices.id')
                ->where(\DB::raw('YEAR(invoices.send_date)'), '=', $date)
                ->where(\DB::raw('MONTH(invoices.send_date)'), '=', $month)
                ->where('invoices.created_by', \Auth::user()->creatorId())
                ->groupBy('invoice')
                ->get()
                ->keyBy('invoice');
        } elseif ($date != '') {
            $InvoiceProducts = \DB::table('invoice_products')
                ->select('invoice_products.invoice_id as invoice',
                    \DB::raw('SUM(quantity) as total_quantity'),
                    \DB::raw('SUM(discount) as total_discount'),
                    \DB::raw('SUM(price * quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
                    LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
                    WHERE invoice_products.invoice_id = invoices.id) as tax_values')
                ->leftJoin('invoices', 'invoice_products.invoice_id', 'invoices.id')
                ->where(\DB::raw('(invoices.send_date)'), '=', $date)
                ->where('invoices.created_by', \Auth::user()->creatorId())
                ->groupBy('invoice')
                ->get()
                ->keyBy('invoice');
        } elseif ($month != '') {
            $InvoiceProducts = \DB::table('invoice_products')
                ->select('invoice_products.invoice_id as invoice',
                    \DB::raw('SUM(quantity) as total_quantity'),
                    \DB::raw('SUM(discount) as total_discount'),
                    \DB::raw('SUM(price * quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
                    LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
                    WHERE invoice_products.invoice_id = invoices.id) as tax_values')
                ->leftJoin('invoices', 'invoice_products.invoice_id', 'invoices.id')
                ->where(\DB::raw('MONTH(invoices.send_date)'), '=', $month)
                ->where('invoices.created_by', \Auth::user()->creatorId())
                ->groupBy('invoice')
                ->get()
                ->keyBy('invoice');
        }

        $InvoiceProducts->map(function ($invoice) {
            $invoice->total = $invoice->sub_total + $invoice->tax_values - $invoice->total_discount;
            return $invoice;
        });

        $total = 0;
        foreach ($InvoiceProducts as $invoice) {
            $total += ($invoice->total);
        }

        return $total;
    }

    public static function getBillProductsData($date = '', $month = '')
    {
        if ($month != '' && $date != '') {
            $BillProducts = \DB::table('bill_products')
                ->select('bill_products.bill_id as bill',
                    \DB::raw('SUM(bill_products.quantity) as total_quantity'),
                    \DB::raw('SUM(bill_products.discount) as total_discount'),
                    \DB::raw('SUM(bill_products.price * bill_products.quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM(bill_accounts.price) FROM bill_accounts
                    WHERE bill_accounts.ref_id = bills.id) as acc_price')
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
                                        LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
                                        WHERE bill_products.bill_id = bills.id) as tax_values')
                ->leftJoin('bills', 'bill_products.bill_id', 'bills.id')
                ->where(\DB::raw('YEAR(bills.send_date)'), '=', $date)
                ->where(\DB::raw('MONTH(bills.send_date)'), '=', $month)
                ->where('bills.created_by', \Auth::user()->creatorId())
                ->groupBy('bill')
                ->get()
                ->keyBy('bill');
        } elseif ($date != '') {
            $BillProducts = \DB::table('bill_products')
                ->select('bill_products.bill_id as bill',
                    \DB::raw('SUM(quantity) as total_quantity'),
                    \DB::raw('SUM(discount) as total_discount'),
                    \DB::raw('SUM(bill_products.price * bill_products.quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM(bill_accounts.price) FROM bill_accounts
                    WHERE bill_accounts.ref_id = bills.id) as acc_price')
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
                                LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
                                WHERE bill_products.bill_id = bills.id) as tax_values')
                ->leftJoin('bills', 'bill_products.bill_id', 'bills.id')
                ->where(\DB::raw('(bills.send_date)'), '=', $date)
                ->where('bills.created_by', \Auth::user()->creatorId())
                ->groupBy('bill')
                ->get()
                ->keyBy('bill');
        } elseif ($month != '') {
            $BillProducts = \DB::table('bill_products')
                ->select('bill_products.bill_id as bill',
                    \DB::raw('SUM(quantity) as total_quantity'),
                    \DB::raw('SUM(discount) as total_discount'),
                    \DB::raw('SUM(bill_products.price * bill_products.quantity)  as sub_total'))
                ->selectRaw('(SELECT SUM(bill_accounts.price) FROM bill_accounts
                    WHERE bill_accounts.ref_id = bills.id) as acc_price')
                ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
                                LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
                                WHERE bill_products.bill_id = bills.id) as tax_values')
                ->leftJoin('bills', 'bill_products.bill_id', 'bills.id')
                ->where(\DB::raw('MONTH(bills.send_date)'), '=', $month)
                ->where('bills.created_by', \Auth::user()->creatorId())
                ->groupBy('bill')
                ->get()
                ->keyBy('bill');
        }
        $BillProducts->map(function ($bill) {
            $bill->total = $bill->sub_total + $bill->acc_price + $bill->tax_values - $bill->total_discount;
            return $bill;
        });
        $total = 0;
        foreach ($BillProducts as $bill) {
            $total += ($bill->total);
        }

        return $total;
    }

    public function getincExpBarChartData()
    {
        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');
        $dataArr['month'] = $month;

        $user_id = \Auth::user()->creatorId();
        for ($i = 1; $i <= 12; $i++) {
            $monthlyIncome = Revenue::selectRaw('sum(amount) amount')->where('created_by', '=', $user_id)->whereRaw('year(`date`) = ?', array(date('Y')))->whereRaw('month(`date`) = ?', $i)->first();
            $invoiceTotal = self::getInvoiceProductsData((date('Y')), $i);

            $totalIncome = (!empty($monthlyIncome) ? $monthlyIncome->amount : 0) + (!empty($invoiceTotal) ? ($invoiceTotal) : 0);

            $incomeArr[] = !empty($totalIncome) ? str_replace(",", "", number_format($totalIncome, 2)) : 0;

            $monthlyExpense = Payment::selectRaw('sum(amount) amount')->where('created_by', '=', $this->creatorId())->whereRaw('year(`date`) = ?', array(date('Y')))->whereRaw('month(`date`) = ?', $i)->first();
            $billTotal = self::getBillProductsData((date('Y')), $i);

            $totalExpense = (!empty($monthlyExpense) ? $monthlyExpense->amount : 0) + (!empty($billTotal) ? ($billTotal) : 0);

            $expenseArr[] = !empty($totalExpense) ? str_replace(",", "", number_format($totalExpense, 2)) : 0;
        }

        $dataArr['income'] = $incomeArr;
        $dataArr['expense'] = $expenseArr;

        return $dataArr;

    }

    public function getIncExpLineChartDate()
    {
        $usr = \Auth::user();
        $m = date("m");
        $de = date("d");
        $y = date("Y");
        $format = 'Y-m-d';
        $arrDate = [];
        $arrDateFormat = [];

        for ($i = 0; $i <= 15 - 1; $i++) {
            $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));

            $arrDay[] = date('D', mktime(0, 0, 0, $m, ($de - $i), $y));
            $arrDate[] = $date;
            $arrDateFormat[] = date("d-M", strtotime($date));
        }
        $dataArr['day'] = $arrDateFormat;
        for ($i = 0; $i < count($arrDate); $i++) {
            $dayIncome = Revenue::selectRaw('sum(amount) amount')->where('created_by', \Auth::user()->creatorId())->whereRaw('date = ?', $arrDate[$i])->first();

            $invoiceTotal = self::getInvoiceProductsData($arrDate[$i]);

            $incomeAmount = (!empty($dayIncome->amount) ? $dayIncome->amount : 0) + (!empty($invoiceTotal) ? ($invoiceTotal) : 0);
            $incomeArr[] = str_replace(",", "", number_format($incomeAmount, 2));

            $dayExpense = Payment::selectRaw('sum(amount) amount')->where('created_by', \Auth::user()->creatorId())->whereRaw('date = ?', $arrDate[$i])->first();

            $billTotal = self::getBillProductsData($arrDate[$i]);

            $expenseAmount = (!empty($dayExpense->amount) ? $dayExpense->amount : 0) + (!empty($billTotal) ? ($billTotal) : 0);
            $expenseArr[] = str_replace(",", "", number_format($expenseAmount, 2));
        }

        $dataArr['income'] = $incomeArr;
        $dataArr['expense'] = $expenseArr;

        return $dataArr;
    }
    public function totalCompanyUser($id)
    {
        return User::where('created_by', '=', $id)->count();
    }

    public function totalCompanyCustomer($id)
    {
        return Customer::where('created_by', '=', $id)->count();
    }

    public function totalCompanyVender($id)
    {
        return Vender::where('created_by', '=', $id)->count();
    }

    public function planPrice()
    {
        $user = \Auth::user();
        if ($user->type == 'super admin') {
            $userId = $user->id;
        } else {
            $userId = $user->created_by;
        }

        return DB::table('settings')->where('created_by', '=', $userId)->get()->pluck('value', 'name');

    }

    public function currentPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan');
    }

    public function invoicesData($start, $current)
    {
        $InvoiceProducts = Invoice::select('invoices.invoice_id as invoice')
            ->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('(SELECT SUM(credit_notes.amount) FROM credit_notes WHERE credit_notes.invoice = invoices.id) as credit_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->where('issue_date', '>=', $start)->where('issue_date', '<=', $current)
            ->where('invoices.created_by', \Auth::user()->creatorId())
            ->groupBy('invoice')
            ->get()
            ->keyBy('invoice')
            ->toArray();


        $invoicepayment = Invoice::select('invoices.invoice_id as invoice')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->where('issue_date', '>=', $start)->where('issue_date', '<=', $current)
            ->where('invoices.created_by', \Auth::user()->creatorId())
            ->groupBy('invoice')
            ->get()
            ->keyBy('invoice')
            ->toArray();

        $mergedArray = [];

        foreach ($InvoiceProducts as $key => $value) {
            if (isset($invoicepayment[$key])) {
                $mergedArray[$key] = array_merge($value, $invoicepayment[$key]);
            }
        }

        $invoiceTotal = 0;
        $invoicePaid = 0;
        $invoiceDue = 0;
        $invoiceData = [];

        foreach ($mergedArray as $invoice) {
            $invoiceTotal += $invoice['price'] + $invoice['total_tax'];
            $invoicePaid += $invoice['pay_price'] + $invoice['credit_price'];
            $invoiceDue += ($invoice['price'] + $invoice['total_tax']) - $invoice['credit_price'] - $invoice['pay_price'];
        }

        $invoiceData = [
            "invoiceTotal" => $invoiceTotal,
            "invoicePaid" => $invoicePaid,
            'invoiceDue' => $invoiceDue,
        ];

        return $invoiceData;
    }

    public function billsData($start, $current)
    {
        $billProducts = Bill::select('bills.bill_id as bill')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('(SELECT SUM(debit_notes.amount) FROM debit_notes
             WHERE debit_notes.bill = bills.id) as debit_price')
            ->selectRaw('(SELECT SUM(bill_accounts.price) FROM bill_accounts
             WHERE bill_accounts.ref_id = bills.id) as acc_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
             WHERE bill_products.bill_id = bills.id) as total_tax')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id')
            ->where('bill_date', '>=', $start)->where('bill_date', '<=', $current)
            ->where('bills.created_by', \Auth::user()->creatorId())
            ->where('bills.type', 'Bill')
            ->groupBy('bill')
            ->get()
            ->keyBy('bill')
            ->toArray();


        $billPayment = Bill::select('bills.bill_id as bill')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->where('bill_date', '>=', $start)->where('bill_date', '<=', $current)
            ->where('bills.created_by', \Auth::user()->creatorId())
            ->where('bills.type', 'Bill')
            ->groupBy('bill')
            ->get()
            ->keyBy('bill')
            ->toArray();

        $mergedArray = [];

        foreach ($billProducts as $key => $value) {
            if (isset($billPayment[$key])) {
                $mergedArray[$key] = array_merge($value, $billPayment[$key]);
            } else {
                $mergedArray[$key] = ($value);
            }
        }

        $billTotal = 0;
        $billPaid = 0;
        $billDue = 0;
        $billData = [];

        foreach ($mergedArray as $bill) {
            $billTotal += $bill['price'] + $bill['total_tax'] + $bill['acc_price'];
            $billPaid += $bill['pay_price'] + $bill['debit_price'];
            $billDue += ($bill['price'] + $bill['total_tax'] + $bill['acc_price']) - $bill['pay_price'] - $bill['debit_price'];
        }

        $billData = [
            "billTotal" => $billTotal,
            "billPaid" => $billPaid,
            'billDue' => $billDue,
        ];

        return $billData;
    }

    public function expenseData($start, $current)
    {
        $billProducts = Bill::select('bills.bill_id as bill')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('(SELECT SUM(debit_notes.amount) FROM debit_notes
             WHERE debit_notes.bill = bills.id) as debit_price')
            ->selectRaw('(SELECT SUM(bill_accounts.price) FROM bill_accounts
             WHERE bill_accounts.ref_id = bills.id) as acc_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
             WHERE bill_products.bill_id = bills.id) as total_tax')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id')
            ->where('bill_date', '>=', $start)->where('bill_date', '<=', $current)
            ->where('bills.created_by', \Auth::user()->creatorId())
            ->where('bills.type', 'Expense')
            ->groupBy('bill')
            ->get()
            ->keyBy('bill')
            ->toArray();


        $billPayment = Bill::select('bills.bill_id as bill')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->where('bill_date', '>=', $start)->where('bill_date', '<=', $current)
            ->where('bills.created_by', \Auth::user()->creatorId())
            ->where('bills.type', 'Expense')
            ->groupBy('bill')
            ->get()
            ->keyBy('bill')
            ->toArray();

        $mergedArray = [];

        foreach ($billProducts as $key => $value) {
            if (isset($billPayment[$key])) {
                $mergedArray[$key] = array_merge($value, $billPayment[$key]);
            } else {
                $mergedArray[$key] = ($value);
            }
        }

        $billTotal = 0;
        $billPaid = 0;
        $billDue = 0;
        $billData = [];

        foreach ($mergedArray as $bill) {
            $billTotal += $bill['price'] + $bill['total_tax'] + $bill['acc_price'];
            $billPaid += $bill['pay_price'] + $bill['debit_price'];
            $billDue += ($bill['price'] + $bill['total_tax'] + $bill['acc_price']) - $bill['pay_price'] - $bill['debit_price'];
        }

        $billData = [
            "billTotal" => $billTotal,
            "billPaid" => $billPaid,
            'billDue' => $billDue,
        ];

        return $billData;
    }
    public function weeklyInvoice()
    {
        $staticstart = date('Y-m-d', strtotime('last Week'));
        $currentDate = date('Y-m-d');
        $invoices = self::invoicesData($staticstart, $currentDate);

        $invoiceDetail['invoiceTotal'] = $invoices['invoiceTotal'];
        $invoiceDetail['invoicePaid'] = $invoices['invoicePaid'];
        $invoiceDetail['invoiceDue'] = $invoices['invoiceDue'];

        return $invoiceDetail;
    }

    public function monthlyInvoice()
    {
        $staticstart = date('Y-m-d', strtotime('last Month'));
        $currentDate = date('Y-m-d');
        $invoices = self::invoicesData($staticstart, $currentDate);

        $invoiceDetail['invoiceTotal'] = $invoices['invoiceTotal'];
        $invoiceDetail['invoicePaid'] = $invoices['invoicePaid'];
        $invoiceDetail['invoiceDue'] = $invoices['invoiceDue'];

        return $invoiceDetail;
    }

    public function weeklyBill()
    {
        $staticstart = date('Y-m-d', strtotime('last Week'));
        $currentDate = date('Y-m-d');
        $bills = self::billsData($staticstart, $currentDate);
        $expense = self::expenseData($staticstart, $currentDate);

        $billDetail['billTotal'] = $bills['billTotal'] + $expense['billTotal'];
        $billDetail['billPaid'] = $bills['billPaid'] + $expense['billPaid'];
        $billDetail['billDue'] = $bills['billDue'] + $expense['billDue'];
        return $billDetail;
    }

    public function monthlyBill()
    {
        $staticstart = date('Y-m-d', strtotime('last Month'));
        $currentDate = date('Y-m-d');
        $bills = self::billsData($staticstart, $currentDate);
        $expense = self::expenseData($staticstart, $currentDate);

        $billDetail['billTotal'] = $bills['billTotal'] + $expense['billTotal'];
        $billDetail['billPaid'] = $bills['billPaid'] + $expense['billPaid'];
        $billDetail['billDue'] = $bills['billDue'] + $expense['billDue'];
        return $billDetail;
    }

    public function clientEstimations()
    {
        return $this->hasMany('App\Models\Estimation', 'client_id', 'id');
    }

    public function clientContracts()
    {
        return $this->hasMany('App\Models\Contract', 'client_name', 'id');
    }

    public function deals()
    {
        return $this->belongsToMany('App\Models\Deal', 'user_deals', 'user_id', 'deal_id');
    }

    public function leads()
    {
        return $this->belongsToMany('App\Models\Lead', 'user_leads', 'user_id', 'lead_id');
    }

    public function clientDeals()
    {
        return $this->belongsToMany('App\Models\Deal', 'client_deals', 'client_id', 'deal_id');
    }

    public function getBranch($branch_id)
    {
        $branch = Branch::where('id', '=', $branch_id)->first();

        return $branch;
    }

    public function getDepartment($department_id)
    {
        $department = Department::where('id', '=', $department_id)->first();

        return $department;
    }

    public function getDesignation($designation_id)
    {
        $designation = Designation::where('id', '=', $designation_id)->first();

        return $designation;
    }

    public function getEmployee($employee)
    {
        $employee = Employee::where('id', '=', $employee)->first();

        return $employee;
    }

    public function getLeaveType($leave_type)
    {
        $leavetype = LeaveType::where('id', '=', $leave_type)->first();

        return $leavetype;
    }

    public function projects()
    {
        return $this->belongsToMany('App\Models\Project', 'project_users', 'user_id', 'project_id')->withTimestamps();
    }

    // check project is shared or not
    public function checkProject($project_id)
    {
        $user_projects = $this->projects->pluck('project_id')->toArray();
        if (array_key_exists($project_id, $user_projects)) {
            $projectstatus = $user_projects[$project_id] == 'owner' ? 'Owner' : 'Shared';
        }
        return 'Owner';
    }

    // Make new attribute for directly get image
    public function getImgImageAttribute()
    {
        $userDetail = Employee::where('user_id', $this->id)->first();
        if (!empty($userDetail)) {
            if (!empty($userDetail->avatar)) {
                return asset(\Storage::url('uploads/avatar/'.$userDetail->avatar));
            } else {
                return asset(\Storage::url('uploads/avatar/avatar.png'));
            }
        } else {
            return asset(\Storage::url('uploads/avatar/avatar.png'));
        }
    }

    // Get task users
    public function tasks()
    {
        if (\Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::find($this->id);
        }
        if ($user->type == 'company') {
            return ProjectTask::where('created_by', $user->creatorId())->get();
        } else {
            return ProjectTask::whereRaw("find_in_set('" . $this->id . "',assign_to)")->get();
        }
    }

    public function bugNumberFormat($number)
    {
        $settings = Utility::settings();

        return $settings["bug_prefix"] . sprintf("%05d", $number);
    }

    // Get User's Contact
    public function contacts()
    {
        return $this->hasMany('App\Models\UserContact', 'parent_id', 'id');
    }

    public function todo()
    {
        return $this->hasMany('App\Models\UserToDo', 'user_id', 'id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'id');
    }

    public function total_lead()
    {
        if (\Auth::user()->type == 'company') {
            return Lead::where('created_by', '=', $this->creatorId())->count();
        } elseif (\Auth::user()->type == 'client') {
            return Lead::where('client', '=', $this->authId())->count();
        } else {
            return Lead::where('owner', '=', $this->authId())->count();
        }
    }

    public function last_projectstage()
    {
        return TaskStage::where('created_by', '=', $this->creatorId())->orderBy('order', 'DESC')->first();
    }

    public function user_project()
    {
        if (\Auth::user()->type != 'client') {
            return $this->belongsToMany('App\Models\Project', 'project_users', 'user_id', 'project_id')->count();
        } else {
            return Project::where('client_id', '=', $this->authId())->count();
        }
    }

    public function created_total_project_task()
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.created_by', '=', $this->creatorId())->count();
        } elseif (\Auth::user()->type == 'client') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.client_id', '=', $this->authId())->count();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_users.id as up_id')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->where('project_users.user_id', '=', $this->authId())->count();
        }

    }

    public function project_complete_task($project_last_stage)
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.created_by', '=', $this->creatorId())->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        } elseif (\Auth::user()->type == 'client') {
            $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();

            return ProjectTask::whereIn('project_id', $user_projects)->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_users.id as up_id')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->where('project_users.user_id', '=', $this->authId())->where('project_tasks.stage_id', '=', $project_last_stage)->count();
        }
    }

    public function created_top_due_task()
    {
        if (\Auth::user()->type == 'company') {
            return ProjectTask::select('projects.*', 'project_tasks.id as task_id', 'project_tasks.name', 'project_tasks.end_date as task_due_date', 'project_tasks.assign_to', 'projectstages.name as stage_name')->join('projects', 'projects.id', '=', 'project_tasks.project_id')->join('projectstages', 'project_tasks.stage_id', '=', 'projectstages.id')->where('projects.created_by', '=', \Auth::user()->creatorId())->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->orderBy('task_due_date', 'ASC')->get();
        } elseif (\Auth::user()->type == 'client') {
            $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();

            return ProjectTask::whereIn('project_id', $user_projects)->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->get();
        } else {
            return ProjectTask::select('project_tasks.*', 'project_tasks.end_date as task_due_date', 'project_users.id as up_id', 'projects.project_name as project_name', 'projectstages.name as stage_name')->join('project_users', 'project_users.project_id', '=', 'project_tasks.project_id')->join('projects', 'project_users.project_id', '=', 'projects.id')->join('projectstages', 'project_tasks.stage_id', '=', 'projectstages.id')->where('project_users.user_id', '=', $this->authId())->where('project_tasks.end_date', '>', date('Y-m-d'))->limit(5)->orderBy(
                'project_tasks.end_date', 'ASC'
            )->get();
        }
    }

    public function show_dashboard()
    {
        $user_type = \Auth::user()->type;

        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = Auth::user();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return $user->plan;
        // return !empty($user->plan)?Plan::find($user->plan)->crm:'';
    }

    public static function show_crm()
    {
        $user_type = \Auth::user()->type;

        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();

        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->crm : '';
    }

    public static function show_hrm()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->hrm : '';

    }

    public static function show_account()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }

        return !empty($user->plan) ? Plan::find($user->plan)->account : '';
    }

    public static function show_project()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }
        return !empty($user->plan) ? Plan::find($user->plan)->project : '';

    }

    public static function show_pos()
    {
        $user_type = \Auth::user()->type;
        if ($user_type == 'company' || $user_type == 'super admin') {
            $user = User::where('id', \Auth::user()->id)->first();
        } else {
            $user = User::where('id', \Auth::user()->created_by)->first();
        }
        return !empty($user->plan) ? Plan::find($user->plan)->pos : '';

    }

    public function clientProjects()
    {
        return $this->hasMany('App\Models\Project', 'client_id', 'id');
    }

    public function isUser()
    {

        return $this->type === 'user' ? 1 : 0;
    }

    public function isClient()
    {
        return $this->type == 'client' ? 1 : 0;
    }

    // For Email template Module
    public static function defaultEmail()
    {
        // Email Template
        $emailTemplate = [
            'New User',
            'New Client',
            'New Support Ticket',
            'Lead Assigned',
            'Deal Assigned',
            'New Award',
            'Customer Invoice Sent',
            'New Invoice Payment',
            'New Payment Reminder',
            'New Bill Payment',
            'Bill Resent',
            'Proposal Sent',
            'Complaint Resent',
            'Leave Action Sent',
            'Payslip Sent',
            'Promotion Sent',
            'Resignation Sent',
            'Termination Sent',
            'Transfer Sent',
            'Trip Sent',
            'Vender Bill Sent',
            'Warning Sent',
            'New Contract',
            'New Project',
            'New Task',
            'Task Status Updated',
            'New Leave',
            'Project Assign Member',
        ];

        foreach ($emailTemplate as $eTemp) {
            $emailTemp = EmailTemplate::where('name', $eTemp)->count();
            if ($emailTemp == 0) {
                EmailTemplate::create(
                    [
                        'name' => $eTemp,
                        'from' => env('APP_NAME'),
                        'slug' => strtolower(str_replace(' ', '_', $eTemp)),
                        'created_by' => 1,
                    ]
                );
            }
        }

        $defaultTemplate = [
            'new_user' => [
                'subject' => 'New User',
                'lang' => [
                    'en' => '<p>Hello,&nbsp;<br>Welcome to {app_name}.</p><p><b>Email </b>: {email}<br><b>Password</b> : {password}</p><p>{app_url}</p><p>Thanks,<br>{app_name}</p>',

                ],
            ],
            'new_client' => [
                'subject' => 'New Client',
                'lang' => [
                     'en' => '<p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Hello {client_name},</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">You are now Client..</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><b data-stringify-type="bold" style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Email&nbsp;</b><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">: {client_email}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><b data-stringify-type="bold" style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Password</b><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">&nbsp;: {client_password}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">{app_url}</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">Thanks,</span><br style="box-sizing: inherit; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);"><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; background-color: rgb(248, 248, 248);">{app_name}</span><br></p>',

                ],
            ],
            'new_support_ticket' => [
                'subject' => 'New Support Ticket',
                'lang' => [
                    'en' => '<p><span style="font-size: 12pt;"><b>Hi</b>&nbsp;{support_name}</span><br><br><span style="font-size: 12pt;">New support ticket has been opened.</span><br><br><span style="font-size: 12pt;"><strong>Title:</strong>&nbsp;{support_title}</span><br><span style="font-size: 12pt;"><strong>Priority:</strong>&nbsp;{support_priority}</span><span style="font-size: 12pt;"><br></span><span style="font-size: 12pt;"><b>End Date</b>: {support_end_date}</span></p><p><br><span style="font-size: 12pt;"><strong>Support message:</strong></span><br><span style="font-size: 12pt;">{support_description}</span><span style="font-size: 12pt;"><br><br><b>Kind Regards</b>,</span><br>{app_name}</p>',
                    ],
            ],
            'lead_assigned' => [
                'subject' => 'Lead Assigned',
                'lang' => [
                   'en' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">﻿</span><span style="font-family: " open="" sans";"="">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: " open="" sans";"="">New Lead has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Name</b></span><span style="" open="" sans";"="">&nbsp;: {lead_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span open="" sans";"="" style="font-size: 1rem;"><b>Lead Email</b></span><span open="" sans";"="" style="font-size: 1rem;">&nbsp;: {lead_email}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Pipeline</b></span><span style="" open="" sans";"="">&nbsp;: {lead_pipeline}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="" open="" sans";"=""><b>Lead Stage</b></span><span style="" open="" sans";"="">&nbsp;: {lead_stage}</span></p><p style="line-height: 28px;"><span style="" open="" sans";"=""><b>Lead Subject</b>: {lead_subject}</span></p><p></p>',
                   ],
            ],
            'deal_assigned' => [
                'subject' => 'Deal Assigned',
                'lang' => [
                    'en' => '<p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;">Hello,</span><br style="font-family: sans-serif;"><span style="font-family: sans-serif;">New Deal has been Assign to you.</span></p><p style="line-height: 28px; font-family: Nunito, &quot;Segoe UI&quot;, arial; font-size: 14px;"><span style="font-family: sans-serif;"><span style="font-weight: bolder;">Deal Name</span>&nbsp;: {deal_name}<br><span style="font-weight: bolder;">Deal Pipeline</span>&nbsp;: {deal_pipeline}<br><span style="font-weight: bolder;">Deal Stage</span>&nbsp;: {deal_stage}<br><span style="font-weight: bolder;">Deal Status</span>&nbsp;: {deal_status}<br><span style="font-weight: bolder;">Deal Price</span>&nbsp;: {deal_price}</span></p><p></p>',
                    ],
            ],
            'new_award' => [
                'subject' => 'New Award',
                'lang' => [
                    'en' => '<p>Hi , <span style="font-family: var(--bs-body-font-family); font-size: var(--bs-body-font-size); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{award_name}</span></p><p>I am much pleased to nominate .</p><p>I am satisfied that he/she is the best employee for the award. </p><p>I have realized  that he/she is a goal-oriented person, efficient and very punctual .</p><p>Feel free to reach out if you have any question.<br></p><p>Thank You, </p><p>{app_name}</p><p>{app_url}</p>',

                ],
            ],
            'customer_invoice_sent' => [
                'subject' => 'Customer Invoice Sent',
                'lang' => [
                    'en' => '<p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"=""><span style="font-family: " open="" sans";"="">﻿</span><span style="text-align: var(--bs-body-text-align);">Hi ,{invoice_name}</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Welcome to {app_name}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Hope this email finds you well! Please see attached invoice number {invoice_number}<span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">} for product/service.</span></p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Simply click on the button below: </p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{invoice_url}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Feel free to reach out if you have any questions.</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Thank You,</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">Regards,</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{company_name}</p><p style="line-height: 28px; font-family: Nunito, " segoe="" ui",="" arial;="" font-size:="" 14px;"="">{app_url}</p><p></p>',

                ],
            ],
            'new_invoice_payment' => [
                'subject' => 'New Invoice Payment',
                'lang' => [
                    'en' => '<p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Hi,</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Welcome to {app_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Dear {invoice_payment_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">We have recieved your amount {invoice_payment_amount} payment for {invoice_number} submited on date {invoice_payment_date}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Your {invoice_number} Due amount is {payment_dueAmount}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">We appreciate your prompt payment and look forward to continued business with you in the future.</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Thank you very much and have a good day!!</span></span></p>
                    <p>&nbsp;</p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Regards,</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">{company_name}</span></span></p>
                    <p><span style="color: #1d1c1d; font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif;">
                    <span style="font-size: 15px; font-variant-ligatures: common-ligatures;">{app_url}</span></span></p>',
                ],
            ],
            'new_payment_reminder' => [
                'subject' => 'New Payment Reminder',
                'lang' => [
                    'en' => '<p>Dear, {payment_reminder_name}</p>
                    <p>I hope you&rsquo;re well.This is just a reminder that payment on invoice {invoice_payment_number} total dueAmount {invoice_payment_dueAmount} , which we sent on {payment_reminder_date} is due today.</p>
                    <p>You can make payment to the bank account specified on the invoice.</p>
                    <p>I&rsquo;m sure you&rsquo;re busy, but I&rsquo;d appreciate if you could take a moment and look over the invoice when you get a chance.</p>
                    <p>If you have any questions whatever, please reply and I&rsquo;d be happy to clarify them.</p>
                    <p>&nbsp;</p>
                    <p>Thanks,&nbsp;</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>',
                ],
            ],
            'new_bill_payment' => [
                'subject' => 'New Bill Payment',
                'lang' => [
                    'en' => '<p>Hi , {payment_name}</p><p>Welcome to {app_name}</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We are writing to inform you that we has sent your {payment_bill} payment.</span></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We has sent your amount {payment_amount} payment for {payment_bill} submited&nbsp; on date {payment_date} via {payment_method}.</span></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You very much and have a good day !!!!</span></p><p>{company_name}</p><p>{app_url}</p>',

                ],
            ],
            'bill_resent' => [
                'subject' => 'Bill Resent',
                'lang' => [
                    'en' => '<p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hi , {bill_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welcome to {app_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Hope this email finds you well! Please see attached bill number {bill_bill} for product/service.</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Simply click on the button below .</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;{bill_url}</span></p><p>Feel free to reach out if you have any questions.</p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You for your business !!!!</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Regards,</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><br></p><p><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p><div><br></div>',

                ],
            ],
            'proposal_sent' => [
                'subject' => 'Proposal Sent',
                'lang' => [
                   'en' => '<p>Hi, {proposal_name}</p>
                    <p>Hope this email ﬁnds you well! Please see attached proposal number {proposal_number} for product/service.</p>
                    <p>simply click on the button below</p>
                    <p>{proposal_url}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you for your business!!</p>
                    <p>&nbsp;</p>
                    <p>Regards,</p>
                    <p>{company_name}</p>
                    <p>{app_url}</p>',
                ],
            ],
            'complaint_resent' => [
                'subject' => 'Complaint Resent',
                'lang' => [
                    'en' => '<p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Hi ,</span></font></p><p><span style="font-size: 15px; font-variant-ligatures: common-ligatures; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Welcome to {app_name}</span><br></p><p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">HR department/company to send complaints letter.<br></span></font></p><p><font color="#1d1c1d" face="Slack-Lato, Slack-Fractions, appleLogo, sans-serif"><span style="font-size: 15px; font-variant-ligatures: common-ligatures;">Dear {complaint_name}</span></font></p><p>I would like to report a conflict between you and the other person. There  have been several incidents over the last few days, and I feel that its is time to report a formal complaint against him/her.</p><p>Feel free to reach out if you have any questions.</p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Thank You,</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">Regards,</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR Department.</span></p><p><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{company_name}</span><span style="color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-size: 15px; font-variant-ligatures: common-ligatures; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);"><br></span></p><p><span style="font-size: 15px; font-variant-ligatures: common-ligatures; color: rgb(29, 28, 29); font-family: Slack-Lato, Slack-Fractions, appleLogo, sans-serif; font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_url}</span><br></p>',
                    ],
            ],
            'leave_action_sent' => [
                'subject' => 'Leave Action Sent',
                'lang' => [
                    'en' => '<p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Subject : "HR department/company to send approval letter to {leave_status} a vacation or leave" .</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">﻿Hi ,{leave_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; I have {leave_status} your leave request for&nbsp; {leave_reason} from {leave_start_date} to {leave_end_date}. {total_leave_days} days I have&nbsp; {leave_status} your leave request for {leave_reason}.</span><br></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">We request you to complete all your pending work or any other important issue so that the company does not face any any loss or problem during your absence. We appreciate your thoughtfulness to inform us well in advance.</span></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Feel free to reach out if you have any questions.</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Thank You,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Regards,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">HR Department,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_url}</p><p></p>',
                    ],
            ],
            'payslip_sent' => [
                'subject' => 'Payslip Sent',
                'lang' => [
                    'en' => '<p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Subject :&nbsp; " HR&nbsp; Department / Company to send&nbsp; payslips by email at time of confirmation of payslip. "</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">﻿Dear ,{payslip_name}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">&nbsp; &nbsp;&nbsp;</span>&nbsp; &nbsp; Hope this email finds you well! Please see attached payslip for {payslip_salary_month} . Simply click on the button below :&nbsp;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {payslip_url}</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Feel free to&nbsp; reach out if you have any questions.</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">Regards ,</p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">HR Department ,</span></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;"><span style="font-family: var(--bs-body-font-family); font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">{app_name}</span><br></p><p segoe="" ui",="" arial;="" font-size:="" 14px;"="" style="line-height: 28px;">{app_url}</p><p></p>',
                    ],
            ],
            'promotion_sent' => [
                'subject' => 'Promotion Sent',
                'lang' => [
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Subject:-HR department/Company to send job promotion congratulation letter.</strong></p>
                    <p><strong>Dear {employee_name},</strong></p>
                    <p>Congratulations on your promotion to {promotion_designation} {promotion_title} effective {promotion_date}.</p>
                    <p>We shall continue to expect consistency and great results from you in your new role. We hope that you will set an example for the other employees of the organization.</p>
                    <p>We wish you luck for your future performance, and congratulations!.</p>
                    <p>Again, congratulations on the new position.</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
            'resignation_sent' => [
                'subject' => 'Resignation Sent',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send resignation letter .</b></p>
                    <p ><b>Dear {assign_user},</b></p>
                    <p >It is with great regret that I formally acknowledge receipt of your resignation notice on {notice_date} to {resignation_date} is your final day of work. </p>
                    <p >It has been a pleasure working with you, and on behalf of the team, I would like to wish you the very best in all your future endeavors. Included with this letter, please find an information packet with detailed information on the resignation process. </p>
                    <p>Thank you again for your positive attitude and hard work all these years.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><b>Regards,</b></p>EmailTemplate
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                    ],
            ],
            'termination_sent' => [
                'subject' => 'Termination Sent',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send termination letter.</strong></p>
                    <p><strong>Dear {employee_termination_name},</strong></p>
                    <p>This letter is written to notify you that your employment with our company is terminated.</p>
                    <p>More detail about termination:</p>
                    <p>Notice Date :{notice_date}</p>
                    <p>Termination Date:{termination_date}</p>
                    <p>Termination Type:{termination_type}</p>
                    <p>&nbsp;</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    ],
            ],
            'transfer_sent' => [
                'subject' => 'Transfer Sent',
                'lang' => [
                    'en' => '<p ><b>Subject:-HR department/Company to send transfer letter to be issued to an employee from one location to another.</b></p>
                    <p ><b>Dear {transfer_name},</b></p>
                    <p >As per Management directives, your services are being transferred w.e.f.{transfer_date}. </p>
                    <p >Your new place of posting is {transfer_department} department of {transfer_branch} branch and date of transfer {transfer_date}. </p>
                    {transfer_description}.
                    <p>Feel free to reach out if you have any questions.</p>
                    <p><b>Thank you</b></p>
                    <p><b>Regards,</b></p>
                    <p><b>HR Department,</b></p>
                    <p><b>{app_name}</b></p>',
                    ],
            ],
            'trip_sent' => [
                'subject' => 'Trip Sent',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send trip letter .</strong></p>
                    <p><strong>Dear {employee_name},</strong></p>
                    <p>Top of the morning to you! I am writing to your department office with a humble request to travel for a {purpose_of_visit} abroad.</p>
                    <p>It would be the leading climate business forum of the year and have been lucky enough to be nominated to represent our company and the region during the seminar.</p>
                    <p>My three-year membership as part of the group and contributions I have made to the company, as a result, have been symbiotically beneficial. In that regard, I am requesting you as my immediate superior to permit me to attend.</p>
                    <p>More detail about trip:{start_date} to {end_date}</p>
                    <p>Trip Duration:{start_date} to {end_date}</p>
                    <p>Purpose of Visit:{purpose_of_visit}</p>
                    <p>Place of Visit:{place_of_visit}</p>
                    <p>Description:{trip_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    ],
            ],
            'vender_bill_sent' => [
                'subject' => 'Vendor Bill Sent',
                'lang' => [
                    'en' => '<p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hi, {bill_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Welcome to {app_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Hope this email finds you well!! Please see attached bill number {bill_number} for product/service.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Simply click on the button below.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{bill_url}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Feel free to reach out if you have any questions.</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Thank You,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">Regards,</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{company_name}</span></p>
                    <p style="line-height: 28px; font-family: Nunito,;"><span style="font-family: sans-serif;">{app_url}</span></p>',
                    ],
            ],
            'warning_sent' => [
                'subject' => 'Warning Sent',
                'lang' => [
                    'en' => '<p><strong>Subject:-HR department/Company to send warning letter.</strong></p>
                    <p><strong>Dear {employee_warning_name},</strong></p>
                    <p>{warning_subject}</p>
                    <p>{warning_description}</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    ],
            ],
            'new_contract' => [
                'subject' => 'New Contract',
                'lang' => [
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Hi</strong> {contract_client}</p>
                    <p><b>Contract Subject</b>&nbsp;: {contract_subject}</p>
                    <p><b>Contract Project</b>&nbsp;: {contract_project}</p>
                    <p><b>Start Date&nbsp;</b>: {contract_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {contract_end_date}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    ],
            ],
            'new_project' => [
                'subject' => 'New Project',
                'lang' => [
                    'en' => '<p><strong>Hi</strong> {project_user}</p>
                    <p><b>Project Name</b>&nbsp;: {project_name}</p>
                    <p><b>Start Date&nbsp;</b>: {project_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {project_end_date}</p>
                    <p><b>Estimated Hours&nbsp;</b>: {hours}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    ],
            ],
            'project_assign_member' => [
                'subject' => 'Project Assign Member',
                'lang' => [
                    'en' => '<p><strong>Hi</strong> {project_user}</p>
                    <p><b>Project Name</b>&nbsp;: {project_name}</p>
                    <p><b>Start Date&nbsp;</b>: {project_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {project_end_date}</p>
                    <p><b>Estimated Hours&nbsp;</b>: {hours}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    ],
            ],
            'new_task' => [
                'subject' => 'New Task',
                'lang' => [
                    'en' => '<p><strong>Hi</strong> {task_user}</p>
                    <p><b>Task Name</b>&nbsp;: {task_name}</p>
                    <p><b>Project Name</b>&nbsp;: {project_name}</p>
                    <p><b>Start Date&nbsp;</b>: {task_start_date}</p>
                    <p><b>End Date&nbsp;</b>: {task_end_date}</p>
                    <p><b>Estimated Hours&nbsp;</b>: {hours}</p>
                    <p>Looking forward to hear from you.</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    ],
            ],
            'task_status_updated' => [
                'subject' => 'Task Status Updated',
                'lang' => [
                    'en' => '<p>&nbsp;</p>
                    <p><strong>Hi</strong> {task_user}</p>
                    <p>{task_name} status changed from {old_stage_name} to {new_stage_name}</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{company_name}</p>',
                    ],
            ],
            'new_leave' => [
                'subject' => 'New Leave',
                'lang' => [
                    'en' => '<p><strong>Hi</strong> {user_name}</p>
                    <p>New Leave create from {start_date} to {end_date} for {leave_reason}</p>
                    <p><strong>Kind Regards, </strong></p>
                    <p>{employee_name}</p>',
                    ],
            ],
        ];

        $email = EmailTemplate::all();

        foreach ($email as $e) {
            foreach ($defaultTemplate[$e->slug]['lang'] as $lang => $content) {
                $emailNoti = EmailTemplateLang::where('parent_id', $e->id)->where('lang', $lang)->count();
                if ($emailNoti == 0) {
                    EmailTemplateLang::create(
                        [
                            'parent_id' => $e->id,
                            'lang' => $lang,
                            'subject' => $defaultTemplate[$e->slug]['subject'],
                            'content' => $content,
                        ]
                    );
                }

            }
        }
    }

    public static function userDefaultData()
    {

        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();
        foreach ($allEmail as $email) {
            UserEmailTemplate::create(
                [
                    'template_id' => $email->id,
                    'user_id' => 2,
                    'is_active' => 1,
                ]
            );
        }
    }

    public function userDefaultDataRegister($user_id)
    {

        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();

        foreach ($allEmail as $email) {
            $emailTemplate = UserEmailTemplate::where('template_id', $email->id)->where('user_id',$user_id)->first();
            if($emailTemplate == null)
            {
                UserEmailTemplate::create(
                    [
                        'template_id' => $email->id,
                        'user_id' => $user_id,
                        'is_active' => 1,
                    ]
                );
            }

        }
    }

    public static function userDefaultWarehouse()
    {
        warehouse::create(
        [
            'name' => 'Default Warehouse',
            'address' => '',
            'city' => '',
            'city_zip' => null,
            'created_by' => 2,
        ]
    );
}

    public function userWarehouseRegister($user_id)
    {
       warehouse::create(
        [
            'name' => 'Default Warehouse',
            'address' => '',
            'city' => '',
            'city_zip' => null,
            'created_by' => $user_id,
        ]
    );
}

    //default bank account for new company
    public function userDefaultBankAccount($user_id)
    {
        BankAccount::create(
            [
                'holder_name' => 'cash',
                'bank_name' => '',
                'account_number' => '-',
                'opening_balance' => '0.00',
                'contact_number' => '-',
                'bank_address' => '-',
                'created_by' => $user_id,
            ]
        );

    }

    public function extraKeyword()
    {
        $keyArr = [
            __('Sun'),
            __('Mon'),
            __('Tue'),
            __('Wed'),
            __('Thu'),
            __('Fri'),
            __('Last 7 Days'),
            __('In Progress'),
            __('Complete'),
            __('Canceled'),
            __('Lead User Name'),
            __('CRM'),
            __('POS'),
            __('HRM'),
            __('Old Stage Name'),
            __('New Stage Name'),
            __('Contract Start Date'),
            __('Contract Name'),
            __('Contract Price'),
            __('Branch Name'),
            __('Support User Name'),
            __('Award Date'),
            __('Holiday Title'),
            __('Holiday Date'),
            __('Event Start Date'),
            __('Company Policy Name'),
            __('Invoice Issue Date'),
            __('Invoice Due Date'),
            __('Budget Name'),
            __('Budget Year'),
            __('Revenue Amount'),
            __('Revenue Date'),
            __('Payment Price'),
            __('New User'),
            __('Lifetime'),
            __('Coupon'),
            __('CoinGate'),
            __('Cashflow'),

        ];
    }

    public function barcodeFormat()
    {
        $settings = Utility::settings();
        return isset($settings['barcode_format']) ? $settings['barcode_format'] : 'code128';
    }

    public function barcodeType()
    {
        $settings = Utility::settings();
        return isset($settings['barcode_type']) ? $settings['barcode_type'] : 'css';
    }

    public static function employeeIdFormat($number)
    {
        $settings = Utility::settings();

        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }

    //user log details
    public static function userCurrentLocation()
    {
        $company_id = Auth::User()->Company_ID();
        if (Auth::user()->user_type == 'company') {
            $location = location::where(['id' => Auth::User()->current_location, 'company_id' => $company_id, 'is_active' => 1])->first();

            if (!is_null($location)) {
                return $location->id;
            } else {
                return 0;
            }

        } elseif (Auth::user()->user_type != 'company' && Auth::user()->user_type != 'super admin') {

            if (Auth::user()->current_location == 0) {
                Auth::user()->current_location = Auth::user()->location_id;
            }

            $location = location::where('id', Auth::user()->current_location)->where('company_id', $company_id)->first();
            return $location->id;
        }
    }

    public function countEmployees()
    {
        return Employee::where('created_by', '=', $this->creatorId())->count();
    }

/**
 * Clone all company defaults from first company
 */
public function cloneCompanyDefaults($user_id, $sourceCompanyId = null)
{
    try {
        // Force log messages to show
        \Log::channel('single')->info("=== STARTING COMPANY CLONING FOR USER: {$user_id} ===");

        $cloner = new CompanyClonerService($user_id, $sourceCompanyId);
        $result = $cloner->cloneAllCompanyData();

        \Log::channel('single')->info("=== SUCCESSFULLY COMPLETED CLONING FOR USER: {$user_id} ===");
        return $result;
    } catch (\Exception $e) {
        \Log::channel('single')->error("=== CLONING FAILED FOR USER {$user_id} ===");
        \Log::channel('single')->error("Error: " . $e->getMessage());
        \Log::channel('single')->error("Stack trace: " . $e->getTraceAsString());

        // Fallback to original method if cloning fails
        \Log::channel('single')->info("=== FALLING BACK TO ORIGINAL METHODS FOR USER: {$user_id} ===");
        $this->createFallbackDefaults($user_id);
        return false;
    }
}

/**
 * Fallback method using original defaults if cloning fails
 */
private function createFallbackDefaults($user_id)
{
    try {
        // Original methods as fallback
        $this->userDefaultDataRegister($user_id);
        $this->userWarehouseRegister($user_id);
        $this->userDefaultBankAccount($user_id);

        Utility::chartOfAccountTypeData($user_id);
        Utility::chartOfAccountData1($user_id);
        Utility::pipeline_lead_deal_Stage($user_id);
        Utility::project_task_stages($user_id);
        Utility::labels($user_id);
        Utility::sources($user_id);
        Utility::jobStage($user_id);

        GenerateOfferLetter::defaultOfferLetterRegister($user_id);
        ExperienceCertificate::defaultExpCertificatRegister($user_id);
        JoiningLetter::defaultJoiningLetterRegister($user_id);
        NOC::defaultNocCertificateRegister($user_id);

        \Log::info("Fallback defaults created for user: {$user_id}");
    } catch (\Exception $e) {
        \Log::error("Error creating fallback defaults: " . $e->getMessage());
    }
}
}
