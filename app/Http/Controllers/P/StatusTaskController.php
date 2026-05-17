<?php

namespace App\Http\Controllers\P;

use App\Http\Controllers\Controller;
use App\Service\P\StatusTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusTaskController extends Controller
{
    /*
     * 创建班级
     */
    public function addClass(Request $request): JsonResponse
    {
        $className = $request->input('name');

        return StatusTaskService::addClass($className) ? $this->success() : $this->error(400, '添加班级失败');
    }

    /**
     * 添加学生
     */
    public function addStudent(Request $request): JsonResponse
    {
        $classId = $request->input('classId');
        $name    = $request->input('name');

        if (!$classId || !$name) {
            return $this->error(400, '参数错误');
        }

        return StatusTaskService::addStudent($classId, $name) ? $this->success() : $this->error(400, '添加学生失败');
    }

    /**
     * 添加任务
     */
    public function addTask(Request $request): JsonResponse
    {
        $data = $request->only(['studentId', 'taskNo', 'type']);

        if (empty($data['studentId']) || empty($data['taskNo'])) {
            return $this->error(400, '参数错误');
        }

        return StatusTaskService::addTask($data) ? $this->success() : $this->error(400, '添加任务失败');
    }

    /**
     * 更新任务状态
     */
    public function updateTaskStatus(Request $request): JsonResponse
    {
        $taskId = $request->input('taskId');
        $status = $request->input('status'); // 这里传的是状态名称，需要转换

        if (!$taskId || $status === null) {
            return $this->error(400, '参数错误');
        }

        return StatusTaskService::updateTaskStatus($taskId, $status) ? $this->success() : $this->error(400, '更新状态失败');
    }
}
