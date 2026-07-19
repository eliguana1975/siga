<?php

namespace App\Http\Controllers;

use App\Models\NotificacionOperativa;
use App\Support\OperationalNotificationService;
use Illuminate\Http\Request;

class OperationalNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:notificaciones-operativas.ver');
    }

    public function index(Request $request, OperationalNotificationService $service)
    {
        if ($request->boolean('sync')) {
            $service->sync();
        }

        $estado = $request->input('estado', 'abiertas');
        $tipo = trim((string) $request->input('tipo', ''));
        $severidad = trim((string) $request->input('severidad', ''));

        $notificaciones = NotificacionOperativa::query()
            ->with('resolvedBy:id,name')
            ->when($estado === 'abiertas', fn ($query) => $query->whereNull('resolved_at'))
            ->when($estado === 'resueltas', fn ($query) => $query->whereNotNull('resolved_at'))
            ->when($tipo !== '', fn ($query) => $query->where('tipo', $tipo))
            ->when($severidad !== '', fn ($query) => $query->where('severidad', $severidad))
            ->orderByRaw("FIELD(severidad, 'critica', 'alta', 'media', 'baja')")
            ->latest('last_seen_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'abiertas' => NotificacionOperativa::query()->whereNull('resolved_at')->count(),
            'no_leidas' => NotificacionOperativa::query()->whereNull('read_at')->whereNull('resolved_at')->count(),
            'criticas' => NotificacionOperativa::query()->where('severidad', 'critica')->whereNull('resolved_at')->count(),
        ];
        $openSummary = NotificacionOperativa::query()
            ->whereNull('resolved_at')
            ->orderByRaw("FIELD(severidad, 'critica', 'alta', 'media', 'baja')")
            ->latest('last_seen_at')
            ->limit(20)
            ->get(['severidad', 'titulo', 'mensaje', 'url', 'last_seen_at']);
        $tipos = NotificacionOperativa::query()->select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');
        $severidades = ['critica', 'alta', 'media', 'baja'];

        return view('admin.notificaciones-operativas.index', compact(
            'notificaciones',
            'counts',
            'tipos',
            'severidades',
            'estado',
            'tipo',
            'severidad',
            'openSummary'
        ));
    }

    public function read(Request $request, NotificacionOperativa $notificacion)
    {
        $notificacion->forceFill(['read_at' => now()])->save();

        return back()->with('success', 'Notificacion marcada como leida.');
    }

    public function resolve(Request $request, NotificacionOperativa $notificacion)
    {
        $notificacion->forceFill([
            'read_at' => $notificacion->read_at ?: now(),
            'resolved_at' => now(),
            'resolved_by_user_id' => $request->user()?->id,
        ])->save();

        return back()->with('success', 'Notificacion resuelta.');
    }
}
