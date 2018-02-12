<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Home page
$app->get('/', function () use ($app) {
	return var_dump(env('DBX_APP_SECRET'));
});

// DBX Webhook
$app->get('/webhook','WebhookController@webhookverify');
$app->post('/webhook','WebhookController@webhook');

// Posts
$app->get('/posts','PostController@index');
$app->post('/posts','PostController@store');
$app->get('/posts/{post_id}','PostController@show');
$app->put('/posts/{post_id}', 'PostController@update');
$app->patch('/posts/{post_id}', 'PostController@update');
$app->delete('/posts/{post_id}', 'PostController@destroy');

// dbxUsers
$app->get('/dbxusers/get', 'dbxUserController@index');
$app->post('/dbxusers/add', 'dbxUserController@store');
$app->get('/dbxusers/verify/{user_token}', 'dbxUserController@verify');
$app->get('/dbxusers/get/uid/{dbid}', 'dbxUserController@getuid');
$app->get('/dbxusers/{user_id}', 'dbxUserController@show');
$app->put('/dbxusers/{user_id}', 'dbxUserController@update');
$app->patch('/dbxusers/{user_id}', 'dbxUserController@update');
$app->delete('/dbxusers/{user_id}', 'dbxUserController@destroy');

// Subs
$app->get('/dbxusers/get/subs/{user_id}', 'SubsController@index');
$app->get('/dbxusers/subs/get/{user_id}', 'SubsController@index');
$app->post('/dbxusers/add/subs', 'SubsController@add');
$app->post('/dbxusers/subs/add', 'SubsController@add');
$app->get('/dbxusers/delete/sub/{sub_id}', 'SubsController@destroy');
$app->get('/dbxusers/sub/delete/{sub_id}', 'SubsController@destroy');
$app->get('/dbxusers/subs/list', 'SubsController@list');
$app->get('/dbxusers/sub/verify/{sub_domain}', 'SubsController@verify');

// Users
$app->get('/users/', 'UserController@index');
$app->post('/users/', 'UserController@store');
$app->get('/users/{user_id}', 'UserController@show');
$app->put('/users/{user_id}', 'UserController@update');
$app->patch('/users/{user_id}', 'UserController@update');
$app->delete('/users/{user_id}', 'UserController@destroy');

// Comments
$app->get('/comments', 'CommentController@index');
$app->get('/comments/{comment_id}', 'CommentController@show');

// Comment(s) of a post
$app->get('/posts/{post_id}/comments', 'PostCommentController@index');
$app->post('/posts/{post_id}/comments', 'PostCommentController@store');
$app->put('/posts/{post_id}/comments/{comment_id}', 'PostCommentController@update');
$app->patch('/posts/{post_id}/comments/{comment_id}', 'PostCommentController@update');
$app->delete('/posts/{post_id}/comments/{comment_id}', 'PostCommentController@destroy');

// Request an access token
$app->post('/oauth/access_token', function() use ($app){
    return response()->json($app->make('oauth2-server.authorizer')->issueAccessToken());
});
