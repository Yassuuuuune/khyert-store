<?php

namespace App\Policies;

use App\Models\SellerOrder;
use App\Models\User;

class SellerOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSeller();
    }

    /** A seller may only access the sub-orders addressed to them. */
    public function view(User $user, SellerOrder $sellerOrder): bool
    {
        return $user->isAdmin() || $sellerOrder->seller_id === $user->id;
    }

    public function update(User $user, SellerOrder $sellerOrder): bool
    {
        return $this->view($user, $sellerOrder);
    }
}
