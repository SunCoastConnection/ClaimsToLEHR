<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Forms extends Eloquent {
	protected $table = 'forms';

	public $timestamps = false;

	// INSERT INTO `forms` (`id`, `date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`) VALUES

}