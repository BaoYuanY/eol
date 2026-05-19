@php
    $classes = \App\Models\P\ClassModel::all();
    $students = \App\Models\P\StudentModel::all();
    $tasks = \App\Models\P\StudentTaskModel::with(['student.class'])
        ->whereIn('status', [\App\Models\P\StudentTaskModel::STATUS_PENDING, \App\Models\P\StudentTaskModel::STATUS_ONGOING])
        ->orderBy('id', 'desc')
        ->get();

    $tasks->transform(function($task) {
        try {
            $task->taskNo = decrypt($task->taskNo);
        } catch (\Exception $e) {
            // 如果解密失败（可能是旧数据），保持原样
        }
        $task->type_name = \App\Models\P\StudentTaskModel::TASK_MAPPING[$task->type] ?? '未知';
        $task->status_name = \App\Models\P\StudentTaskModel::STATUS_MAPPING[$task->status] ?? '未知';
        return $task;
    });

    $statusMap = \App\Models\P\StudentTaskModel::STATUS_MAPPING;
    $statusColors = [
        '未开始' => 'secondary',
        '进行中' => 'primary',
        '已完成' => 'success',
        '失败' => 'danger'
    ];
@endphp
        <!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>任务中心</title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f7f8fa;
            color: #2f3542;
        }

        .simple-card {
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.04);
        }

        .table-card {
            overflow: hidden;
        }

        .task-table {
            font-size: 0.875rem;
        }

        .task-table th,
        .task-table td {
            white-space: nowrap;
            vertical-align: middle;
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
        }

        .task-time {
            min-width: 180px;
        }

        .task-time-main {
            font-weight: 600;
            color: #212529;
        }

        .elapsed-text {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* 选中按钮强化样式 */
        .btn-selectable {
            margin-right: 5px;
            margin-bottom: 5px;
            border-color: #dee2e6;
            background-color: #fff;
            color: #495057;
            transition: all 0.2s;
        }
        .btn-selectable:hover {
            background-color: #f8f9fa;
        }
        .btn-selectable.active {
            background-color: #007bff !important;
            border-color: #0056b3 !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: 0 0 8px rgba(0,123,255,0.5);
            transform: translateY(-1px);
        }

        /* 标题红点提示 */
        .title-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #dc3545;
            color: white;
            font-size: 0.75rem;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-left: 8px;
            vertical-align: middle;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
    </style>
</head>
<body>
<div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 300px;"></div>

<nav class="navbar navbar-light bg-white border-bottom">
    <div class="container-fluid px-4">
        <span class="navbar-brand mb-0 h1">
            任务中心
            <span id="timeoutCountBadge" class="title-badge" style="display: none;">0</span>
        </span>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div id="statsSection" class="mb-4" style="display: none;">
        <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="alert-heading font-weight-bold mb-0"><i class="fas fa-chart-line mr-2"></i>今日完成情况战报：</h6>
                <button type="button" class="close" onclick="$('#statsSection').hide()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="statsContent">
                <!-- 统计内容将通过 AJAX 加载 -->
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-info" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div class="mb-3 mb-lg-0 d-flex flex-wrap">
            <div class="mr-3 mb-2">
                <input type="text" id="searchClass" class="form-control form-control-sm" placeholder="搜索班级...">
            </div>
            <div class="mr-3 mb-2">
                <input type="text" id="searchStudent" class="form-control form-control-sm" placeholder="搜索学生...">
            </div>
            <div class="mr-3 mb-2">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary filter-status-btn active" data-status="all">全部</button>
                    <button type="button" class="btn btn-outline-secondary filter-status-btn" data-status="进行中">进行中</button>
                </div>
            </div>
            <div class="mr-3 mb-2">
                <button type="button" class="btn btn-info btn-sm" id="btnShowStats">查看今日战报</button>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2 mb-lg-0" data-toggle="modal"
                    data-target="#classModal">添加班级
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm mb-2 mb-lg-0" data-toggle="modal"
                    data-target="#studentModal">添加学生
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- 内联添加任务表单 -->
            <div class="card simple-card mb-4">
                <div class="card-body">
                    <h6 class="card-title font-weight-bold mb-3"><i class="fas fa-plus-circle mr-2 text-primary"></i>快速新增任务</h6>
                    <form id="addTaskForm">
                        <input type="hidden" name="classId" id="selectedClassId">
                        <input type="hidden" name="studentId" id="selectedStudentId">
                        <input type="hidden" name="type" id="selectedType" value="1">
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">1. 任务编号 (每行一个，回车发布)</label>
                                    <textarea name="taskNo" class="form-control form-control-sm" rows="6" placeholder="请输入编号..." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">2. 选择班级</label>
                                    <div class="d-flex flex-wrap" id="classButtonGroup">
                                        @foreach ($classes as $class)
                                            <button type="button" class="btn btn-sm btn-selectable class-select-btn" data-id="{{ $class['id'] }}">
                                                {{ $class['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">3. 选择学生</label>
                                    <div class="d-flex flex-wrap" id="studentButtonGroup">
                                        <div class="text-muted small py-1">请先选择班级</div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mr-3">4. 任务类型</label>
                                    <div class="d-flex flex-column align-items-start">
                                        <div class="btn-group btn-group-sm mb-3" id="typeButtonGroup">
                                            @foreach(\App\Models\P\StudentTaskModel::TASK_MAPPING as $typeId => $typeName)
                                                <button type="button" class="btn btn-selectable type-select-btn {{ $typeId == 1 ? 'active' : '' }}" data-id="{{ $typeId }}">
                                                    {{ $typeName }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm px-5 font-weight-bold" id="saveTaskBtn">立即发布任务</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card simple-card table-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 mb-0">全部任务</h2>
                        <span class="badge badge-light">{{ count($tasks) }} 项</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover task-table mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>时间</th>
                                <th>班级</th>
                                <th>学生</th>
                                <th>任务编号</th>
                                <th>任务类型</th>
                                <th>任务状态</th>
                                <th class="text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($tasks as $task)
                                <tr data-task-id="{{ $task->id }}">
                                    <td class="task-time">
                                        <div class="task-time-main">{{ $task->created_at }}</div>
                                        <div
                                                class="elapsed-text elapsed-timer mt-1"
                                                data-start="{{ $task->created_at }}"
                                                data-status="{{ $task->status_name }}"
                                                style="{{ in_array($task->status_name, ['已完成', '失败']) ? 'display: none;' : '' }}"
                                        >
                                            已过去 0秒
                                        </div>
                                    </td>
                                    <td>{{ $task->student->class->name ?? '无' }}</td>
                                    <td>{{ $task->student->name ?? '无' }}</td>
                                    <td>{{ $task->taskNo }}</td>
                                    <td>{{ $task->type_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusColors[$task->status_name] ?? 'secondary' }} status-badge">{{ $task->status_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($task->status_name !== '已完成' && $task->status_name !== '失败')
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-success py-0 px-2 quick-finish-btn" type="button" data-status="已完成">
                                                    完成
                                                </button>
                                                <button class="btn btn-danger py-0 px-2 quick-finish-btn" type="button" data-status="失败">
                                                    失败
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted small">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-2">暂无任务数据</div>
                                        <small>请在上方输入任务编号并发布</small>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 模态框 1：添加班级 -->
<div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">添加班级</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClassForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>班级名称</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveClassBtn">保存班级</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 模态框 2：添加学生 -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">添加学生</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addStudentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>学生姓名 (支持换行输入多个)</label>
                        <textarea name="name" class="form-control" rows="5" placeholder="请在此输入学生姓名，每行一个" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>所属班级</label>
                        <select name="classId" class="form-control" required>
                            @foreach ($classes as $class)
                                <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveStudentBtn">保存学生</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>
<script>
    var statusColorMap = {
        '未开始': 'secondary',
        '进行中': 'primary',
        '已完成': 'success',
        '失败': 'danger'
    };

    // 存储所有学生信息供前端联动
    var allStudents = @json($students);

    function formatElapsed(seconds) {
        if (seconds < 60) {
            return seconds + '秒';
        }
        if (seconds < 3600) {
            var minutes = Math.floor(seconds / 60);
            var remainSeconds = seconds % 60;
            return minutes + '分' + remainSeconds + '秒';
        }
        var hours = Math.floor(seconds / 3600);
        var remainMinutes = Math.floor((seconds % 3600) / 60);
        var remainSecondsAfterHour = seconds % 60;
        return hours + '小时' + remainMinutes + '分' + remainSecondsAfterHour + '秒';
    }

    function updateElapsedTimers() {
        var timers = document.querySelectorAll('.elapsed-timer');
        var now = new Date();

        timers.forEach(function (timer) {
            if (timer.dataset.status === '已完成' || timer.dataset.status === '失败') {
                timer.style.display = 'none';
                return;
            } else {
                timer.style.display = 'block';
            }

            var dateString = timer.dataset.start.replace(/-/g, '/');
            var start = new Date(dateString);

            var diff = Math.max(0, Math.floor((now - start) / 1000));
            timer.textContent = '已过去 ' + formatElapsed(diff);
        });
    }

    function showAlert(message, type = 'success') {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        $('#alertContainer').append(alertHtml);
        setTimeout(function () {
            $('.alert').alert('close');
        }, 3000);
    }

    function submitForm(formId, url) {
        let formData = {};
        let t = $("#" + formId).serializeArray();
        $.each(t, function () {
            formData[this.name] = this.value;
        });

        // Simple validation
        let isValid = true;
        $("#" + formId + " [required]").each(function () {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            showAlert('请填写所有必填字段', 'danger');
            return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: (res) => {
                if (res.code === 200) {
                    showAlert('提交成功');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(res.msg || '提交失败', 'danger');
                }
            },
            error: () => {
                showAlert('网络错误，请稍后再试', 'danger');
            }
        });
    }

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 状态筛选逻辑
        var currentStatusFilter = 'all';
        $('.filter-status-btn').on('click', function() {
            $('.filter-status-btn').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            performSearch();
        });

        // 超时任务统计逻辑 (30分钟)
        function checkTimeoutTasks() {
            var timeoutCount = 0;
            var now = new Date();
            $('.elapsed-timer').each(function() {
                var $timer = $(this);
                var status = $timer.attr('data-status');
                if (status !== '已完成' && status !== '失败') {
                    var dateString = $timer.attr('data-start').replace(/-/g, '/');
                    var start = new Date(dateString);
                    var diffMinutes = Math.floor((now - start) / (1000 * 60));
                    if (diffMinutes >= 30) {
                        timeoutCount++;
                    }
                }
            });
            
            var $badge = $('#timeoutCountBadge');
            if (timeoutCount > 0) {
                $badge.text(timeoutCount).show();
            } else {
                $badge.hide();
            }
        }
        setInterval(checkTimeoutTasks, 10000); // 每10秒检查一次
        checkTimeoutTasks();

        $('.quick-finish-btn').on('click', function () {
            var $row = $(this).closest('tr');
            var taskId = $row.data('task-id');
            var newStatus = $(this).data('status');

            $.ajax({
                url: '/api/updateTaskStatus',
                method: 'POST',
                data: {
                    taskId: taskId,
                    status: newStatus
                },
                success: (res) => {
                    if (res.code === 200) {
                        var $badge = $row.find('.status-badge');
                        $badge.text(newStatus);
                        $badge.removeClass('badge-secondary badge-primary badge-danger badge-success')
                            .addClass('badge-' + statusColorMap[newStatus]);

                        var $timer = $row.find('.elapsed-timer');
                        $timer.attr('data-status', newStatus);
                        $timer.hide();

                        $row.find('.text-center').html('<span class="text-muted small">--</span>');
                        
                        showAlert('任务状态已更新');
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        showAlert(res.msg || '更新失败', 'danger');
                    }
                },
                error: () => {
                    showAlert('网络错误', 'danger');
                }
            });
        });

        // 搜索功能逻辑
        function performSearch() {
            var classQuery = $('#searchClass').val().toLowerCase();
            var studentQuery = $('#searchStudent').val().toLowerCase();

            $('.task-table tbody tr').each(function() {
                var $row = $(this);
                if ($row.find('td').length <= 1) return;

                var className = $row.find('td:nth-child(2)').text().toLowerCase();
                var studentName = $row.find('td:nth-child(3)').text().toLowerCase();
                var status = $row.find('.status-badge').text().trim();

                var classMatch = className.indexOf(classQuery) > -1;
                var studentMatch = studentName.indexOf(studentQuery) > -1;
                var statusMatch = (currentStatusFilter === 'all') || (status === currentStatusFilter);

                if (classMatch && studentMatch && statusMatch) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        }

        $('#searchClass, #searchStudent').on('keyup', performSearch);

        // 新增任务表单交互逻辑
        // 班级选择
        $('.class-select-btn').on('click', function() {
            $('.class-select-btn').removeClass('active');
            $(this).addClass('active');
            var classId = $(this).data('id');
            $('#selectedClassId').val(classId);
            
            // 联动学生
            var $studentGroup = $('#studentButtonGroup');
            $studentGroup.empty();
            $('#selectedStudentId').val(''); // 重置已选学生

            var filteredStudents = allStudents.filter(function(s) {
                return s.classId == classId;
            });

            if (filteredStudents.length > 0) {
                filteredStudents.forEach(function(s) {
                    $studentGroup.append('<button type="button" class="btn btn-sm btn-selectable student-select-btn" data-id="' + s.id + '">' + s.name + '</button>');
                });
            } else {
                $studentGroup.append('<div class="text-muted small py-1">该班级暂无学生</div>');
            }
        });

        // 学生选择 (动态委派)
        $('#studentButtonGroup').on('click', '.student-select-btn', function() {
            $('.student-select-btn').removeClass('active');
            $(this).addClass('active');
            $('#selectedStudentId').val($(this).data('id'));
        });

        // 类型选择
        $('.type-select-btn').on('click', function() {
            $('.type-select-btn').removeClass('active');
            $(this).addClass('active');
            $('#selectedType').val($(this).data('id'));
        });

        $('#saveClassBtn').click(function () {
            submitForm('addClassForm', '/api/addClass');
        });

        $('#saveStudentBtn').click(function () {
            submitForm('addStudentForm', '/api/addStudent');
        });

        $('#saveTaskBtn').click(function () {
            // 校验表单
            if (!$('#selectedClassId').val()) {
                showAlert('请选择班级', 'danger');
                return;
            }
            if (!$('#selectedStudentId').val()) {
                showAlert('请选择学生', 'danger');
                return;
            }
            if (!$('#addTaskForm textarea[name="taskNo"]').val().trim()) {
                showAlert('请输入任务编号', 'danger');
                return;
            }
            submitForm('addTaskForm', '/api/addTask');
        });

        // 加载战报逻辑
        $('#btnShowStats').click(function() {
            var $section = $('#statsSection');
            var $content = $('#statsContent');
            
            $section.show();
            $content.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-info" role="status"></div></div>');

            $.ajax({
                url: '/api/getTodayStats',
                method: 'GET',
                success: function(res) {
                    if (res.code === 200 && res.data && Object.keys(res.data).length > 0) {
                        var html = '';
                        $.each(res.data, function(className, students) {
                            if (!students || students.length === 0) return;
                            html += '<div class="mb-3 border-bottom pb-2 last-child-border-0">';
                            html += '<div class="text-muted small font-weight-bold mb-2">' + className + '</div>';
                            html += '<div class="d-flex flex-wrap">';
                            $.each(students, function(idx, stat) {
                                html += '<div class="mr-3 mb-2">';
                                html += '<span class="badge badge-light border px-2 py-1">';
                                html += '<strong>' + stat.student_name + '</strong> ';
                                html += 'H: <span class="text-primary">' + stat.type1_count + '</span> ';
                                html += 'X: <span class="text-success">' + stat.type2_count + '</span>';
                                html += '</span></div>';
                            });
                            html += '</div></div>';
                        });
                        
                        if (html === '') {
                            $content.html('<div class="text-center text-muted py-2">今日暂无完成记录</div>');
                        } else {
                            $content.html(html);
                        }
                    } else {
                        $content.html('<div class="text-center text-muted py-2">今日暂无完成记录</div>');
                    }
                },
                error: function() {
                    $content.html('<div class="text-center text-danger py-2">加载失败，请重试</div>');
                }
            });
        });

        // 班级学生联动逻辑
        $('#addTaskForm select[name="classId"]').on('change', function() {
            var classId = $(this).val();
            var $studentSelect = $('#addTaskForm select[name="studentId"]');
            
            $studentSelect.empty();
            
            if (!classId) {
                $studentSelect.append('<option value="">请先选择班级</option>');
                return;
            }
            
            var filteredStudents = allStudents.filter(function(s) {
                return s.classId == classId;
            });
            
            if (filteredStudents.length > 0) {
                filteredStudents.forEach(function(s) {
                    $studentSelect.append('<option value="' + s.id + '">' + s.name + '</option>');
                });
            } else {
                $studentSelect.append('<option value="">该班级暂无学生</option>');
            }
        });

        // 初始触发一次
        $('#addTaskForm select[name="classId"]').trigger('change');
    });

    updateElapsedTimers();
    setInterval(updateElapsedTimers, 1000);
</script>
</body>
</html>