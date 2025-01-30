<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Validation\Rule;
use App\Models\Assessment\KeyBehavior;
use App\Models\Assessment\Competency;

class BehaviorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        $validatedData = $request->validate([
            'description' => [
                'required',
                'distinct',
                Rule::unique('key_behaviors')->where(function ($query) use ($request){
                return $query->where('competence_id', $request->competence_id);
            })
            ]
        ], [
            'description.required' => 'Perilaku Kunci tidak boleh kosong',
            'description.unique' => 'Perilaku Kunci sudah ada',
            'description.distinct' => 'Perilaku Kunci sudah ada',
        ]);

        for ($index=0; $index < count($request->description) ; $index++) {
            $behavior = new KeyBehavior;
            $behavior->competence_id = $request->competence_id;
            $behavior->description = $request->description[$index];
            $behavior->save();
        }

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
        $competence = Competency::find($id);
        $id = $competence->id;
        $name = $competence->name;
        $definition = $competence->definition;

        return view('assessment.behavior.show' , ['id' => $id , 'name' => $name , 'definition' => $definition]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $behavior = KeyBehavior::find($id);
        $description = $behavior->description;
        $competence_id = $behavior->competence_id;

        $competence = Competency::find($competence_id);
        $name = $competence->name;
        $definition = $competence->definition;
        return view('assessment.behavior.edit' , [
            'id' => $id ,
            'description' => $description ,
            'name' => $name ,
            'definition' => $definition
        ]);
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
            'description' => [
                'required',
                Rule::unique('key_behaviors')->where(function ($query) use ($id){
                return $query->where('competence_id', KeyBehavior::find($id)->competence_id);
            })->ignore($id, 'id')
            ]
        ], [
            'description.required' => 'Perilaku Kunci tidak boleh kosong',
            'description.unique' => 'Perilaku Kunci sudah ada',
        ]);

        $behavior = KeyBehavior::find($id);
        $behavior->description = $request->description;
        $behavior->save();

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
        $behavior = KeyBehavior::find($id);
        $behavior->delete();

        return redirect('assessment/competencies');
    }

    public function behaviorData($id){
        return DataTables::of(KeyBehavior::where('competence_id' , $id)->get())
        ->addIndexColumn()
        ->addColumn('action', function($behavior){
            $csrf = csrf_token();
            return '
            <a href="/assessment/behaviors/'.$behavior->id.'/edit" class="btn btn-sm btn-default">
                <i class="far fa-edit"></i>Ubah
            </a>';
        })
        ->make(true);
    }
}
