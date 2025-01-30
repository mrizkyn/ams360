<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Competency;
use App\Models\Assessment\DevelopmentActivity;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;

class DevelopmentActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.activity.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $competencies = Competency::all();
        return view('assessment.activity.create', compact('competencies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'competence_id' => 'required | unique:development_activities',
                'description' => 'required',
            ],
            [
                'competence_id.required' => 'Harap pilih item pada field Kompetensi',
                'competence_id.unique' => 'Aktivitas Pengembangan sudah ada',
                'description.required' => 'Harap isi field Aktivitas Pengembangan',
            ]
        );

        $activities = new DevelopmentActivity;
        $activities->competence_id = $request->competence_id;
        $activities->description = $request->description;
        $activities->save();

        return redirect('/assessment/development-activities/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $competencies = Competency::all();
        $activity = DevelopmentActivity::find($id);
        return view('assessment.activity.edit', compact('competencies', 'activity'));
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
        $validatedData = $request->validate(
            [
                'competence_id' => 'required, | unique:competence_id',
                'description' => 'required',
            ],
            [
                'competence_id.required' => 'Harap pilih item pada field Kompetensi',
                'competence_id.unique' => 'Aktivitas Pengembangan sudah ada',
                'description.required' => 'Harap isi field Aktivitas Pengembangan',
            ]
        );

        $activities = DevelopmentActivity::find($id);
        $activities->competence_id = $request->competence_id;
        $activities->description = $request->description;
        $activities->save();

        return redirect('/assessment/development-activities/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $activities = DevelopmentActivity::find($id);
        $activities->delete();

        return redirect('/assessment/development-activities/');
    }

    public function developmentActivityData()
    {
        DB::statement('set @rownum=0');
        return DataTables::of(DevelopmentActivity::with('competence')->get([
            'development_activities.*',
            DB::raw('@rownum := @rownum + 1 as no')
        ]))
            ->addColumn('action', function ($activity) {
                $csrf = csrf_token();
                return '
                <a href="/assessment/development-activities/' . $activity->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
            })
            ->make(true);
    }
}
