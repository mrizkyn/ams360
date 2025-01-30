<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeParticipantIdProjectsParticipantRepondentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->table('project_participant_repondents', function($table)
        {
            $table->bigInteger('participant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->table('project_participant_repondents', function($table)
        {
            $table->dropUnique('project_participant_repondents_participant_id_unique');
        });
    }
}
