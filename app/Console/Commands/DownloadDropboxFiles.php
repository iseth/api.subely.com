<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Subs;
use App\dbxUser;
use App\Http\Controllers\dbxUserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

use Spatie\FlysystemDropbox\DropboxAdapter;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use DB;

class DownloadDropboxFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:dropbox-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloading the latest changes of dropbox user files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

    	$dbxusers = DB::table('dbxqueue')->get();

			$check_dbxusers = count($dbxusers);

				$total_files = 0;

			if($check_dbxusers != 0)
			{

			 foreach ($dbxusers as $key => $user) {

			 if($user->status == 0)
			 {

			   	$path = $user->dbid;

			   	if (!file_exists(base_path().'/public/dropbox-files/'.$path)) {

			   		mkdir(base_path().'/public/dropbox-files/'.$path, 0777, true);
				}
			   	

			  
			   	
				$dbxuid = dbxUser::where('dbid', '=', $user->dbid)->first();
				$dbxUserController = new dbxUserController;
				$dbxaccessToken = $dbxUserController->getToken($dbxuid->uid);

				$client = new Client($dbxaccessToken);

				$check_folder_exists = 0;

					if($user->cursor == null)
					{
						// catch exception if folder do not exist
							try {
						        $files = $client->listFolder();


								$check_folder_exists = 1;
						    } catch (\Exception $e) {

						    	$check_folder_exists = 0;

						    }


					  if($check_folder_exists == 1)
					  {
					    if($files['has_more'] != 'false')
					    {
					    	$list_continue = $client->listFolderContinue($files['cursor']);

					    	$check_list_continue = count($list_continue['entries']);

					    	if($check_list_continue != 0)
					    	{
					    		foreach ($list_continue['entries'] as $list_file) {
					    			array_push($files['entries'], $list_file);
					    		}
					    	}
					    }

					  }

					}
					else
					{
					  try {
							 $files = $client->listFolderContinue($user->cursor);
							 $check_folder_exists = 1;
						   }catch (\Exception $e) {

						   	$check_folder_exists = 0;
						  }
					}


						if($check_folder_exists == 1)
						{

							DB::table('dbxqueue')->where('dbid','=',$user->dbid)->update(
							    ['cursor' => $files['cursor'],'status' => 1]
							);
						

					    

					    $check_entries = count($files['entries']);
					    $total_files = $check_entries;
							    if($check_entries != 0)
							    {
							    	foreach($files['entries'] as $file)
							    	{

							    		$download = $client->download($file['path_lower']);	

										file_put_contents(base_path().'/public/dropbox-files/'.$path.'/'.$file['name'], $download);
										
										
							    	}
							   	}
						}
					}
				}

				return response()->json('files downloaded successfully');

			}
			else
			{
				return response()->json('No Users found in quene');
			}


    }
}
