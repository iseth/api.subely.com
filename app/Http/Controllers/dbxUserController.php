<?php

namespace App\Http\Controllers;

use App\dbxUser;
use App\User;
use App\Subs;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class dbxUserController extends Controller{

	public function __construct(){

		$this->middleware('oauth', ['except' => ['verify']]);
		// $this->middleware('authorize:' . __CLASS__, ['except' => ['verify']]);
	}

	public function index(){

		$users = dbxUser::all();
		return $this->success($users, 200);
	}

	public function store(Request $request){

		$this->validateRequest($request);

		$user = dbxUser::create([
					'uid'							=> 		Uuid::uuid4(),
					'dbid' 						=> 		$request->get('dbid'),
					'email' 					=> 		$request->get('email'),
					'display_name'		=> 		$request->get('display_name'),
					'firstName' 			=> 		$request->get('firstName'),
					'lastName' 				=> 		$request->get('lastName'),
					'profile_pic_url' => 		$request->get('profile_pic_url'),
					'verified' 				=> 		$request->get('verified'),
					'country'					=>		$request->get('country'),
					'language'				=>		$request->get('language'),
					'remember_token'	=>		$request->get('remember_token')
				]);

		return $this->success("The user with with id {$user->uid} has been created", 201);
	}

	public function show($id){
		$user = dbxUser::where('uid', '=', $id)->first();

		// $user = dbxUser::find($id);
		return var_dump($user);

		if(!$user){
			return $this->error("The user with {$id} doesn't exist", 404);
		}

		return $this->success($user, 200);
	}

	public function update(Request $request, $id){

		$user = dbxUser::find($id);

		if(!$user){
			return $this->error("The user with {$id} doesn't exist", 404);
		}

		$this->validateRequest($request);

		$user->email 		= $request->get('email');
		$user->password 	= Hash::make($request->get('password'));

		$user->save();

		return $this->success("The user with with id {$user->id} has been updated", 200);
	}

	public function destroy($id){

		$user = dbxUser::find($id);

		if(!$user){
			return $this->error("The user with {$id} doesn't exist", 404);
		}

		$user->delete();

		return $this->success("The user with with id {$id} has been deleted", 200);
	}

	public function validateRequest(Request $request){

		$rules = [
			'email' => 'required|email|unique:dbx_users',
			'dbid' => 'required|unique:dbx_users',
			'remember_token' => 'required|min:6'
		];

		$this->validate($request, $rules);
	}

	public function validateSubRequest(Request $request){

		$rules = [
			'sub_domain' 	=> 'required|unique:subs',
			'user_id'	 		=> 'required',
			'provider' 		=> 'required',
			'www' 				=> 'required'
		];

		$this->validate($request, $rules);
	}

	public function isAuthorized(Request $request){

		$resource = "users";
		// $user     = dbxUser::find($this->getArgs($request)["user_id"]);

		return $this->authorizeUser($request, $resource);
	}

	public function verify($email){

		$dbxusers = dbxUser::where('email', '=', $email)->first();

		if(!$dbxusers){
			$users = User::where('email', '=', $email)->first();
			if(!$users){
				return $this->error("The user with {$email} doesn't exist", 404);
			}
		}

		return $this->success('verfied', 200);
	}

	public function getuid($dbid='') {
		$uid = dbxUser::where('dbid', '=', $dbid)->first(['uid']);

		if(!$uid){
				return $this->error("The user with {$dbid} doesn't exist", 404);
		}

		return $this->success($uid, 200);

	}

}
