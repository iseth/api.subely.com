<?php

namespace App\Http\Controllers;

use App\Subs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

use Spatie\FlysystemDropbox\DropboxAdapter;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;

class SubsController extends Controller{

	public function __construct(){

		//$this->middleware('oauth', ['except' => ['verify']]);
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

		// $appSecret = env('DBX_APP_SECRET');

		$this->validateRequest($request);

		$subscription = DB::table('subscriptions')->where('user_id','=',$request->get('user_id'))->first();
		$plan = DB::table('plan')->get();
		if($subscription != null){

		$plan = DB::table('plan')->where('id','=',$subscription->plan_id)->first();

		$total_subs = Subs::where('owner','=',$request->get('user_id'))->count();

		if($total_subs <= $plan->folders)
		{
			$sub_id = Uuid::uuid4();

			$sub = Subs::create([
						'sub_id'							=> 		$sub_id,
						'owner' 							=> 		$request->get('user_id'),
						'sub_domain' 					=> 		$request->get('sub_domain') .'.subely.me',
						'domain' 							=> 		'domain',
						'status'							=> 		'1',
						'provider' 						=> 		$request->get('provider'),
						'www' 								=> 		$request->get('www') .'.subely.me',
						'host'								=> 		'001',
						'isActive' 						=> 		'0',
					]);

			if (app()->environment('local')) {

				$directory = base_path().'/public/dropbox-files/'. $request->get('sub_domain') . '.subely.me';
				if (!file_exists($directory)) {
					$success = mkdir($directory);
				}

				$dbxUserController = new dbxUserController;
				$dbxaccessToken = $dbxUserController->getToken($request->get('user_id'));

				$client = new Client($dbxaccessToken);
		    	$adapter = new DropboxAdapter($client);
		    	$filesystem = new Filesystem($adapter);

		    	try{

		    		$filesystem->createDir($request->get('sub_domain') . '.subely.me',[]);
					$filesystem->write($request->get('sub_domain') . '.subely.me' . '/index.php', '<?php echo(\'<h1>Subely Hosting</h1><br>Just Upload Your Files Here\');');

		    	} catch (\Exception $e) {

		    	}



				//var_dump($filesystem->createDir($request->get('sub_domain')));
				// var_dump($filesystem->createDir('/secure'));
				// $filesystem->write('secure/README.md', '<h1>Subely Hosting</h1><br>This area is secure from the world.');
			}

			if (app()->environment('production', 'staging')) {
				$directory = base_path().'/public/dropbox-files/'. $request->get('sub_domain') . '.subely.me';
				if (!file_exists($directory)) {
					$success = mkdir($directory);
				}

				$dbxUserController = new dbxUserController;
				$dbxaccessToken = $dbxUserController->getToken($request->get('user_id'));

				$client = new Client($dbxaccessToken);
		    	$adapter = new DropboxAdapter($client);
		    	$filesystem = new Filesystem($adapter);


		    	try{

		    		$filesystem->createDir($request->get('sub_domain') . '.subely.me',[]);
					$filesystem->write($request->get('sub_domain') . '.subely.me' . '/index.php', '<?php echo(\'<h1>Subely Hosting</h1><br>Just Upload Your Files Here\');');

		    	} catch (\Exception $e) {

		    	}

				// var_dump($filesystem->createDir('/secure'));
				// $filesystem->write('secure/README.md', '<h1>Subely Hosting</h1><br>This area is secure from the world.');
			}

			return $this->success("The sub with with id {$sub->sub_id} has been created".$success, 201);
		  }
		  else
		  {
		  	return response()->json('Your limit has been exceeded',429);
		  }

		}
		else
		{
			return response()->json('You need to buy a package to create your folders',429);
		}

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

	public function delTree($dir) {
		if(is_dir($dir)) {
	   $files = array_diff(scandir($dir), array('.','..'));
	    foreach ($files as $file) {
	      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
	    }
	    return rmdir($dir);
		}
	}

	public function destroy(Request $request,$uid){

		$subs = Subs::where('sub_id', '=', $uid);

		$sub_domain = Subs::select('sub_domain')->where('sub_id', '=', $uid)->first();

		$sub_domain = json_decode($sub_domain)->sub_domain;

		$owner_uid = Subs::select('owner')->where('sub_id', '=', $uid)->first()->owner;

		if(!$subs){
			return $this->error("The sub doesn't exist", 404);
		}

		if (app()->environment('local')) {

			$directory = base_path().'/public/dropbox-files/'. $sub_domain;
			if (file_exists($directory)) {
				$success = $this->delTree($directory);
			}

			$dbxUserController = new dbxUserController;
			$dbxaccessToken = $dbxUserController->getToken($owner_uid);

			$client = new Client($dbxaccessToken);
	    	$adapter = new DropboxAdapter($client);

	    	$filesystem = new Filesystem($adapter);


	     if($request->deletefromdropbox == 1)
	     {
			$filesystem->deleteDir($sub_domain);
		 }

			// return $directory;
		}

		if (app()->environment('production', 'staging')) {
			$directory = base_path().'/public/dropbox-files/'. $sub_domain;
			if (file_exists($directory)) {
				$success = $this->delTree($directory);
			}

			$dbxUserController = new dbxUserController;
			$dbxaccessToken = $dbxUserController->getToken($owner_uid);

			$client = new Client($dbxaccessToken);
	    	$adapter = new DropboxAdapter($client);

	    	$filesystem = new Filesystem($adapter);

			if($request->deletefromdropbox == 1)
		    {
				$filesystem->deleteDir($sub_domain);
		 	}

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
	public function webhookverify(Request $request)
	{

		return var_dump($request->challenge);
	}
	public function webhook(Request $request)
	{

		return var_dump($request);
	}
	public function list()
	{
		$subs = Subs::all();
		return $this->success($subs, 200);
	}

}
