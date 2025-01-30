<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRecommendationInProjectParticipationsDbassessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('project_participations', function (Blueprint $table) {
            $table->dropColumn('recommendation');
            $table->bigInteger('item_recommendation_id')->after('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_participations', function (Blueprint $table) {
            //
        });
    }
}
