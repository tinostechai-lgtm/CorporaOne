<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReconciliationFieldsToBankStatementSubmissions extends Migration
{
    public function up()
    {
        Schema::table('bank_statement_submissions', function (Blueprint $table) {
            $table->string('reconciliation_status')->nullable()->after('extraction_confidence');
            $table->timestamp('reconciled_at')->nullable()->after('reconciliation_status');
            $table->json('reconciled_transactions')->nullable()->after('reconciled_at');
        });
    }

    public function down()
    {
        Schema::table('bank_statement_submissions', function (Blueprint $table) {
            $table->dropColumn(['reconciliation_status', 'reconciled_at', 'reconciled_transactions']);
        });
    }
}