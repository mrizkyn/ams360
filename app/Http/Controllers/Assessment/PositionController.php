<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\Position;
use Yajra\DataTables\Facades\DataTables;
use DB;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.position.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.position.create');
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
            'name' => 'required|unique:positions,name'
        ],[
            'name.unique' => 'Jabatan sudah ada'
        ]);

        $position = Position::create([
            'name' => $request->name
        ]);

        return redirect('assessment/positions');
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
        $position = Position::find($id);

        return view('assessment.position.edit',compact('position'));
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
            'name' => 'required|unique:positions,name,'.$id,
        ], [
            'name.unique' => 'Jabatan sudah ada',
        ]);

        $position = Position::where('id',$id)->update([
            'name' => $request->name
        ]);

        return redirect('assessment/positions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $position = Position::destroy($id);

        return redirect('assessment/positions');
    }

    public function positionData(){
        return DataTables::of(Position::get())
        ->addIndexColumn()
        ->addColumn('action', function($position){
            $csrf = csrf_token();
            return '
                <a href="/assessment/positions/'.$position->id.'/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
        })
        ->make(true);
    }
}
