<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\Competency;
use App\Models\Assessment\KeyBehavior;

class CompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $competencies = Competency::orderBy('name' , 'ASC')->get();

        return view('assessment.competence.index' , compact('competencies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.competence.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate =$this->validate($request , [
            'name' => 'required|unique:competencies' ,
            'definition' => 'required'
        ], [
            'name.required' => 'Kompetensi tidak boleh kosong',
            'name.unique' => 'Kompetensi sudah ada',
            'definition.required' => 'Definisi tidak boleh kosong'
        ]);

        $competence = new Competency;
        $competence->name = $request->name;
        $competence->definition = $request->definition;
        $competence->save();

        return redirect('assessment/competencies');
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
        $competence = Competency::find($id);
        $name = $competence->name;
        $definition = $competence->definition;

        return view('assessment.competence.edit' , [ 'id' => $id , 'name' => $name , 'definition' => $definition]);
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
        $validate =$this->validate($request , [
            'name' => 'required|unique:competencies,name,'. $id,
            'definition' => 'required'
        ], [
            'name.required' => 'Kompetensi tidak boleh kosong',
            'name.unique' => 'Kompetensi sudah ada',
            'definition.required' => 'Definisi tidak boleh kosong'
        ]);

        $competence = Competency::find($id);
        $competence->name = $request->name;
        $competence->definition = $request->definition;
        $competence->save();

        return redirect('assessment/competencies');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $competence = Competency::find($id);
        $competence->delete();

        $behavior = KeyBehavior::where('competence_id' , $id)->delete();

        return redirect('assessment/competencies');
    }

    public function definitionData($id){
        $competence = Competency::find($id);

        return \response()->json([
            'definition' => $competence->definition
        ]);
    }
}
