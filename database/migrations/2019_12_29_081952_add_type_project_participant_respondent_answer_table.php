<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeProjectParticipantRespondentAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->table('project_participant_repondent_answers', function($table){
            $table->string('type')->after('key_behavior_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->table('project_participant_repondent_answers', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
