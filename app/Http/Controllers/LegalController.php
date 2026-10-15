<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Algemene Voorwaarden (Terms of Service).
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Privacybeleid conform AVG / GDPR.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * Cookiebeleid conform Telecommunicatiewet & AVG.
     */
    public function cookies(): View
    {
        return view('legal.cookies');
    }

    /**
     * Colofon & Fictief Rollenspel Disclaimer.
     */
    public function disclaimer(): View
    {
        return view('legal.disclaimer');
    }
}
