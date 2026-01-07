<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Profile extends Model
{
    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'display_name',
        'post_number',
        'address',
        'building',
        'user_image',
    ];

    /**
     * Profile は User に属する（1:1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
