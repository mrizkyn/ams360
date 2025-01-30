<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\Division;
use Yajra\DataTables\Facades\DataTables;
use DB;

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
        return view('assessment.division.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.division.create');
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
            'name' => 'required|unique:divisions',
        ], [
            'name.unique' => 'Divisi sudah ada',
        ]);

        $division = Division::create([
            'name' => $request->name
        ]);

        return redirect('assessment/divisions');
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

        return view('assessment.division.edit',compact('division'));
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
            'name' => 'required|unique:divisions,name,'.$id
        ], [
            'name.unique' => 'Divisi sudah ada',
        ]);

        $division = Division::where('id',$id)->update([
            'name' => $request->name
        ]);

        return redirect('assessment/divisions');
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

        return redirect('assessment/divisions');
    }

    public function divisionData(){
        return DataTables::of(Division::get())
        ->addIndexColumn()
        ->addColumn('action', function($division){
            $csrf = csrf_token();
            return '
                <a href="/assessment/divisions/'.$division->id.'/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
        })
        ->make(true);
    }
}
