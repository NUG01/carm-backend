<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function setLocale($lang)
    {
        // Validate if the selected locale exists in your application
        if (in_array($lang, config('app.available_locales'))) {
            session()->put('locale', $lang);
        }

        return response()->noContent();
    }
}
