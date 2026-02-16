<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDaybooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daybooks', function (Blueprint $table) {
            $table->string('account_type')
                ->default('sync_data')
                ->after('sync');

            $table->integer('reference_id')
                ->default(0)
                ->after('account_type');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daybooks', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'reference_id']);
        });
    }

}
