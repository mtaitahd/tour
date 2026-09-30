<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * The Breeze views (forgot-password, reset-password, verify-email,
     * confirm-password) all wrap themselves in <x-guest-layout>, and this component
     * is what resolves it. It pointed at Breeze's own resources/views/layouts/guest
     * blade, which this project never had — the file sits under admin/layouts
     * instead — so every one of those pages threw "View [layouts.guest] not found"
     * and returned a 500. The login page links straight to /forgot-password, so a
     * real admin who forgot their password hit that 500.
     */
    public function render(): View
    {
        return view('admin.layouts.guest');
    }
}
