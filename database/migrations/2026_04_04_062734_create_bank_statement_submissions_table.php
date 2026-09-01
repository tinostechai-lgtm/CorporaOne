<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankStatementSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('bank_statement_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->json('transactions')->nullable();
            $table->json('extraction_confidence')->nullable();
            $table->string('original_file_name');
            $table->string('stored_file_name');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_statement_submissions');
    }
}