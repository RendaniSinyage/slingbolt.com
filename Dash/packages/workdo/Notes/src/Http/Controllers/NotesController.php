<?php

namespace Workdo\Notes\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Workdo\Notes\Entities\Notes;
use Workdo\Notes\Events\CreateNotes;
use Workdo\Notes\Events\UpdateNotes;
use Workdo\Notes\Events\DestroyNotes;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(Auth::check())
        {
            $getActiveWorkSpace = getActiveWorkSpace();
            if (Auth::user()->isAbleTo('note manage')) {
                $personal_notes = Notes::where('type', '=', 'personal')->where('workspace_id', '=', $getActiveWorkSpace)->where('created_by', '=', Auth::user()->id)->get();
                $shared_notes   = Notes::where('type', '=', 'shared')->where('workspace_id', '=', $getActiveWorkSpace)
                    ->whereRaw("find_in_set('" . \Auth::user()->id . "',notes.assign_to)")->get();

                return view('notes::index', compact('shared_notes', 'personal_notes'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('note create')) {
                $users = User::where('workspace_id', getActiveWorkSpace())->get();
            return view('notes::create', compact('users'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('note create')) {

            try {
                $request->validate([
                    'title' => 'required',
                    'text' => 'required',
                    'color' => 'required',
                ]);

                $assign_to = $request->assign_to;
                $assign_to[] = Auth::user()->id;
                $note = new Notes();
                $note->title = $request->title;
                $note->text = $request->text;
                $note->color = $request->color;
                $note->type = $request->type;
                $note->assign_to = implode(',', $assign_to);
                $note->workspace_id = getActiveWorkSpace();
                $note->created_by = $request->type == 'personal' ? Auth::user()->id : creatorId();
                $note->save();
                event(new CreateNotes($request, $note));

                return redirect()->route('notes.index')->with('success', __('The note has been created successfully.'));

            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }else{

            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('notes::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('note edit')) {
            $note = Notes::where('workspace_id', '=', getActiveWorkSpace())->where('created_by', '=', creatorId())->where('id', '=', $id)->first();
            $note->assign_to = explode(',', $note->assign_to);
            $users = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'admin')->get();
            return view('notes::edit', compact('note', 'users'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('note edit')) {
            try {
                $request->validate([
                    'title' => 'required',
                    'text' => 'required',
                    'color' => 'required',
                ]);
                $assign_to = $request->assign_to;
                $assign_to[] = Auth::user()->id;
                $note = Notes::where('workspace_id', '=', getActiveWorkSpace())->where('created_by', '=', Auth::user()->id)->where('id', '=', $id)->first();
                $note->title = $request->title;
                $note->text = $request->text;
                $note->color = $request->color;
                $note->type = $request->type;
                $note->assign_to = implode(',', $assign_to);
                $note->save();

                event(new UpdateNotes($request, $note));

                return redirect()->route('notes.index')->with('success', __('The note details are updated successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('note delete')) {
            $objUser = Auth::user();
            $note = Notes::find($id);
            if ($note->created_by == $objUser->id) {

                event(new DestroyNotes($note));

                $note->delete();
                return redirect()->route('notes.index')->with('success', __('The note has been deleted.'));
            } else {
                return redirect()->route('notes.index')->with('error', __("You can't delete Note!"));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
