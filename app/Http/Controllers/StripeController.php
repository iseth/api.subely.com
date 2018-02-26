<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use URL;
use Session;
use Redirect;
use Input;
use App\User;
use App\Subs;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Stripe\Error\Card;
use DB;



class StripeController extends Controller{


	public function __construct(){

      //$this->middleware('oauth');
	  // $this->middleware('authorize:' . __CLASS__);
	}


	public function postPaymentWithStripe(Request $request)
    {

        $plans = DB::table('plans')->get();

        $verify_plan = false;

        $plan_selected = null;

        foreach ($plans as $plan) {
            if($plan->name == $request->package_name)
            {
                $verify_plan = true;
                $plan_selected = $plan;
            }
            
        }

        if($verify_plan == true){


        $validator = Validator::make($request->all(), [
            'card_no' => 'required',
            'ccExpiryMonth' => 'required',
            'ccExpiryYear' => 'required',
            'cvvNumber' => 'required',
            'user_id' => 'required',
        ]);

        $user = DB::table('dbx_users')->where('uid','=',$request->user_id)->first();

        $subscription_exist = DB::table('subscriptions')->where('user_id','=',$request->user_id)->first();

        $user_subs_created = Subs::where('owner','=',$request->user_id)->count();

        if($user_subs_created <= $plan_selected->folders){

        $input = $request->all();
        if ($validator->passes()) {           
            $input = array_except($input,array('_token'));            
            $stripe = Stripe::make('sk_test_ZvFhTjD71Zq4FswCdv76hKR4');
            try {
                $token = $stripe->tokens()->create([
                    'card' => [
                        'number'    => $request->get('card_no'),
                        'exp_month' => $request->get('ccExpiryMonth'),
                        'exp_year'  => $request->get('ccExpiryYear'),
                        'cvc'       => $request->get('cvvNumber'),
                    ],
                ]);
                if (!isset($token['id'])) {
                  //  \Session::put('error','The Stripe Token was not generated correctly');
                    //return redirect()->route('stripform');
                    return response()->json('The Stripe Token was not generated correctly');
                }

               if($subscription_exist == null)
                {

                    $customer = $stripe->Customers()->create([
                        'source' => $token['id'],
                        'email' =>  $user->email,
                        'description' => 'Subscribed to '.$plan_selected->name.' plan',
                    ]);


                    $subscription = $stripe->Subscriptions()->create($customer['id'],[
                        'plan' => $plan_selected->name,
                    ]);

                }
                else
                {

                   /* $customer = $stripe->Customers()->update($subscription_exist->stripe_id,[
                        'source' => $token['id'],
                        'email' =>  $user->email,
                        'description' => 'Subscribed to '.$plan_selected->name.' plan',
                    ]); */

                     $subscription = $stripe->Subscriptions()->update($subscription_exist->stripe_id,$subscription_exist->stripe_subscription_id,[
                        'plan' => $plan_selected->name,
                    ]);

                }
              /* $charge = $stripe->charges()->create([
                    'customer' => $customer['id'],
                    'currency' => 'USD',
                    'amount'   => $plan_selected->price,
                    'description' => $plan_selected->name.' plan',
                ]); */
                if($subscription['status'] == 'active') {

                   if($subscription_exist == null)
                   {
                        $start_time = Carbon::now();
                        $end_time = Carbon::now()->addMonth();

                        DB::table('subscriptions')->insert([
                            'user_id' => $request->user_id,
                            'plan_id' => $plan_selected->id,
                            'stripe_id' => $customer['id'],
                            'stripe_subscription_id' => $subscription['id'],
                            'started_at' => $start_time,
                            'ends_at' => $end_time,
                        ]);
                    }
                    else
                    {
                        $start_time = Carbon::now();
                        $end_time = Carbon::now()->addMonth();

                        dd($start_time."  ".$end_time);

                        DB::table('subscriptions')->where('user_id','=',$request->user_id)->update([
                            'plan_id' => $plan_selected->id,
                            'stripe_subscription_id' => $subscription['id'],
                            'started_at' => $start_time,
                            'ends_at' => $end_time,
                        ]);

                    }
                    return response()->json('Payment Successful');
                } else {
                   // \Session::put('error','Money not add in wallet!!');
                   // return redirect()->route('stripform');
                    return response()->json('Payment unsuccessful');
                }
            } catch (Exception $e) {
                //\Session::put('error',$e->getMessage());
                //return redirect()->route('stripform');
                 return response()->json($e->getMessage());

            } catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
               // \Session::put('error',$e->getMessage());
               // return redirect()->route('stripform');
            	return response()->json($e->getMessage());
            } catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
               // \Session::put('error',$e->getMessage());
               // return redirect()->route('stripform');
            	return response()->json($e->getMessage());
            }

           }
           // \Session::put('error','All fields are required!!');
           // return redirect()->route('stripform');
            return response()->json('All fields are required!!');

           }
           else
           {
              return response()->json('Please delete extra created folders to upgrade to this package');
           }

        }
        else
        {
            return response()->json('This plan does not exist');
        }
    }    


}
    