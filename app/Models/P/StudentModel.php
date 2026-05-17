<?php

namespace App\Models\P;

use Illuminate\Database\Eloquent\Model;

class StudentModel extends Model
{
    protected $table = 'p_student';

    protected $fillable = [
        'classId',
        'name'
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'classId');
    }
}
