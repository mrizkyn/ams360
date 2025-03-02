<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectParticipantResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('project_participant_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('project_participant_id');
            $table->bigInteger('key_behavior_id');
            $table->string('type');
            $table->double('value');
            $table->double('gap');
            $table->double('loa')->nullable();
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
        Schema::dropIfExists('project_participant_results');
    }
}
