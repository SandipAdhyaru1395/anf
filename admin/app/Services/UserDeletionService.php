<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class UserDeletionService
{
    public function delete(User $user): void
    {
        // Prevent deleting Super Admin
        if ($user->role_id == 1) {
            throw new Exception('Super Admin cannot be deleted.');
        }

        // Prevent deleting yourself
        if ($user->id == Auth::id()) {
            throw new Exception('You cannot delete your own account.');
        }

        $user->delete();
    }
}
