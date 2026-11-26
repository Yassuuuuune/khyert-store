<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** The customer, any seller involved in the order, or an admin. */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $order->customer_id === $user->id) {
            return true;
        }

        return $order->sellerOrders()->where('seller_id', $user->id)->exists();
    }

    /** Customers may only cancel their own pending/confirmed orders. */
    public function cancel(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id && $order->isCancellable();
    }

    /** Status transitions are an admin operation. */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
