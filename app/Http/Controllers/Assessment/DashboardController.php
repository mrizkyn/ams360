<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Company;
use App\Models\Assessment\Project;
use App\Models\Assessment\ProjectParticipant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('assessment.dashboard.index', compact('companies'));
    }

    public function getProjectsByCompanyId($companyId)
    {
        $projects = Project::where('company_id', $companyId)->get();
        return $projects;
    }

    public function getParticipantByProjectId($projectId)
    {
        $participant = ProjectParticipant::with('participant')
            ->with('projectParticipantStatus')
            ->where('project_id', $projectId)
            ->get();

        return $participant;
    }
}
