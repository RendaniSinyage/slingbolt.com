<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/trainings",
     *     summary="Get all trainings",
     *     tags={"Training & Development"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Training")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function index()
    {
        if (!Auth::user()->can('manage training')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $trainings = Training::where('created_by', '=', Auth::user()->creatorId())->with(['branches', 'types'])->get();
        return response()->json($trainings);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trainings",
     *     summary="Create a new training",
     *     tags={"Training & Development"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Training")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Training created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Training")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('create training')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'branch' => 'required',
            'training_type' => 'required',
            'training_cost' => 'required',
            'employee' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $training = new Training();
        $training->branch = $request->branch;
        $training->trainer_option = $request->trainer_option;
        $training->training_type = $request->training_type;
        $training->trainer = $request->trainer;
        $training->training_cost = $request->training_cost;
        $training->employee = $request->employee;
        $training->start_date = $request->start_date;
        $training->end_date = $request->end_date;
        $training->description = $request->description;
        $training->created_by = Auth::user()->creatorId();
        $training->save();

        return response()->json($training, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trainings/{id}",
     *     summary="Get a specific training",
     *     tags={"Training & Development"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Training")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function show(Training $training)
    {
        if (!Auth::user()->can('manage training')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        return response()->json($training->load(['branches', 'types']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/trainings/{id}",
     *     summary="Update a training",
     *     tags={"Training & Development"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Training")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Training updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Training")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Training $training)
    {
        if (!Auth::user()->can('edit training')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'branch' => 'required',
            'training_type' => 'required',
            'training_cost' => 'required',
            'employee' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $training->branch = $request->branch;
        $training->trainer_option = $request->trainer_option;
        $training->training_type = $request->training_type;
        $training->trainer = $request->trainer;
        $training->training_cost = $request->training_cost;
        $training->employee = $request->employee;
        $training->start_date = $request->start_date;
        $training->end_date = $request->end_date;
        $training->description = $request->description;
        $training->save();

        return response()->json($training);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trainings/{id}",
     *     summary="Delete a training",
     *     tags={"Training & Development"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Training deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function destroy(Training $training)
    {
        if (!Auth::user()->can('delete training')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $training->delete();

        return response()->json(null, 204);
    }
}
