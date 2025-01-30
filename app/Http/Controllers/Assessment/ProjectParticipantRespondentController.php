<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\ProjectParticipantRepondentAnswer;
use Illuminate\Http\Request;

class ProjectParticipantRespondentController extends Controller
{
    public function show($id){
        $projectParticipantRespondentAnswers = ProjectParticipantRepondentAnswer::where('project_participant_repondent_id', $id)->get();

        return view('assessment/project_participant_respondent/show', [
            'projectParticipantRespondentAnswers' => $projectParticipantRespondentAnswers
        ]);
    }

    public function edit($id){
        $projectParticipantRespondentAnswers = ProjectParticipantRepondentAnswer::where('project_participant_repondent_id', $id)->get();

        return view('assessment/project_participant_respondent/edit', [
            'projectParticipantRespondentAnswers' => $projectParticipantRespondentAnswers
        ]);
    }

    public function update(Request $request, $id){
        $projectParticipantRepondentAnswers = ProjectParticipantRepondentAnswer::where('project_participant_repondent_id', $id)->get();
        $answers = $request->answers;

        for ($i=0; $i < count($projectParticipantRepondentAnswers) - 1; $i++) {
            $projectParticipantRepondentAnswers[$i]->answer = $answers[$i];
            $projectParticipantRepondentAnswers[$i]->save();
        }

        return redirect('/assessment/project-participants/'.$projectParticipantRepondentAnswers[$i]->projectParticipantRepondent->project_participant_id);
    }
}
