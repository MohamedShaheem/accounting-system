<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')
                ->nullable()
                ->constrained('banks')
                ->nullOnDelete();
                
            $table->enum('debit_credit', ['debit', 'credit']);
            $table->date('transaction_date');
            $table->decimal('transaction_amount', 15, 2);
            $table->text('transaction_description')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->foreignId('edited_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->boolean('sync')->default(false);
            $table->boolean('double_entry')->default(false);

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
        Schema::dropIfExists('bank_transactions');
    }
}
