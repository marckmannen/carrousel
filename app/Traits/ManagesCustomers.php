<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ManagesCustomers
{
    /**
     * Ensure the customer is not an admin user.
     */
    protected function ensureNotAdmin($customer): void
    {
        if ($customer->isAdmin()) {
            abort(403, 'Je kunt geen admin-accounts beheren via deze pagina.');
        }
    }
}
