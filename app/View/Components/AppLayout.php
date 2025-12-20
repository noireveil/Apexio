<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Component untuk layout aplikasi utama.
 * 
 * Digunakan sebagai wrapper untuk halaman yang memerlukan layout app.
 */
class AppLayout extends Component
{
    /**
     * Render view component.
     *
     * @return View View layout app
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}