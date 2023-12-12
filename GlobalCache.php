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
				'pc1' => [
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

$wgObjectCaches['db-mainstash'] = [
	'class' => SqlBagOStuff::class,
	'server' => [
		'type'     => 'mysql',
		'host'     => $wmgDBHostname,
		'dbname'   => 'mainstash',
		'user'     => $wgDBuser,
		'password' => $wgDBpassword,
		'ssl'      => false,
		'flags'    => 0,
	],
	'dbDomain' => 'mainstash',
	'globalKeyLbDomain' => 'mainstash',
	'tableName' => 'objectstash',
	'multiPrimaryMode' => false,
	'purgePeriod' => 100,
	'purgeLimit' => 1000,
	'reportDupes' => false
];

$wgMainStash = 'db-mainstash';

$wgStatsCacheType = 'mcrouter';
$wgMicroStashType = 'mcrouter';

$wgObjectCaches['redis-session'] = [
	'class' => RedisBagOStuff::class,
	'servers' => [ $wmgRedisHostname ],
	'password' => $wmgRedisPassword,
	'loggroup' => 'redis',
	'reportDupes' => false,
];

$wgSessionCacheType = 'redis-session';

// Same as $wgMainStash
$wgMWOAuthSessionCacheType = 'db-mainstash';

$wgMainCacheType = 'mcrouter';
$wgMessageCacheType = 'mcrouter';

$wgParserCacheType = 'mysql-multiwrite';

$wgChronologyProtectorStash = 'mcrouter';

$wgParsoidCacheConfig = [
	// use the same as main stash
	'StashType' => null,
	'StashDuration' => 24 * 60 * 60,
	'CacheThresholdTime' => 0.0,
	'WarmParsoidParserCache' => true,
];

$wgLanguageConverterCacheType = CACHE_ACCEL;

$wgQueryCacheLimit = 5000;

// 7 days
$wgParserCacheExpireTime = 86400 * 7;

// 7 days
$wgRevisionCacheExpiry = 86400 * 7;

// 1 day
$wgObjectCacheSessionExpiry = 86400;

// 7 days
$wgDLPMaxCacheTime = 86400 * 7;

$wgDLPQueryCacheTime = 120;
$wgDplSettings['queryCacheTime'] = 120;

$wgSearchSuggestCacheExpiry = 10800;

$wgEnableSidebarCache = true;

$wgUseLocalMessageCache = true;
$wgInvalidateCacheOnLocalSettingsChange = false;

$wgResourceLoaderUseObjectCacheForDeps = true;

$wgCdnMatchParameterOrder = false;

$wgJobTypeConf['default'] = [
	'class' => JobQueueRedis::class,
	'redisServer' => $wmgRedisHostname,
	'redisConfig' => [
		'connectTimeout' => 2,
		'password' => $wmgRedisPassword,
		'compression' => 'gzip',
	],
	'daemonized' => true,
];

if ( PHP_SAPI === 'cli' ) {
	// APC not available in CLI mode
	$wgLanguageConverterCacheType = CACHE_NONE;
}

if ( $wgDBname === 'metawiki' ) {
	$wgUseFileCache = true;
	$wgUseGzip = true;
	$wgFileCacheDirectory = '/srv/mediawiki/cache/file/' . $wgDBname;
}
