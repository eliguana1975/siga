<?php

namespace App\Http\Controllers;

use App\Models\DocumentoOperativo;
use App\Models\Compra;
use App\Models\Entrada;
use App\Models\EntregaHerramienta;
use App\Models\EntregaRopaEpp;
use App\Models\OrdenTrabajo;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentoOperativoController extends Controller
{
    private const TYPES = [
        'solicitud_repuesto' => [
            'class' => SolicitudRepuesto::class,
            'folder' => 'documentos/solicitudes-repuestos',
            'view_permission' => 'solicitudes-repuestos.ver',
            'edit_permission' => 'solicitudes-repuestos.editar',
            'route' => 'admin.solicitudes-repuestos.show',
        ],
        'reparacion_articulo' => [
            'class' => ReparacionArticulo::class,
            'folder' => 'documentos/reparaciones-articulos',
            'view_permission' => 'reparaciones-articulos.ver',
            'edit_permission' => 'reparaciones-articulos.editar',
            'route' => 'admin.reparaciones-articulos.show',
        ],
        'orden_trabajo' => [
            'class' => OrdenTrabajo::class,
            'folder' => 'documentos/ordenes-trabajo',
            'view_permission' => 'ordenes-trabajo.ver',
            'edit_permission' => 'ordenes-trabajo.editar',
            'route' => 'admin.ordenes-trabajo.articulos',
        ],
        'compra' => [
            'class' => Compra::class,
            'folder' => 'documentos/ordenes-compra',
            'view_permission' => 'ordenes-compra.ver',
            'edit_permission' => 'ordenes-compra.editar',
            'route' => 'admin.ordenes-compra.show',
        ],
        'entrada' => [
            'class' => Entrada::class,
            'folder' => 'documentos/entradas',
            'view_permission' => 'entradas.ver',
            'edit_permission' => 'entradas.editar',
            'route' => 'admin.entradas.show',
        ],
        'entrega_herramienta' => [
            'class' => EntregaHerramienta::class,
            'folder' => 'documentos/entregas-herramientas',
            'view_permission' => 'entregas-herramientas.ver',
            'edit_permission' => 'entregas-herramientas.editar',
            'route' => 'admin.entregas-herramientas.show',
        ],
        'entrega_ropa_epp' => [
            'class' => EntregaRopaEpp::class,
            'folder' => 'documentos/entregas-ropa-epp',
            'view_permission' => 'entregas-ropa-epp.ver',
            'edit_permission' => 'entregas-ropa-epp.editar',
            'route' => 'admin.entregas-ropa-epp.show',
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'documentable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'documentable_id' => ['required', 'integer'],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string'],
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ]);

        $config = self::TYPES[$validated['documentable_type']];
        $this->authorizeDocumentAction($request, $config['edit_permission']);
        $documentable = $this->findDocumentable($config['class'], (int) $validated['documentable_id']);
        $file = $request->file('archivo');
        $path = Storage::disk('public')->putFile($config['folder'], $file);

        DocumentoOperativo::create([
            'documentable_type' => $config['class'],
            'documentable_id' => $documentable->getKey(),
            'user_id' => $request->user()?->id,
            'titulo' => trim((string) $validated['titulo']),
            'descripcion' => trim((string) ($validated['descripcion'] ?? '')) ?: null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        return redirect()
            ->route($config['route'], $documentable->getKey())
            ->with('success', 'Documento adjuntado correctamente.');
    }

    public function download(Request $request, DocumentoOperativo $documento)
    {
        $config = $this->configForClass($documento->documentable_type);
        $this->authorizeDocumentAction($request, $config['view_permission']);

        abort_unless(Storage::disk($documento->disk)->exists($documento->path), 404);

        return Storage::disk($documento->disk)->download($documento->path, $documento->original_name);
    }

    public function destroy(Request $request, DocumentoOperativo $documento)
    {
        $config = $this->configForClass($documento->documentable_type);
        $this->authorizeDocumentAction($request, $config['edit_permission']);
        $documentableId = $documento->documentable_id;

        Storage::disk($documento->disk)->delete($documento->path);
        $documento->delete();

        return redirect()
            ->route($config['route'], $documentableId)
            ->with('success', 'Documento eliminado correctamente.');
    }

    private function findDocumentable(string $class, int $id): Model
    {
        /** @var Model $model */
        $model = $class::query()->findOrFail($id);

        return $model;
    }

    private function configForClass(string $class): array
    {
        foreach (self::TYPES as $config) {
            if ($config['class'] === $class) {
                return $config;
            }
        }

        abort(404);
    }

    private function authorizeDocumentAction(Request $request, string $permission): void
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperUsuario() || $user->can($permission)), 403);
    }
}
