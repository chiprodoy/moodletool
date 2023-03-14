<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;
    protected $fillable=['groupid','userid','timeadded'];

    public $timestamps = false;

    protected $casts=[
        'timeadded'=> 'timestamp',
    ];
}
