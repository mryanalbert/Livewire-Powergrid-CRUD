<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class people extends Model
{
    /** @use HasFactory<\Database\Factories\PeopleFactory> */
    use HasFactory;

    protected $fillable = ['name', 'gender'];
}
