<?php

namespace App\Service\P;

use App\Models\P\ClassModel;
use App\Models\P\StudentModel;
use App\Models\P\StudentTaskModel;

class StatusTaskService
{
    /*
     * 添加班级
     */
    public static function addClass ($name): bool
    {
        $class       = new ClassModel();
        $class->name = $name;
        return $class->save();
    }

    /**
     * 添加学生
     */
    public static function addStudent(int $classId, string $name): bool
    {
        return StudentModel::create([
            'classId' => $classId,
            'name'    => $name
        ])->exists;
    }

    /**
     * 添加任务（支持批量）
     */
    public static function addTask(array $data): bool
    {
        $taskNos = explode("\n", str_replace("\r", "", $data['taskNo']));
        $success = true;

        foreach ($taskNos as $taskNo) {
            $taskNo = trim($taskNo);
            if (empty($taskNo)) {
                continue;
            }

            $res = StudentTaskModel::create([
                'studentId' => $data['studentId'],
                'taskNo'    => $taskNo,
                'type'      => $data['type'] ?? StudentTaskModel::TASK_PHONE,
                'status'    => StudentTaskModel::STATUS_ONGOING,
                'title'     => $data['title'] ?? ''
            ]);

            if (!$res) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 更新任务状态
     */
    public static function updateTaskStatus(int $taskId, string $statusName): bool
    {
        $statusMap = array_flip(StudentTaskModel::STATUS_MAPPING);
        $statusId = $statusMap[$statusName] ?? null;

        if ($statusId === null) {
            return false;
        }

        $task = StudentTaskModel::find($taskId);
        if (!$task) {
            return false;
        }

        $task->status = $statusId;
        return $task->save();
    }
}