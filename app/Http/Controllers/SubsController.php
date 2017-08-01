<?php

namespace App\Http\Controllers;

use App\Subs;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class SubsController extends Controller{

	public function __construct(){

		$this->middleware('oauth', ['except' => ['verify']]);
		// $this->middleware('authorize:' . __CLASS__, ['except' => ['verify']]);
	}

	public function index($uid){

		$subs = Subs::where('owner', '=', $uid)->get();

		if(!$subs){
			return $this->error("The user has no subs", 404);
		}
		return $this->success($subs, 200);
	}

	public function add(Request $request){

		$this->validateRequest($request);

		$sub_id = Uuid::uuid4();

		$sub = Subs::create([
					'sub_id'							=> 		$sub_id,
					'owner' 							=> 		$request->get('user_id'),
					'sub_domain' 					=> 		$request->get('sub_domain'),
					'domain' 							=> 		'domain',
					'status'							=> 		'1',
					'provider' 						=> 		$request->get('provider'),
					'www' 								=> 		$request->get('www'),
					'host'								=> 		'001',
					'isActive' 						=> 		'0',
				]);

		return $this->success("The sub with with id {$sub->sub_id} has been created", 201);

	}

	public function show($id){

		$sub = Subs::find($id);

		if(!$sub){
			return $this->error("The user with {$id} doesn't exist", 404);
		}

		return $this->success($sub, 200);
	}

	public function update(Request $request, $id){

		$sub = Subs::find($id);

		if(!$sub){
			return $this->error("The user with {$id} doesn't exist", 404);
		}

		$this->validateRequest($request);

		$sub->email 		= $request->get('email');
		$sub->password 	= Hash::make($request->get('password'));

		$sub->save();

		return $this->success("The user with with id {$sub->id} has been updated", 200);
	}

	public function destroy($uid){

		$subs = Subs::where('sub_id', '=', $uid);

		if(!$subs){
			return $this->error("The sub doesn't exist", 404);
		}

		$subs->delete();
		return $this->success("The sub has been deleted", 200);
	}

	public function destroya($uid){

		// $sub = Subs::where('id', '2')->first();
		$sub = Subs::where('id', '2')->get();
		return ($sub);
		// DB::table('users')->where('proviver', '=', 100)->delete();

		// if(!$sub){
		// 	return $this->error("The user with {$sub_id} doesn't exist", 404);
		// }

		// $sub->delete();

		// return $this->success("The user with with id {$sub_id} has been deleted", 200);
	}

	public function validateRequest(Request $request){

		$rules = [
			// 'subdomain' => 'required|unique:subs',
			'access_token'=> 'required|min:6',
			'user_id' 		=> 'required|min:6',
			'sub_domain' 	=> 'required|unique:subs',
			'provider' 		=> 'required',
			'www' 				=> 'required'
		];

		$this->validate($request, $rules);
	}

	public function isAuthorized(Request $request){

		$resource = "users";
		// $sub     = Subs::find($this->getArgs($request)["user_id"]);

		return $this->authorizeUser($request, $resource);
	}

	public function verify($sub_domain){

		$sub = Subs::where('sub_domain', '=', $sub_domain)->first();

		if(!$sub){
				return $this->success("The sub is avalible", 200);
		}

		return $this->error("The sub already exists", 409);
	}

	public function getSubs($uid)	{

		$subs = Subs::where('owner', '=', $uid)->first();

		if(!$subs){
			return $this->error("The user has no subs", 404);
		}
		return $this->success($subs, 200);

	}
	public function storeSubs(Request $request){

		$this->validateSubRequest($request);
		$sub_id = Uuid::uuid4();

		$sub = Subs::create([
					'sub_id'							=> 		$sub_id,
					'owner' 							=> 		$request->get('user_id'),
					'sub_domain' 					=> 		$request->get('sub_domain'),
					'domain' 							=> 		'domain',
					'status'							=> 		'1',
					'provider' 						=> 		$request->get('provider'),
					'www' 								=> 		$request->get('www'),
					'host'								=> 		'001',
					'isActive' 						=> 		'0',
				]);

		return $this->success("The sub with with id {$sub->sub_id} has been created", 201);
	}
	public function list()
	{
		$subs = Subs::all();
		return $this->success($subs, 200);
	}

}
