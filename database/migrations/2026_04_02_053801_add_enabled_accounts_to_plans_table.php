<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnabledAccountsToPlansTable extends Migration
{
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('enabled_accounts')->nullable()->after('storage_limit');
        });
    }

    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('enabled_accounts');
        });
    }
}