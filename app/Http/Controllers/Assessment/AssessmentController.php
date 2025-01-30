<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssesionResource;
use App\Http\Resources\AssessmentQuestion;
use App\Models\Assessment\Project;
use App\Models\Assessment\ProjectParticipant;
use App\Models\Assessment\ProjectParticipantRepondent;
use App\Models\Assessment\ProjectParticipantRepondentAnswer;
use App\Models\Assessment\ProjectParticipantStatus;
use App\Models\Assessment\ProjectQuestion;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index(){
        return view('assessment.assessment.index', [
            'projects' => Project::where('status', '!=', 'Selesai')->orderBy('name')->get()
        ]);
    }

    public function create(Request $request){
        $projectParticipantRespondent = ProjectParticipantRepondent::create([
            'project_participant_id' => $request->project_participant_id,
            'respondent_name' =>$request->rater_name,
            'type' => $request->rater_type
            ]);

        $answers = json_decode($request->answers);
        foreach($answers as $answer){
            ProjectParticipantRepondentAnswer::create([
                'project_participant_repondent_id' => $projectParticipantRespondent->id,
                'key_behavior_id' => $answer->id,
                'type' => $answer->type,
                'answer' => $answer->value
                ]);
        }

        $this->changeProjectParticipantStatusDifference($request->project_participant_id, $request->rater_type);
        $this->changeProjectParticipantStatus($request->project_participant_id);
        $this->changeProjectStatus($request->project_id);

        return redirect(url('assessment/assessments'));
    }

    protected function changeProjectParticipantStatusDifference($project_participant_id, $rater_type){
        $projectParticipantStatus = ProjectParticipantStatus::where([
            'project_participant_id' => $project_participant_id,
            'type' => $rater_type
            ])->first();
        $projectParticipantStatus->difference = $projectParticipantStatus->difference - 1;
        $projectParticipantStatus->save();
    }

    public static function changeProjectParticipantStatus($project_participant_id){
        $projectParticipantStatusDifference = ProjectParticipantStatus::where([
            'project_participant_id' => $project_participant_id,
            ])->average('difference');
        if($projectParticipantStatusDifference  == 0){
            $projectParticipant = ProjectParticipant::find($project_participant_id);
            $projectParticipant->status = 'Selesai';
            $projectParticipant->save();
        }
    }

    public static function changeProjectStatus($project_id){
        $projectParticipantCount = ProjectParticipant::where(['project_id' => $project_id, 'status' => 'Belum Selesai'])->count();
        $project = Project::find($project_id);
        if($projectParticipantCount == 0){
            $project->status = 'Selesai';
            $project->save();
        }else if($project->status == 'Draft'){
            $project->status = 'Dalam Proses';
            $project->save();
        }
    }

    public function getAssesionByProjectName($projectId){
        return AssesionResource::collection(ProjectParticipant::where(['project_id' => $projectId, 'status' => 'Belum Selesai'])->get())->sortBy('name');
    }

    public function getAssesionTypeByProjectParticipantId($id){
        return ProjectParticipantStatus::where(['project_participant_id' => $id, ['difference', '>', 0]])->get();
    }

    public function getQuestionByProjectName($projectId){
        return AssessmentQuestion::collection(ProjectQuestion::where('project_id', $projectId)->get());
    }
}
