<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeConstraintCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('companies', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            $table->string('pic_name')->nullable()->change();
            $table->string('pic_phone')->nullable()->change();
            $table->string('pic_mail')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
