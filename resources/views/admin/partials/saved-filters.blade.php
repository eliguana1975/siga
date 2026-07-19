@php
    $filterKey = \Illuminate\Support\Str::slug($filterKey ?? request()->route()?->getName() ?? 'default', '_');
    $filterRoute = $filterRoute ?? request()->route()?->getName();
    $filterParams = collect($filterParams ?? request()->query())
        ->reject(fn ($value, $key) => in_array((string) $key, ['page', '_token'], true) || trim((string) $value) === '')
        ->all();
    $savedFilters = data_get(auth()->user()?->dashboard_preferences, "saved_filters.{$filterKey}", []);
@endphp

<div class="border rounded p-3 mb-3">
    <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-end">
        <form method="POST" action="{{ route('admin.saved-filters.store') }}" class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
            @csrf
            <input type="hidden" name="key" value="{{ $filterKey }}">
            <input type="hidden" name="route" value="{{ $filterRoute }}">
            <input type="hidden" name="back" value="{{ url()->full() }}">
            @foreach ($filterParams as $paramKey => $paramValue)
                <input type="hidden" name="params[{{ $paramKey }}]" value="{{ $paramValue }}">
            @endforeach
            <div class="flex-grow-1">
                <label class="form-label mb-1" for="saved-filter-name-{{ $filterKey }}">Guardar filtro actual</label>
                <input type="text" id="saved-filter-name-{{ $filterKey }}" name="name" class="form-control"
                    maxlength="80" placeholder="Nombre del filtro" required>
            </div>
            <div class="align-self-md-end">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-bookmark-plus"></i> Guardar
                </button>
            </div>
        </form>
    </div>

    @if (!empty($savedFilters))
        <div class="d-flex flex-wrap gap-2 mt-3">
            @foreach ($savedFilters as $savedFilter)
                <div class="btn-group" role="group">
                    <a href="{{ route($savedFilter['route'], $savedFilter['params'] ?? []) }}" class="btn btn-sm btn-light-primary">
                        <i class="bi bi-bookmark"></i> {{ $savedFilter['name'] ?? 'Filtro' }}
                    </a>
                    <form method="POST" action="{{ route('admin.saved-filters.destroy', [$filterKey, $savedFilter['id'] ?? '']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light-danger" title="Eliminar filtro">
                            <i class="bi bi-x"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
