<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    public function switchLocale($lang)
    {
        if (in_array($lang, ['en', 'si'])) {
            session(['locale' => $lang]);
            
            if (Auth::check()) {
                $user = Auth::user();
                $user->language = $lang;
                $user->save();
            }
        }
        return redirect()->back();
    }
}
