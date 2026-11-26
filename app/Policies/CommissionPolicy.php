<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

/**
 * Commission rows are system-owned: affiliates can never create, edit or
 * delete them. They may only read their own ledger.
 */
class CommissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAffiliate();
    }

    public function view(User $user, Commission $commission): bool
    {
        return $user->isAdmin() || $commission->affiliate_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Commission $commission): bool
    {
        return false;
    }

    public function approve(User $user, Commission $commission): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, Commission $commission): bool
    {
        return $user->isAdmin();
    }
}
