<?php

/**
 * LocalSettings.php for WikiTide.
 * Authors of initial version: Universal Omega, Miraheze contributors
 */

// Don't allow web access.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

// Configure PHP request timeouts.
if ( PHP_SAPI === 'cli' ) {
	$wgRequestTimeLimit = 0;
} elseif ( in_array( $_SERVER['HTTP_HOST'] ?? '', [ 'jobrunner1.wikitide.net' ] ) ) {
	$wgRequestTimeLimit = 1200;
} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$wgRequestTimeLimit = 200;
} else {
	$wgRequestTimeLimit = 60;
}

/**
 * When using ?forceprofile=1, a profile can be found as an HTML comment
 * Disabled on production hosts because it seems to be causing performance issues (how ironic)
 */
$forceprofile = $_GET['forceprofile'] ?? 0;
if ( ( $forceprofile == 1 || PHP_SAPI === 'cli' ) && extension_loaded( 'tideways_xhprof' ) ) {
	$xhprofFlags = TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY | TIDEWAYS_XHPROF_FLAGS_NO_BUILTINS;
	tideways_xhprof_enable( $xhprofFlags );

	$wgProfiler = [
		'class' => ProfilerXhprof::class,
		'flags' => $xhprofFlags,
		'running' => true,
		'output' => 'text',
	];

	$wgHTTPTimeout = 60;
}

// Show custom database maintenance error page on these clusters.
$wgDatabaseClustersMaintenance = [];

require_once '/srv/mediawiki/config/initialize/WikiTideFunctions.php';
$wi = new WikiTideFunctions();

// Load PrivateSettings (e.g. $wgDBpassword)
require_once '/srv/mediawiki/config/PrivateSettings.php';

// Load global extensions
require_once '/srv/mediawiki/config/GlobalExtensions.php';

$wmgUploadHostname = 'static.wikitide.net';
$wmgDBHostname = 'db1.wikitide.net';
$wmgRedisHostname = 'jobchron1.wikitide.net';

