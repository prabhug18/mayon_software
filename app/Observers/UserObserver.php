<?php
namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function created(User $user)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->log('User created (observer)');
    }

    public function updated(User $user)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->log('User updated (observer)');
    }

    public function deleted(User $user)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->log('User deleted (observer)');
    }
}
