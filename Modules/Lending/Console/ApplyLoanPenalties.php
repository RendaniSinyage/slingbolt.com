<?php

namespace Modules\Lending\Console;

use Illuminate\Console\Command;
use Modules\Lending\Services\LoanService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ApplyLoanPenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lending:apply-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for overdue loan installments and applies penalties.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(LoanService $loanService)
    {
        $this->info('Applying loan penalties...');
        $loanService->applyPenalties();
        $this->info('Loan penalties applied successfully.');
    }
}