$wgConf->settings += [
	// invalidates user sessions - do not change unless it is an emergency.
	'wgAuthenticationTokenVersion' => [
		'default' => '2',
	],

	// 3D
	'wg3dProcessor' => [
		'ext-3d' => [
			'/usr/bin/xvfb-run',
			'-a',
			'-s',
			'-ac -screen 0 1280x1024x24',
			'/srv/3d2png/src/3d2png.js',
		],
	],

	// AbuseFilter
	'wgAbuseFilterActions' => [
		'default' => [
			'block' => true,
			'blockautopromote' => true,
			'degroup' => true,
			'disallow' => true,
			'rangeblock' => false,
			'tag' => true,
			'throttle' => true,
			'warn' => true,
		],
	],
	'wgAbuseFilterCentralDB' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgAbuseFilterIsCentral' => [
		'default' => false,
		'metawiki' => true,
		'metanexttide' => true,
	],
	'wgAbuseFilterBlockDuration' => [
		'default' => 'indefinite',
	],
	'wgAbuseFilterAnonBlockDuration' => [
		'default' => 2592000,
	],
	'wgAbuseFilterNotifications' => [
		'default' => 'udp',
	],
	'wgAbuseFilterLogPrivateDetailsAccess' => [
		'default' => true,
	],
	'wgAbuseFilterPrivateDetailsForceReason' => [
		'default' => true,
	],
	'wgAbuseFilterEmergencyDisableThreshold' => [
		'default' => [
			'default' => 0.05,
		],
	],
	'wgAbuseFilterEmergencyDisableCount' => [
		'default' => [
			'default' => 2,
		],
	],

	// AdminLinks
	'wgAdminLinksDelimiter' => [
		'default' => '•',
	],

	// AdvancedSearch => [
	// We don't have SPARQL installed which is required
	'wgAdvancedSearchDeepcatEnabled' => [
		'default' => false,
	],

	// Anti-spam
	'wgAccountCreationThrottle' => [
		'default' => [
			[
				'count' => 3,
				'seconds' => 300,
			],
			[
				'count' => 10,
				'seconds' => 86400,
			],
		],
	],

	'wgPasswordAttemptThrottle' => [
		'default' => [
			// this is X attempts per IP globally
			// user accounts are not taken into consideration
			[
				/** 5 attempts in 5 minutes */
				'count' => 5,
				'seconds' => 300,
			],
			[
				/** 40 attempts in 24 hours */
				'count' => 40,
				'seconds' => 86400,
			],
			[
				/** 60 attempts in 48 hours */
				'count' => 60,
				'seconds' => 172800,
			],
			[
				/** 75 attempts in 72 hours */
				'count' => 75,
				'seconds' => 259200,
			],
		],
	],
	// https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:SpamBlacklist#Block_list_syntax
	'wgBlacklistSettings' => [
		'default' => [
			'spam' => [
				'files' => [
					'https://meta.wikitide.org/wiki/Spam_blacklist?action=raw&sb_ver=1',
				],
			],
		],
	],
	'wgLogSpamBlacklistHits' => [
		'default' => true,
	],
	'wgTitleBlacklistLogHits' => [
		'default' => true,
	],

	// ApprovedRevs
	'egApprovedRevsEnabledNamespaces' => [
		'default' => [
			NS_MAIN => true,
			NS_USER => true,
			NS_FILE => true,
			NS_TEMPLATE => true,
			NS_HELP => true,
			NS_PROJECT => true
		],
	],
	'egApprovedRevsAutomaticApprovals' => [
		'default' => true,
	],
	'egApprovedRevsShowApproveLatest' => [
		'default' => false,
	],
	'egApprovedRevsShowNotApprovedMessage' => [
		'default' => false,
	],

	// ArticleCreationWorkflow
	'wgArticleCreationLandingPage' => [
		'default' => 'Project:Article wizard',
	],
	'wgUseCustomLandingPageStyles' => [
		'default' => true,
	],

	// ArticlePlaceholder
	'wgArticlePlaceholderImageProperty' => [
		'default' => 'P18',
	],
	'wgArticlePlaceholderReferencesBlacklist' => [
		'default' => 'P143',
	],
	'wgArticlePlaceholderSearchEngineIndexed' => [
		'default' => false,
	],
	'wgArticlePlaceholderRepoApiUrl' => [
		'default' => 'https://www.wikidata.org/w/api.php',
	],

	// Babel
	'wgBabelCategoryNames' => [
		'default' => [
			'0' => 'User %code%-0',
			'1' => 'User %code%-1',
			'2' => 'User %code%-2',
			'3' => 'User %code%-3',
			'4' => 'User %code%-4',
			'5' => 'User %code%-5',
			'N' => 'User %code%-N',
		],
	],
	'wgBabelMainCategory' => [
		'default' => 'User %code%',
	],

	// BetaFeatures
	'wgMediaViewerIsInBeta' => [
		'default' => false,
	],
	'wgVisualEditorEnableWikitextBetaFeature' => [
		'default' => false,
	],
	'wgVisualEditorEnableDiffPageBetaFeature' => [
		'default' => false,
	],
	'wgPopupsReferencePreviewsBetaFeature' => [
		'default' => true,
	],

	// Block
	'wgAutoblockExpiry' => [
		// 24 hours * 60 minutes * 60 seconds
		'default' => 86400,
	],
	'wgBlockAllowsUTEdit' => [
		'default' => true,
	],
	'wgEnableBlockNoticeStats' => [
		'default' => false,
	],
	'wgEnablePartialActionBlocks' => [
		'default' => true,
	],

	// Bot passwords
	'wgBotPasswordsDatabase' => [
		'wikitide' => $wi::GLOBAL_DATABASE[$wi->wikifarm],
	],

	// Cache
	'wgCacheDirectory' => [
		'default' => '/srv/mediawiki/cache',
	],
	'wgExtensionEntryPointListFiles' => [
		'default' => [
			'/srv/mediawiki/config/extension-list'
		],
	],

	// Captcha
	'wgCaptchaTriggers' => [
		'default' => [
			'edit' => false,
			'create' => false,
			'sendemail' => false,
			'addurl' => true,
			'createaccount' => true,
			'badlogin' => true,
			'badloginperuser' => true
		],
		'+ext-WikiForum' => [
			'wikiforum' => true,
		],
	],
	'wgHCaptchaSiteKey' => [
		'default' => '68a0117d-0fba-4b0e-9325-b54d9d7e1cfe',
	],

	// Cargo
	'wgCargoDBname' => [
		'default' => $wi->dbname . 'cargo',
	],
	'wgCargoDBuser' => [
		'default' => 'cargouser',
	],
	'wgCargoFileDataColumns' => [
		'default' => [],
	],
	'wgCargoPageDataColumns' => [
		'default' => [],
	],

	// Categories
	'wgCategoryCollation' => [
		// updateCollation.php should be ran after changing
		'default' => 'uppercase',
		'ext-CategorySortHeaders' => CustomHeaderCollation::class,
	],
	'wgCategoryPagingLimit' => [
		'default' => 200,
	],

	// CategoryTree
	'wgCategoryTreeDefaultMode' => [
		'default' => 0,
	],
	'wgCategoryTreeCategoryPageMode' => [
		'default' => 0,
	],
	'wgCategoryTreeMaxDepth' => [
		'default' => [ 10 => 1, 20 => 1, 0 => 2 ],
	],

	// CentralAuth
	'wgCentralAuthAutoCreateWikis' => [
		'wikitide' => [
			$wi::CENTRAL_WIKI[$wi->wikifarm],
		],
	],
	'wgCentralAuthAutoMigrate' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgCentralAuthAutoMigrateNonGlobalAccounts' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgCentralAuthCookies' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgCentralAuthCookiePrefix' => [
		'default' => 'centralauth_',
		'nexttide' => 'centralauth_nexttide_',
	],
	'wgCentralAuthCreateOnView' => [
		'wikitide' => false,
		'nexttide' => false,
	],
	'wgCentralAuthDatabase' => [
		'wikitide' => $wi::GLOBAL_DATABASE[$wi->wikifarm],
		'nexttide' => $wi::GLOBAL_DATABASE[$wi->wikifarm],
	],
	'wgCentralAuthEnableGlobalRenameRequest' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgCentralAuthGlobalBlockInterwikiPrefix' => [
		'wikitide' => 'meta',
		'nexttide' => 'meta',
	],
	'wgCentralAuthLoginWiki' => [
		'wikitide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
		'nexttide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgCentralAuthOldNameAntiSpoofWiki' => [
		'wikitide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
		'nexttide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgCentralAuthPreventUnattached' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgGlobalRenameDenylist' => [
		'wikitide' => 'https://meta.wikitide.org/wiki/MediaWiki:Global_rename_denylist?action=raw',
	],
	'wgGlobalRenameDenylistRegex' => [
		'wikitide' => true,
	],

	// CentralNotice
	'wgNoticeInfrastructure' => [
		'metawiki' => true,
		'metanexttide' => true,
	],
	'wgCentralSelectedBannerDispatcher' => [
		'wikitide' => 'https://meta.wikitide.org/wiki/Special:BannerLoader',
	],
	'wgCentralBannerRecorder' => [
		'wikitide' => 'https://meta.wikitide.org/wiki/Special:RecordImpression',
	],
	'wgCentralDBname' => [
		'wikitide' => 'metawiki',
		'nexttide' => 'metanexttide',
	],
	'wgCentralHost' => [
		'wikitide' => 'https://meta.wikitide.org',
		'nexttide' => 'https://meta.nexttide.org',
	],
	'wgNoticeProjects' => [
		'wikitide' => [
			'all',
			'optout',
		],
	],
	'wgNoticeUseTranslateExtension' => [
		'wikitide' => true,
	],

	// Chameleon
	'egChameleonLayoutFile' => [
		'default' => '/srv/mediawiki/' . $wi->version . '/skins/chameleon/layouts/standard.xml',
	],
	'egChameleonEnableExternalLinkIcons' => [
		'default' => false,
	],

	// CheckUser
	'wgCheckUserActorMigrationStage' => [
		'default' => SCHEMA_COMPAT_WRITE_BOTH | SCHEMA_COMPAT_READ_OLD,
	],
	'wgCheckUserLogActorMigrationStage' => [
		'default' => SCHEMA_COMPAT_WRITE_BOTH | SCHEMA_COMPAT_READ_OLD,
	],
	'wgCheckUserForceSummary' => [
		'default' => true,
	],
	'wgCheckUserEnableSpecialInvestigate' => [
		'default' => true,
	],
	'wgCheckUserLogLogins' => [
		'default' => true,
	],
	'wgCheckUserCAtoollink' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgCheckUserGBtoollink' => [
		'wikitide' => [
			'centralDB' => 'metawiki',
			'groups' => [
				'steward',
			],
		],
	],
	'wgCheckUserCAMultiLock' => [
		'wikitide' => [
			'centralDB' => 'metawiki',
			'groups' => [
				'steward',
			],
		],
	],

	// Citizen
	'wgCitizenThemeDefault' => [
		'default' => 'auto',
	],
	'wgCitizenEnableCollapsibleSections' => [
		'default' => true,
	],
	'wgCitizenEnableDrawerSiteStats' => [
		'default' => true,
	],
	'wgCitizenEnableManifest' => [
		'default' => true,
	],
	'wgCitizenEnablePreferences' => [
		'default' => true,
	],
	'wgCitizenGlobalToolsPortlet' => [
		'default' => 'first',
	],
	'wgCitizenManifestBackgroundColor' => [
		'default' => '#131a21',
	],
	'wgCitizenManifestThemeColor' => [
		'default' => '#131a21',
	],
	'wgCitizenShowPageTools' => [
		'default' => true,
	],
	'wgCitizenThemeColor' => [
		'default' => '#131a21',
	],
	'wgCitizenSearchGateway' => [
		'default' => 'mwActionApi',
	],
	'wgCitizenSearchModule' => [
		'default' => 'skins.citizen.search',
	],
	'wgCitizenSearchDescriptionSource' => [
		'default' => 'textextracts',
	],
	'wgCitizenMaxSearchResults' => [
		'default' => 6,
	],
	'wgCitizenEnableCJKFonts' => [
		'default' => false,
	],

	// Citoid
	'wgCitoidFullRestbaseURL' => [
		'ext-Citoid' => "https://{$wi->hostname}/{$wi->hostname}/",
	],

	// Collection
	'wgCommunityCollectionNamespace' => [
		'ext-Collection' => 5,
	],
	'wgCollectionMWServeURL' => [
		'ext-Collection' => 'https://restbase.wikitide.net',
	],
	'wgCollectionPODPartners' => [
		'ext-Collection' => [],
	],

	// Comments
	'wgCommentsDefaultAvatar' => [
		'default' => '/' . $wi->version . '/extensions/SocialProfile/avatars/default_ml.gif',
	],
	'wgCommentsInRecentChanges' => [
		'default' => false,
	],
	'wgCommentsSortDescending' => [
		'default' => false,
	],
	'wgCommentTempTableSchemaMigrationStage' => [
		'default' => [
			'rev_comment' => SCHEMA_COMPAT_NEW,
		],
	],

	// CommentStreams
	'wgCommentStreamsEnableTalk' => [
		'default' => false,
	],
	'wgCommentStreamsEnableSearch' => [
		'default' => true,
	],
	'wgCommentStreamsNewestStreamsOnTop' => [
		'default' => false,
	],
	'wgCommentStreamsUserAvatarPropertyName' => [
		'default' => null,
	],
	'wgCommentStreamsEnableVoting' => [
		'default' => false,
	],
	'wgCommentStreamsModeratorFastDelete' => [
		'default' => false,
	],

	// CommonsMetadata
	'wgCommonsMetadataForceRecalculate' => [
		'default' => false,
	],

	// ContactPage
	'wmgContactPageRecipientUser' => [
		'default' => null,
	],

	// Contribution Scores
	'wgContribScoreDisableCache' => [
		'default' => true,
	],
	'wgContribScoreIgnoreBots' => [
		'default' => false,
	],

	// Cookies
	'wgCookieExpiration' => [
		'default' => 30 * 86400,
	],
	'wgCookieSameSite' => [
		'default' => 'None',
	],
	'wgUseSameSiteLegacyCookies' => [
		'default' => true,
	],
	'wgCookieSetOnAutoblock' => [
		'default' => true,
	],
	'wgCookieSetOnIpBlock' => [
		'default' => true,
	],
	'wgExtendedLoginCookieExpiration' => [
		'default' => 365 * 86400,
	],

	// Cosmos
	'wgCosmosBackgroundImage' => [
		'default' => false,
	],
	'wgCosmosBackgroundImageFixed' => [
		'default' => true,
	],
	'wgCosmosBackgroundImageRepeat' => [
		'default' => false,
	],
	'wgCosmosBackgroundImageSize' => [
		'default' => 'cover',
	],
	'wgCosmosBannerBackgroundColor' => [
		'default' => '#c0c0c0',
	],
	'wgCosmosButtonBackgroundColor' => [
		'default' => '#c0c0c0',
	],
	'wgCosmosContentBackgroundColor' => [
		'default' => '#ffffff',
	],
	'wgCosmosContentWidth' => [
		'default' => 'default',
	],
	'wgCosmosContentOpacityLevel' => [
		'default' => 100,
	],
	'wgCosmosEnablePortableInfoboxEuropaTheme' => [
		'default' => true,
	],
	'wgCosmosEnabledRailModules' => [
		'default' => [
			'recentchanges' => 'normal',
			'interface' => [
				'cosmos-custom-rail-module' => 'normal',
				'cosmos-custom-sticky-rail-module' => 'sticky',
			],
		],
	],
	'wgCosmosEnableWantedPages' => [
		'default' => false,
	],
	'wgCosmosFooterBackgroundColor' => [
		'default' => '#c0c0c0',
	],
	'wgCosmosLinkColor' => [
		'default' => '#0645ad',
	],
	'wgCosmosMainBackgroundColor' => [
		'default' => '#1A1A1A',
	],
	'wgCosmosMaxSearchResults' => [
		'default' => 6,
	],
	'wgCosmosSearchDescriptionSource' => [
		'default' => 'textextracts',
	],
	'wgCosmosSearchUseActionAPI' => [
		'default' => true,
	],
	'wgCosmosSocialProfileAllowBio' => [
		'default' => true,
	],
	'wgCosmosSocialProfileFollowBioRedirects' => [
		'default' => false,
	],
	'wgCosmosSocialProfileModernTabs' => [
		'default' => true,
	],
	'wgCosmosSocialProfileNumberofGroupTags' => [
		'default' => 2,
	],
	'wgCosmosSocialProfileRoundAvatar' => [
		'default' => true,
	],
	'wgCosmosSocialProfileShowEditCount' => [
		'default' => true,
	],
	'wgCosmosSocialProfileShowGroupTags' => [
		'default' => true,
	],
	'wgCosmosSocialProfileTagGroups' => [
		'default' => [
			'bureaucrat',
			'bot',
			'sysop',
			'interface-admin'
		],
	],
	'wgCosmosToolbarBackgroundColor' => [
		'default' => '#000000',
	],
	'wgCosmosUseSocialProfileAvatar' => [
		'default' => true,
	],
	'wgCosmosWikiHeaderBackgroundColor' => [
		'default' => '#c0c0c0',
	],
	'wgCosmosWordmark' => [
		'default' => false,
	],

	// CreatePageUw
	'wgCreatePageUwUseVE' => [
		'default' => false,
	],

	// CreateWiki
	'wgCreateWikiDisallowedSubdomains' => [
		'default' => [
			'(.*)wikiforge(.*)',
			'(.*)wikitide(.*)',
			'(.*)fwikis(.*)',
			'subdomain',
			'example',
			'meta',
			'beta(meta)?',
			'community',
			'test(wiki)?',
			'wikitest',
			'help',
			'noc',
			'wc',
			'm',
			'sandbox',
			'outreach',
			'gazett?eer',
			'semantic(mediawiki)?',
			'smw',
			'wikitech',
			'wikis?',
			'www',
			'security',
			'donate',
			'blog',
			'health',
			'status',
			'acme',
			'ssl',
			'sslhost',
			'sslrequest',
			'letsencrypt',
			'deployment',
			'hostmaster',
			'wildcard',
			'list',
			'localhost',
			'mailman',
			'webmail',
			'phabricator',
			'phorge(\d+)?',
			'support',
			'static',
			'upload',
			'grafana',
			'icinga',
			'csw(\d+)?',
			'matomo(\d+)?',
			'prometheus(\d+)?',
			'misc\d+',
			'db\d+',
			'cp\d+',
			'mw\d+',
			'jobrunner\d+',
			'gluster(fs)?(\d+)?',
			'ns\d+',
			'bacula\d+',
			'mail(\d+)?',
			'ldap(\d+)?',
			'cloud\d+',
			'mon\d+',
			'swift(ac|fs|object|proxy)?(\d+)?',
			'lizardfs\d+',
			'elasticsearch(\d+)?',
			'opensearch(\d+)?',
			'rdb\d+',
			'phab(\d+)?',
			'services\d+',
			'puppet\d+',
			'test\d+',
			'dbbackup\d+',
			'graylog(\d+)?',
			'mem\d+',
			'jobchron\d+',
			'mwtask(\d+)?',
			'bots(\d+)?',
			'es\d+',
			'os\d+',
			'bast(ion)?(\d+)?',
			'reports(\d+)?',
			'(.*)wiki(pedi)?a(.*)',
		],
	],
	'wgCreateWikiCannedResponses' => [
		'default' => [
			'Approval reasons' => [
				'Perfect request' => 'Perfect. Clear purpose, scope, and topic. Please ensure your wiki complies with all aspects of the Content Policy and Code of Conduct at all times and that it does not deviate from the approved scope or else your wiki may be closed. Thank you for choosing WikiTide!',
				'Good request' => 'Pretty good. Purpose and description are a bit vague, but there is nonetheless a clear enough purpose, scope, and/or topic here. Please ensure your wiki complies with all aspects of the Content Policy and Code of Conduct at all times and that it does not deviate from the approved scope or else your wiki will be closed. Thank you for choosing WikiTide!',
				'Okay request' => 'Okay-ish. Description doesn\'t meet our requirements, but in this case the sitename, URL, and categorisation suggest this is a wiki that would follow the Content Policy made clear by the preceding fields, and it is conditionally approved as such. Please be advised that if your wiki deviates too much from this approval, remedial action can be taken by a Steward which includes wiki closure and potential revocation of wiki requesting privileges, if necessary. Please ensure your wiki complies with all aspects of Content Policy and Code of Conduct at all times. Thank you.',
				'Categorised as private' => 'The purpose and scope of your wiki is clear enough. Please ensure your wiki complies with all aspects of the Content Policy and Code of Conduct at all times or it may be closed. Please also note that I have categorised your wiki as "Private". Thank you.',
			],
			'Decline reasons' => [
				'Needs more details' => 'Can you give us a few more details on the purpose for, scope of, and topic of your wiki, and briefly describe some of your wiki\'s content in approximately 2-3 sentences? Additionally can you elaborate on your wiki\'s scope and topical focus a bit further? A few sentences describing the scope of your wiki and the sort of content it will contain should be helpful. Please go back into your original request and add to, but do not replace, your existing description. Thank you.',
				'Invalid or unclear subdomain' => 'The scope and purpose of the wiki seem clear enough. However, your requested subdomain is either invalid, is too generic, conveys a WikiTide affiliation, or suggests the wiki is an English language or multilingual wiki when it is not. Please change it to something that better reflects your wiki\'s purpose and scope. Thank you.',
				'Invalid sitename/subdomain (obsence wording)' => 'The scope and purpose of the wiki seem clear enough. However, the requested wiki name or subdomain is in violation of our Content Policy which prohibits obsence wording in wiki names and subdomains. Please change it to something that is better. Thank you.',
				'Use Public Test Wiki' => 'Please use Public Test Wiki, https://publictestwiki.com, to test the administrator and bureaucrat tools. You should review and follow all TestWiki:Policies, especially TestWiki:Testing policy and TestWiki:Main policy, reverting all tests you perform in the reverse order which you performed them. Request permissions at TestWiki:Request permissions. Thank you.',
				'Database exists (wiki active)' => 'A wiki already exists at the selected subdomain. Please visit the local wiki and contribute there. Please reach out to any local bureaucrat to request any permissions if you require them. If bureaucrats are not active on the wiki after a reasonable period of time, please start a local election and ask a Steward to evaluate it on the Stewards\' noticeboard. Thanks.',
				'Database exists (wiki closed)' => 'A wiki exists at the subdomain selected but is closed. Please visit the Requests for reopening wikis page to request to reopen the wiki or ask for help on Community noticeboard.',
				'Database exists (wiki already deleted)' => 'A wiki exists at the selected subdomain but has been deleted in accordance with the Dormancy Policy. I will request a Steward undelete it for you. When it has been undeleted and reopened, please visit the local wiki and ensure you make at least one edit or log action every 45 days. Wikis are only deleted after 6 months of complete inactivity; if you require a Dormancy Policy exemption, you should review the policy and request it once your wiki has at least 40-60 content pages. Thank you.',
				'Database exists (wiki undeleted)' => 'The selected wiki database name already exists and the wiki was closed, however, the wiki has now been reopened. Please visit the wiki and ensure you make at least one edit or log action every 45 days. Wikis are only deleted after 6 months of complete inactivity. Please reach out to any local bureaucrat to request any permissions if you require them. If bureaucrats are not active on the wiki after a reasonable period of time, please start a local election and ask a Steward to evaluate it on the Stewards\' noticeboard. Thank you.',
				'Database exists (unrelated purpose)' => 'Wiki database name and subdomain already exist. The wiki does not however seem to have the same purpose as the one you are requesting here, so you will need to request a different subdomain.  Please update this request once you have selected a new subdomain to reopen it for consideration.',
				'Duplicate request' => 'Declining as a duplicate request, which needs more information. Please do not edit this request and instead go back into your original request. Also, please do not submit duplicate requests. Thank you.',
				'Excessive requests' => 'Declining as you have requested an excessive amount of wikis. Thank you for your understanding. If you believe you have legitimate need for this amount of wikis, please reply to this request with a 2-3 sentence reasoning on why you need the wikis.',
				'Vandal request' => 'Declining as this wiki request is product of either vandalism or trolling.',
				'Content Policy (commercial activity)' => 'Declining per Content Policy provision, "The primary purpose of your wiki cannot be for commercial activity." Thank you for understanding. If in error, please edit this wiki request and articulate a clearer purpose and scope for your wiki that makes it clear how this wiki would not violate this criterion of Content Policy.',
				'Content Policy (deceive, defraud or mislead)' => 'Declining per Content Policy provision, "WikiTide does not host wikis with the sole purpose of deceiving, defrauding, or misleading people." Thank you for your understanding.',
				'Content Policy (duplicate/similar wiki)' => 'Your proposed wiki appears to duplicate, either substantially or entirely, the scope of an existing wiki, which is prohibited by the Content Policy. Could you please describe in a few more sentences by adding to, but not replacing, your existing description, the scope and focus for your wiki, and also assure us that your wiki will not be a complete or substantial duplication? If your wiki fouses on a subtopic of a bigger wiki, please clarify that. Thank you.',
				'Content Policy (file sharing service)' => 'Declining per Content Policy provision, "WikiTide does not host wikis whose main purpose is to act as a file sharing service." Thank you for your understanding.',
				'Content Policy (forks)' => 'Declining per Content Policy provision, "Direct forks of other WikiTide wikis where no attempts at mediations are made are not allowed." Thank you for your understanding.',
				'Content Policy (illegal UK activity)' => 'Declining per Content Policy provision, "WikiTide does not host any content that is illegal in the United States or the United Kingdom." Thank you for understanding. If you believe this decline reason was used incorrectly, please address this with the declining wiki creator on their user talk page first before escalating your concern to the Stewards\' noticeboard. Thank you.',
				'Content Policy (makes it difficult for other wikis)' => 'Declining per Content Policy provision, "A wiki must not create problems which make it difficult for other wikis." Thank you for understanding.',
				'Content Policy (no anarchy wikis)' => 'Declining per Content Policy provision, "WikiTide does not host wikis that operate on the basis of an anarchy system (i.e. no leadership and no rules)." Thank you for understanding.',
				'Content Policy (sexual nature involving minors)' => 'Declining per Content Policy provision, "WikiTide does not host wikis of a sexual nature which involve minors in any way." Thank you for your understanding.',
				'Content Policy (toxic communities)' => 'Declining per Content Policy provision, "WikiTide does not host wikis where the community has developed in such a way as to be characterised as toxic." Thank you for your understanding.',
				'Content Policy (unsubstantiated insult)' => 'Declining per Content Policy provision, "WikiTide does not host wikis which spread unsubstantiated insult, hate or rumours against a person or group of people." Thank you for understanding.',
				'Content Policy (violence, hatred or harrassment)' => 'Declining per Content Policy provision, "WikiTide does not host wikis that promote violence, hatred, or harassment against a person or group of people." Thank you for your understanding.',
				'Content Policy (Wikimedia-like wikis/forks)' => 'Declining per Content Policy provision, "Direct forks and forks where a substantial amount of content is copied from a Wikimedia project are not allowed." Thank you for your understanding.',
				'Content Policy (reception wiki)' => 'Declining per Content Policy provision, "No reception wikis will be permitted on the platform." Thank you for your understanding.',
				'Author request' => 'Declined at the request of the wiki requestor.',
			],
			'On hold reasons' => [
				'On hold pending response' => 'On hold pending response from the wiki requester (see the "Request Comments" tab). Please reply to the questions left by the wiki creator on this request but do not create another wiki request. Thank you.',
				'On hold pending review from another wiki creator' => 'On hold pending review from another wiki creator or Steward.',
			],
		],
	],
	'wgCreateWikiCustomDomainPage' => [
		'default' => 'Special:MyLanguage/Custom_domains',
	],
	'wgCreateWikiDatabaseClusters' => [
		'default' => [
			'c1',
		],
	],
	// Use if you want to stop wikis being created on this cluster
	'wgCreateWikiDatabaseClustersInactive' => [
		'default' => []
	],
	'wgCreateWikiDatabaseSuffix' => [
		'default' => 'wiki',
	],
	'wgCreateWikiEnableManageInactiveWikis' => [
		'default' => true,
	],
	'wgCreateWikiGlobalWiki' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
		'test21wiki' => 'test21wiki',
	],
	'wgCreateWikiEmailNotifications' => [
		'default' => true,
	],
	'wgCreateWikiNotificationEmail' => [
		'default' => 'sre@wikitide.org',
	],
	'wgCreateWikiPurposes' => [
		'default' => [
			'Alternate history wiki' => 'Alternate history wiki',
			'Class or group project education wiki' => 'Class or group project education wiki',
			'Curriculum resource wiki' => 'Curriculum resource wiki',
			'Documentation (hardware) wiki' => 'Documentation (hardware) wiki',
			'Documentation (software) wiki' => 'Documentation (software) wiki',
			'Encyclopedia (general) wiki' => 'Encyclopedia (general) wiki',
			'Encyclopedia (specialized) wiki' => 'Encyclopedia (specialized) wiki',
			'Eurovision-style song contest statistics tracking wiki' => 'Eurovision-style song contest statistics tracking wiki',
			'Fictional worldbuilding/constructed world wiki' => 'Fictional worldbuilding/constructed world wiki',
			'Minecraft server wiki' => 'Minecraft server wiki',
			'Organization (coordination) wiki' => 'Organization (coordination) wiki',
			'Political simulation wiki' => 'Political simulation wiki',
			'Roleplaying game wiki' => 'Roleplaying game wiki',
			'Video game (specified video game) information wiki' => 'Video game (specified video game) information wiki',
			'Video game (broad genre or video game series) information wiki' => 'Video game (broad genre or video game series) information wiki',
			'None of the above' => 'None of the above',
		],
	],
	'wgCreateWikiShowBiographicalOption' => [
		'default' => true,
	],
	'wgCreateWikiSQLfiles' => [
		'default' => [
			"$IP/maintenance/tables-generated.sql",
			"$IP/extensions/AbuseFilter/db_patches/mysql/tables-generated.sql",
			"$IP/extensions/AntiSpoof/sql/mysql/tables-generated.sql",
			"$IP/extensions/BetaFeatures/sql/tables-generated.sql",
			"$IP/extensions/CheckUser/schema/mysql/tables-generated.sql",
			"$IP/extensions/DataDump/sql/data_dump.sql",
			"$IP/extensions/Echo/sql/mysql/tables-generated.sql",
			"$IP/extensions/GlobalBlocking/sql/mysql/tables-generated-global_block_whitelist.sql",
			"$IP/extensions/OAuth/schema/mysql/tables-generated.sql",
		],
	],
	'wgCreateWikiStateDays' => [
		'default' => [
			'inactive' => 45,
			'closed' => 15,
			'removed' => 120,
			'deleted' => 14
		],
	],
	'wgCreateWikiCacheDirectory' => [
		'default' => '/srv/mediawiki/cache'
	],
	'wgCreateWikiCategories' => [
		'default' => [
			'Art & Architecture' => 'artarc',
			'Automotive' => 'automotive',
			'Business & Finance' => 'businessfinance',
			'Community' => 'community',
			'Education' => 'education',
			'Electronics' => 'electronics',
			'Entertainment' => 'entertainment',
			'Fandom' => 'fandom',
			'Fantasy' => 'fantasy',
			'Gaming' => 'gaming',
			'Geography' => 'geography',
			'History' => 'history',
			'Humor/Satire' => 'humor',
			'Language/Linguistics' => 'langling',
			'Leisure' => 'leisure',
			'Literature/Writing' => 'literature',
			'Media/Journalism' => 'media',
			'Medicine/Medical' => 'medical',
			'Military/War' => 'military',
			'Music' => 'music',
			'Podcast' => 'podcast',
			'Politics' => 'politics',
			'Private' => 'private',
			'Religion' => 'religion',
			'Science' => 'science',
			'Software/Computing' => 'software',
			'Song Contest' => 'songcontest',
			'Sports' => 'sport',
			'Transportation' => 'transportation',
			'Uncategorized' => 'uncategorized',
		],
	],
	'wgCreateWikiUseCategories' => [
		'default' => true,
	],
	'wgCreateWikiSubdomain' => [
		'default' => 'wikitide.org',
		'nexttide' => 'nexttide.org',
	],
	'wgCreateWikiUseClosedWikis' => [
		'default' => true,
	],
	'wgCreateWikiUseCustomDomains' => [
		'default' => true,
	],
	'wgCreateWikiUseEchoNotifications' => [
		'default' => true,
	],
	'wgCreateWikiUseExperimental' => [
		'default' => true,
	],
	'wgCreateWikiUseInactiveWikis' => [
		'default' => true,
	],
	'wgCreateWikiUsePrivateWikis' => [
		'default' => true,
	],
	'wgCreateWikiContainers' => [
		'default' => [
			'avatars' => 'public-private',
			'awards' => 'public-private',
			'local-public' => 'public-private',
			'local-thumb' => 'public-private',
			'local-transcoded' => 'public-private',
			'local-temp' => 'private',
			'local-deleted' => 'private',
			'dumps-backup' => 'public-private',
			'timeline-render' => 'public-private'
		],
	],
	'wgCreateWikiUseJobQueue' => [
		// TODO: enable
		'default' => false,
	],
	'wgRequestWikiConfirmAgreement' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgRequestWikiDisablePrivateRequests' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgRequestWikiMinimumLength' => [
		'wikitide' => 250,
	],
	'wgRequestWikiMigrationInquire' => [
		'wikitide' => true,
		'nexttide' => true,
	],
	'wgRequestWikiUseDescriptions' => [
		'wikitide' => true,
		'nexttide' => true,
	],

	// CookieWarning
	'wgCookieWarningMoreUrl' => [
		'default' => 'https://meta.wikitide.org/wiki/Special:MyLanguage/Privacy_Policy',
	],
	'wgCookieWarningEnabled' => [
		'default' => true,
	],
	'wgCookieWarningGeoIPLookup' => [
		'default' => 'php',
	],
	'wgCookieWarningGeoIp2' => [
		'default' => true,
	],
	'wgCookieWarningGeoIp2Path' => [
		'default' => '/srv/GeoLite2-City.mmdb',
	],

	// Darkmode
	'wgDarkModeTogglePosition' => [
		'default' => 'personal',
	],

	// Database
	'wgAllowSchemaUpdates' => [
		'default' => false,
	],
	'wgCompressRevisions' => [
		'default' => false,
	],
	'wgDBadminuser' => [
		'default' => 'wikiadmin',
	],
	'wgDBuser' => [
		'default' => 'mediawiki',
	],
	'wgReadOnly' => [
		'default' => false,
	],
	'wgSharedTables' => [
		'default' => [],
	],

	// Delete
	'wgDeleteRevisionsLimit' => [
		// databases don't have much memory
		// let's not overload them
		'default' => 1000,
	],

	// DiscordNotifications
	'wgDiscordAvatarUrl' => [
		'default' => '',
	],
	'wgDiscordIgnoreMinorEdits' => [
		'default' => false,
	],
	'wgDiscordIncludePageUrls' => [
		'default' => true,
	],
	'wgDiscordIncludeUserUrls' => [
		'default' => true,
	],
	'wgDiscordIncludeDiffSize' => [
		'default' => true,
	],
	'wgDiscordNotificationMovedArticle' => [
		'default' => true,
	],
	'wgDiscordNotificationFileUpload' => [
		'default' => true,
	],
	'wgDiscordNotificationProtectedArticle' => [
		'default' => true,
	],
	'wgDiscordNotificationAfterImportPage' => [
		'default' => true,
	],
	'wgDiscordNotificationShowSuppressed' => [
		'default' => false,
	],
	'wgDiscordNotificationCentralAuthWikiUrl' => [
		'wikitide' => 'https://meta.wikitide.org/',
	],
	'wgDiscordNotificationBlockedUser' => [
		'default' => true,
	],
	'wgDiscordNotificationNewUser' => [
		'default' => true,
	],
	'wgDiscordNotificationIncludeAutocreatedUsers' => [
		'default' => true,
		'metawiki' => false,
	],
	'wgDiscordAdditionalIncomingWebhookUrls' => [
		'default' => [],
	],
	'wgDiscordDisableEmbedFooter' => [
		'default' => false,
	],
	'wgDiscordExcludeConditions' => [
		'default' => [
			'experimental' => [
				'article_inserted' => [
					'groups' => [
						'sysop',
					],
					'permissions' => [
						'bot',
						'managewiki',
					],
				],
				'article_saved' => [
					'groups' => [
						'sysop',
					],
					'permissions' => [
						'bot',
						'managewiki',
					],
				],
			],
			'users' => [
				// Exclude excessive bots from all feeds
				'FuzzyBot',
			],
		],
		'+metawiki' => [
			'article_inserted' => [
				'groups' => [
					'bot',
					'flood',
				],
			],
			'article_saved' => [
				'groups' => [
					'bot',
					'flood',
				],
			],
		],
	],
	'wgDiscordEnableExperimentalCVTFeatures' => [
		'default' => true,
	],
	'wgDiscordExperimentalCVTMatchFilter' => [
		'default' => [ '(n[1i!*]gg[3*e]r|r[e3*]t[4@*a]rd|f[@*4]gg[0*o]t|ch[1!i*]nk)' ],
	],
	'wgDiscordExperimentalFeedLanguageCode' => [
		'default' => 'en',
	],

	// Description2
	'wgEnableMetaDescriptionFunctions' => [
		'ext-Description2' => true,
	],

	// DismissableSiteNotice
	'wgDismissableSiteNoticeForAnons' => [
		'default' => true,
	],

	// Display Title
	'wgDisplayTitleFollowRedirects' => [
		'default' => true,
	],
	'wgDisplayTitleHideSubtitle' => [
		'default' => false,
	],

	// DJVU
	'wgDjvuDump' => [
		'default' => '/usr/bin/djvudump',
	],
	'wgDjvuRenderer' => [
		'default' => '/usr/bin/ddjvu',
	],
	'wgDjvuTxt' => [
		'default' => '/usr/bin/djvutxt',
	],

	// DynamicPageList
	'wgDLPAllowUnlimitedCategories' => [
		'default' => false,
	],
	'wgDLPAllowUnlimitedResults' => [
		'default' => false,
	],

	// Echo
	'wgEchoCrossWikiNotifications' => [
		'default' => true,
	],
	'wgEchoUseJobQueue' => [
		'default' => true,
	],
	'wgEchoSharedTrackingCluster' => [
		'default' => 'echo',
	],
	'wgEchoSharedTrackingDB' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgEchoMentionStatusNotifications' => [
		'default' => true,
	],
	'wgEchoMaxMentionsInEditSummary' => [
		'default' => 0,
	],
	'wgEchoPerUserBlacklist' => [
		'default' => true,
	],
	'wgEchoWatchlistNotifications' => [
		'default' => false,
	],
	'wgEchoWatchlistEmailOncePerPage' => [
		'default' => true,
	],

	// Editing
	'wgEditSubmitButtonLabelPublish' => [
		'default' => false,
	],

	// ElasticSearch
	'wmgShowPopupsByDefault' => [
		'default' => false,
	],
	'wgWatchlistExpiry' => [
		'default' => false,
	],

	// ElectronPdfService
	'wgElectronPdfServiceRESTbaseURL' => [
		'ext-ElectronPdfService' => "https://restbase.wikitide.net/{$wi->hostname}/v1/page/pdf/",
	],

	// EmbedVideo
	'wgEmbedVideoEnableVideoHandler' => [
		'default' => true,
	],
	'wgEmbedVideoRequireConsent' => [
		'default' => true,
	],
	'wgEmbedVideoFetchExternalThumbnails' => [
		'default' => true,
	],
	'wgEmbedVideoDefaultWidth' => [
		'default' => 320,
	],

	// External Data
	'wgExternalDataSources' => [
		/**
		 * @note Databases should NEVER be configured here!
		 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:External_Data/Databases
		 *
		 * @note Programs should NEVER be configured here!
		 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:External_Data/Local_programs
		 *
		 * @note LDAP should NEVER be configured here!
		 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:External_Data/LDAP
		 *
		 * @note If configuring local files here, please be mindful of how it is done to avoid security implications.
		 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:External_Data/Local_files
		 *
		 * @note SOAP should NEVER be configured here, unless you understand it and can confirm the security of it is acceptable.
		 */
		'ext-ExternalData' => [
			'*' => [
				'min cache seconds' => 3600,
				'always use stale cache' => false,
				'throttle key' => '$2nd_lvl_domain$',
				'throttle interval' => 0,
				'replacements' => [],
				'allowed urls' => [],
				'options' => [
					'timeout' => 'default',
				],
				'encodings' => [
					'ASCII',
					'UTF-8',
					'Windows-1251',
					'Windows-1252',
					'Windows-1254',
					'KOI8-R',
					'ISO-8859-1',
				],
				'params' => [],
				'param filters' => [],
				'verbose' => true,
			],
		],
		'+cslmodswiki' => [
			'api.steampowered.com/*' => [
				'replacements' => [
					'STEAM_API_KEY' => $wmgExternalDataCredsCslmodswiki,
				],
			],
		],
	],

	// HTTP
	'wgHTTPConnectTimeout' => [
		'default' => 3.0,
	],
	'wgHTTPTimeout' => [
		'default' => 20,
	],

	// FlaggedRevs
	'wgFlaggedRevsProtection' => [
		'default' => false,
	],
	'wgFlaggedRevsOverride' => [
		'default' => true,
	],
	'wgFlaggedRevsTags' => [
		'default' => [
			'accuracy' => [
				'levels' => 3,
				'quality' => 2,
				'pristine' => 4,
			],
		],
	],
	'wgFlaggedRevsTagsRestrictions' => [
		'default' => [
			'accuracy' => [
				'review' => 1,
				'autoreview' => 1,
			],
		],
	],
	'wgFlaggedRevsTagsAuto' => [
		'default' => [
			'accuracy' => 1,
		],
	],
	'wgFlaggedRevsAutopromote' => [
		'default' => false,
	],
	'wgFlaggedRevsAutoReview' => [
		'default' => 3,
	],
	'wgFlaggedRevsRestrictionLevels' => [
		'default' => [
			'sysop',
		],
	],
	'wgSimpleFlaggedRevsUI' => [
		'default' => true,
	],
	'wgFlaggedRevsLowProfile' => [
		'default' => true,
	],

	// Footers
	'+wgFooterIcons' => [
		'wikitide' => [
			'poweredby' => [
				'wikitide' => [
					'src' => 'https://static.wikitide.net/commonswiki/8/8a/Hosted_by_WikiTide.svg',
					'url' => 'https://meta.wikitide.org/wiki/Special:MyLanguage/WikiTide',
					'alt' => 'Hosted by WikiTide',
				],
			],
		],
		'hsckwiki' => [
			'poweredby' => [
				'songnguxyz' => [
					'src' => 'https://static.wikitide.net/lhmnwiki/5/58/Footer.SN.xyz.svg',
					'url' => 'https://songngu.xyz',
					'alt' => 'Dự án được bảo quản bởi SongNgư.xyz',
				],
				'wikitide' => [
					'src' => 'https://static.wikitide.net/commonswiki/8/8a/Hosted_by_WikiTide.svg',
					'url' => 'https://meta.wikitide.org/wiki/Special:MyLanguage/WikiTide',
					'alt' => 'Hosted by WikiTide',
				],
			],
		],
		'lhmnwiki' => [
			'poweredby' => [
				'songnguxyz' => [
					'src' => 'https://static.wikitide.net/lhmnwiki/5/58/Footer.SN.xyz.svg',
					'url' => 'https://songngu.xyz',
					'alt' => 'Dự án được bảo quản bởi SongNgư.xyz',
				],
				'wikitide' => [
					'src' => 'https://static.wikitide.net/commonswiki/8/8a/Hosted_by_WikiTide.svg',
					'url' => 'https://meta.wikitide.org/wiki/Special:MyLanguage/WikiTide',
					'alt' => 'Hosted by WikiTide',
				],
			],
		],
		'snxyzincubatorwiki' => [
			'poweredby' => [
				'songnguxyz' => [
					'src' => 'https://static.wikitide.net/lhmnwiki/5/58/Footer.SN.xyz.svg',
					'url' => 'https://songngu.xyz',
					'alt' => 'This project is a part of SongNgu.xyz',
				],
				'wikitide' => [
					'src' => 'https://static.wikitide.net/commonswiki/8/8a/Hosted_by_WikiTide.svg',
					'url' => 'https://meta.wikitide.org/wiki/Special:MyLanguage/WikiTide',
					'alt' => 'Hosted by WikiTide',
				],
			],
		],
	],
	'wmgWikiapiaryFooterPageName' => [
		'default' => '',
	],
	'wgMaxCredits' => [
		'default' => 0,
	],
	'wgShowCreditsIfMax' => [
		'default' => true,
	],

	// Files
	'wgEnableUploads' => [
		'default' => true,
	],
	'wgMaxUploadSize' => [
		/** 250MB */
		'default' => 1024 * 1024 * 250,
		/** 20MB - qixwikiwiki */
		'qixwikiwiki' => 1024 * 1024 * 20,
	],
	'wgAllowCopyUploads' => [
		'default' => false,
	],
	'wgCopyUploadsFromSpecialUpload' => [
		'default' => false,
	],
	'wgFileExtensions' => [
		'default' => [
			'djvu',
			'gif',
			'ico',
			'jpg',
			'jpeg',
			'ogg',
			'pdf',
			'png',
			'svg',
			'webp',
		],
	],
	'wgUseQuickInstantCommons' => [
		'default' => true,
	],
	'wgQuickInstantCommonsPrefetchMaxLimit' => [
		'default' => 500,
	],
	'wgMaxImageArea' => [
		'default' => false,
	],
	'wgMaxAnimatedGifArea' => [
		'default' => '1.25e7',
	],
	'wgWikiTideCommons' => [
		'wikitide' => true,
	],
	'wgEnableImageWhitelist' => [
		'default' => false,
	],
	'wgImagePreconnect' => [
		'default' => true,
	],
	'wgShowArchiveThumbnails' => [
		'default' => true,
	],
	'wgVerifyMimeType' => [
		'default' => true,
	],
	'wgSVGMetadataCutoff' => [
		'default' => 262144,
	],
	'wgSVGConverter' => [
		'default' => 'ImageMagick',
	],
	'wgUploadMissingFileUrl' => [
		'default' => false,
	],
	'wgUploadNavigationUrl' => [
		'default' => false,
	],

	// Gallery Options
	'wgGalleryOptions' => [
		'default' => [
			'imagesPerRow' => 0,
			'imageWidth' => 120,
			'imageHeight' => 120,
			'captionLength' => true,
			'showBytes' => true,
			'showDimensions' => true,
			'mode' => 'traditional',
		],
		'theunnamedwikiwiki' => [
			'imagesPerRow' => 0,
			'imageWidth' => 120,
			'imageHeight' => 120,
			'captionLength' => true,
			'showBytes' => true,
			'showDimensions' => true,
			'mode' => 'packed',
		],
	],

	// GeoData
	'wgGlobes' => [
		'default' => [],
	],

	// GlobalBlocking
	'wgApplyGlobalBlocks' => [
		'wikitide' => true,
	],
	'wgGlobalBlockingDatabase' => [
		'default' => $wi::GLOBAL_DATABASE[$wi->wikifarm],
	],

	// GlobalCssJs
	'wgGlobalCssJsConfig' => [
		'wikitide' => [
			'wiki' => $wi::CENTRAL_WIKI[$wi->wikifarm],
			'source' => $wi::CENTRAL_WIKI[$wi->wikifarm],
		],
	],
	'+wgResourceLoaderSources' => [
		'wikitide' => [
			'metawiki' => [
				'apiScript' => '//meta.wikitide.org/w/api.php',
				'loadScript' => '//meta.wikitide.org/w/load.php',
			],
		],
	],
	'wgUseGlobalSiteCssJs' => [
		'wikitide' => false,
	],

	// GlobalPreferences
	'wgGlobalPreferencesDB' => [
		'wikitide' => $wi::GLOBAL_DATABASE[$wi->wikifarm],
	],

	// GlobalUsage
	'wgGlobalUsageDatabase' => [
		'wikitide' => 'commonswiki',
	],
	'wgGlobalUsageSharedRepoWiki' => [
		'default' => false,
	],
	'wgGlobalUsagePurgeBacklinks' => [
		'default' => false,
	],

	// GlobalUserPage
	'wgGlobalUserPageAPIUrl' => [
		'wikitide' => 'https://meta.wikitide.org/w/api.php',
	],
	'wgGlobalUserPageDBname' => [
		'wikitide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],

	// Grant Permissions for BotPasswords and OAuth
	'+wgGrantPermissions' => [
		'default' => [
			'basic' => [
				'user' => true,
			],
			'usedatadumpapi' => [
				'view-dump' => true,
				'generate-dump' => true,
				'delete-dump' => true,
			],
		],
	],
	'+wgGrantPermissionGroups' => [
		'default' => [],
	],

	// GrowthExperiments
	'wgWelcomeSurveyEnabled' => [
		'default' => false,
	],

	// HAWelcome
	'wgHAWelcomeWelcomeUsername' => [
		'default' => 'HAWelcome',
	],
	'wgHAWelcomeStaffGroupName' => [
		'default' => 'sysop',
	],
	'wgHAWelcomeSignatureFromPreferences' => [
		'default' => false,
	],

	// HasSomeColours
	'wgHasSomeColoursColourOne' => [
		'default' => '#555',
	],
	'wgHasSomeColoursColourTwo' => [
		'default' => '#d77',
	],

	// HeaderTabs
	'wgHeaderTabsRenderSingleTab' => [
		'default' => false,
	],
	'wgHeaderTabsDisableDefaultToc' => [
		'default' => true,
	],
	'wgHeaderTabsGenerateTabTocs' => [
		'default' => false,
	],
	'wgHeaderTabsEditTabLink' => [
		'default' => true,
	],

	// HideSection
	'wgHideSectionImages' => [
		'default' => false,
	],

	// HighlightLinks
	'wgHighlightLinksInCategory' => [
		'default' => [],
	],

	// Image Limits
	'wgImageLimits' => [
		'default' => [
			[ 320, 240 ],
			[ 640, 480 ],
			[ 800, 600 ],
			[ 1024, 768 ],
			[ 1280, 1024 ],
			[ 2560, 2048 ],
		],
	],

	// Image Scaling
	'wgCustomConvertCommand' => [
		'default' => 'nice -n 19 ionice -c2 -n7 gm convert %s -resize %wx%h %d',
	],

	// IncidentReporting
	'wgIncidentReportingDatabase' => [
		'default' => 'incidents',
	],
	'wgIncidentReportingServices' => [
		'default' => [],
	],
	'wgIncidentReportingTaskUrl' => [
		'default' => 'https://issue-tracker.wikitide.org/',
	],

	// Interwiki
	'wgEnableScaryTranscluding' => [
		'default' => true,
	],
	'wgInterwikiCentralDB' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgExtraInterlanguageLinkPrefixes' => [
		'default' => [
			'simple',
		],
	],
	'wgExtraLanguageNames' => [
		'default' => [],
	],

	// InterwikiDispatcher
	'wgIWDPrefixes' => [
		'default' => [
			'fandom' => [
				/** Fandom */
				'interwiki' => 'fandom',
				'url' => 'https://$2.fandom.com/wiki/$1',
				'urlInt' => 'https://$2.fandom.com/$3/wiki/$1',
				'baseTransOnly' => true,
			],
			'miraheze' => [
				/** Miraheze */
				'interwiki' => 'miraheze',
				'url' => 'https://$2.miraheze.org/wiki/$1',
				'baseTransOnly' => true,
			],
			'wikitide' => [
				/** WikiTide */
				'interwiki' => 'wt',
				'url' => 'https://$2.wikitide.org/wiki/$1',
				'dbname' => '$2wiki',
				'baseTransOnly' => true,
			],
			'wikiforge' => [
				/** WikiForge */
				'interwiki' => 'wf',
				'url' => 'https://$2.wikiforge.net/wiki/$1',
				'baseTransOnly' => true,
			],
		],
	],

	// InterwikiSorting
	'wgInterwikiSortingSort' => [
		'ext-InterwikiSorting' => 'code',
	],

	// ImportDump
	'wgImportDumpCentralWiki' => [
		'default' => 'metawiki',
	],
	'wgImportDumpInterwikiMap' => [
		'default' => [
			'fandom.com' => 'fandom',
			'miraheze.org' => 'wikiforge',
			'wikiforge.net' => 'wf',
			'wikitide.org' => 'wt',
		],
	],
	'wgImportDumpScriptCommand' => [
		'default' => 'screen -d -m bash -c ". /etc/swift-env.sh; swift download wikitide-metawiki-local-public {file-path} -o /home/$USER/{file-name}; mwscript importDump.php {wiki} -y --no-updates --username-prefix={username-prefix} /home/$USER/{file-name}; mwscript rebuildall.php {wiki} -y; mwscript initSiteStats.php {wiki} --active --update -y; rm /home/$USER/{file-name}"',
	],
	'wgImportDumpUsersNotifiedOnAllRequests' => [
		'default' => [
			'Agent Isai',
			'Reception123',
			'Universal Omega',
		],
	],

	// Imports
	'wgImportSources' => [
		'default' => [
			'meta',
			'mw',
			'wikipedia',
			'metawikimedia',
		],
		'+hkrailwiki' => [
			'zhwikipedia',
			'hkrailfan',
		],
	],

	// IPInfo
	'wgIPInfoGeoLite2Prefix' => [
		'default' => '/srv/mediawiki/geoip/GeoLite2-',
	],

	// JavascriptSlideshow
	'wgHtml5' => [
		'ext-JavascriptSlideshow' => true,
	],

	// JsonConfig
	'wgJsonConfigEnableLuaSupport' => [
		'default' => true,
	],
	'wgJsonConfigInterwikiPrefix' => [
		'default' => 'commons',
		'commonswiki' => 'meta',
	],
	'wgJsonConfigModels' => [
		'default' => [
			'Map.JsonConfig' => JsonConfig\JCMapDataContent::class,
			'Tabular.JsonConfig' => JsonConfig\JCTabularContent::class,
		],
	],

	// Kartographer
	'wgKartographerDfltStyle' => [
		'default' => 'osm-intl',
		'hkrailwiki' => '.',
	],
	'wgKartographerEnableMapFrame' => [
		'default' => true,
	],
	'wgKartographerMapServer' => [
		'default' => 'https://tile.openstreetmap.org',
	],
	'wgKartographerSrcsetScales' => [
		'default' => [
			1.3,
			1.5,
			2,
			2.6,
			3,
		],
		'hkrailwiki' => [
			1,
		],
	],
	'wgKartographerStaticMapframe' => [
		'default' => false,
	],
	'wgKartographerSimpleStyleMarkers' => [
		'default' => true,
		'hkrailwiki' => false,
	],
	'wgKartographerStyles' => [
		'default' => [
			'osm-intl',
			'osm',
		],
	],
	'wgKartographerUseMarkerStyle' => [
		'default' => false,
	],
	'wgKartographerWikivoyageMode' => [
		'default' => false,
	],

	// Language
	'wgLanguageCode' => [
		'default' => 'en',
	],

	// LDAP
	'wgLDAPDomainNames' => [
		'accountsinternalwiki' => [
			'wikitide',
		],
	],
	'wgLDAPServerNames' => [
		'accountsinternalwiki' => [
			'wikitide' => 'ldap.wikitide.net',
		],
	],
	'wgLDAPEncryptionType' => [
		'accountsinternalwiki' => [
			'wikitide' => 'ssl',
		],
	],
	'wgLDAPSearchAttributes' => [
		'accountsinternalwiki' => [
			'wikitide' => 'uid',
		],
	],
	'wgLDAPBaseDNs' => [
		'accountsinternalwiki' => [
			'wikitide' => 'dc=wikitide,dc=org',
		],
	],
	'wgLDAPUserBaseDNs' => [
		'accountsinternalwiki' => [
			'wikitide' => 'ou=people,dc=wikitide,dc=org',
		],
	],
	'wgLDAPProxyAgent' => [
		'accountsinternalwiki' => [
			'wikitide' => 'cn=admin,dc=wikitide,dc=org',
		],
	],
	'wgLDAPProxyAgentPassword' => [
		'accountsinternalwiki' => [
			'wikitide' => $wmgLdapPassword,
		],
	],
	'wgLDAPWriterDN' => [
		'accountsinternalwiki' => [
			'wikitide' => 'cn=admin,dc=wikitide,dc=org',
		],
	],
	'wgLDAPWriterPassword' => [
		'accountsinternalwiki' => [
			'wikitide' => $wmgLdapPassword,
		],
	],
	'wgLDAPWriteLocation' => [
		'accountsinternalwiki' => [
			'wikitide' => 'ou=people,dc=wikitide,dc=org',
		],
	],
	'wgLDAPAddLDAPUsers' => [
		'accountsinternalwiki' => [
			'wikitide' => true,
		],
	],
	'wgLDAPUpdateLDAP' => [
		'accountsinternalwiki' => [
			'wikitide' => true,
		],
	],
	'wgLDAPPasswordHash' => [
		'accountsinternalwiki' => [
			'wikitide' => 'ssha',
		],
	],
	'wgLDAPPreferences' => [
		'accountsinternalwiki' => [
			'wikitide' => [
				'email' => 'mail',
				'realname' => 'givenName',
			],
		],
	],
	'wgLDAPUseFetchedUsername' => [
		'accountsinternalwiki' => [
			'wikitide' => true,
		],
	],
	'wgLDAPLowerCaseUsernameScheme' => [
		'accountsinternalwiki' => [
			'wikitide' => false,
			'invaliddomain' => false,
		],
	],
	'wgLDAPLowerCaseUsername' => [
		'accountsinternalwiki' => [
			'wikitide' => false,
			'invaliddomain' => false,
		],
	],
	'wgLDAPOptions' => [
		'accountsinternalwiki' => [
			'wikitide' => [
				'LDAP_OPT_X_TLS_CACERTFILE' => '/etc/ssl/certs/ISRG_Root_X1.pem',
			],
		],
	],
	'wgLDAPDebug' => [
		'accountsinternalwiki' => 1,
	],

	// License
	'wgRightsIcon' => [],
	'wgRightsPage' => [
		'default' => '',
	],
	'wgRightsText' => [
		'harrypotterwiki' => 'Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)',
	],
	'wgRightsUrl' => [
		'harrypotterwiki' => 'https://creativecommons.org/licenses/by-sa/3.0',
	],
	'wmgWikiLicense' => [
		'default' => 'cc-by-sa',
	],

	// Links?
	'+wgUrlProtocols' => [
		'default' => [],
	],
	'wgExternalLinksSchemaMigrationStage' => [
		'default' => SCHEMA_COMPAT_WRITE_BOTH | SCHEMA_COMPAT_READ_OLD,
	],

	// LinkTarget
	'wgLinkTargetParentClasses' => [
		'default' => [],
		'sdiywiki' => [
			'_blank' => [
				''
			]
		 ],
		'scruffywiki' => [
			'_blank' => [
				''
			]
		 ],
		'simpleelectronicswiki' => [
			'_blank' => [
				''
			]
		 ],
	],

	// LinkTitles
	'wgLinkTitlesFirstOnly' => [
		'default' => true,
	],
	'wgLinkTitlesParseOnEdit' => [
		'default' => true,
	],
	'wgLinkTitlesSameNamespace' => [
		'default' => true,
	],
	'wgLinkTitlesSourceNamespaces' => [
		'default' => [],
	],
	'wgLinkTitlesTargetNamespaces' => [
		'default' => [],
	],

	// Linter
	'wgLinterSubmitterWhitelist' => [
		'ext-Linter' => [
			/** localhost */
			'127.0.0.1' => true,
			/** jobrunner21 */
			'10.0.2.8' => true,
			/** mw21 */
			'10.0.2.5' => true,
			'23.95.103.21' => true,
			/** mw22 */
			'10.0.2.6' => true,
			'23.95.103.22' => true,
			/** test21 */
			'10.0.2.9' => true,
			'23.95.103.27' => true,
		],
	],

	// Lingo
	'wgexLingoPage' => [
		'default' => 'Terminology',
	],
	'wgexLingoDisplayOnce' => [
		'default' => false,
	],
	'wgexLingoEnableApprovedRevs' => [
		'default' => false,
	],

	// Loops
	'egLoopsCountLimit' => [
		// DO NOT RAISE FOR ANY WIKI -- Universal Omega
		'default' => 100,
	],

	// Mail
	'wgAllowHTMLEmail' => [
		'default' => true,
	],
	'wgEnableEmail' => [
		'default' => true,
	],
	'wgSMTP' => [
		'default' => [
			'host' => 'ssl://smtp-relay.gmail.com',
			'port' => 465,
			'IDHost' => 'wikitide.org',
			'auth' => false,
		],
	],
	'wgEnotifWatchlist' => [
		'default' => true,
	],
	'wgUserEmailUseReplyTo' => [
		'default' => true,
	],
	'wgEmailConfirmToEdit' => [
		'default' => false,
	],
	'wgEmergencyContact' => [
		'default' => 'noreply@wikitide.org',
	],
	'wgEnableSpecialMute' => [
		'default' => true,
	],
	'wgEnableUserEmailMuteList' => [
		'default' => true,
	],
	'wgPasswordSender' => [
		'default' => 'noreply@wikitide.org',
	],

	// ManageWiki
	'wgManageWiki' => [
		'default' => [
			'core' => true,
			'extensions' => true,
			'namespaces' => true,
			'permissions' => true,
			'settings' => true,
		],
	],
	'wgManageWikiExtensionsDefault' => [
		'default' => [
			'cite',
			'citethispage',
			'codeeditor',
			'darkmode',
			'globaluserpage',
			'minervaneue',
			'mobilefrontend',
			'monobook',
			'purge',
			'syntaxhighlight_geshi',
			'textextracts',
			'timeless',
			'wikiseo',
		],
	],
	'wgManageWikiPermissionsAdditionalAddGroups' => [
		'default' => [],
	],
	'wgManageWikiPermissionsAdditionalRights' => [
		'default' => [
			'*' => [
				'editmyoptions' => true,
				'editmyprivateinfo' => true,
				'editmywatchlist' => true,
				'oathauth-enable' => true,
				'read' => true,
				'viewmyprivateinfo' => true,
				'writeapi' => true,
			],
			'suppress' => [
				'abusefilter-hidden-log' => true,
				'abusefilter-hide-log' => true,
				'browsearchive' => true,
				'deletedhistory' => true,
				'deletedtext' => true,
				'deletelogentry' => true,
				'deleterevision' => true,
				'hideuser' => true,
				'suppressionlog' => true,
				'suppressrevision' => true,
				'viewsuppressed' => true,
			],
			'user' => [
				'mwoauthmanagemygrants' => true,
				'user' => true,
			],
		],
		'+metawiki' => [
			'checkuser' => [
				'abusefilter-privatedetails' => true,
				'abusefilter-privatedetails-log' => true,
				'checkuser' => true,
				'checkuser-log' => true,
				'securepoll-view-voter-pii' => true,
			],
			'confirmed' => [
				'mwoauthproposeconsumer' => true,
				'mwoauthupdateownconsumer' => true,
			],
			'global-renamer' => [
				'centralauth-rename' => true,
			],
			'global-sysop' => [
				'abusefilter-modify-global' => true,
				'centralauth-lock' => true,
				'globalblock' => true,
			],
			'proxybot' => [
				'globalblock' => true,
				'centralauth-lock' => true,
			],
			'steward' => [
				'abusefilter-modify-global' => true,
				'centralauth-lock' => true,
				'centralauth-suppress' => true,
				'centralauth-rename' => true,
				'centralauth-unmerge' => true,
				'createwiki' => true,
				'globalblock' => true,
				'managewiki' => true,
				'managewiki-restricted' => true,
				'noratelimit' => true,
				'oathauth-verify-user' => true,
				'userrights' => true,
				'userrights-interwiki' => true,
				'globalgroupmembership' => true,
				'globalgrouppermissions' => true,
			],
			'sysadmin' => [
				'centralauth-lock' => true,
				'centralauth-rename' => true,
				'globalblock' => true,
				'globalgroupmembership' => true,
				'globalgrouppermissions' => true,
				'handle-import-dump-interwiki' => true,
				'handle-import-dump-requests' => true,
				'handle-pii' => true,
				'oathauth-verify-user' => true,
				'oathauth-disable-for-user' => true,
				'view-private-import-dump-requests' => true,
				'userrights' => true,
				'userrights-interwiki' => true,
			],
			'sysop' => [
				'interwiki' => true,
			],
			'user' => [
				'request-import-dump' => true,
				'requestwiki' => true,
			],
			'wiki-request-reviewer' => [
				'createwiki' => true,
			],
		],
		'+metanexttide' => [
			'checkuser' => [
				'abusefilter-privatedetails' => true,
				'abusefilter-privatedetails-log' => true,
				'checkuser' => true,
				'checkuser-log' => true,
				'securepoll-view-voter-pii' => true,
			],
			'confirmed' => [
				'mwoauthproposeconsumer' => true,
				'mwoauthupdateownconsumer' => true,
			],
			'global-renamer' => [
				'centralauth-rename' => true,
			],
			'global-sysop' => [
				'abusefilter-modify-global' => true,
				'centralauth-lock' => true,
				'globalblock' => true,
			],
			'proxybot' => [
				'globalblock' => true,
				'centralauth-lock' => true,
			],
			'steward' => [
				'abusefilter-modify-global' => true,
				'centralauth-lock' => true,
				'centralauth-suppress' => true,
				'centralauth-rename' => true,
				'centralauth-unmerge' => true,
				'createwiki' => true,
				'globalblock' => true,
				'managewiki' => true,
				'managewiki-restricted' => true,
				'noratelimit' => true,
				'oathauth-verify-user' => true,
				'userrights' => true,
				'userrights-interwiki' => true,
				'globalgroupmembership' => true,
				'globalgrouppermissions' => true,
			],
			'sysadmin' => [
				'centralauth-lock' => true,
				'centralauth-rename' => true,
				'globalblock' => true,
				'globalgroupmembership' => true,
				'globalgrouppermissions' => true,
				'handle-import-dump-interwiki' => true,
				'handle-import-dump-requests' => true,
				'handle-pii' => true,
				'oathauth-verify-user' => true,
				'oathauth-disable-for-user' => true,
				'view-private-import-dump-requests' => true,
				'userrights' => true,
				'userrights-interwiki' => true,
			],
			'sysop' => [
				'interwiki' => true,
			],
			'user' => [
				'request-import-dump' => true,
			],
			'wiki-request-reviewer' => [
				'createwiki' => true,
			],
		],
		'+testwiki' => [
			'sysop' => [
				'createwiki' => true,
			],
		],
		'+wikitide' => [
			'checkuser' => [
				'abusefilter-privatedetails' => true,
				'abusefilter-privatedetails-log' => true,
				'checkuser' => true,
				'checkuser-log' => true,
				'checkuser-temporary-account' => true,
				'checkuser-temporary-account-log' => true,
			],
			'steward' => [
				'userrights' => true,
			],
		],
		'+nexttide' => [
			'checkuser' => [
				'abusefilter-privatedetails' => true,
				'abusefilter-privatedetails-log' => true,
				'checkuser' => true,
				'checkuser-log' => true,
				'checkuser-temporary-account' => true,
				'checkuser-temporary-account-log' => true,
			],
			'steward' => [
				'userrights' => true,
			],
		],
		'+ext-Flow' => [
			'suppress' => [
				'flow-suppress' => true,
			],
		],
	],
	'wgManageWikiPermissionsAdditionalRemoveGroups' => [
		'default' => [],
	],
	'wgManageWikiPermissionsDisallowedRights' => [
		'default' => [
			'any' => [
				'abusefilter-modify-global',
				'bigdelete',
				'checkuser',
				'checkuser-log',
				'checkuser-temporary-account',
				'checkuser-temporary-account-log',
				'createwiki',
				'editothersprofiles-private',
				'managewiki-editdefault',
				'managewiki-restricted',
				'moderation-checkuser',
				'requestwiki',
				'securepoll-view-voter-pii',
				'siteadmin',
				'smw-admin',
				'smw-viewjobqueuewatchlist',
				'themedesigner',
				'userrights',
				'userrights-interwiki',
				'writeapi',
			],
			'*' => [
				'autoconfirmed',
				'editmyoptions',
				'editmyprivateinfo',
				'editmywatchlist',
				'editsitecss',
				'editsitejs',
				'editsitejson',
				'editusercss',
				'edituserjs',
				'edituserjson',
				'generate-dump',
				'interwiki',
				'ipblock-exempt',
				'managewiki',
				'noratelimit',
				'read',
				'skipcaptcha',
				'stopforumspam',
				'torunblocked',
				'viewmyprivateinfo',
				'viewmywatchlist',
			],
			'user' => [
				'autoconfirmed',
				'interwiki',
				'ipblock-exempt',
				'managewiki',
				'noratelimit',
				'skipcaptcha',
			],
		],
		'+wikitide' => [
			'any' => [
				'abusefilter-hide-log',
				'abusefilter-hidden-log',
				'abusefilter-private',
				'abusefilter-private-log',
				'abusefilter-privatedetails',
				'abusefilter-privatedetails-log',
				'autocreateaccount',
				'centralauth-createlocal',
				'centralauth-lock',
				'centralauth-rename',
				'centralauth-suppress',
				'centralauth-unmerge',
				'flow-suppress',
				'globalblock',
				'globalblock-exempt',
				'globalgroupmembership',
				'globalgrouppermissions',
				'hideuser',
				'ipinfo',
				'ipinfo-view-basic',
				'ipinfo-view-full',
				'ipinfo-view-log',
				'mwoauthmanageconsumer',
				'mwoauthmanagemygrants',
				'mwoauthsuppress',
				'mwoauthviewprivate',
				'mwoauthviewsuppressed',
				'oathauth-api-all',
				'oathauth-enable',
				'oathauth-disable-for-user',
				'oathauth-verify-user',
				'oathauth-view-log',
				'renameuser',
				'smw-patternedit',
				'suppressionlog',
				'suppressrevision',
				'titleblacklistlog',
				'updatepoints',
				'viewglobalprivatefiles',
				'viewpmlog',
				'viewsuppressed',
			],
		],
		'+nexttide' => [
			'any' => [
				'abusefilter-hide-log',
				'abusefilter-hidden-log',
				'abusefilter-private',
				'abusefilter-private-log',
				'abusefilter-privatedetails',
				'abusefilter-privatedetails-log',
				'autocreateaccount',
				'centralauth-createlocal',
				'centralauth-lock',
				'centralauth-rename',
				'centralauth-suppress',
				'centralauth-unmerge',
				'flow-suppress',
				'globalblock',
				'globalblock-exempt',
				'globalgroupmembership',
				'globalgrouppermissions',
				'hideuser',
				'ipinfo',
				'ipinfo-view-basic',
				'ipinfo-view-full',
				'ipinfo-view-log',
				'mwoauthmanageconsumer',
				'mwoauthmanagemygrants',
				'mwoauthsuppress',
				'mwoauthviewprivate',
				'mwoauthviewsuppressed',
				'oathauth-api-all',
				'oathauth-enable',
				'oathauth-disable-for-user',
				'oathauth-verify-user',
				'oathauth-view-log',
				'renameuser',
				'smw-patternedit',
				'suppressionlog',
				'suppressrevision',
				'titleblacklistlog',
				'updatepoints',
				'viewglobalprivatefiles',
				'viewpmlog',
				'viewsuppressed',
			],
			'*' => [
				'centralauth-merge',
				'globalblock-whitelist',
			],
			'user' => [
				'globalblock-whitelist',
			],
		],
	],
	'wgManageWikiPermissionsDisallowedGroups' => [
		'wikitide' => [
			'checkuser',
			'oversight',
			'smwadministrator',
			'steward',
			'staff',
			'suppress',
			'sysadmin',
		],
		'nexttide' => [
			'checkuser',
			'oversight',
			'smwadministrator',
			'steward',
			'staff',
			'suppress',
			'sysadmin',
		],
	],
	'wgManageWikiPermissionsDefaultPrivateGroup' => [
		'default' => 'member',
	],
	'wgManageWikiHelpUrl' => [
		'default' => '//meta.wikitide.org/wiki/Special:MyLanguage/ManageWiki',
	],
	'wgManageWikiForceSidebarLinks' => [
		'default' => false,
	],

	// Maps
	'egMapsDefaultService' => [
		'ext-Maps' => 'leaflet',
	],
	'egMapsDisableSmwIntegration' => [
		'ext-Maps' => true,
	],

	// MassMessage
	'wgAllowGlobalMessaging' => [
		'default' => false,
		'metawiki' => true,
	],

	// MediaWikiChat settings
	'wgChatLinkUsernames' => [
		'default' => false,
	],
	'wgChatMeCommand' => [
		'default' => false,
	],

	// Medik settings
	'wgMedikColor' => [
		'default' => '#FFBE00',
	],
	'wgMedikContentWidth' => [
		'default' => 'default',
	],
	'wgMedikLogoWidth' => [
		'default' => 'default',
	],
	'wgMedikResponsive' => [
		'default' => true,
	],
	'wgMedikShowLogo' => [
		'default' => 'none',
	],
	'wgMedikUseLogoWithoutText' => [
		'default' => false,
	],

	// Metrolook settings
	'wgMetrolookDownArrow' => [
		'default' => true,
	],
	'wgMetrolookUploadButton' => [
		'default' => true,
	],
	'wgMetrolookBartile' => [
		'default' => true,
	],
	'wgMetrolookMobile' => [
		'default' => true,
	],
	'wgMetrolookUseIconWatch' => [
		'default' => true,
	],
	'wgMetrolookLine' => [
		'default' => true,
	],
	'wgMetrolookFeatures' => [
		'default' => [
			'collapsiblenav' => [
				'global' => false,
				'user' => true
			]
		],
	],

	// MinervaNeue
	'wgMinervaEnableSiteNotice' => [
		'default' => true,
	],
	'wgMinervaApplyKnownTemplateHacks' => [
		'default' => true,
	],
	'wgMinervaAlwaysShowLanguageButton' => [
		'default' => true,
	],
	'wgMinervaTalkAtTop' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => true,
		],
	],
	'wgMinervaHistoryInPageActions' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMinervaAdvancedMainMenu' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMinervaPersonalMenu' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMinervaOverflowInPageActions' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMinervaShowCategories' => [
		'default' => [
			'base' => false,
			'loggedin' => false,
			'amc' => true,
		],
	],

	// Miscellaneous
	'wgAllowDisplayTitle' => [
		'default' => true,
	],
	'wgRestrictDisplayTitle' => [
		'default' => true,
		'ext-NoTitle' => false,
	],
	'wgCapitalLinks' => [
		'default' => true,
	],
	'wgEnableMagicLinks' => [
		'default' => [
			'ISBN' => false,
			'PMID' => false,
			'RFC' => false,
		],
	],
	'wgActiveUserDays' => [
		'default' => 30,
	],
	'wgEnableCanonicalServerLink' => [
		'default' => true,
	],
	'wgPageCreationLog' => [
		'default' => true,
	],
	'wgRCWatchCategoryMembership' => [
		'default' => false,
	],
	'wgExpensiveParserFunctionLimit' => [
		'default' => 99,
	],
	'wgAllowSlowParserFunctions' => [
		'default' => false,
	],
	'wgExternalLinkTarget' => [
		'default' => false,
	],
	'wgGitInfoCacheDirectory' => [
		'default' => '/srv/mediawiki/cache/' . $wi->version . '/gitinfo',
	],
	'wgAllowExternalImages' => [
		'default' => false,
	],
	'wgFragmentMode' => [
		'default' => [
			'html5',
			'legacy'
		],
	],
	'wgTrustedMediaFormats' => [
		'default' => [
			MEDIATYPE_BITMAP,
			MEDIATYPE_AUDIO,
			MEDIATYPE_VIDEO,
			'image/svg+xml',
			'application/pdf',
		],
		'+ext-3d' => [
			'application/sla',
		],
	],
	'wgNativeImageLazyLoading' => [
		'default' => true,
	],
	'wgShellRestrictionMethod' => [
		'default' => 'firejail',
	],
	'wgShellboxUrls' => [
		'default' => [
			'default' => null,
		],
		'+ext-Score' => [
			'score' => 'http://localhost:6024/shellbox',
		],
	],
	'wgCrossSiteAJAXdomains' => [
		'default' => [
			'meta.wikitide.org',
		],
		'nexttide' => [
			'meta.wikitide.org',
		],
	],
	'wgTidyConfig' => [
		'default' => [
			'driver' => 'RemexHtml',
			'pwrap' => false,
		],
	],
	'wgWhitelistRead' => [
		'default' => [],
	],
	'wgWhitelistReadRegexp' => [
		'default' => [],
	],
	'wgDisabledVariants' => [
		'default' => [],
		'hkrailwiki' => [
			'zh',
			'zh-hant',
			'zh-hans',
		],
	],
	'wgDefaultLanguageVariant' => [
		'default' => false,
		'hkrailwiki' => 'zh-hk',
	],

	// MobileFrontend
	'wgMFAutodetectMobileView' => [
		'default' => false,
	],
	'wgDefaultMobileSkin' => [
		'default' => 'minerva',
	],
	'wgMobileUrlTemplate' => [
		'default' => '',
	],
	'wgMFMobileHeader' => [
		'ext-MobileFrontend' => 'X-Subdomain',
	],
	'wgMFRemovableClasses' => [
		'default' => [
			'beta' => [],
			'base' => [
				'.navbox',
				'.vertical-navbox',
				'.nomobile',
			],
		],
	],
	'wgMFNoindexPages' => [
		'ext-MobileFrontend' => false,
	],
	'wgMFStripResponsiveImages' => [
		'1.39' => false,
	],
	'wgMFUseDesktopSpecialHistoryPage' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMFUseDesktopSpecialWatchlistPage' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMFUseDesktopContributionsPage' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'amc' => true,
		],
	],
	'wgMFQueryPropModules' => [
		'default' => [
			'pageprops',
		],
	],
	'wgMFSearchAPIParams' => [
		'default' => [
			'ppprop' => 'displaytitle',
		],
	],
	'wgMFSearchGenerator' => [
		'default' => [
			'name' => 'prefixsearch',
			'prefix' => 'ps',
		],
	],
	'wgMFEnableWikidataDescriptions' => [
		'default' => [
			'base' => false,
			'beta' => true,
		],
	],
	'wgMFDisplayWikibaseDescriptions' => [
		'default' => [
			'search' => false,
			'nearby' => false,
			'watchlist' => false,
			'tagline' => false,
		],
	],
	'wgMFCollapseSectionsByDefault' => [
		'default' => true,
	],

	// Moderation extension settings
	// Enable or disable notifications.
	'wgModerationNotificationEnable' => [
		'default' => false,
	],
	// Notify administrator only about new pages requests.
	'wgModerationNotificationNewOnly' => [
		'default' => false,
	],
	// Email to send notifications to.
	'wgModerationEmail' => [
		'default' => $wgPasswordSender,
	],
	'wgModerationPreviewLink' => [
		'default' => false,
	],
	'wgModerationEnableEditChange' => [
		'default' => false,
	],
	'wgModerationOnlyInNamespaces' => [
		'default' => [],
	],

	// MsCatSelect vars
	'wgMSCS_WarnNoCategories' => [
		'default' => true,
	],

	// MsUpload settings
	'wgMSU_useDragDrop' => [
		'default' => true,
	],
	'wgMSU_showAutoCat' => [
		'default' => false,
	],
	'wgMSU_checkAutoCat' => [
		'default' => false,
	],
	'wgMSU_confirmReplace' => [
		'default' => false,
	],

	// MultimediaViewer (not beta)
	'wgMediaViewerEnableByDefault' => [
		'default' => true,
	],

	// Math
	'wgMathValidModes' => [
		'default' => [
			'mathml'
		],
	],

	// MatomoAnalytics
	'wgMatomoAnalyticsDatabase' => [
		'default' => 'wtglobal',
	],
	'wgMatomoAnalyticsServerURL' => [
		'default' => 'https://analytics.wikitide.net/',
	],
	'wgMatomoAnalyticsUseDB' => [
		'default' => true,
	],
	'wgMatomoAnalyticsSiteID' => [
		'default' => 1,
	],
	'wgMatomoAnalyticsGlobalID' => [
		'default' => 1,
	],
	'wgMatomoAnalyticsDisableCookie' => [
		'default' => true,
	],

	// MultiBoilerplate
	'wgMultiBoilerplateOptions' => [
		'ext-MultiBoilerplate' => false,
	],
	'wgMultiBoilerplateOverwrite' => [
		'ext-MultiBoilerplate' => false,
	],

	// NamespacePreload
	'wgNamespacePreloadDoExpansion' => [
		'default' => true,
	],

	// NearbyPages
	'wgNearbyPagesUrl' => [
		'default' => '/w/api.php',
	],

	// New User Email Notification
	'wgNewUserNotifEmailTargets' => [
		'default' => [],
	],

	// NewUserMessage configs
	'wgNewUserMessageOnAutoCreate' => [
		'default' => false,
	],

	// nofollow links
	'wgNoFollowLinks' => [
		'default' => true,
	],
	'wgNoFollowNsExceptions' => [
		'default' => [],
	],

	// Users Notified On All Changes
	'wgUsersNotifiedOnAllChanges' => [
		'default' => [],
	],

	// OATHAuth
	'wgOATHExclusiveRights' => [
		'default' => [
			'abusefilter-privatedetails',
			'abusefilter-privatedetails-log',
			'checkuser',
			'checkuser-log',
			'suppressionlog',
			'suppressrevision',
			'userrights',
			'userrights-interwiki',
		],
		'+metawiki' => [
			'editsitejs',
			'edituserjs',
		],
		'+wikitide' => [
			'centralauth-lock',
			'centralauth-rename',
			'centralauth-suppress',
			'editsitejs',
			'editusercss',
			'edituserjs',
			'globalblock',
			'globalgroupmembership',
			'globalgrouppermissions',
		],
	],
	'wgOATHRequiredForGroups' => [
		'metawiki' => [
			'checkuser',
			'global-sysop',
			'interface-admin',
			'steward',
			'sysadmin',
			'suppress',
		],
		'metanexttide' => [
			'checkuser',
			'global-sysop',
			'interface-admin',
			'steward',
			'sysadmin',
			'suppress',
		],
		'wikitide' => [
			'checkuser',
			'interface-admin',
			'steward',
			'suppress',
		],
		'nexttide' => [
			'checkuser',
			'interface-admin',
			'steward',
			'suppress',
		],
	],
	// OAuth
	'wgMWOAuthCentralWiki' => [
		'wikitide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
		'nexttide' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],
	'wgOAuth2GrantExpirationInterval' => [
		'default' => 'PT4H',
	],
	'wgOAuth2RefreshTokenTTL' => [
		'default' => 'P365D',
	],
	'wgMWOAuthSecureTokenTransfer' => [
		'default' => true,
	],
	'wgOAuth2PublicKey' => [
		'default' => '/srv/mediawiki/config/OAuth2.key.pub',
	],
	'wgOAuth2PrivateKey' => [
		'default' => '/srv/mediawiki/config/OAuth2.key',
	],

	// Page Images
	'wgPageImagesNamespaces' => [
		'default' => [
			NS_MAIN,
		],
	],
	'wgPageImagesDenylist' => [
		'ext-PageImages' => [
			[
				'type' => 'db',
				'page' => 'MediaWiki:Pageimages-denylist',
				'db' => false,
			],
		],
	],
	'wgPageImagesExpandOpenSearchXml' => [
		'default' => false,
	],
	'wgPageImagesLeadSectionOnly' => [
		'default' => true,
	],

	// Pagelang
	'wgPageLanguageUseDB' => [
		'default' => false,
	],

	// PageForms
	'wgPageFormsRenameEditTabs' => [
		'default' => false,
	],
	'wgPageFormsRenameMainEditTab' => [
		'default' => false,
	],
	'wgPageFormsSimpleUpload' => [
		'default' => false,
	],
	'wgPageFormsLinkAllRedLinksToForms' => [
		'default' => false,
	],

	// Page Size
	'wgMaxArticleSize' => [
		'default' => 2048,
	],

	// ParserFunctions
	'wgPFEnableStringFunctions' => [
		'default' => false,
	],

	// Parsoid
	'wgParsoidSettings' => [
		'default' => [
			'useSelser' => true,
		],
		'+ext-Linter' => [
			'linting' => true,
		],
	],

	// PdfHandler
	'wgPdfProcessor' => [
		'default' => '/usr/local/bin/mediawiki-firejail-ghostscript',
	],
	'wgPdfPostProcessor' => [
		'default' => '/usr/local/bin/mediawiki-firejail-convert',
	],

	// Permissions
	'wgGroupsAddToSelf' => [
		'default' => [],
	],
	'wgGroupsRemoveFromSelf' => [
		'default' => [],
	],
	'+wgRevokePermissions' => [
		'default' => [],
		'+ext-MediaWikiChat' => [
			'blockedfromchat' => [
				'chat' => true,
			],
		],
	],
	'wgImplicitGroups' => [
		'default' => [
			'*',
			'user',
			'autoconfirmed'
		],
	],

	// Password policy
	'wgPasswordPolicy' => [
		'default' => [
			'policies' => [
				'default' => [
					'MinimalPasswordLength' => [ 'value' => 6, 'suggestChangeOnLogin' => true ],
					'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
					'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				],
				'bot' => [
					'MinimalPasswordLength' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
					'MinimumPasswordLengthToLogin' => [ 'value' => 6, 'suggestChangeOnLogin' => true ],
					'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
					'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				],
				'sysop' => [
					'MinimalPasswordLength' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
					'MinimumPasswordLengthToLogin' => [ 'value' => 6, 'suggestChangeOnLogin' => true ],
					'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
					'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				],
				'bureaucrat' => [
					'MinimalPasswordLength' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
					'MinimumPasswordLengthToLogin' => [ 'value' => 6, 'suggestChangeOnLogin' => true ],
					'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
					'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
					'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				],
			],
			'checks' => [
				'MinimalPasswordLength' => 'PasswordPolicyChecks::checkMinimalPasswordLength',
				'MinimumPasswordLengthToLogin' => 'PasswordPolicyChecks::checkMinimumPasswordLengthToLogin',
				'PasswordCannotBeSubstringInUsername' => 'PasswordPolicyChecks::checkPasswordCannotBeSubstringInUsername',
				'PasswordCannotMatchDefaults' => 'PasswordPolicyChecks::checkPasswordCannotMatchDefaults',
				'MaximalPasswordLength' => 'PasswordPolicyChecks::checkMaximalPasswordLength',
				'PasswordNotInCommonList' => 'PasswordPolicyChecks::checkPasswordNotInCommonList',
			],
		],
	],
	'wgCentralAuthGlobalPasswordPolicies' => [
		'wikitide' => [
			'global-sysop' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
			'steward' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
			'sysadmin' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
		],
		'nexttide' => [
			'global-sysop' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
			'steward' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
			'sysadmin' => [
				'MinimalPasswordLength' => [ 'value' => 12, 'suggestChangeOnLogin' => true ],
				'MinimumPasswordLengthToLogin' => [ 'value' => 8, 'suggestChangeOnLogin' => true ],
				'PasswordCannotBeSubstringInUsername' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'PasswordCannotMatchDefaults' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
				'MaximalPasswordLength' => [ 'value' => 4096, 'suggestChangeOnLogin' => true ],
				'PasswordNotInCommonList' => [ 'value' => true, 'suggestChangeOnLogin' => true ],
			],
		],
	],

	// Popups
	'wgPopupsHideOptInOnPreferencesPage' => [
		'default' => false,
	],
	'wgPopupsOptInDefaultState' => [
		'default' => 0,
	],

	// PortableInfobox
	'wgPortableInfoboxResponsiblyOpenCollapsed' => [
		'default' => true,
	],
	'wgPortableInfoboxCacheRenderers' => [
		'default' => true,
	],

	// Preferences
	'+wgDefaultUserOptions' => [
		'default' => [
			'enotifwatchlistpages' => 0,
			'math' => 'mathml',
			'usebetatoolbar' => 1,
			'usebetatoolbar-cgd' => 1,
		],
		'+ext-CleanChanges' => [
			'usenewrc' => 1,
		],
	],
	'wgCleanSignatures' => [
		'default' => true,
	],

	// Preloader
	'wgPreloaderSource' => [
		'default' => [
			0 => 'Template:Boilerplate',
		],
	],

	// ProofreadPage
	'wgProofreadPageNamespaceIds' => [
		'ext-ProofreadPage' => [
			'index' => 252,
			'page' => 250,
		],
	],

	// PropertySuggester
	'wgPropertySuggesterDeprecatedIds' => [
		'default' => [],
	],
	'wgPropertySuggesterClassifyingPropertyIds' => [
		'default' => [],
	],
	'wgPropertySuggesterInitialSuggestions' => [
		'default' => [],
	],
	'wgPropertySuggesterMinProbability' => [
		'default' => 0.05,
	],

	// PWA
	'wgPWAConfigs' => [
		'ext-PWA' => [
			'main' => [
				'manifest' => 'manifest.json',
				'patterns' => [ '/.*/' ],
			],
		],
	],

	// RateLimits
	'+wgRateLimits' => [
		'default' => [],
	],

	// RatePage
	'wgRPRatingPageBlacklist' => [
		'default' => [],
	],
	'wgRPAddSidebarSection' => [
		'default' => true,
	],
	'wgRPSidebarPosition' => [
		'default' => 2,
	],
	'wgRPShowResultsBeforeVoting' => [
		'default' => false,
	],
	'wgRPUseMMVModule' => [
		'default' => true,
	],

	// RecentChanges
	'wgFeedLimit' => [
		'default' => 50,
	],
	'wgRCMaxAge' => [
		'default' => 180 * 24 * 3600,
	],
	'wgRCLinkDays' => [
		'default' => [ 1, 3, 7, 14, 30 ],
	],
	'wgRCLinkLimits' => [
		'default' => [ 50, 100, 250, 500 ],
	],
	'wgUseRCPatrol' => [
		'default' => true,
	],

	// Resources
	'wgExtensionAssetsPath' => [
		'default' => '/' . $wi->version . '/extensions',
	],
	'wgLocalStylePath' => [
		'default' => '/' . $wi->version . '/skins',
	],
	'wgResourceBasePath' => [
		'default' => '/' . $wi->version,
	],
	'wgResourceLoaderMaxQueryLength' => [
		'default' => 5000,
	],
	'wgStylePath' => [
		'default' => '/' . $wi->version . '/skins',
	],

	// RelatedArticles
	'wgRelatedArticlesFooterAllowedSkins' => [
		'default' => [
			'citizen',
			'cosmos',
			'minerva',
			'timeless',
			'vector',
			'vector-2022',
		],
	],
	'wgRelatedArticlesCardLimit' => [
		'default' => 3,
	],
	'wgRelatedArticlesDescriptionSource' => [
		'default' => false,
	],

	// RemovePII
	'wgRemovePIIAllowedWikis' => [
		'wikitide' => [
			'metawiki',
		],
	],
	'wgRemovePIIAutoPrefix' => [
		'wikitide' => 'WikiTideGDPR_',
	],
	'wgRemovePIIHashPrefixOptions' => [
		'wikitide' => [
			'GDPR' => 'WikiTideGDPR_',
			'Vanishing' => 'Vanished user ',
		],
	],
	'wgRemovePIIHashPrefix' => [
		'wikitide' => 'WikiTideGDPR_',
	],

	// Restriction types
	'wgRestrictionLevels' => [
		'default' => [
			'',
			'user',
			'autoconfirmed',
			'sysop'
		],
		'+ext-AuthorProtect' => [
			'author',
		],
	],
	'wgRestrictionTypes' => [
		'default' => [
			'create',
			'edit',
			'move',
			'upload',
		],
	],

	// Rights
	'+wgAvailableRights' => [
		'default' => [],
		'+ext-SocialProfile' => [
			'updatepoints',
		],
	],

	// RightFunctions
	'wgRightFunctionsUserGroups' => [
		'default' => [
			'*',
			'user',
			'autoconfirmed',
			'sysop',
			'bureaucrat',
		],
	],

	// RottenLinks
	'wgRottenLinksCurlTimeout' => [
		'default' => 10,
	],
	'wgRottenLinksExcludeWebsites' => [
		'default' => [
			'localhost',
			'127.0.0.1',
		],
	],

	// Robot policy
	'wgDefaultRobotPolicy' => [
		'default' => 'index,follow',
	],
	'wgNamespaceRobotPolicies' => [
		'default' => [
			NS_SPECIAL => 'noindex',
		],
	],

	// Referrer Policy
	'wgReferrerPolicy' => [
		'default' => [
			'origin-when-cross-origin',
			'origin'
		],
	],

	// RSS Settings
	'wgRSSCacheAge' => [
		'default' => 3600,
	],
	'wgRSSDateDefaultFormat' => [
		'default' => 'Y-m-d H:i:s'
	],
	'wgRSSUrlWhitelist' => [
		'ext-RSSfeed' => [
			'*',
		],
	],

	// Score
	'wgScoreImageMagickConvert' => [
		'ext-Score' => '/usr/bin/convert',
	],
	'wgScoreSafeMode' => [
		'ext-Score' => true,
	],

	// ScratchBlocks
	'wgScratchBlocks4BlockVersion' => [
		'default' => 3,
	],

	// Scribunto
	'wgCodeEditorEnableCore' => [
		'default' => true,
	],
	'wgScribuntoDefaultEngine' => [
		'default' => 'luasandbox',
	],
	'wgScribuntoUseCodeEditor' => [
		'default' => true,
	],
	'wgScribuntoSlowFunctionThreshold' => [
		'default' => 0.99,
	],

	// SecurePoll
	'wgSecurePollUseLogging' => [
		'default' => true,
	],
	'wgSecurePollUseNamespace' => [
		'default' => true,
	],

	// Server
	'wgArticlePath' => [
		'default' => '/wiki/$1',
	],
	'wgDisableOutputCompression' => [
		'default' => true,
	],
	'wgImgAuthPath' => [
		'default' => '/w/img_auth.php',
	],
	'wgLoadScript' => [
		'default' => '/w/load.php',
	],
	'wgRestPath' => [
		'default' => '/' . $wi->version . '/rest.php',
		'metawiki' => '/w/rest.php',
	],
	'wgScript' => [
		'default' => '/w/index.php',
	],
	'wgScriptPath' => [
		'default' => '/' . $wi->version,
		'metawiki' => '/w',
	],
	'wgShowHostnames' => [
		'default' => true,
	],
	'wgThumbnailScriptPath' => [
		'default' => '/w/thumb.php',
	],
	'wgThumbPath' => [
		'default' => '/w/thumb_handler.php',
	],
	'wgUsePathInfo' => [
		'default' => true,
	],

	// SimpleChanges
	'wgSimpleChangesOnlyContentNamespaces' => [
		'default' => false,
	],
	'wgSimpleChangesOnlyLatest' => [
		'default' => true,
	],
	'wgSimpleChangesShowUser' => [
		'default' => false,
	],
	// Share
	'wgShareEmail' => [
		'default' => false,
	],
	'wgShareFacebook' => [
		'default' => true,
	],
	'wgShareLinkedIn' => [
		'default' => false,
	],
	'wgShareReddit' => [
		'default' => false,
	],
	'wgShareTumblr' => [
		'default' => false,
	],
	'wgShareTwitter' => [
		'default' => true,
	],
	'wgShareUseBasicButtons' => [
		'default' => false,
	],
	'wgShareUsePlainLinks' => [
		'default' => true,
	],

	// ShortDescription
	'wgShortDescriptionEnableTagline' => [
		'default' => true,
	],
	'wgShortDescriptionExtendOpenSearchXml' => [
		'default' => false,
	],
	// Skins
	'wgSkipSkins' => [
		'default' => [],
	],

	// SocialProfile
	'wgUserBoard' => [
		'default' => false,
	],
	'wgUserProfileThresholds' => [
		'default' => [
			'edits' => 0,
		],
	],
	'wgUserProfileDisplay' => [
		'default' => [
			'activity' => false,
			'articles' => true,
			'avatar' => true,
			'awards' => true,
			'board' => false,
			'custom' => true,
			'foes' => false,
			'friends' => false,
			'games' => false,
			'gifts' => true,
			'interests' => true,
			'personal' => true,
			'profile' => true,
			'stats' => false,
			'userboxes' => false,
		],
	],
	'wgUserStatsPointValues' => [
		'default' => [
			'edit' => 50,
			'vote' => 0,
			'comment' => 0,
			'comment_plus' => 0,
			'comment_ignored' => 0,
			'opinions_created' => 0,
			'opinions_pub' => 0,
			'referral_complete' => 0,
			'friend' => 0,
			'foe' => 0,
			'gift_rec' => 0,
			'gift_sent' => 0,
			'points_winner_weekly' => 0,
			'points_winner_monthly' => 0,
			'user_image' => 1000,
			'poll_vote' => 0,
			'quiz_points' => 0,
			'quiz_created' => 0,
		],
	],
	'wgFriendingEnabled' => [
		'default' => true,
	],
	'wgUserPageChoice' => [
		'default' => true,
	],

	// Statistics
	'wgArticleCountMethod' => [
		'default' => 'link',
	],

	// StopForumSpam
	// Download from https://www.stopforumspam.com/downloads (recommended listed_ip_90_all.zip)
	// for ipv4 + ipv6 combined.
	'wgSFSIPListLocation' => [
		'default' => '/srv/mediawiki/stopforumspam/listed_ip_90_ipv46_all.txt',
	],

	// Styling
	'wgAllowUserCss' => [
		'default' => true,
	],
	'wgAllowUserJs' => [
		'default' => true,
	],
	'wgAppleTouchIcon' => [
		'default' => '/apple-touch-icon.png',
	],
	'wgCentralAuthLoginIcon' => [
		'wikitide' => '/srv/mediawiki/favicons/default.ico',
		'nexttide' => '/srv/mediawiki/favicons/default.ico',
	],
	'wgDefaultSkin' => [
		'default' => 'vector-2022',
	],
	'wgFallbackSkin' => [
		'default' => 'vector-2022',
	],
	'wgFavicon' => [
		'default' => '/favicon.ico',
	],
	'wgLogo' => [
		'wikitide' => "https://$wmgUploadHostname/commonswiki/6/64/WikiTide_logo_135px.png",
		'nexttide' => "https://$wmgUploadHostname/commonswiki/6/64/WikiTide_logo_135px.png",
	],
	'wgIcon' => [
		'default' => false,
	],
	'wgWordmark' => [
		'default' => false,
	],
	'wgWordmarkHeight' => [
		'default' => 18,
	],
	'wgWordmarkWidth' => [
		'default' => 116,
	],
	'wgMaxTocLevel' => [
		'default' => 999,
	],

	// TabberNeue
	'wgTabberNeueEnableMD5Hash' => [
		'default' => true,
	],

	// TemplateStyles
	'wgTemplateStylesAllowedUrls' => [
		'default' => [
			'audio' => [
				'<^(?:https:)?//upload\\.wikimedia\\.org/wikipedia/commons/>',
				'<^(?:https:)?//static\\.wikitide\\.net/>',
			],
			'image' => [
				'<^(?:https:)?//upload\\.wikimedia\\.org/wikipedia/commons/>',
				'<^(?:https:)?//static\\.wikitide\\.net/>',
			],
			'svg' => [
				'<^(?:https:)?//upload\\.wikimedia\\.org/wikipedia/commons/[^?#]*\\.svg(?:[?#]|$)>',
				'<^(?:https:)?//static\\.wikitide\\.net/[^?#]*\\.svg(?:[?#]|$)>',
			],
			'font' => [],
			'namespace' => [
				'<.>',
			],
			'css' => [],
		],
	],

	// TextExtracts
	'wgExtractsRemoveClasses' => [
		'default' => [
			'table',
			'div',
			'script',
			'input',
			'style',
			'ul.gallery',
			'.mw-editsection',
			'sup.reference',
			'ol.references',
			'.error',
			'.nomobile',
			'.noprint',
			'.noexcerpt',
			'.sortkey',
		],
	],

	// TimedMediaHandler
	'wgOggThumbLocation' => [
		'default' => false,
	],
	'wgTmhEnableMp4Uploads' => [
		'default' => false,
	],

	// Timeless
	'wgTimelessBackdropImage' => [
		'default' => 'cat.svg',
	],
	'wgTimelessLogo' => [
		'default' => null,
	],
	'wgTimelessWordmark' => [
		'default' => null,
	],

	// Timeline
	'wgTimelineFontDirectory' => [
		'default' => '/usr/share/fonts/truetype/freefont',
	],

	// Time
	'wgLocaltimezone' => [
		'default' => 'UTC',
	],
	'wgAmericanDates' => [
		'default' => false,
	],

	// Theme
	'wgDefaultTheme' => [
		'default' => '',
	],

	// TitleBlacklist
	'wgTitleBlacklistSources' => [
		'wikitide' => [
			'global' => [
				'type' => 'url',
				'src' => 'https://meta.wikitide.org/wiki/Title_blacklist?action=raw&tb_ver=1',
			],
			'local' => [
				'type' => 'localpage',
				'src' => 'MediaWiki:Titleblacklist',
			],
		],
	],
	'wgTitleBlacklistUsernameSources' => [
		'default' => '*',
	],
	'wgTitleBlacklistLogHits' => [
		'default' => false,
	],
	'wgTitleBlacklistBlockAutoAccountCreation' => [
		'default' => false,
	],

	// Translate
	'wgTranslateDisabledTargetLanguages' => [
		'default' => [],
		'hkrailwiki' => [
			'*' => [
				'zh-hant' => '本站已配置[[Project:繁簡處理|自動繁簡轉換]]功能，請在語言表單選擇翻譯語言為「中文」而非「中文（繁體）」。',
				'zh-hk' => '本站已配置[[Project:繁簡處理|自動繁簡轉換]]功能，請在語言表單選擇翻譯語言為「中文」而非「中文（香港）」。',
				'zh-tw' => '本站已配置[[Project:繁簡處理|自動繁簡轉換]]功能，請在語言表單選擇翻譯語言為「中文」而非「中文（台灣）」。',
				'zh-mo' => '本站已配置[[Project:繁簡處理|自動繁簡轉換]]功能，請在語言表單選擇翻譯語言為「中文」而非「中文（澳門）」。',
				'zh-hant' => '本站已配置[[Project:繁簡處理|自动简繁转换]]功能，请在语言表单选择翻译语言为「中文」而非「中文（简体）」。',
				'zh-cn' => '本站已配置[[Project:繁簡處理|自动简繁转换]]功能，请在语言表单选择翻译语言为「中文」而非「中文（中国大陆）」。',
				'zh-sg' => '本站已配置[[Project:繁簡處理|自动简繁转换]]功能，请在语言表单选择翻译语言为「中文」而非「中文（新加坡）」。',
				'zh-my' => '本站已配置[[Project:繁簡處理|自动简繁转换]]功能，请在语言表单选择翻译语言为「中文」而非「中文（马来西亚）」。',
			],
		],
		'metawiki' => [
			'*' => [
				'en' => 'English is the source language.',
			],
		],
	],
	'wgTranslateDocumentationLanguageCode' => [
		'default' => false,
	],
	'wgTranslatePageTranslationULS' => [
		'default' => false,
	],
	'wgTranslateTranslationServices' => [
		'default' => [],
	],

	// Tweeki
	'wgTweekiSkinUseBootstrap4' => [
		'default' => false,
	],
	'wgTweekiSkinImagePageTOCTabs' => [
		'default' => false,
	],
	'wgTweekiSkinFooterIcons' => [
		'default' => false,
	],
	'wgTweekiSkinUseBtnParser' => [
		'default' => false,
	],
	'wgTweekiSkinUseTooltips' => [
		'default' => false,
	],
	'wgTweekiSkinUseIconWatch' => [
		'default' => false,
	],
	'wgTweekiSkinHideAnon' => [
		'default' => [
			'subnav' => true
		],
	],

	// UploadWizard
	'wmgUploadWizardFlickrApiKey' => [
		'ext-UploadWizard' => 'aeefff139445d825d4460796616f9349',
	],

	// Uploads
	'wmgEnableSharedUploads' => [
		'default' => false,
	],
	'wmgSharedUploadBaseUrl' => [
		'default' => false,
	],
	'wmgSharedUploadDBname' => [
		'default' => false,
	],
	'wmgSharedUploadClientDBname' => [
		'default' => false,
	],

	// UniversalLanguageSelector
	'wgULSAnonCanChangeLanguage' => [
		'default' => false,
	],
	'wgULSLanguageDetection' => [
		'default' => false,
	],
	'wgULSPosition' => [
		'default' => 'personal',
	],
	'wgULSGeoService' => [
		'ext-Translate' => false,
		'ext-UniversalLanguageSelector' => false,
	],
	'wgULSIMEEnabled' => [
		'default' => true,
	],
	'wgULSWebfontsEnabled' => [
		'default' => true,
	],

	// UrlShortener
	'wgUrlShortenerTemplate' => [
		'default' => '/m/$1',
	],
	'wgUrlShortenerDBName' => [
		'default' => $wi::CENTRAL_WIKI[$wi->wikifarm],
	],

	// UserFunctions
	'wgUFEnabledPersonalDataFunctions' => [
		/**
		 * 'ip', 'realname' and/or 'useremail' should never
		 * be enabled here under any circumstances, in order
		 * to ensure privacy.
		 */
		'default' => [
			'nickname',
			'username',
		],
	],
	'wgUFAllowedNamespaces' => [
		'default' => '8',
	],

	// UserPageEditProtection
	'wgOnlyUserEditUserPage' => [
		'ext-UserPageEditProtection' => true,
	],

	// Varnish
	'wgUseCdn' => [
		'default' => true,
	],
	'wgCdnServers' => [
		'default' => [
			/** cp1 (US) */
			'[2604:4300:A:24:0:0:0:113]:81',
			/** cp2 (CAN) */
			'[2607:5300:205:200::2aa8]:81',
			/** cp3 (UK) */
			'[2001:41d0:801:2000::4089]:81',
			/** cp4 (PL) */
			'[2001:41d0:601:1100::4062]:81',
			/** cp5 (SGP) */
			'[2402:1f00:8000:800::1b3d]:81',
			/** cp6 (AU) */
			'[2402:1f00:8100:400::1d23]:81',

		],
	],
	'wgCdnServersNoPurge' => [
		'default' => [
			'127.0.0.1',
			/** cp1 (US) */
			'2604:4300:a:24::113',
			/** cp2 (CAN) */
			'2607:5300:205:200::2aa8',
			/** cp3 (UK) */
			'2001:41d0:801:2000::4089',
			/** cp4 (PL) */
			'2001:41d0:601:1100::4062',
			/** cp5 (SGP) */
			'2402:1f00:8000:800::1b3d',
			/** cp6 (AU) */
			'2402:1f00:8100:400::1d23',
		],
	],

	// Vector
	'wgVectorResponsive' => [
		'default' => true,
	],
	'wgVectorDefaultSidebarVisibleForAnonymousUser' => [
		'default' => true,
	],
	'wgVectorWvuiSearchOptions' => [
		'default' => [
			'showThumbnail' => true,
			'showDescription' => true,
		],
	],
	'wgVectorMaxWidthOptions' => [
		'default' => [
			'exclude' => [
				'mainpage' => false,
				'querystring' => [
					'action' => '(history|edit)',
					'diff' => '.+',
				],
				'namespaces' => [
					NS_SPECIAL,
					NS_CATEGORY,
				],
			],
			'include' => [
				'Special:Preferences',
			],
		],
	],
	'wgVectorStickyHeader' => [
		'default' => [
			'logged_in' => true,
			'logged_out' => false,
		],
	],
	'wgVectorLanguageInHeader' => [
		'default' => [
			'logged_in' => true,
			'logged_out' => true,
		],
	],

	// VisualEditor
	'wmgVisualEditorEnableDefault' => [
		'default' => false,
		'ext-VisualEditor' => true,
	],
	'wgVisualEditorEnableWikitext' => [
		'default' => false,
	],
	'wgVisualEditorShowBetaWelcome' => [
		'default' => true,
	],
	'wgVisualEditorUseSingleEditTab' => [
		'default' => false,
	],
	'wgVisualEditorEnableDiffPage' => [
		'default' => false,
	],
	'wgVisualEditorEnableVisualSectionEditing' => [
		'default' => 'mobile',
	],
	'wgVisualEditorTransclusionDialogSuggestedValues' => [
		'default' => false,
	],
	'wgVisualEditorTransclusionDialogInlineDescriptions' => [
		'default' => false,
	],
	'wgVisualEditorTransclusionDialogBackButton' => [
		'default' => false,
	],
	'wgVisualEditorTransclusionDialogNewSidebar' => [
		'default' => false,
	],
	'wgVisualEditorTemplateSearchImprovements' => [
		'default' => false,
	],

	// ProtectSite
	'wgProtectSiteLimit' => [
		'default' => '1 week',
	],
	'wgProtectSiteDefaultTimeout' => [
		'default' => '1 hour',
	],

	// WebAuthn
	'wgWebAuthnRelyingPartyName' => [
		'default' => 'WikiTide',
		'wikitide' => 'NextTide',
	],
	'wgWebAuthnRelyingPartyID' => [
		'default' => 'wikitide.org',
		'nexttide' => 'nexttide.org',
	],

	// Wikibase
	'wmgAllowEntityImport' => [
		'default' => false,
	],
	'wmgCanonicalUriProperty' => [
		'default' => false,
	],
	'wmgEnableEntitySearchUI' => [
		'default' => false,
	],
	'wmgFederatedPropertiesEnabled' => [
		'default' => false,
	],
	'wmgFormatterUrlProperty' => [
		'default' => false,
	],
	'wmgWikibaseRepoDatabase' => [
		'default' => $wi->dbname,
	],
	'wmgWikibaseRepoUrl' => [
		'default' => 'https://wikidata.org',
	],
	'wmgWikibaseItemNamespaceID' => [
		'default' => 0,
	],
	'wmgWikibasePropertyNamespaceID' => [
		'default' => 120,
	],
	'wmgWikibaseRepoItemNamespaceID' => [
		'default' => 860,
	],
	'wmgWikibaseRepoPropertyNamespaceID' => [
		'default' => 862,
	],

	// WikibaseLexeme
	'wgLexemeLanguageCodePropertyId' => [
		'default' => null,
	],
	'wgLexemeEnableDataTransclusion' => [
		'default' => false,
	],

	// WikibaseQualityConstraints
	'wgWBQualityConstraintsInstanceOfId' => [
		'default' => 'P31',
	],
	'wgWBQualityConstraintsSubclassOfId' => [
		'default' => 'P279',
	],
	'wgWBQualityConstraintsStartTimePropertyIds' => [
		'default' => [
			'P569',
			'P571',
			'P580',
			'P585',
		],
	],
	'wgWBQualityConstraintsEndTimePropertyIds' => [
		'default' => [
			'P570',
			'P576',
			'P582',
			'P585',
		],
	],
	'wgWBQualityConstraintsPropertyConstraintId' => [
		'default' => 'P2302',
	],
	'wgWBQualityConstraintsExceptionToConstraintId' => [
		'default' => 'P2303',
	],
	'wgWBQualityConstraintsConstraintStatusId' => [
		'default' => 'P2316',
	],
	'wgWBQualityConstraintsMandatoryConstraintId' => [
		'default' => 'Q21502408',
	],
	'wgWBQualityConstraintsSuggestionConstraintId' => [
		'default' => 'Q62026391',
	],
	'wgWBQualityConstraintsDistinctValuesConstraintId' => [
		'default' => 'Q21502410',
	],
	'wgWBQualityConstraintsMultiValueConstraintId' => [
		'default' => 'Q21510857',
	],
	'wgWBQualityConstraintsUsedAsQualifierConstraintId' => [
		'default' => 'Q21510863',
	],
	'wgWBQualityConstraintsSingleValueConstraintId' => [
		'default' => 'Q19474404',
	],
	'wgWBQualityConstraintsSymmetricConstraintId' => [
		'default' => 'Q21510862',
	],
	'wgWBQualityConstraintsTypeConstraintId' => [
		'default' => 'Q21503250',
	],
	'wgWBQualityConstraintsValueTypeConstraintId' => [
		'default' => 'Q21510865',
	],
	'wgWBQualityConstraintsInverseConstraintId' => [
		'default' => 'Q21510855',
	],
	'wgWBQualityConstraintsItemRequiresClaimConstraintId' => [
		'default' => 'Q21503247',
	],
	'wgWBQualityConstraintsValueRequiresClaimConstraintId' => [
		'default' => 'Q21510864',
	],
	'wgWBQualityConstraintsConflictsWithConstraintId' => [
		'default' => 'Q21502838',
	],
	'wgWBQualityConstraintsOneOfConstraintId' => [
		'default' => 'Q21510859',
	],
	'wgWBQualityConstraintsMandatoryQualifierConstraintId' => [
		'default' => 'Q21510856',
	],
	'wgWBQualityConstraintsAllowedQualifiersConstraintId' => [
		'default' => 'Q21510851',
	],
	'wgWBQualityConstraintsRangeConstraintId' => [
		'default' => 'Q21510860',
	],
	'wgWBQualityConstraintsDifferenceWithinRangeConstraintId' => [
		'default' => 'Q21510854',
	],
	'wgWBQualityConstraintsCommonsLinkConstraintId' => [
		'default' => 'Q21510852',
	],
	'wgWBQualityConstraintsContemporaryConstraintId' => [
		'default' => 'Q25796498',
	],
	'wgWBQualityConstraintsFormatConstraintId' => [
		'default' => 'Q21502404',
	],
	'wgWBQualityConstraintsUsedForValuesOnlyConstraintId' => [
		'default' => 'Q21528958',
	],
	'wgWBQualityConstraintsUsedAsReferenceConstraintId' => [
		'default' => 'Q21528959',
	],
	'wgWBQualityConstraintsNoBoundsConstraintId' => [
		'default' => 'Q51723761',
	],
	'wgWBQualityConstraintsAllowedUnitsConstraintId' => [
		'default' => 'Q21514353',
	],
	'wgWBQualityConstraintsSingleBestValueConstraintId' => [
		'default' => 'Q52060874',
	],
	'wgWBQualityConstraintsAllowedEntityTypesConstraintId' => [
		'default' => 'Q52004125',
	],
	'wgWBQualityConstraintsCitationNeededConstraintId' => [
		'default' => 'Q54554025',
	],
	'wgWBQualityConstraintsPropertyScopeConstraintId' => [
		'default' => 'Q53869507',
	],
	'wgWBQualityConstraintsLexemeLanguageConstraintId' => [
		'default' => 'Q55819106',
	],
	'wgWBQualityConstraintsLabelInLanguageConstraintId' => [
		'default' => 'Q108139345',
	],
	'wgWBQualityConstraintsLanguagePropertyId' => [
		'default' => 'P424',
	],
	'wgWBQualityConstraintsClassId' => [
		'default' => 'P2308',
	],
	'wgWBQualityConstraintsRelationId' => [
		'default' => 'P2309',
	],
	'wgWBQualityConstraintsInstanceOfRelationId' => [
		'default' => 'Q21503252',
	],
	'wgWBQualityConstraintsSubclassOfRelationId' => [
		'default' => 'Q21514624',
	],
	'wgWBQualityConstraintsInstanceOrSubclassOfRelationId' => [
		'default' => 'Q30208840',
	],
	'wgWBQualityConstraintsPropertyId' => [
		'default' => 'P2306',
	],
	'wgWBQualityConstraintsQualifierOfPropertyConstraintId' => [
		'default' => 'P2305',
	],
	'wgWBQualityConstraintsMinimumQuantityId' => [
		'default' => 'P2313',
	],
	'wgWBQualityConstraintsMaximumQuantityId' => [
		'default' => 'P2312',
	],
	'wgWBQualityConstraintsMinimumDateId' => [
		'default' => 'P2310',
	],
	'wgWBQualityConstraintsMaximumDateId' => [
		'default' => 'P2311',
	],
	'wgWBQualityConstraintsNamespaceId' => [
		'default' => 'P2307',
	],
	'wgWBQualityConstraintsFormatAsARegularExpressionId' => [
		'default' => 'P1793',
	],
	'wgWBQualityConstraintsSyntaxClarificationId' => [
		'default' => 'P2916',
	],
	'wgWBQualityConstraintsConstraintScopeId' => [
		'default' => 'P4680',
	],
	'wgWBQualityConstraintsConstraintEntityTypesId' => [
		'default' => 'P4680',
	],
	'wgWBQualityConstraintsSeparatorId' => [
		'default' => 'P4155',
	],
	'wgWBQualityConstraintsConstraintCheckedOnMainValueId' => [
		'default' => 'Q46466787',
	],
	'wgWBQualityConstraintsConstraintCheckedOnQualifiersId' => [
		'default' => 'Q46466783',
	],
	'wgWBQualityConstraintsConstraintCheckedOnReferencesId' => [
		'default' => 'Q46466805',
	],
	'wgWBQualityConstraintsNoneOfConstraintId' => [
		'default' => 'Q52558054',
	],
	'wgWBQualityConstraintsIntegerConstraintId' => [
		'default' => 'Q52848401',
	],
	'wgWBQualityConstraintsWikibaseItemId' => [
		'default' => 'Q29934200',
	],
	'wgWBQualityConstraintsWikibasePropertyId' => [
		'default' => 'Q29934218',
	],
	'wgWBQualityConstraintsWikibaseLexemeId' => [
		'default' => 'Q51885771',
	],
	'wgWBQualityConstraintsWikibaseFormId' => [
		'default' => 'Q54285143',
	],
	'wgWBQualityConstraintsWikibaseSenseId' => [
		'default' => 'Q54285715',
	],
	'wgWBQualityConstraintsWikibaseMediaInfoId' => [
		'default' => 'Q59712033',
	],
	'wgWBQualityConstraintsPropertyScopeId' => [
		'default' => 'P5314',
	],
	'wgWBQualityConstraintsAsMainValueId' => [
		'default' => 'Q54828448',
	],
	'wgWBQualityConstraintsAsQualifiersId' => [
		'default' => 'Q54828449',
	],
	'wgWBQualityConstraintsAsReferencesId' => [
		'default' => 'Q54828450',
	],
	'wgWBQualityConstraintsEnableSuggestionConstraintStatus' => [
		'default' => false,
	],

	// WebChat config
	'wgWebChatServer' => [
		'default' => false,
	],
	'wgWebChatChannel' => [
		'default' => false,
	],
	'wgWebChatClient' => [
		'default' => 'LiberaChat',
	],

	// WikiEditor
	'wgWikiEditorRealtimePreview' => [
		'default' => false,
	],

	// WikiForum
	'wgWikiForumAllowAnonymous' => [
		'default' => true,
	],
	'wgWikiForumLogsInRC' => [
		'default' => true,
	],

	// WikiDiscover
	'wgWikiDiscoverUseDescriptions' => [
		'default' => true,
	],

	// WikiTide
	'wgWikiTideMagicServicesRepo' => [
		'wikitide' => '/srv/services/services',
	],
	'wgWikiTideMagicSurveyEnabled' => [
		'default' => false,
	],

	// WikimediaIncubator
	'wmincProjects' => [
		'default' => [
			'p' => [
				'name' => 'Wikipedia',
				'dbsuffix' => 'wiki',
				'wikitag' => 'wikipedia',
				'sister' => false,
			],
			'b' => [
				'name' => 'Wikibooks',
				'dbsuffix' => 'wikibooks',
				'wikitag' => 'wikibooks',
				'sister' => false,
			],
			't' => [
				'name' => 'Wiktionary',
				'dbsuffix' => 'wiktionary',
				'wikitag' => 'wiktionary',
				'sister' => false,
			],
			'q' => [
				'name' => 'Wikiquote',
				'dbsuffix' => 'wikiquote',
				'wikitag' => 'wikiquote',
				'sister' => false,
			],
			'n' => [
				'name' => 'Wikinews',
				'dbsuffix' => 'wikinews',
				'wikitag' => 'wikinews',
				'sister' => false,
			],
			'y' => [
				'name' => 'Wikivoyage',
				'dbsuffix' => 'wikivoyage',
				'wikitag' => 'wikivoyage',
				'sister' => false,
			],
			's' => [
				'name' => 'Wikisource',
				'dbsuffix' => 'wikisource',
				'wikitag' => 'wikisource',
				'sister' => false,
			],
			'v' => [
				'name' => 'Wikiversity',
				'dbsuffix' => 'wikiversity',
				'wikitag' => 'wikiversity',
				'sister' => false,
			],
		],
		'snxyzincubatorwiki' => [
			'k' => [
				'name' => 'Cookie Run: Kingdom Wiki',
				'dbsuffix' => 'crk',
				'wikitag' => 'cookierunkingdom',
				'sister' => false,
			],
			'c' => [
				'name' => 'Cookie Run Wiki',
				'dbsuffix' => 'cr',
				'wikitag' => 'cookierun',
				'sister' => false,
			],
		],
	],
	'wmincProjectSite' => [
		'default' => [
			'name' => 'Incubator Plus 2.0',
			'short' => 'incplus',
		],
		'snxyzincubatorwiki' => [
			'name' => "Pisces's Incubator",
			'short' => 'pi',
		]
	],
	'wmincExistingWikis' => [
		'default' => null,
	],
	'wmincClosedWikis' => [
		'default' => false,
	],
	'wmincMultilingualProjects' => [
		'default' => [],
	],
	'wmincTestWikiNamespaces' => [
		'default' => [
			NS_MAIN,
			NS_TALK,
			NS_TEMPLATE,
			NS_TEMPLATE_TALK,
			NS_CATEGORY,
			NS_CATEGORY_TALK,
			/** NS_MODULE */
			828,
			/** NS_MODULE_TALK */
			829,
		],
	],
	// WikiLove
	'wgWikiLoveGlobal' => [
		'ext-WikiLove' => true,
	],

	// WikiSEO configs
	'wgTwitterCardType' => [
		'default' => 'summary_large_image',
	],
	'wgGoogleSiteVerificationKey' => [
		'default' => false,
	],
	'wgBingSiteVerificationKey' => [
		'default' => false,
	],
	'wgFacebookAppId' => [
		'default' => false,
	],
	'wgYandexSiteVerificationKey' => [
		'default' => false,
	],
	'wgAlexaSiteVerificationKey' => [
		'default' => false,
	],
	'wgPinterestSiteVerificationKey' => [
		'default' => false,
	],
	'wgNaverSiteVerificationKey' => [
		'default' => false,
	],
	'wgWikiSeoDefaultImage' => [
		'default' => null,
	],
	'wgWikiSeoDisableLogoFallbackImage' => [
		'default' => false,
	],
	'wgWikiSeoEnableAutoDescription' => [
		'default' => true,
	],
	'wgWikiSeoTryCleanAutoDescription' => [
		'default' => true,
	],
	'wgMetadataGenerators' => [
		'default' => '',
	],
	'wgTwitterSiteHandle' => [
		'default' => '',
	],
	'wgWikiSeoDefaultLanguage' => [
		'default' => '',
	],

	// CreateWiki Defined Special Variables
	'cwClosed' => [
		'wikitide' => false,
	],
	'cwExperimental' => [
		'wikitide' => false,
	],
	'cwInactive' => [
		'wikitide' => false,
	],
	'cwLocked' => [
		'wikitide' => false,
	],
	'cwPrivate' => [
		'default' => false,
	],

	// Uncategorised
	'wgRandomGameDisplay' => [
		'default' => [
			'random_picturegame' => false,
			'random_poll' => false,
			'random_quiz' => false,
		],
	],
	'wgForceHTTPS' => [
		'default' => true,
	],

	// Logging configuation (Graylog)
	'wmgLogToDisk' => [
		'default' => false,
	],
	'wmgMonologChannels' => [
		'default' => [
			'404' => 'debug',
			'AbuseFilter' => false,
			'ActionFactory' => false,
			'antispoof' => false,
			'api' => 'warning',
			'api-feature-usage' => false,
			'api-readonly' => false,
			// When using this, use buffer.
			'api-request' => [ 'graylog' => 'debug', 'buffer' => true ],
			'api-warning' => false,
			'authentication' => 'info',
			'authevents' => 'info',
			'autoloader' => false,
			'BlockManager' => false,
			'BlogPage' => false,
			'BounceHandler' => false,
			'cache-cookies' => false,
			'caches' => false,
			'captcha' => 'debug',
			'cargo' => false,
			'CentralAuth' => 'info',
			'CentralAuthRename' => false,
			'CentralAuthVerbose' => false,
			'CentralNotice' => false,
			'cite' => false,
			'ContentHandler' => false,
			'CookieWarning' => false,
			'cookie' => false,
			'CreateWiki' => 'debug',
			'rdbms' => 'warning',
			'DeferredUpdates' => 'error',
			'DBConnection' => 'warning',
			'DBPerformance' => false,
			'DBQuery' => false,
			'DBReplication' => false,
			'DBTransaction' => false,
			'DeferredUpdates' => 'error',
			'deprecated' => [ 'graylog' => 'debug', 'sample' => 100 ],
			'diff' => 'debug',
			'DuplicateParse' => false,
			'dynamic-sidebar' => false,
			'editpage' => false,
			'Echo' => 'debug',
			'EditConflict' => 'error',
			'EditConstraintRunner' => 'error',
			'error' => 'debug',
			'error-json' => false,
			'EventLogging' => false,
			'exception' => 'debug',
			'exception-json' => false,
			'exec' => 'debug',
			'export' => false,
			'ExternalStore' => false,
			'fatal' => 'debug',
			'FileImporter' => false,
			'FileOperation' => false,
			'Flow' => 'debug',
			'formatnum' => false,
			'FSFileBackend' => false,
			'gitinfo' => false,
			'GlobalTitleFail' => false,
			'GlobalWatchlist' => false,
			'headers-sent' => false,
			'http' => 'warning',
			'HitCounters' => false,
			// Only log http errors with a 500+ code
			'HttpError' => 'error',
			// 'JobExecutor' => [ 'logstash' => 'warning' ],
			'JobQueueRedis' => 'debug',
			'localisation' => false,
			'ldap' => 'warning',
			'LinkBatch' => false,
			'Linter' => 'debug',
			'LocalFile' => 'warning',
			'localhost' => false,
			'LockManager' => 'warning',
			'logging' => false,
			'LoginNotify' => 'info',
			'ManageWiki' => 'debug',
			'MassMessage' => false,
			'Math' => 'info',
			'MatomoAnalytics' => 'debug',
			'Mime' => false,
			// debug sprews too much information + sample
			// otherwise we get 2 million+ messages within a few minutes
			'memcached' => [ 'graylog' => 'error' ],
			'message-format' => false,
			'MessageCache' => false,
			'MessageCacheError' => false,
			'mobile' => false,
			'NewUserMessage' => false,
			'OAuth' => 'info',
			'objectcache' => false,
			'OldRevisionImporter' => false,
			'OutputBuffer' => false,
			'PageTriage' => false,
			'ParserCache' => false,
			'Parsoid' => 'warning',
			'poolcounter' => 'debug',
			'preferences' => false,
			'purge' => false,
			'query' => false,
			'quickinstantcommons' => 'error',
			'ratelimit' => false,
			'readinglists' => false,
			'recursion-guard' => false,
			'RecursiveLinkPurge' => false,
			'redis' => 'info',
			'Renameuser' => 'debug',
			'resourceloader' => false,
			'ResourceLoaderImage' => false,
			'RevisionStore' => false,
			'runJobs' => 'warning',
			'SaveParse' => false,
			'security' => 'debug',
			'session' => 'info',
			'session-ip' => 'info',
			'SimpleAntiSpam' => false,
			'slow-parse' => 'debug',
			'slow-parsoid' => 'info',
			'SocialProfile' => false,
			'SpamBlacklist' => false,
			'SpamBlacklistHit' => false,
			'SpamRegex' => false,
			'StopForumSpam' => false,
			'SQLBagOStuff' => false,
			'SwiftBackend' => 'info',
			'squid' => false,
			'StashEdit' => false,
			'T263581' => false,
			'texvc' => false,
			'throttler' => false,
			'thumbnail' => 'debug',
			'thumbnailaccess' => false,
			'TitleBlacklist' => false,
			'TitleBlacklist-cache' => false,
			'torblock' => false,
			'TranslationNotifications.Jobs' => false,
			'Translate.Jobs' => false,
			'Translate' => false,
			'UpdateRepo' => false,
			'updateTranstagOnNullRevisions' => false,
			'upload' => false,
			'UserOptionsManager' => false,
			'VisualEditor' => 'debug',
			'warning' => false,
			'wfDebug' => false,
			'wfLogDBError' => 'debug',
			'Wikibase' => false,
			'Wikibase.NewItemIdFormatter' => false,
			'WikibaseQualityConstraints' => false,
			'WikiTideMagic' => 'debug',
			'xff' => false,
			'XMP' => false,
		],
	],
	// Control MediaWiki Deprecation Warnings
	'wgDeprecationReleaseLimit' => [
		'default' => '1.34',
		'test1wiki' => false,
	],
];

// Start settings requiring external dependency checks/functions

if ( wfHostname() === 'test1.wikitide.net' ) {
	// Prevent cache (better be safe than sorry)
	$wgConf->settings['wgUseCdn']['default'] = false;
}

// CookieWarning exempt ElectronPdfService (services1)
if ( in_array( $_SERVER['REMOTE_ADDR'] ?? '', [ '10.0.0.115' ] ) ) {
	$wgConf->settings['wgCookieWarningEnabled']['default'] = false;
}

// ManageWiki settings
require_once __DIR__ . '/ManageWikiExtensions.php';
$wi::$disabledExtensions = [
	'lingo',
	'pageproperties',
	'wikiforum',
];

$globals = WikiTideFunctions::getConfigGlobals();

// phpcs:ignore MediaWiki.Usage.ForbiddenFunctions.extract
extract( $globals );

$wi->loadExtensions();

require_once __DIR__ . '/ManageWikiNamespaces.php';
require_once __DIR__ . '/ManageWikiSettings.php';

$wgUploadPath = "//$wmgUploadHostname/$wgDBname";
$wgUploadDirectory = false;

if ( $wi->version >= 1.40 ) {
	// These are not loaded by mergeMessageFileList.php on MediaWiki 1.40+ due to not being on ExtensionRegistry
	$wgMessagesDirs['SocialProfile'] = $IP . '/extensions/SocialProfile/i18n';
	$wgExtensionMessagesFiles['SocialProfileAlias'] = $IP . '/extensions/SocialProfile/SocialProfile.alias.php';
	$wgMessagesDirs['SocialProfileUserProfile'] = $IP . '/extensions/SocialProfile/UserProfile/i18n';
	$wgExtensionMessagesFiles['SocialProfileNamespaces'] = $IP . '/extensions/SocialProfile/SocialProfile.namespaces.php';
	$wgExtensionMessagesFiles['AvatarMagic'] = $IP . '/extensions/SocialProfile/UserProfile/includes/avatar/Avatar.i18n.magic.php';
}

$wgLocalisationCacheConf['storeClass'] = LCStoreCDB::class;
$wgLocalisationCacheConf['storeDirectory'] = '/srv/mediawiki/cache/' . $wi->version . '/l10n';
$wgLocalisationCacheConf['manualRecache'] = true;

if ( !file_exists( '/srv/mediawiki/cache/' . $wi->version . '/l10n/l10n_cache-en.cdb' ) ) {
	$wgLocalisationCacheConf['manualRecache'] = false;
}

if ( extension_loaded( 'wikidiff2' ) ) {
	$wgDiff = false;
}

// we set $wgInternalServer to $wgServer to get varnish cache purging working
// we convert $wgServer to http://, as varnish does not support purging https requests
$wgInternalServer = str_replace( 'https://', 'http://', $wgServer );

if ( $wgRequestTimeLimit ) {
	$wgHTTPMaxTimeout = $wgHTTPMaxConnectTimeout = $wgRequestTimeLimit;
}

// Include other configuration files
require_once '/srv/mediawiki/config/Database.php';
require_once '/srv/mediawiki/config/GlobalCache.php';
require_once '/srv/mediawiki/config/GlobalLogging.php';
require_once '/srv/mediawiki/config/Sitenotice.php';
require_once '/srv/mediawiki/config/FileBackend.php';

if ( $wi->missing ) {
	require_once '/srv/mediawiki/ErrorPages/MissingWiki.php';
}

// Define last to avoid all dependencies
require_once '/srv/mediawiki/config/GlobalSettings.php';
require_once '/srv/mediawiki/config/LocalWiki.php';

// Define last - Extension message files for loading extensions
if (
	file_exists( __DIR__ . '/ExtensionMessageFiles-' . $wi->version . '.php' ) &&
	!defined( 'MW_NO_EXTENSION_MESSAGES' )
) {
	require_once __DIR__ . '/ExtensionMessageFiles-' . $wi->version . '.php';
}

// Don't need a global here
unset( $wi );
