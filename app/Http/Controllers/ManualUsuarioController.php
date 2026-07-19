<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManualUsuarioController extends Controller
{
    public function index(): View
    {
        $path = base_path('docs/flujo-funcional-siga.md');

        abort_unless(File::exists($path), 404);

        $markdown = File::get($path);
        $sections = $this->extractHeadings($markdown);
        $html = (string) Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $this->addHeadingAnchors($html);

        return view('admin.manual-usuario.index', [
            'manualHtml' => $html,
            'sections' => $sections,
            'updatedAt' => File::lastModified($path),
        ]);
    }

    private function extractHeadings(string $markdown): array
    {
        preg_match_all('/^(#{1,3})\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);

        $used = [];

        return collect($matches)
            ->map(function (array $match) use (&$used) {
                $title = trim(preg_replace('/\s+#+$/', '', $match[2]));

                return [
                    'level' => strlen($match[1]),
                    'title' => $title,
                    'id' => $this->uniqueSlug($title, $used),
                ];
            })
            ->values()
            ->all();
    }

    private function addHeadingAnchors(string $html): string
    {
        $used = [];

        return preg_replace_callback('/<h([1-3])>(.*?)<\/h\1>/is', function (array $match) use (&$used) {
            $level = $match[1];
            $content = $match[2];
            $title = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $id = $this->uniqueSlug($title, $used);

            return sprintf('<h%s id="%s">%s</h%s>', $level, e($id), $content, $level);
        }, $html) ?? $html;
    }

    private function uniqueSlug(string $title, array &$used): string
    {
        $base = Str::slug($title) ?: 'seccion';
        $slug = $base;
        $counter = 2;

        while (isset($used[$slug])) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        $used[$slug] = true;

        return $slug;
    }
}
