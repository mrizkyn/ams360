<?php

namespace App\Http\Controllers\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DB\DbPosition;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DbPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.position.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.position.create');
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
            'name' => 'required|unique:mysql2.db_positions,name'
        ],[
            'name.unique' => 'Jabatan sudah ada'
        ]);

        $position = DbPosition::create([
            'name' => $request->name
        ]);

        return redirect('db-assessment/positions');
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
        $position = DbPosition::find($id);

        return view('db_assessment.position.edit',compact('position'));
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
            'name' => 'required|unique:mysql2.db_positions,name,'.$id,
        ], [
            'name.unique' => 'Jabatan sudah ada',
        ]);

        $position = DbPosition::where('id',$id)->update([
            'name' => $request->name
        ]);

        return redirect('db-assessment/positions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function positionData(){
            return DataTables::of(DbPosition::get())
            ->addIndexColumn()
            ->addColumn('action', function($posisiton){
                $csrf = csrf_token();
                return '
                    <a href="/db-assessment/positions/'.$posisiton->id.'/edit" class="btn btn-sm btn-default">
                        <i class="far fa-edit"></i>Ubah
                    </a>

                ';
            })
            ->make(true);
    }
}
