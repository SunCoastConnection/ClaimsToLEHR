<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Billing extends Eloquent {
	protected $table = 'billing';

	public $timestamps = false;

	// INSERT INTO `billing` (`id`, `date`, `code_type`, `code`, `pid`,
	// `provider_id`, `user`, `groupname`, `authorized`, `encounter`, `code_text`, `billed`, `activity`,
	// `payer_id`, `bill_process`, `bill_date`, `process_date`, `process_file`, `modifier`,  `units`,
	// `fee`, `justify`) VALUES

}