<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

use Illuminate\Support\Facades\Hash;

class dbxUser extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'uid',
      'dbid',
      'email',
      'display_name',
      'firstName',
      'lastName',
      'profile_pic_url',
      'verified',
      'country',
      'language',
      'type',
      'isActive',
      'remember_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * Verify user's credentials.
     *
     * @param  string $email
     * @param  string $password
     * @return int|boolean
     * @see    https://github.com/lucadegasperi/oauth2-server-laravel/blob/master/docs/authorization-server/password.md
     */
    public function verify($email){

        $user = dbxUser::where('email', $email)->first();

        if($user){
            return $user->uid;
        }

        return "false";
    }
}
