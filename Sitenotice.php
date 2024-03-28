<?php

$wgNoticeProject = 'all';
if ( $wmgSiteNoticeOptOut ) {
	// Only show important notices when optout
	$wgNoticeProject = 'all';
}

// Increment this version number whenever you change the site notice
$wgMajorSiteNoticeID = 100;

/**
 * Wrap your sitenotice with <div data-nosnippet>(sitenotice)</div>
 * or Google will use the sitenotice for their search result snippet.
 */

// Global SiteNotice
	$wgHooks['SiteNoticeAfter'][] = 'wfGlobalSiteNotice';

	function wfGlobalSiteNotice( &$siteNotice, $skin ) {
		$skin->getOutput()->enableOOUI();
		$skin->getOutput()->addInlineStyle(
			'.mw-dismissable-notice .mw-dismissable-notice-body { margin: unset; }' .
			'.skin-cosmos #sitenotice-learnmore-button { margin-left: 50px; }'
		);

		$siteNotice .= <<<EOF
			<table style="width: 100%;">
				<tbody><tr><td style="font-size: 120%; border-left: 4px solid #d33; background-color: #fee7e6; padding: 10px 15px; color: black;">
					<div data-nosnippet style="padding-top:0.3em; padding-bottom:0.1em;">
						<div class="floatleft"><img alt="Goodbye!" src="https://upload.wikimedia.org/wikipedia/commons/7/7c/OOjs_UI_icon_moon.svg" decoding="async" width="50" height="50"></div>
						<div style="font-weight: bold; color: black;">
							WikiTide is shutting down on April 15th
						</div>
						<div style="padding-bottom: 15px; font-size: 13pt; color: black;">
							Following the merger of WikiTide with Miraheze and notice of impending shutdown since January, WikiTide is officially shutting down on April 15th. Please migrate any wikis by then.
						</div>

						<span id="sitenotice-learnmore-button" class="oo-ui-widget oo-ui-widget-enabled oo-ui-buttonElement oo-ui-buttonElement-framed oo-ui-iconElement oo-ui-labelElement oo-ui-buttonWidget">
							<a class="oo-ui-buttonElement-button" role="button" tabindex="0" href="https://meta.wikitide.org/wiki/WikiTide_Foundation/Merger_FAQ">
								<span class="oo-ui-iconElement-icon oo-ui-icon-info"></span>
								<span class="oo-ui-labelElement-label">Learn more</span>
								<span class="oo-ui-indicatorElement-indicator oo-ui-indicatorElement-noIndicator"></span>
							</a>
						</span>
            <span id="sitenotice-learnmore-button" class="oo-ui-widget oo-ui-widget-enabled oo-ui-buttonElement oo-ui-buttonElement-framed oo-ui-iconElement oo-ui-labelElement oo-ui-buttonWidget">
							<a class="oo-ui-buttonElement-button" role="button" tabindex="0" href="https://meta.wikitide.org/wiki/WikiTide_Foundation/Merger_FAQ#Migration">
								<span class="oo-ui-iconElement-icon oo-ui-icon-info"></span>
								<span class="oo-ui-labelElement-label">Wiki migration FAQ</span>
								<span class="oo-ui-indicatorElement-indicator oo-ui-indicatorElement-noIndicator"></span>
							</a>
						</span>
					</div>
				</td></tr></tbody>
			</table>
		EOF;
	}
