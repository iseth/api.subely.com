<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;

class PlansController extends Controller{

	public function getAllPlans(Request $request)
	{

		$plans = [];
		$all_plans = DB::table('plans')->get();
		$subscription_exist = DB::table('subscriptions')->where('user_id','=',$request->get('uid'))->first();

		$check_plan = 0;

		if($subscription_exist != null)
		{
			$check_plan = DB::table('plans')->where('plan_id','=',$subscription_exist->plan_id)->first();
		}

		$plans['plans'] = $all_plans;

		$plans['check_plan'] = $check_plan;
		
		return response()->json($plans);
	}
}
