<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;

class PlansController extends Controller{

	public function getAllPlans(Request $request)
	{
		$plans = DB::table('plans')->get();


		$subscription_exist = DB::table('subscriptions')->where('user_id','=',$request->get('uid'));

		$check_plan = null;

		if($subscription_exist != null)
		{
			$check_plan = DB::table('plans')->where('plan_id','=',$subscription_exist);
		}

		$plans['plans'] = $plans;

		$plans['check_plan'] = $check_plan;
		
		return response()->json($plans);
	}
}
