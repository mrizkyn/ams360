<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Competency;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;

class CompetencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.competence.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.competence.create');
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
                'name' => 'required|unique:mysql2.competencies',
            ],
            [
                'name.required' => 'Harap isi field nama kompetensi',
                'name.unique' => 'Kompetensi sudah ada'
            ]
        );

        $compentecy = new Competency;
        $compentecy->name = $request->name;
        $compentecy->save();

        return redirect('db-assessment/competencies');
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
        $competency = Competency::find($id);
        return view('db_assessment.competence.edit', compact('competency'));
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
                'name' => 'required|unique:mysql2.competencies,name,'.$id,
            ],
            [
                // 'name.required' => 'Harap isi field nama kompetensi',
                'name.unique' => 'Kompetensi sudah ada'
            ]
        );

        $compentecy = Competency::find($id);
        $compentecy->name = $request->name;
        $compentecy->save();

        return redirect('db-assessment/competencies');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $compentecy = Competency::find($id);
        $compentecy->delete();

        return redirect('db-assessment/competencies');
    }

    public function competencyData()
    {
            return DataTables::of(Competency::get())
            ->addIndexColumn()
            ->addColumn('action', function($compentecy){
                $csrf = csrf_token();
                return '
                <a href="/db-assessment/competencies/' . $compentecy->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>
                ';
            })
            ->make(true);
    }
}
