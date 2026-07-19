<?php

namespace App\Http\Controllers;

use App\Support\GlobalSearchService;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(private readonly GlobalSearchService $searchService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $results = $query !== ''
            ? $this->searchService->search($request->user(), $query, 12)->groupBy('module')
            : collect();

        return view('admin.global-search.index', compact('query', 'results'));
    }

    public function suggest(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $results = $query !== ''
            ? $this->searchService->search($request->user(), $query, 3)->take(12)->values()
            : collect();

        return response()->json([
            'query' => $query,
            'results' => $results,
        ]);
    }
}
