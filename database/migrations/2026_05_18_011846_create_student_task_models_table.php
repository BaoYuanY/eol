<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('p_student_task', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('studentId')->comment('学生ID');
            $table->string('taskNo')->comment('任务编号');
            $table->string('title')->nullable()->comment('任务标题');
            $table->tinyInteger('type')->default(1)->comment('任务类型: 1-电话, 2-短信');
            $table->tinyInteger('status')->default(0)->comment('任务状态: 0-未开始, 1-进行中, 2-已完成');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_student_task');
    }
};
