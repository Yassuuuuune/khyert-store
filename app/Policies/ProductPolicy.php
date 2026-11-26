<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /** Catalogue is public; dashboards require authentication. */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    /** Only approved sellers may list products. */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSellerApproved;
    }

    /** A seller only manages their own catalogue; admins manage everything. */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || ($user->isSeller() && $product->seller_id === $user->id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    // ─── Moderation (admin only) ───────────────────────────────────────────────

    public function approve(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function feature(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
