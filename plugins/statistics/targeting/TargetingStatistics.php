<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.3                                                              |
| =============                                                             |
|                                                                           |
| Copyright (c) 2003-2007 Openads Ltd                                       |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
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
 *
 *
 * @package    MaxPlugin
 * @subpackage TargetingStatistics
 * @author     Robert Hunter <roh@m3.net>
 */
class TargetingStatistics
{
    var $_minimum;
    var $_maximum;

    function setMinimumRequestRate($rate)
    {
        $this->_minimum = $rate;
    }

    function minimumRequestRate()
    {
        return $this->_minimum;
    }

    function setMaximumRequestRate($rate)
    {
        $this->_maximum = $rate;
    }

    function maximumRequestRate()
    {
        return $this->_maximum;
    }

    function isVariationExcessive()
    {
    	$max = $this->_maximum;
        $min = $this->_minimum;

        // HACK: Cheap hack to avoid division by zero
        if ($min == 0) {
            $min = 1;
        }

        $range = $max - $min;
        $proportion = $range / $min;
        if ($proportion > 0.10) {
            return true;
        }

        return false;
    }

}

?>
