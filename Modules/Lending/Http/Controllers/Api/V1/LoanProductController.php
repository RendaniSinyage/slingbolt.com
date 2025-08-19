<?php

namespace Modules\Lending\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanProduct;
use Modules\Lending\Http\Resources\LoanProductResource;
use App\Traits\ApiResponser;

class LoanProductController extends Controller
{
    use ApiResponser;

    public function index()
    {
        // Logic to be added
    }

    public function store(Request $request)
    {
        // Logic to be added
    }

    public function show(LoanProduct $loanProduct)
    {
        // Logic to be added
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        // Logic to be added
    }

    public function destroy(LoanProduct $loanProduct)
    {
        // Logic to be added
    }
}
