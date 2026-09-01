<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToJoinUsTable extends Migration
{
    public function up()
    {
        Schema::table('join_us', function (Blueprint $table) {
            $table->string('name', 255)->after('id');
            // Add phone and email if missing too
            if (!Schema::hasColumn('join_us', 'phone')) {
                $table->string('phone', 255)->after('name');
            }
            if (!Schema::hasColumn('join_us', 'email')) {
                $table->string('email', 255)->unique()->after('phone');
            }
        });
    }

    public function down()
    {
        Schema::table('join_us', function (Blueprint $table) {
            $table->dropColumn('name');
            // Drop phone and email if added
            if (Schema::hasColumn('join_us', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('join_us', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
}