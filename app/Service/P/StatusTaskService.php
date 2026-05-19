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
     * 添加学生（支持批量）
     */
    public static function addStudent(int $classId, string $names): bool
    {
        $nameList = explode("\n", str_replace("\r", "", $names));
        $success = true;

        foreach ($nameList as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }

            $res = StudentModel::create([
                'classId' => $classId,
                'name'    => $name
            ]);

            if (!$res) {
                $success = false;
            }
        }

        return $success;
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

    /**
     * 获取今日战报
     */
    public static function getTodayStats(): array
    {
        return \DB::table('p_student as s')
            ->join('p_student_task as t', 's.id', '=', 't.studentId')
            ->join('p_class as c', 's.classId', '=', 'c.id')
            ->whereDate('t.created_at', now()->today())
            ->selectRaw("
                c.name as class_name,
                s.name as student_name,
                SUM(CASE WHEN t.type = 1 AND t.status = 2 THEN 1 ELSE 0 END) as type1_count,
                SUM(CASE WHEN t.type = 2 AND t.status = 2 THEN 1 ELSE 0 END) as type2_count
            ")
            ->groupBy('c.id', 'c.name', 's.id', 's.name')
            ->get()
            ->groupBy('class_name')
            ->toArray();
    }
}