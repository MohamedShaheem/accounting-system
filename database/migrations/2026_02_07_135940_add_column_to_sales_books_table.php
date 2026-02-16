<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToSalesBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_books', function (Blueprint $table) {
            $table->decimal('silver_weight', 15, 3)->default(0)->after('gold_weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_books', function (Blueprint $table) {
            $table->dropColumn('silver_weight');
        });
    }
}
