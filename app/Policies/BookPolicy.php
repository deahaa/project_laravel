<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Book;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('manage-books');
    }

    //الانشلء كتاب

    public function create(User $user)
    {
        return $user->hasPermission('manage-books');
    }

    //تتحدبث كتاب

    public function update(User $user, Book $book)
    {
        return $user->hasPermission('manage-books');
    }

    //حدف 

    public function delete(User $user, Book $book)
    {
        return $user->hasPermission('manage-books');
    }
}
