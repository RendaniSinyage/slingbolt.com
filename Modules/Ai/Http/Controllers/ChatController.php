<?php

namespace App\Http\Controllers\AiAssistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Handle the incoming chat request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Modules\Ai\Services\AiAssistantService $aiAssistant
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, AiAssistantService $aiAssistant)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $request->input('message');
        $reply = $aiAssistant->processMessage($message);

        return response()->json(['reply' => $reply]);
    }
}
