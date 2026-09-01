<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add columns without 'after' — Laravel will place them at the end (safe)
            $table->string('webhook_token')->nullable();
            $table->text('webhook_notes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['webhook_token', 'webhook_notes']);
        });
    }
};