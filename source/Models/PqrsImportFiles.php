<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class PqrsImportFiles extends Eloquent {
	protected $table = 'pqrs_import_files';

	public $timestamps = false;
}