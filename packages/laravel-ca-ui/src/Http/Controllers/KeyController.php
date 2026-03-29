<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Key\Models\Key;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class KeyController extends Controller
{
    public function index(): View
    {
        $keys = Key::with('certificateAuthority')
            ->orderByDesc('created_at')
            ->paginate(config('ca-ui.items_per_page', 25));

        return view('ca::pages.keys.index', compact('keys'));
    }

    public function show(string $uuid): View
    {
        $key = Key::where('uuid', $uuid)
            ->with('certificateAuthority')
            ->firstOrFail();

        return view('ca::pages.keys.show', compact('key'));
    }
}
