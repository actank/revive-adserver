<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

// Require the initialisation file
// Note that MAX_PATH will have been defined by the invocated code
require_once MAX_PATH . '/init-delivery.php';

// Include required files
require_once MAX_PATH . '/lib/max/Delivery/adSelect.php';
require_once MAX_PATH . '/lib/max/Delivery/flash.php';

// init-variables will have set "loc" to $_SERVER['HTTP_REFERER']
// however - in local mode (only), this is not the case
global $referer, $loc;
$referer = (!empty($loc)) ? $loc : '';
$loc = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' .
    OX_getHostName() .
    $_SERVER['REQUEST_URI'];
// init-delivery.php is not setting invocationType properly for local invocation type
// we should set it here
$GLOBALS['_OA']['invocationType'] = 'local';
// This function is a wrapper to view raw, this allows for future migration
function view_local($what, $zoneid = 0, $campaignid = 0, $bannerid = 0, $target = '', $source = '', $withtext = '', $context = '', $charset = '')
{
    // start stacked output buffering
    ob_start();

    if (empty($what) && !((strstr($what, 'zone')) or (strstr($what, 'campaign')) or (strstr($what, 'banner')))) {
        if ($zoneid) {
            $what = "zone:" . $zoneid;
        }
        if ($campaignid) {
            $what = "campaignid:" . $campaignid;
        }
        if ($bannerid) {
            $what = "bannerid:" . $bannerid;
        }
    }

    $output = MAX_adSelect($what, $campaignid, $target, $source, $withtext, $charset, $context, true, '', $GLOBALS['loc'], $GLOBALS['referer']);

    // Add any $context information to the global phpAds_context array
    if (
        isset($GLOBALS['phpAds_context']) && is_array($GLOBALS['phpAds_context']) &&
        isset($output['context']) && is_array($output['context'])
       ) {
        // Check if the new context item is already in the global array, and add it if not
        foreach ($GLOBALS['phpAds_context'] as $idx => $item) {
            foreach ($output['context'] as $newidx => $newItem) {
                if ($newItem === $item) {
                    unset($output['context'][$newidx]);
                }
            }
        }
        $GLOBALS['phpAds_context'] = $GLOBALS['phpAds_context'] + $output['context'];
    }
    MAX_cookieFlush();
    // add cookies to output html
    $output['html'] .= ob_get_clean();
    return $output;
}

/**
 * This is the SPC wrapper for local-mode invocation
 *
 * @param mixed $what Either a predifined array of $what = array(zoneid => array('name' => 'zonename'))
 *                    or an the publisher id (int)
 * @param string $target The target window for links
 * @param string $source The "source" value, used for "Site:Source" delivery limitations
 * @param int    $withtext 1/0 should the "Text below banner" be appended to the banner HTML
 * @param int    $block 1/0 Should a banner only be shown once on the page?
 * @param int    $blockcampaign (0/1) Should only one banner per campaign be shown on the page
 * @return array An array of the HTML to render the selected ads
 */
function view_spc($what, $target = '', $source = '', $withtext = 0, $block = 0, $blockcampaign = 0)
{
    global $context;
    if (is_numeric($what)) {
        $zones = OA_cacheGetPublisherZones($what);
        $nz = false;
    } else {
        $zones = $what;
        $nz = true;
    }

    $spc_output = [];
    $fo_required = false;
    foreach ($zones as $zone => $data) {
        if (empty($zone)) {
            continue;
        }
        // nz is set when "named zones" are being used, this allows a zone to be selected more than once
        if ($nz) {
            $varname = $zone;
            $zoneid = $data;
        } else {
            $varname = $zoneid = $zone;
        }

        // Get the banner
        $output = MAX_adSelect('zone:' . $zoneid, '', $target, $source, $withtext, $charset, $context, true, '', $GLOBALS['loc'], $GLOBALS['referer']);
        $spc_output[$varname] = $output['html'];

        // Block this banner for next invocation
        if (!empty($block) && !empty($output['bannerid'])) {
            $output['context'][] = ['!=' => 'bannerid:' . $output['bannerid']];
        }
        // Block this campaign for next invocation
        if (!empty($blockcampaign) && !empty($output['campaignid'])) {
            $output['context'][] = ['!=' => 'campaignid:' . $output['campaignid']];
        }
        // Pass the context array back to the next call, have to iterate over elements to prevent duplication
        if (!empty($output['context'])) {
            foreach ($output['context'] as $id => $contextArray) {
                if (!in_array($contextArray, $context)) {
                    $context[] = $contextArray;
                }
            }
        }
    }

    // Make sure the FlashObject library is available if required
    if ($fo_required) {
        echo MAX_flashGetFlashObjectExternal();
    }

    return $spc_output;
}
