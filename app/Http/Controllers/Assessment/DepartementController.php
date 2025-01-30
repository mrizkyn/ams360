<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

use App\Models\Assessment\Departement;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.departement.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.departement.create');
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
            'name' => 'required|unique:departements',
        ], [
            'name.required' => 'Departemen tidak boleh kosong',
            'name.unique' => 'Departemen sudah ada',
        ]);

        $departements = new Departement;
        $departements->name = $request->name;
        $departements->save();

        return redirect('/assessment/departements');
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
        $departements = Departement::find($id);
        $id = $departements->id;
        $departement = $departements->name;

        return view('assessment.departement.edit' , [ 'id' => $id , 'departement' => $departement]);
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
            'name' => 'required|unique:departements,name,'.$id,
        ], [
            'name.required' => 'Departemen tidak boleh kosong',
            'name.unique' => 'Departemen sudah ada',
        ]);

        $departement = Departement::find($id);
        $departement->name = $request->name;
        $departement->save();

        return redirect('assessment/departements');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $departement = Departement::find($id);
        $departement->delete();

        return redirect('assessment/departements');
    }


    public function departementData(){
        return DataTables::of(Departement::get())
        ->addIndexColumn()
        ->addColumn('action', function($departement){
            $csrf = csrf_token();
            return '
                <a href="/assessment/departements/'.$departement->id.'/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
        })
        ->make(true);
    }
}
