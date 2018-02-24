<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use URL;
use Session;
use Redirect;
use Input;
use App\User;
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
        ]);
        
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
                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => 'USD',
                    'amount'   => $plan_selected->price,
                    'description' => 'Add in wallet',
                ]);
                if($charge['status'] == 'succeeded') {
                    /**
                    * Write Here Your Database insert logic.
                    */
                   // \Session::put('success','Money add successfully in wallet');
                   // return redirect()->route('stripform');

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
            return response()->json('This plan does not exist');
        }
    }    


}
    