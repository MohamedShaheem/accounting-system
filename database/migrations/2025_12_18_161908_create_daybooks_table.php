<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaybooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daybooks', function (Blueprint $table) {
            $table->id();
            $table->enum('debit_credit', ['debit', 'credit']);
            $table->date('transaction_date');
            $table->decimal('transaction_amount', 15, 2);
            $table->text('transaction_description')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('sync')->default(false);
            // $table->string('ac_invoice_no')->nullable(); // i am going to change this to another tbl
            // Foreign keys to users table
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
        Schema::dropIfExists('daybooks');
    }
}
