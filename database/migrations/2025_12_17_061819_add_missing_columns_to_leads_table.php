<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('leads', function (Blueprint $table) {
        if (!Schema::hasColumn('leads', 'user_id')) {
            $table->unsignedBigInteger('user_id')->nullable()->after('subject');
        }
        if (!Schema::hasColumn('leads', 'date')) {
            $table->date('date')->nullable()->after('created_by');
        }
        if (!Schema::hasColumn('leads', 'created_by')) {
            $table->unsignedBigInteger('created_by')->after('stage_id');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};
