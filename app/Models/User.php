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

            // Store previous plan BEFORE changing
            $this->previous_plan = $this->plan;
            $this->plan = $plan->id;
            if($this->trial_expire_date != null)
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
        // Get all email templates from database
        $emailTemplates = EmailTemplate::all(); // ← This WILL return templates!
        foreach ($emailTemplates as $template) {
            // Get all language versions for this template from database
            $templateLangs = EmailTemplateLang::where('parent_id', $template->id)->get();

            // If no language versions exist, this template is incomplete
            if ($templateLangs->isEmpty()) {
                \Log::warning("Email template '{$template->name}' has no language versions");
                continue;
            }
            // Template and its content exist in database - nothing more needed
            // The actual email sending will fetch from EmailTemplate and EmailTemplateLang tables
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
