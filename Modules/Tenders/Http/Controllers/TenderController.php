<?php

namespace Modules\Tenders\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tenders\Entities\Tender;
use Modules\Tenders\Entities\TenderSetting;
use Modules\Tenders\Entities\DeniedTender;
use Illuminate\Support\Facades\Auth;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $tenders = Tender::paginate(10);
        return view('tenders::index', compact('tenders'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('tenders::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('tenders::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('tenders::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function settings()
    {
        $settings = TenderSetting::where('company_id', Auth::user()->id)->first();
        return view('tenders::settings', compact('settings'));
    }

    public function settingsStore(Request $request)
    {
        $request->validate([
            'categories' => 'nullable|array',
            'provinces' => 'nullable|array',
            'type' => 'nullable|string',
            'submission_type' => 'nullable|string',
        ]);

        TenderSetting::updateOrCreate(
            ['company_id' => Auth::user()->id],
            [
                'categories' => json_encode($request->categories),
                'provinces' => json_encode($request->provinces),
                'type' => $request->type,
                'submission_type' => $request->submission_type,
            ]
        );

        return redirect()->route('tenders.settings')->with('success', 'Settings saved successfully.');
    }

    public function accept($id)
    {
        // For now, just redirect back with a success message.
        // Later, this could trigger a notification or some other action.
        return redirect()->route('tenders.index')->with('success', 'Tender accepted.');
    }

    public function deny($id)
    {
        $tender = Tender::findOrFail($id);

        DeniedTender::create([
            'company_id' => Auth::user()->id,
            'ocid' => $tender->ocid,
        ]);

        $tender->delete();

        return redirect()->route('tenders.index')->with('success', 'Tender denied.');
    }
}
