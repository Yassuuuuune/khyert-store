<?php

namespace App\Policies;

use App\Models\PayoutRequest;
use App\Models\User;

class PayoutRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAffiliate();
    }

    public function view(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->isAdmin() || $payoutRequest->affiliate_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAffiliateApproved;
    }

    public function approve(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->isAdmin();
    }

    public function pay(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->isAdmin();
    }
}
