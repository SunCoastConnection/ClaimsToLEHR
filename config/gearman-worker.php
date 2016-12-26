<?php

return [

	'Gearman' => [
		'server' => [
			'ip' => '127.0.0.1',
			'port' => '4730',
		],
	],

	'Credentials' => [
		'path' => __DIR__.'/../cache/credentials',
	],

	'SFTP' => [
		'username' => '',
		'password' => '',
		'privateKey' => [
			'path' => __DIR__.'/../cache/privateKey.key',
			'passphrase' => ''
		],
	],

	'ClaimsConfig' => __DIR__.'/app.php'

];