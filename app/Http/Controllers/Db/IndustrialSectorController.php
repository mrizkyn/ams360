<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DB\IndustrialSector;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IndustrialSectorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.business_fields.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.business_fields.create');
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
                'name' => 'required|unique:mysql2.industrial_sectors,name'
            ],
            [
                'name.required' => 'Harap isi field Nama Bisang Usaha',
                'name.unique' => 'Bidang Usaha sudah ada',
            ]
        );

        $business_fields = new IndustrialSector;
        $business_fields->name = $request->name;
        $business_fields->save();

        return redirect('db-assessment/business-fields');
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
        $business_fields = IndustrialSector::find($id);

        return view('db_assessment.business_fields.edit' , compact('business_fields'));
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
                'name' => 'required|unique:mysql2.industrial_sectors,name,'.$id,
            ],
            [
                'name.required' => 'Harap isi field Nama Bisang Usaha',
                'name.unique' => 'Bidang Usaha sudah ada',
            ]
        );

        $business_fields = IndustrialSector::find($id);
        $business_fields->name = $request->name;
        $business_fields->save();

        return redirect('db-assessment/business-fields');
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

    public function sectorData()
    {
            return DataTables::of(IndustrialSector::get())
            ->addIndexColumn()
            ->addColumn('action', function($sector){
                $csrf = csrf_token();
                return '
                <a href="/db-assessment/business-fields/' . $sector->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>
                ';
            })
            ->make(true);
    }
}
