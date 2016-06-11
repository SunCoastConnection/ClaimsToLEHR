<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class X12Partners extends Eloquent {
	protected $table = 'x12_partners';

	public $timestamps = false;

	// INSERT INTO `x12_partners` (`id`, `name`, `id_number`,`x12_version`,`x12_isa01`,`x12_isa02`,`x12_isa03`,`x12_isa04`,`x12_isa05`,`x12_sender_id`,`x12_isa07`,`x12_receiver_id`,`x12_isa14`,`x12_isa15`,`x12_gs02`,`x12_gs03`) VALUES

}