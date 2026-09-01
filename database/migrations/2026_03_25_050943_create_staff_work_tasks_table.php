<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffWorkTasksTable extends Migration
{
    public function up()
    {
        Schema::create('staff_work_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Employee assignment
            $table->unsignedBigInteger('employee_id'); // Employee ID
            $table->unsignedBigInteger('assigned_by'); // Manager/Admin ID
            
            // References
            $table->unsignedBigInteger('project_id')->nullable(); // Link to existing project if needed
            $table->unsignedBigInteger('department_id')->nullable();
            
            // Dates
            $table->date('start_date');
            $table->date('due_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Status & Priority
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->integer('progress')->default(0);
            
            // Notes
            $table->text('remarks')->nullable();
            $table->text('completion_notes')->nullable();
            
            // Completion
            $table->date('completed_at')->nullable();
            $table->boolean('is_approved')->default(false);
            
            // Audit
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys - adjust table names based on your existing tables
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['status', 'priority']);
            $table->index(['employee_id', 'due_date']);
            $table->index(['start_date', 'due_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_work_tasks');
    }
}