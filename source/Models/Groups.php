<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Groups extends Eloquent {
	protected $table = 'groups';

	public $timestamps = false;

	// INSERT INTO `groups` ( `id`, `name`,`user`) VALUES

}