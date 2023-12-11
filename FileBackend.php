<?php

$wgFileBackends[] = [
	'class'              => SwiftFileBackend::class,
	'name'               => 'wikitide-swift',
	// This is the prefix for the container that it starts with.
	'wikiId'             => "wikitide-$wgDBname",
	'lockManager'        => 'redisLockManager',
	'swiftAuthUrl'       => 'https://swift-lb.wikitide.net/auth',
	'swiftStorageUrl'    => 'https://swift-lb.wikitide.net/v1/AUTH_mw',
	'swiftUser'          => 'mw:media',
	'swiftKey'           => $wmgSwiftPassword,
	'swiftTempUrlKey'    => '',
	'parallelize'        => 'implicit',
	'cacheAuthInfo'      => true,
	'readAffinity'       => true,
	'readUsers'           => [ 'mw:media' ],
	'writeUsers'          => [ 'mw:media' ],
	'connTimeout'         => 10,
	'reqTimeout'          => 900,
];

$wgLockManagers[] = [
	'name' => 'redisLockManager',
	'class' => RedisLockManager::class,
	'lockServers' => [
		// jobchron21
		'rdb1' => '10.0.2.7:6379',
	],
	'srvsByBucket' => [
		0 => [ 'rdb1' ]
	],
	'redisConfig' => [
		'connectTimeout' => 2,
		'readTimeout' => 2,
		'password' => $wmgRedisPassword,
	]
];

$wgGenerateThumbnailOnParse = false;
$wgUploadThumbnailRenderMethod = 'http';
$wgUploadThumbnailRenderHttpCustomHost = 'static.wikitide.net';
$wgUploadThumbnailRenderHttpCustomDomain = 'swift-lb.wikitide.net';

$wgThumbnailBuckets = [ 1920 ];
$wgThumbnailMinimumBucketDistance = 100;

// Thumbnail prerendering at upload time
$wgUploadThumbnailRenderMap = [ 320, 640, 800, 1024, 1280, 1920 ];

if ( $cwPrivate ) {
	$wgUploadThumbnailRenderMap = [];
	$wgUploadPath = '/w/img_auth.php';
	$wgImgAuthUrlPathMap = [
		'/avatars/' => 'mwstore://wikitide-swift/avatars/',
		'/awards/' => 'mwstore://wikitide-swift/awards/',
		'/dumps/' => 'mwstore://wikitide-swift/dumps-backup/',
		'/score/' => 'mwstore://wikitide-swift/score-render/',
		'/timeline/' => 'mwstore://wikitide-swift/timeline-render/',
	];
}

$wgLocalFileRepo = [
	'class' => LocalRepo::class,
	'name' => 'local',
	'backend' => 'wikitide-swift',
	'url' => $wgUploadBaseUrl ? $wgUploadBaseUrl . $wgUploadPath : $wgUploadPath,
	'scriptDirUrl' => $wgScriptPath,
	'hashLevels' => 2,
	'thumbScriptUrl' => $wgThumbnailScriptPath,
	'transformVia404' => true,
	'useJsonMetadata'   => true,
	'useSplitMetadata'  => true,
	'deletedHashLevels' => 3,
	'abbrvThreshold' => 160,
	'isPrivate' => $cwPrivate,
	'zones' => $cwPrivate
		? [
			'thumb' => [ 'url' => "$wgScriptPath/thumb_handler.php" ] ]
		: [],
];
