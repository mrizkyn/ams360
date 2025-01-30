<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\ProjectParticipant;
use App\Models\Assessment\ProjectParticipantRepondent;
use App\Models\Assessment\ProjectParticipantStatus;
use Illuminate\Http\Request;

class ProjectParticipantController extends Controller
{
    public function show($id){
        $projectParticipant = ProjectParticipant::find($id);
        $projectParticipantRespondents = ProjectParticipantRepondent::where('project_participant_id', $projectParticipant->id)->get();

        return view('assessment/project_participant/show', [
            'projectParticipant' => $projectParticipant,
            'projectParticipantRespondents' => $projectParticipantRespondents
        ]);
    }

    public function edit($id){
        return view('assessment/project_participant/edit', [
            'projectParticipant' => ProjectParticipant::find($id)
        ]);
    }

    public function update(Request $request, $id){
        $projectParticipant = ProjectParticipant::find($id);
        $projectParticipant->superior_number = $request->superior_number;
        $projectParticipant->collegue_number = $request->collegue_number;
        $projectParticipant->subordinate_number = $request->subordinate_number;
        $projectParticipant->save();

        $projectParticipantStatuses = ProjectParticipantStatus::where('project_participant_id', $id)->get();
        $projectParticipantStatuses[0]->difference = $this->countDifference(1, $id, 'Diri Sendiri');
        $projectParticipantStatuses[1]->difference = $this->countDifference($request->superior_number, $id, 'Atasan');
        $projectParticipantStatuses[2]->difference = $this->countDifference($request->collegue_number, $id, 'Rekan Kerja');
        $projectParticipantStatuses[3]->difference = $this->countDifference($request->subordinate_number, $id, 'Bawahan');

        foreach ($projectParticipantStatuses as $projectParticipantStatus) {
            $projectParticipantStatus->save();
        }

        AssessmentController::changeProjectParticipantStatus($id);
        AssessmentController::changeProjectStatus($projectParticipant->project_id);

        return redirect('/assessment/projects/'.$projectParticipant->project_id);
    }

    protected function countDifference($total, $id, $type){
        return $total - ProjectParticipantRepondent::where(['project_participant_id' => $id, 'type' => $type])->count();
    }
}
