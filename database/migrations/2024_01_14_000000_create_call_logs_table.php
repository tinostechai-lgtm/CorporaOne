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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_number')->nullable();
            $table->string('destination_number')->nullable();
            $table->string('display_number')->nullable();
            $table->string('call_type')->nullable();
            $table->string('direction')->nullable();
            $table->string('leg')->nullable();
            $table->string('status')->nullable();
            $table->string('agent_status')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration')->default(0);
            $table->string('recording_url')->nullable();
            $table->string('dtmf')->nullable();
            $table->string('event_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('call_id')->nullable();
            $table->string('data_source')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};

