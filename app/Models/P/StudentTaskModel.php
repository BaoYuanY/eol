<?php

namespace App\Models\P;

use Illuminate\Database\Eloquent\Model;

class StudentTaskModel extends Model
{
    protected $table = 'p_student_task';

    protected $fillable = [
        'studentId',
        'taskNo',
        'title',
        'type',
        'status'
    ];


    const int   TASK_PHONE   = 1;
    const int   TASK_MESSAGE = 2;
    const array TASK_MAPPING = [
        self::TASK_PHONE   => '电话',
        self::TASK_MESSAGE => '短信'
    ];


    const int   STATUS_PENDING = 0;
    const int   STATUS_ONGOING = 1;
    const int   STATUS_FINISH  = 2;
    const int   STATUS_FAILED  = 3;
    const array STATUS_MAPPING = [
        self::STATUS_PENDING => '未开始',
        self::STATUS_ONGOING => '进行中',
        self::STATUS_FINISH  => '已完成',
        self::STATUS_FAILED  => '失败'
    ];

    public function student()
    {
        return $this->belongsTo(StudentModel::class, 'studentId');
    }
}
