<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Company;
use App\Models\DB\IndustrialSector;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.company.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.company.create',[
            'businessFields' => IndustrialSector::orderBy('name')->get()
        ]);
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
                'name' => 'required',
                'business_field' => 'required',
                'address' => 'required',
                'city' => 'required',
                // 'phone' => 'required | max:13',
            ],
            [
                'name.required' => 'Harap isi field nama',
                'business_field.required' => 'Harap pilih field Bidang Industri',
                'address.required' => 'Harap isi field Alamat',
                'city.required' => 'Harap isi field Kota',
                // 'phone.required' => 'Harap isi field No Telepon Perusahaan',
                // 'phone.max' => 'Jumlah digit melebihi dari 13 digit',
            ]
        );

        $companies = new Company;
        $companies->name = $request->name;
        $companies->business_field = $request->business_field;
        $companies->address = $request->address;
        $companies->city = $request->city;
        $companies->phone = $request->phone;
        $companies->pic_name = $request->pic_name;
        $companies->pic_phone = $request->pic_phone;
        $companies->pic_mail = $request->pic_mail;
        $companies->save();

        return redirect('db-assessment/companies');
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
        $company = Company::find($id);
        return view('db_assessment.company.edit', compact('company'), [
            'businessFields' => IndustrialSector::orderBy('name')->get()
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
        $validatedData = $request->validate(
            [
                'name' => 'required',
                'business_field' => 'required',
                'address' => 'required',
                'city' => 'required',
                // 'phone' => 'required | max:13',
            ],
            [
                'name.required' => 'Harap isi field nama',
                'business_field.required' => 'Harap pilih field Bidang Industri',
                'address.required' => 'Harap isi field Alamat',
                'city.required' => 'Harap isi field Kota',
                // 'phone.required' => 'Harap isi field No Telepon Perusahaan',
                // 'phone.max' => 'Jumlah digit melebihi dari 13 digit',
            ]
        );

        $companies = Company::find($id);
        $companies->name = $request->name;
        $companies->business_field = $request->business_field;
        $companies->address = $request->address;
        $companies->city = $request->city;
        $companies->phone = $request->phone;
        $companies->pic_name = $request->pic_name;
        $companies->pic_phone = $request->pic_phone;
        $companies->pic_mail = $request->pic_mail;
        $companies->save();

        return redirect('db-assessment/companies');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::find($id);
        $company->delete();

        return redirect('db-assessment/companies');
    }

    public function companyData()
    {
        return DataTables::of(Company::get())
            ->addIndexColumn()
            ->addColumn('action', function($company){
                $csrf = csrf_token();
                return '
                <a href="" class="btn btn-sm btn-default show" id="' . $company->id . '" data-toggle="modal" data-target="#show-modal" data-name="' . $company->name . '" data-business_field="' . $company->business_field . '" data-address="' . $company->address . '" data-city="' . $company->city . '" data-phone="' . $company->phone . '" data-pic_name="' . $company->pic_name . '" data-pic_phone="' . $company->pic_phone . '" data-pic_mail="' . $company->pic_mail . '">
                    <i class="fas fa-eye"></i> Lihat
                </a>

                <a href="/db-assessment/companies/' . $company->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>
                ';
            })
            ->make(true);
    }
}
