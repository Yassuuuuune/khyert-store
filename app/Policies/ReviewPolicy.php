<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /** Reviews are moderated: only an admin may approve or delete them. */
    public function approve(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }
}
