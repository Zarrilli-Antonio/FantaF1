<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED_LOCALES, true), 404);

        session(['locale' => $locale]);

        return back();
    }
}
