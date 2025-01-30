<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Models\DB\Recommendation;
use App\Models\DB\ItemRecommendation;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.recommendation.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('db_assessment.recommendation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'name' => 'required|unique:mysql2.recommendations',
        //     // 'itemName' => 'required',
        // ], [
        //     'name.required' => 'Nama Rekomendasi tidak boleh kosong',
        //     'name.unique' => 'Nama Rekomendasi sudah ada',
        //     // 'itemName.required' => 'Item Rekomendasi tidak boleh kosong',
        // ]);



        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:mysql2.recommendations,name',
            'itemName.index' => 'required',
        ],
            [
                'name.required' => 'Harap isi field Nama Rekomendasi',
                'name.unique' => 'Nama Rekomendasi sudah ada',
            ]);
        // $validator->validate([

        // ]);
        $array = $request->itemName;

        $recommendation = Recommendation::create([
            'name' => $request->name,
            'total_recommendations' => count($array)
        ]);

        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $itemRecommendation = ItemRecommendation::create([
                    'recommendation_id' => $recommendation->id,
                    'name' => $value
                ]);
            }
        }

        return redirect('/db-assessment/recommendations');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $recommendation = Recommendation::find($id);
        $itemRecommendations = ItemRecommendation::where('recommendation_id',$id)->get();

        return view('db_assessment.recommendation.show', compact('recommendation','itemRecommendations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recommendation = Recommendation::find($id);
        $itemRecommendations = ItemRecommendation::where('recommendation_id',$id)->get();

        return view('db_assessment.recommendation.edit', compact('recommendation','itemRecommendations'));
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:mysql2.recommendations',
            'itemName.index' => 'required',
        ],
            [
                'name.required' => 'Harap isi field Nama Nama Rekomendasi',
                'name.unique' => 'Nama Rekomendasi sudah ada',
            ]);
        $recommendation = Recommendation::find($id);
        ItemRecommendation::where('recommendation_id',$id)->delete();
        $array = $request->itemName;
        $arrayNotEmpty = array_filter($array);
        $recommendation->name = $request->name;
        $recommendation->total_recommendations = count($arrayNotEmpty);
        $recommendation->save();

        if (count($arrayNotEmpty) > 0) {
            foreach ($arrayNotEmpty as $key => $value) {
                $itemRecommendation = ItemRecommendation::create([
                    'recommendation_id' => $id,
                    'name' => $value
                ]);
            }
        }

        return redirect('/db-assessment/recommendations');
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

    public function recommendationData()
    {
        return DataTables::of(Recommendation::get())
            ->addIndexColumn()
            ->addColumn('action', function($recommendation){
                $csrf = csrf_token();
                return '
                <a href="/db-assessment/recommendations/' . $recommendation->id . '/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>
                <a href="/db-assessment/recommendations/' . $recommendation->id . '" class="btn btn-sm btn-default">
                    <i class="far fa-eye"></i>Detail
                </a>
                ';
            })
            ->make(true);
    }
}
