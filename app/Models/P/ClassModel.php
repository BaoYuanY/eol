<?php

namespace App\Models\P;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'p_class';

    protected $fillable = [
        'name'
    ];
}
