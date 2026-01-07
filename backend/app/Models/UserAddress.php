<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_number',
        'prefecture',
        'city',
        'address_line1',
        'address_line2',
        'recipient_name',
        'phone',
        'is_primary',
    ];
}
