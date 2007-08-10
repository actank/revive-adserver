<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.5                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/plugins/invocationTags/InvocationTags.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

/**
 * Invocation tag plugin class
 *
 * @package    MaxPlugin
 * @subpackage InvocationTags
 * @author     Radek Maciaszek <radek@m3.net>
 *
 */
class Plugins_InvocationTags_local_local extends Plugins_InvocationTags
{

    /**
     * Return name of plugin
     *
     * @return string
     */
    function getName()
    {
        return MAX_Plugin_Translation::translate('Local Mode Tag', $this->module, $this->package);
    }

    /**
     * Return preference code
     *
     * @return string
     */
    function getPreferenceCode()
    {
        return 'allow_invocation_local';
    }

    /**
     * Check if plugin is allowed
     *
     * @return boolean  True - allowed, false - not allowed
     */
    function isAllowed($extra, $server_same)
    {
        // Set "same_server" as a property on this object, but still permit invocation
        $this->same_server = $server_same;
        return parent::isAllowed($extra);
    }

    /**
     * Return list of options
     *
     * @return array    Group of options
     */
    function getOptionsList()
    {
        $options = array (
            'spacer'      => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'what'          => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'campaignid'    => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'target'        => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'source'        => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'withtext'      => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'block'         => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'blockcampaign' => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
            'raw'           => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
        );

        return $options;
    }

    /**
     * Return invocation code for this plugin (codetype)
     *
     * @return string
     */
    function generateInvocationCode()
    {
        parent::prepareCommonInvocationData();

        $conf = $GLOBALS['_MAX']['CONF'];
        $name = (!empty($GLOBALS['_MAX']['PREF']['name'])) ? $GLOBALS['_MAX']['PREF']['name'] : MAX_PRODUCT_NAME;
        $mi = &$this->maxInvocation;

        $buffer = $mi->buffer;

        // Deal with windows style paths
        $path = MAX_PATH;
        $path = str_replace('\\', '/', $path);

        if (!isset($mi->clientid)   || $mi->clientid == '')   $mi->clientid = 0;
        if (!isset($mi->zoneid)     || $mi->zoneid == '')     $mi->zoneid = 0;
        if (!isset($mi->campaignid) || $mi->campaignid == '') $mi->campaignid = 0;
        if (!isset($mi->bannerid)   || $mi->bannerid == '')   $mi->bannerid = 0;

        $buffer .= "<"."?php\n";
        $buffer .= "  // The MAX_PATH below should point to the base of your {$name} installation\n";
        $buffer .= "  define('MAX_PATH', '" . MAX_PATH . "');\n";
        $buffer .= "  if (@include_once(MAX_PATH . '/www/delivery/{$conf['file']['local']}')) {\n";
        $buffer .= "    if (!isset($"."phpAds_context)) {\n      $"."phpAds_context = array();\n    }\n";
        if (isset($mi->raw) && $mi->raw == '1') {
            $buffer .= "    $"."phpAds_raw = view_local ('$mi->what', $mi->zoneid, $mi->campaignid, $mi->bannerid, '$mi->target', '$mi->source', '$mi->withtext', $"."phpAds_context);\n";
            if (isset($mi->block) && $mi->block == '1') {
                $buffer .= "    $"."phpAds_context[] = array('!=' => 'bannerid:'.$"."phpAds_raw['bannerid']);\n";
            }
            if (isset($mi->blockcampaign) && $mi->blockcampaign == '1') {
                $buffer .= "    $"."phpAds_context[] = array('!=' => 'campaignid:'.$"."phpAds_raw['campaignid']);\n";
            }
            $buffer .= "  }\n    \n";
            $buffer .= "  // " . MAX_Plugin_Translation::translate('Assign the $phpAds_raw[\'html\'] variable to your template', $this->module, $this->package) . "\n";
            $buffer .= "  // echo $"."phpAds_raw['html'];\n";
        } else {
            $buffer .= "    $"."phpAds_raw = view_local('$mi->what', $mi->zoneid, $mi->campaignid, $mi->bannerid, '$mi->target', '$mi->source', '$mi->withtext', $"."phpAds_context);\n";
            if (isset($mi->block) && $mi->block == '1') {
                $buffer .= "    $"."phpAds_context[] = array('!=' => 'bannerid:'.$"."phpAds_raw['bannerid']);\n";
            }
            if (isset($mi->blockcampaign) && $mi->blockcampaign == '1') {
                $buffer .= "    $"."phpAds_context[] = array('!=' => 'campaignid:'.$"."phpAds_raw['campaignid']);\n";
            }
            $buffer .= "    echo $"."phpAds_raw['html'];\n";
            $buffer .= "  }\n";
        }
        $buffer .= "?".">\n";

        return $buffer;
    }

}

?>