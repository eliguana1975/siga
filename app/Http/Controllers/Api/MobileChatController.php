<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'chat.ver')) {
            return $forbidden;
        }

        $userId = (int) $request->user()->id;

        return response()->json([
            'users' => User::query()
                ->select(['id', 'name', 'email'])
                ->where('id', '<>', $userId)
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => $this->userPayload($user))
                ->values(),
            'conversations' => $this->conversations($userId),
            'unread_count' => $this->unreadCount($userId),
        ]);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'chat.ver')) {
            return $forbidden;
        }

        $userId = (int) $request->user()->id;

        if ((int) $user->id === $userId) {
            return response()->json(['message' => 'No puedes abrir una conversacion contigo mismo.'], 422);
        }

        $conversation = ChatConversacion::firstOrCreateBetween($userId, (int) $user->id);
        $this->markAsRead($conversation, $userId);

        $messages = $conversation->mensajes()
            ->with(['emisor:id,name,email', 'receptor:id,name,email'])
            ->oldest()
            ->get()
            ->map(fn (ChatMensaje $message) => $this->messagePayload($message, $userId))
            ->values();

        return response()->json([
            'user' => $this->userPayload($user),
            'conversation_id' => $conversation->id,
            'messages' => $messages,
            'unread_count' => $this->unreadCount($userId),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'chat.crear')) {
            return $forbidden;
        }

        $userId = (int) $request->user()->id;
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        if ((int) $validated['receptor_id'] === $userId) {
            return response()->json(['message' => 'No puedes enviarte mensajes a ti mismo.'], 422);
        }

        $conversation = ChatConversacion::firstOrCreateBetween($userId, (int) $validated['receptor_id']);

        $message = DB::transaction(function () use ($conversation, $userId, $validated) {
            $message = ChatMensaje::create([
                'chat_conversacion_id' => $conversation->id,
                'emisor_id' => $userId,
                'receptor_id' => (int) $validated['receptor_id'],
                'mensaje' => $validated['mensaje'],
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });

        $message->load(['emisor:id,name,email', 'receptor:id,name,email']);

        return response()->json([
            'message' => 'Mensaje enviado.',
            'data' => $this->messagePayload($message, $userId),
            'conversations' => $this->conversations($userId),
        ], 201);
    }

    private function conversations(int $userId)
    {
        return ChatConversacion::with(['userOne:id,name,email', 'userTwo:id,name,email', 'ultimoMensaje.emisor:id,name,email'])
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
            ->map(function (ChatConversacion $conversation) use ($userId) {
                $otherUser = $conversation->otroUsuario($userId);

                return [
                    'id' => $conversation->id,
                    'user' => $otherUser ? $this->userPayload($otherUser) : null,
                    'last_message' => $conversation->ultimoMensaje ? [
                        'text' => Str::limit($conversation->ultimoMensaje->mensaje, 120),
                        'own' => (int) $conversation->ultimoMensaje->emisor_id === $userId,
                        'created_at' => optional($conversation->ultimoMensaje->created_at)->format('Y-m-d H:i'),
                    ] : null,
                    'unread_count' => (int) ($conversation->unread_count ?? 0),
                ];
            })
            ->values();
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function messagePayload(ChatMensaje $message, int $viewerId): array
    {
        return [
            'id' => $message->id,
            'mensaje' => $message->mensaje,
            'own' => (int) $message->emisor_id === $viewerId,
            'emisor' => $message->emisor ? $this->userPayload($message->emisor) : null,
            'receptor' => $message->receptor ? $this->userPayload($message->receptor) : null,
            'created_at' => optional($message->created_at)->format('Y-m-d H:i'),
            'leido_at' => optional($message->leido_at)->format('Y-m-d H:i'),
        ];
    }

    private function markAsRead(ChatConversacion $conversation, int $userId): void
    {
        ChatMensaje::where('chat_conversacion_id', $conversation->id)
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

    private function authorizePermission(Request $request, string $permission): ?JsonResponse
    {
        $user = $request->user();

        if ($user && ($user->isSuperUsuario() || $this->hasPermission($user, $permission))) {
            return null;
        }

        return response()->json(['message' => 'No tiene permisos para esta accion.'], 403);
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $direct = DB::table('permissions')
            ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('permissions.name', $permission)
            ->where('model_has_permissions.model_type', User::class)
            ->where('model_has_permissions.model_id', $user->id)
            ->exists();

        if ($direct) {
            return true;
        }

        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('permissions.name', $permission)
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->exists();
    }
}
