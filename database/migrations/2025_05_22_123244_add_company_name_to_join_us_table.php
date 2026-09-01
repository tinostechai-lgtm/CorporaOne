
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('join_us', function (Blueprint $table) {
            if (!Schema::hasColumn('join_us', 'company_name')) {
                $table->string('company_name')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('join_us', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
};
