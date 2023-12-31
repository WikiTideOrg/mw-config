<?php

$wgLBFactoryConf = [
	'class' => \Wikimedia\Rdbms\LBFactoryMulti::class,
	'sectionsByDB' => $wi->wikiDBClusters,
	'sectionLoads' => [
		'DEFAULT' => [
			'db1' => 0,
		],
		'c1' => [
			'db1' => 0,
		],
	],
	'serverTemplate' => [
		'dbname' => $wgDBname,
		'user' => $wgDBuser,
		'password' => $wgDBpassword,
		'type' => 'mysql',
		'flags' => DBO_DEFAULT,
		'variables' => [
			// https://mariadb.com/docs/reference/mdb/system-variables/innodb_lock_wait_timeout
			'innodb_lock_wait_timeout' => 15,
		],
	],
	'hostsByName' => [
		'db21' => $wmgDBHostname,
	],
	'externalLoads' => [
		'echo' => [
			/** where the metawiki database is located */
			'db1' => 0,
		],
	],
	'readOnlyBySection' => [
		// 'DEFAULT' => 'We are performing database maintenance. Try again in a few hours.',
		// 'c1' => 'Please try again in a few minutes.',
	],
];

// Disallow web request database transactions that are slower than 6 seconds
$wgMaxUserDBWriteDuration = 6;

// Max execution time for expensive queries of special pages (in milliseconds)
$wgMaxExecutionTimeForExpensiveQueries = 30000;

$wgMiserMode = true;

if ( $cwClosed ) {
	// Disable all query pages on closed wikis
	$wgDisableQueryPages = true;
}

/* $wgDisableQueryPageUpdate = [
	'Ancientpages' => 'half-monthly',
	'Deadendpages' => 'half-monthly',
	'Fewestrevisions' => 'half-monthly',
	'Mostlinked' => 'half-monthly',
	'Mostrevisions' => 'half-monthly',
	'Wantedpages' => 'half-monthly',
]; */
