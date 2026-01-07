<?php

namespace App\Auth;

use Illuminate\Http\Request;
use App\Models\User;

interface UserResolver
{
    public function resolve(Request $request): ?User;
}
