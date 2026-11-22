<?php

namespace App\Policies;

use App\Models\Marriage;
use App\Models\User;

class MarriagePolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role->name, ['admin', 'marriage_registrar', 'marriage_teller', 'data_clerk']);
    }

    public function create(User $user)
    {
        return in_array($user->role->name, ['admin', 'marriage_teller', 'data_clerk']);
    }

    public function bulkUpload(User $user)
    {
        return $user->role->name === 'data_clerk';
    }

    public function verify(User $user)
    {
        return in_array($user->role->name, ['admin', 'marriage_registrar']);
    }

    public function manageUsers(User $user)
    {
        return $user->role->name === 'admin';
    }
}