<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $divisions = Division::latest()->get();
        return view('db_assessment.division.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.division.create');
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
            'name' => 'required|unique:mysql2.divisions',
        ], [
            'name.unique' => 'Divisi sudah ada',
        ]);

        $division = Division::create([
            'name' => $request->name
        ]);

        return redirect('db-assessment/divisions');
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
        $division = Division::find($id);

        return view('db_assessment.division.edit',compact('division'));
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
            'name' => 'required|unique:mysql2.divisions,name,'.$id
        ], [
            'name.unique' => 'Divisi sudah ada',
        ]);

        $division = Division::where('id',$id)->update([
            'name' => $request->name
        ]);

        return redirect('db-assessment/divisions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $division = Division::destroy($id);

        return redirect('db-assessment/divisions');
    }

    public function divisionData(){
        return DataTables::of(Division::get())
            ->addIndexColumn()
            ->addColumn('action', function($division){
                $csrf = csrf_token();
                return '
                    <a href="/db-assessment/divisions/'.$division->id.'/edit" class="btn btn-sm btn-default">
                        <i class="far fa-edit"></i>Ubah
                    </a>

                ';
            })
            ->make(true);
    }
}
