<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_books', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('invoice_type');
            $table->string('invoice_no')->nullable();
            $table->string('name')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('gold_weight', 15, 3)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            // Foreign keys to users table
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->foreignId('edited_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('sync')->default(false);
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
        Schema::dropIfExists('sales_books');
    }
}
