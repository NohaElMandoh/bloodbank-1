<?php

namespace App\Http\Controllers\Api;

use App\Models\CarModel;
use App\Models\CarType;
use App\Models\City;
use App\Models\DonationRequest;
use App\Models\Governorate;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function posts()
    {
        $posts = Post::with('category')->paginate(10);
        return responseJson(1,'success',$posts);
    }

    public function donationRequests(Request $request)
    {
        $donations = DonationRequest::where(function ($query) use($request){
            if ($request->has('city_id'))
            {
                $query->where('city_id',$request->city_id);
            }
            if ($request->has('blood_type'))
            {
                $query->where('blood_type',$request->blood_type);
            }
        })->with('city','client')->paginate(10);
        return responseJson(1,'success',$donations);
    }

    public function post(Request $request)
    {
        $post = Post::with('category')->find($request->post_id);
        if (!$post)
        {
            return responseJson(0,'404 no post found');
        }
        return responseJson(1,'success',$post);
    }

    public function donationRequest(Request $request)
    {
        $donation = DonationRequest::with('city','client')->find($request->donation_id);
        if (!$donation)
        {
            return responseJson(0,'404 no donation found');
        }
        return responseJson(1,'success',$donation);
    }

    public function governorates()
    {
        $governorates = Governorate::all();
        return responseJson(1,'success',$governorates);
    }

    public function cities(Request $request)
    {
        $cities = City::where(function ($query) use($request){
            if ($request->has('governorate_id'))
            {
                $query->where('governorate_id',$request->governorate_id);
            }
        })->get();
        return responseJson(1,'success',$cities);
    }

    public function donationRequestCreate(Request $request)
    {
        $rules = [
            'patient_name' => 'required',
            'patient_age' => 'required:digits',
            'blood_type' => 'required|in:O-,O+,B-,B+,A+,A-,AB-,AB+',
            'bags_num' => 'required:digits',
            'hospital_address' => 'required',
            'city_id' => 'required|exists:cities,id',
            'phone' => 'required|digits:11',
        ];
        $validator = validator()->make($request->all(),$rules);
        if ($validator->fails())
        {
            return responseJson(0,$validator->errors()->first(),$validator->errors());
        }
        $donationRequest = $request->user()->requests()->create($request->all());
        return responseJson(1,'تم الاضافة بنجاح',$donationRequest->load('city'));

    }
}
