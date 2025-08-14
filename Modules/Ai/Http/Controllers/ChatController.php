<?php

namespace Modules\Ai\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ai\Services\AiAssistantService;

class ChatController extends Controller
{
    /**
     * Show the chat page.
     *
     * @return \Illuminate\View\View
     */
    public function showChatPage()
    {
        return view('ai::chat');
    }

    /**
     * Handle the incoming chat request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Modules\Ai\Services\AiAssistantService $aiAssistant
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request, AiAssistantService $aiAssistant)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $request->input('message');
        $reply = $aiAssistant->processMessage($message);

        return response()->json(['reply' => $reply]);
    }
}
