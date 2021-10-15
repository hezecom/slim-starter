<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User
 */
class User extends Model
{
	protected $table = 'users';

	protected $fillable = [
		'email',
		'name',
        'username',
		'password',
	];

}
