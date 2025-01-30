<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Assessment\BusinessField;


class BusinessFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.business_field.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.business_field.create');
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
            'name' => 'required|unique:business_fields',
        ], [
            'name.required' => 'Bidang Usaha tidak boleh kosong',
            'name.unique' => 'Bidang Usaha sudah ada',
        ]);

        $business = new BusinessField;
        $business->name = $request->name;
        $business->save();

        return redirect('assessment/business-fields');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business = BusinessField::find($id);
        $id = $business->id;
        $name = $business->name;
        return view('assessment.business_field.edit' , compact('id' , 'name'));
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
            'name' => 'required|unique:business_fields,name,'.$id,
        ], [
            'name.required' => 'Bidang Usaha tidak boleh kosong',
            'name.unique' => 'Bidang Usaha sudah ada',
        ]);

        $business = BusinessField::find($id);
        $business->name = $request->name ;
        $business->save();
        return redirect('assessment/business-fields');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $business = BusinessField::find($id);
        $business->delete();

        return redirect('assessment/business-fields');
    }

    public function businessFieldData(){
        return DataTables::of(BusinessField::get())
        ->addIndexColumn()
        ->addColumn('action', function($business){
            $csrf = csrf_token();
            return '
                <a href="/assessment/business-fields/'.$business->id.'/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
        })
        ->make(true);
    }
}
