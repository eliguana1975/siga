<?php

namespace App\Http\Controllers;

use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $userId = Auth::id();
        $selectedUser = null;
        $conversacion = null;
        $mensajes = collect();

        if ($request->filled('user')) {
            $selectedUser = User::select(['id', 'name', 'email'])
                ->whereKey($request->integer('user'))
                ->whereKeyNot($userId)
                ->first();

            if ($selectedUser) {
                $conversacion = ChatConversacion::firstOrCreateBetween($userId, $selectedUser->id);
                $this->markAsRead($conversacion, $userId);
                $mensajes = $conversacion->mensajes()
                    ->with(['emisor:id,name', 'receptor:id,name'])
                    ->oldest()
                    ->get();
            }
        }

        return view('admin.chat.index', [
            'usuarios' => $this->usuarios($userId),
            'conversaciones' => $this->conversaciones($userId),
            'selectedUser' => $selectedUser,
            'conversacion' => $conversacion,
            'mensajes' => $mensajes,
            'unreadCount' => $this->unreadCount($userId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        if ((int) $validated['receptor_id'] === $userId) {
            return redirect()
                ->route('admin.chat.index')
                ->with('error', 'No puedes enviarte mensajes a ti mismo.');
        }

        $conversacion = ChatConversacion::firstOrCreateBetween($userId, (int) $validated['receptor_id']);

        ChatMensaje::create([
            'chat_conversacion_id' => $conversacion->id,
            'emisor_id' => $userId,
            'receptor_id' => $validated['receptor_id'],
            'mensaje' => $validated['mensaje'],
        ]);

        $conversacion->update(['last_message_at' => now()]);

        return redirect()
            ->route('admin.chat.index', ['user' => $validated['receptor_id']]);
    }

    public function unread(): JsonResponse
    {
        $userId = Auth::id();
        $latest = ChatMensaje::with('emisor:id,name')
            ->where('receptor_id', $userId)
            ->whereNull('leido_at')
            ->latest()
            ->first();

        return response()->json([
            'count' => $this->unreadCount($userId),
            'latest' => $latest ? [
                'from' => $latest->emisor?->name,
                'message' => Str::limit($latest->mensaje, 80),
                'url' => route('admin.chat.index', ['user' => $latest->emisor_id]),
            ] : null,
        ]);
    }

    private function usuarios(int $userId)
    {
        return User::select(['id', 'name', 'email'])
            ->where('id', '<>', $userId)
            ->orderBy('name')
            ->get();
    }

    private function conversaciones(int $userId)
    {
        return ChatConversacion::with(['userOne:id,name', 'userTwo:id,name', 'ultimoMensaje.emisor:id,name'])
            ->withCount([
                'mensajes as unread_count' => function ($query) use ($userId) {
                    $query->where('receptor_id', $userId)
                        ->whereNull('leido_at');
                },
            ])
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (ChatConversacion $conversacion) use ($userId) {
                $conversacion->otro_usuario = $conversacion->otroUsuario($userId);
                return $conversacion;
            });
    }

    private function markAsRead(ChatConversacion $conversacion, int $userId): void
    {
        ChatMensaje::where('chat_conversacion_id', $conversacion->id)
            ->where('receptor_id', $userId)
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);
    }

    private function unreadCount(int $userId): int
    {
        return ChatMensaje::where('receptor_id', $userId)
            ->whereNull('leido_at')
            ->count();
    }
}
