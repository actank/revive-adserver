<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.3                                                              |
| =============                                                             |
|                                                                           |
| Copyright (c) 2003-2007 Openads Ltd                                       |
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

/**
 * @package    MaxPlugin
 * @subpackage 3rdPartyServers
 * @author     Radek Maciaszek <radek@m3.net>
 *
 */

require_once MAX_PATH . '/plugins/3rdPartyServers/3rdPartyServers.php';

/**
 *
 * 3rdPartyServer plugin. Allow for generating different banner html cache
 *
 * @static
 */
class Plugins_3rdPartyServers_mediaplex_mediaplex extends Plugins_3rdPartyServers
{

    /**
     * Return the name of plugin
     *
     * @return string
     */
    function getName()
    {
        include_once MAX_PATH . '/lib/max/Plugin/Translation.php';
        MAX_Plugin_Translation::init($this->module, $this->package);

        return MAX_Plugin_Translation::translate('Rich Media - Mediaplex', $this->module, $this->package);
    }

    /**
     * Return plugin cache
     *
     * @return string
     */
    function getBannerCache($buffer, &$noScript)
    {
        $search = array(
            '#mpt=(ADD_RANDOM_NUMBER_HERE|\[CACHEBUSTER\])#',
            '#mpvc=(.*?)([\'"\\\\])(.*)#',
        );
		$replace = array(
		    'mpt={random}',
		    'mpvc={clickurl}$2$3',
		);

        $buffer = preg_replace($search, $replace, $buffer);

		// Target gets broken from the default REGEX's..
		$search = array(
		    'mpt=\"+cb',
		    'target=\\\'{target}\\\'',
		);
		$replace = array(
		    'mpt=\\\'+cb',
		    'target=\\"{target}\\"',
		);

        $buffer = str_replace($search, $replace, $buffer);

        $noScript[0] = preg_replace($search[0], $replace[0], $noScript[0]);

        return $buffer;
    }

}

?>