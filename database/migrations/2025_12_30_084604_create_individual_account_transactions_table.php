<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndividualAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individual_account_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('individual_account_id')
                ->nullable()
                ->constrained('individual_accounts')
                ->nullOnDelete();

            $table->enum('debit_credit', ['debit', 'credit']);
            $table->date('transaction_date');
            $table->decimal('transaction_amount', 15, 2);
            $table->text('transaction_description')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('double_entry')->default(false);

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->foreignId('edited_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('individual_account_transactions');
    }
}
