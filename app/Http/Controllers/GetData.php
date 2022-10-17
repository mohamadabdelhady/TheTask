<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GetData extends Controller
{
    public function getCategoriesData()
    {
         $response= Http::withHeaders([
             'Content-language' => 'en',
         ])->get('https://staging.mazaady.com/api/get_all_cats');
         return  json_decode($response->body());;
    }

    public function getCategoryOption(Request $request)
    {
        $response=Http::withHeaders([
            'Content-language' => 'en',
        ])->get('https://staging.mazaady.com/api/properties',['cat'=>$request->selectedSubCat]);
        return  json_decode($response->body());
    }

    public function getOptionsChildren($optionId)
    {
        $response=Http::withHeaders([
            'Content-language' => 'en',
        ])->get("https://staging.mazaady.com/api/get-options-child/$optionId");
        return  json_decode($response->body());
    }
}
