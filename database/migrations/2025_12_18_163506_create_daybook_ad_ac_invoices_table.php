<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaybookAdAcInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daybook_ad_ac_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daybook_id')
                ->nullable()
                ->constrained('daybooks')
                ->nullOnDelete();
            $table->string('invoice_no')->nullable();
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
        Schema::dropIfExists('daybook_ad_ac_invoices');
    }
}
