<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantMessage extends Model
{
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'product_ids',
    ];

    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
        ];
    }
}
