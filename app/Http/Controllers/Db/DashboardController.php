<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(){
        // $projects = Project::with('company')
        // ->with('targetJob')->get();

        return view('db_assessment.dasboard.index');
    }

    public function projectData(){
        return Datatables::of(Project::with('company', 'targetJob')->get([
            env("DB_DATABASE2", "db_assessment").'.projects.*'
        ]))
        ->addIndexColumn()
        ->make(true);
    }
}
