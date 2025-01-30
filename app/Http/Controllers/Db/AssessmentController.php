<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DB\Competency;
use App\Models\DB\Company;
use App\Models\DB\TargetJob;
use App\Models\DB\TargetJobCompetency;
use App\Models\DB\Participant;
use App\Models\DB\Project;
use App\Models\DB\ProjectCompetenceStandart;
use App\Models\DB\ProjectParticipant;
use App\Models\DB\ProjectParticipantValue;
use App\Models\DB\Recommendation;
use App\Models\DB\ItemRecommendation;



class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::all();
        $jobs = TargetJob::all();
        $recommendations = Recommendation::all();

        return view('db_assessment.assessment.index' , compact('jobs' , 'companies','recommendations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;
        $standarsIndex = 0;

        $project = new Project;
        $project->name = $request->name ;
        $project->company_id = $request->company;
        $project->target_job_id = $request->job;
        $project->start_date = $request->start_date;
        $project->end_date = $request->last_date;
        $project->recommendation_id = $request->recomendasi;
        $project->save();

        for ($index=0; $index < count($request->competence_id) ; $index++) {
            $project_competence_standar = new ProjectCompetenceStandart;
            $project_competence_standar->project_id = $project->id;
            $project_competence_standar->competence_id = $request->competence_id[$index];
            $project_competence_standar->value = $request->standar[$index];
            $project_competence_standar->save();
        }

        for ($index=0; $index < count($request->participants) ; $index++) {
            $project_participant = new ProjectParticipant;
            $project_participant->project_id = $project->id;
            $project_participant->participant_id = $request->participants[$index];
            $project_participant->item_recommendation_id = $request->recommendations[$index];
            $project_participant->save();

            for ($index2=0; $index2 < count($request->competence_id) ; $index2++) {
                    $project_participant_value = new ProjectParticipantValue;
                    $project_participant_value->competency_id = $request->competence_id[$index2];
                    $project_participant_value->project_participant_id = $project_participant->id;
                    $project_participant_value->value = $request->standars[$standarsIndex];
                    $project_participant_value->save();
                    $standarsIndex++;
            }
        }

        return redirect('db-assessment/assessments');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function competenceData(Request $request){
        $competencies = TargetJobCompetency::with('competency')->where('target_job_id' , $request->job_id)->get();
        return Response()->json($competencies);
    }

    public function participantsData(Request $request){
        $participants = Participant::where("company_id" , $request->id)->get();
        $itemRecomendations = ItemRecommendation::with('recommendation')->where('recommendation_id' , $request->recomendasi_id)->get();
        return Response()->json(['participant' => $participants, 'recomendasi' => $itemRecomendations]);
    }

    public function companyData(Request $request){
        $company = Company::find($request->id);
        return $company;
    }
}
