<?php

$wgMemCachedServers = [];
$wgMemCachedPersistent = false;

// $beta = preg_match( '/^(.*)\.nexttide\.org$/', $wi->server );

$wgObjectCaches['mcrouter'] = [
	'class'                 => MemcachedPeclBagOStuff::class,
	'serializer'            => 'php',
	'persistent'            => false,
	'servers'               => [ '127.0.0.1:11213' ],
	'server_failure_limit'  => 1e9,
	'retry_timeout'         => -1,
	'loggroup'              => 'memcached',
	// 250ms, in microseconds
	'timeout'               => 0.25 * 1e6,
	'allow_tcp_nagle_delay' => false,
];

// Used by WikiTideMagic for clearing keys when a wiki is deleted or renamed
$wgWikiTideMagicMemcachedServer = '10.0.2.4:11211';

$wgObjectCaches['mysql-multiwrite'] = [
	'class' => MultiWriteBagOStuff::class,
	'caches' => [
		0 => [
			'factory' => [ 'ObjectCache', 'getInstance' ],
			'args' => [ 'mcrouter' ]
		],
		1 => [
			'class' => SqlBagOStuff::class,
			'servers' => [
				'parsercache' => [
					'type'      => 'mysql',
					'host'      => $wmgDBHostname,
					'dbname'    => 'parsercache',
					'user'      => $wgDBuser,
					'password'  => $wgDBpassword,
					'flags'     => 0,
				],
			],
			'purgePeriod' => 0,
			'tableName' => 'pc',
			'shards' => 5,
			'reportDupes' => false
		],
	],
	'replication' => 'async',
	'reportDupes' => false
];

if ( $wi->wikifarm === 'wikitide' ) {
	$wgObjectCaches['redis-session'] = [
		'class' => RedisBagOStuff::class,
		'servers' => [ $wmgRedisHostname ],
		'password' => $wmgRedisPassword,
		'loggroup' => 'redis',
		'reportDupes' => false,
	];

	$wgSessionCacheType = 'redis-session';
} else {
	$wgSessionCacheType = 'mcrouter';

	// Same as $wgMainStash
	$wgMWOAuthSessionCacheType = 'db-replicated';
}

$wgMainCacheType = 'mcrouter';
$wgMessageCacheType = 'mcrouter';

$wgParserCacheType = 'mysql-multiwrite';
$wgParsoidCacheConfig = [
	'StashType' => null,
	'StashDuration' => 24 * 60 * 60,
	'CacheThresholdTime' => 0.0,
	// This created over 50,000 parsoidCachePrewarm jobs on a single wiki,
	// and creates jobs faster than the queues can run them.
	// Do not enable yet.
	'WarmParsoidParserCache' => false,
];

$wgLanguageConverterCacheType = CACHE_ACCEL;

// 5 days
$wgParserCacheExpireTime = 86400 * 5;

// 3 days
$wgRevisionCacheExpiry = 86400 * 3;

// 1 day
$wgObjectCacheSessionExpiry = 86400;

$wgDLPQueryCacheTime = 120;
$wgDplSettings['queryCacheTime'] = 120;

$wgEnableSidebarCache = true;

$wgUseLocalMessageCache = true;
$wgInvalidateCacheOnLocalSettingsChange = false;

$wgJobTypeConf['default'] = [
	'class' => JobQueueRedis::class,
	'redisServer' => $wmgRedisHostname,
	'redisConfig' => [
		'connectTimeout' => 2,
		'password' => $wmgRedisPassword,
		'compression' => 'gzip',
	],
	'claimTTL' => 3600,
	'daemonized' => true,
];

if ( PHP_SAPI === 'cli' ) {
	// APC not available in CLI mode
	$wgLanguageConverterCacheType = CACHE_NONE;
}
