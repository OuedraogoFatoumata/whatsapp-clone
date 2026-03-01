<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private string $model  = 'llama3-8b-8192';

   
    private function appelGroq(string $prompt): string
    {
        $response = Http::withToken(config('services.groq.key'))
            ->timeout(30)
            ->post($this->apiUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un assistant utile. Réponds toujours en français.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'max_tokens'  => 500,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            throw new \Exception('Erreur API Groq : ' . $response->body());
        }

        return $response->json('choices.0.message.content', '');
    }

   
    public function recap(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
        ]);

       
        $conversation = auth()->user()->conversations()->findOrFail($request->conversation_id);
        $messages = $conversation->messages()
            ->with('utilisateur:id,name')
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        if ($messages->isEmpty()) {
            return response()->json(['reponse' => 'Aucun message à résumer.']);
        }

        $texte = $messages->map(function ($msg) {
            return $msg->utilisateur->name . ' : ' . $msg->body;
        })->join("\n");

        $prompt = "Voici une conversation entre plusieurs personnes :\n\n{$texte}\n\nFais un résumé concis en 3-4 phrases de ce dont ils ont parlé.";

        $reponse = $this->appelGroq($prompt);

        return response()->json(['reponse' => $reponse]);
    }


    public function suggest(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
        ]);

        $conversation = auth()->user()->conversations()->findOrFail($request->conversation_id);

        $messages = $conversation->messages()
            ->with('utilisateur:id,name')
            ->latest()
            ->take(20)
            ->get()
            ->reverse();

        if ($messages->isEmpty()) {
            return response()->json(['reponse' => 'Aucun message pour suggérer une réponse.']);
        }

        $texte = $messages->map(function ($msg) {
            return $msg->utilisateur->name . ' : ' . $msg->body;
        })->join("\n");

        $monNom = auth()->user()->name;
        $prompt = "Voici une conversation :\n\n{$texte}\n\nJe suis {$monNom}. Propose-moi UNE réponse courte et naturelle que je pourrais envoyer. Donne uniquement le texte de la réponse, sans explication.";

        $reponse = $this->appelGroq($prompt);

        return response()->json(['reponse' => $reponse]);
    }
    public function reformulate(Request $request)
    {
        $request->validate([
            'texte' => 'required|string|min:3|max:1000',
        ]);

        $prompt = "Reformule ce message de manière plus claire et naturelle, en gardant le même sens : \"{$request->texte}\". Donne uniquement le texte reformulé, sans explication.";

        $reponse = $this->appelGroq($prompt);

        return response()->json(['reponse' => $reponse]);
    }
}