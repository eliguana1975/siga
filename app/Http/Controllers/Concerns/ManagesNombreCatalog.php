<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ManagesNombreCatalog
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:' . $this->permissionName);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $modelClass = $this->modelClass;

        $query = $modelClass::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $items = $query->paginate(5)->withQueryString();

        return view('admin.catalogos.index', [
            'items' => $items,
            'search' => $search,
            'routePrefix' => $this->routePrefix,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'entityName' => $this->entityName,
            'entityNamePlural' => $this->entityNamePlural,
            'createModalId' => $this->createModalId(),
            'editModalPrefix' => $this->editModalPrefix(),
            'deleteModalPrefix' => $this->deleteModalPrefix(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique($this->tableName(), 'nombre'),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->routePrefix . '.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', $this->createModalId());
        }

        $modelClass = $this->modelClass;
        $modelClass::create($request->only('nombre'));

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', ucfirst($this->entityName) . ' creado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = $this->findItem($id);

        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique($this->tableName(), 'nombre')->ignore($item->getKey()),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->routePrefix . '.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', $this->editModalPrefix() . '-' . $item->getKey());
        }

        $item->update($request->only('nombre'));

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', ucfirst($this->entityName) . ' actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = $this->findItem($id);
        $item->delete();

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', ucfirst($this->entityName) . ' eliminado correctamente.');
    }

    private function findItem(string $id): Model
    {
        $modelClass = $this->modelClass;

        return $modelClass::findOrFail($id);
    }

    private function tableName(): string
    {
        $modelClass = $this->modelClass;

        return (new $modelClass())->getTable();
    }

    private function createModalId(): string
    {
        return 'createCatalogoModal-' . str_replace('.', '-', $this->routePrefix);
    }

    private function editModalPrefix(): string
    {
        return 'editCatalogoModal-' . str_replace('.', '-', $this->routePrefix);
    }

    private function deleteModalPrefix(): string
    {
        return 'deleteCatalogoModal-' . str_replace('.', '-', $this->routePrefix);
    }
}
