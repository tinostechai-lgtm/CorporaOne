<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            $table->text('tasks_completed');
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->text('challenges')->nullable();
            $table->text('plan_for_tomorrow')->nullable();
            $table->string('attachment')->nullable(); // file path
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('hrm_comment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_reports');
    }
};