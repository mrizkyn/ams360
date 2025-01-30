<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Competency;
use App\Models\Assessment\DevelopmentSource;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;

class DevelopmentSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.development.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $competencies = Competency::all();
        return view('assessment.development.create', compact('competencies'));
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
                'competence_id' => 'required | unique:development_sources',
                'description' => 'required',
            ],
            [
                'competence_id.required' => 'Harap pilih item pada field Kompetensi',
                'competence_id.unique' => 'Saran Pengembangan sudah ada ',
                'description.required' => 'Harap isi field Saran Pengembangan',
            ]
        );
        $developments = new DevelopmentSource;
        $developments->competence_id = $request->competence_id;
        $developments->description = $request->description;
        $developments->save();

        return redirect('/assessment/developments-source/');
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
        $development = DevelopmentSource::find($id);
        return view('assessment.development.edit', compact('competencies', 'development'));
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
                'description' => 'required',
            ],
            [
                'description.required' => 'Harap isi field Sumber Pengembangan',
            ]
        );
        
        $development = DevelopmentSource::find($id);
        $development->competence_id = $request->competency;
        $development->description = $request->description;
        $development->save();

        return redirect('/assessment/developments-source/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $development = DevelopmentSource::find($id);
        $development->delete();

        return redirect('/assessment/developments-source/');
    }

    public function developmentData()
    {
        return DataTables::of(DevelopmentSource::with('competence')->get())
        ->addIndexColumn()
        ->addColumn('action', function($development){
            $csrf = csrf_token();
            return '
            <a href="/assessment/developments-source/' . $development->id . '/edit" class="btn btn-sm btn-default">
                <i class="far fa-edit"></i>Ubah
            </a>

            ';
        })
        ->make(true);
    }
}
