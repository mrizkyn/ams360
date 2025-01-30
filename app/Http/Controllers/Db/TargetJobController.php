<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DB\TargetJob;
use App\Models\DB\TargetJobCompetency;
use App\Models\DB\Competency;
use DB;
use Yajra\DataTables\Facades\DataTables;

class TargetJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.job_target.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $competencies = Competency::orderBy('name', 'ASC')->get();

        return view('db_assessment.job_target.create', compact('competencies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'namaTargetJob' => 'required|unique:mysql2.target_jobs,name'
        ], [
            'namaTargetJob.required' => 'Harap isi field nama target job',
            'namaTargetJob.unique' => 'Target job sudah ada'
        ]);

        $competency = $request->selected_competencies;
        if (empty($request->selected_competencies)) {
            return redirect('db-assessment/targetJobs/create')->with('kompetensi', 'Kompetensi Belum dipilih.');
        } else {
            $targetJob = TargetJob::create([
                'name' => $request->namaTargetJob,
                'total' => count($request->selected_competencies)
            ]);

            for ($i = 0; $i < count($competency); $i++) {
                $targetJobCompetency = TargetJobCompetency::create([
                    'target_job_id' => $targetJob->id,
                    'competency_id' => $competency[$i],
                ]);
            }

            return redirect('db-assessment/targetJobs');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $targetJob = TargetJob::find($id);
        $targetJobCompetencies = TargetJobCompetency::where('target_job_id', $id)->pluck('competency_id')->toArray();
        $competencies = Competency::all();

        return view('db_assessment.job_target.show', compact('targetJob', 'targetJobCompetencies', 'competencies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $targetJob = TargetJob::find($id);
        $targetJobCompetencies = TargetJobCompetency::where('target_job_id', $id)->pluck('competency_id')->toArray();
        $competencies = Competency::orderBy('name', 'ASC')->get();

        return view('db_assessment.job_target.edit', compact('targetJob', 'targetJobCompetencies', 'competencies'));
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
        $validatedData = $request->validate([
            'namaTargetJob' => 'required|unique:mysql2.target_jobs,name,' . $id . ''
        ], [
            'namaTargetJob.required' => 'Harap isi field nama target job',
            'namaTargetJob.unique' => 'Target job sudah ada'
        ]);

        $competency = $request->selected_competencies;
        if (empty($request->selected_competencies)) {
            return redirect('db-assessment/targetJobs/' . $id . '/edit')->with('kompetensi', 'Kompetensi Belum dipilih.');
        } else {
            $targetJob = TargetJob::where('id', $id)->update([
                'name' => $request->namaTargetJob,
                'total' => count($request->selected_competencies)
            ]);

            $deleteTargetJobCompetencies = TargetJobCompetency::where('target_job_id', $id)->delete();

            for ($i = 0; $i < count($competency); $i++) {
                $targetJobCompetency = TargetJobCompetency::create([
                    'target_job_id' => $id,
                    'competency_id' => $competency[$i],
                ]);
            }

            return redirect('db-assessment/targetJobs');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $targetJobCompetencies = TargetJobCompetency::where('target_job_id', $id)->delete();

        $targetJob = TargetJob::destroy($id);

        return redirect('db-assessment/targetJobs');
    }

    public function targetJobData()
    {
        return DataTables::of(TargetJob::get())
            ->addIndexColumn()
            ->addColumn('action', function ($targetjob) {
                $csrf = csrf_token();
                return '
                <a href="/db-assessment/targetJobs/' . $targetjob->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>
                <a href="/db-assessment/targetJobs/' . $targetjob->id . '" class="btn btn-sm btn-default">
                    <i class="far fa-eye"></i>Detail
                </a>
                ';
            })
            ->make(true);
    }
}
