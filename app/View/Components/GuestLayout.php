<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Component untuk layout guest (tanpa autentikasi).
 * 
 * Digunakan untuk halaman login, register, forgot password, dll.
 */
class GuestLayout extends Component
{
    /**
     * Render view component.
     *
     * @return View View layout guest
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}