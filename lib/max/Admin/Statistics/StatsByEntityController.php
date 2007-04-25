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

require_once MAX_PATH . '/lib/max/Admin/Statistics/StatsController.php';



/**
 * Controller class for displaying entitiy type statistics screens
 *
 * Always use the factory method to instantiate fields -- it will create
 * the right subclass for you.
 *
 * @package    Max
 * @subpackage Admin_Statistics
 * @author     Matteo Beccati <matteo@beccati.com>
 *
 * @see StatsControllerFactory
 */
class StatsByEntityController extends StatsController
{
    /** @var boolean */
    var $showHideInactive = false;
    /** @var array */
    var $entities;
    /** @var int */
    var $startLevel;
    /** @var boolean */
    var $hideInactive;
    /** @var int */
    var $hiddenEntities = 0;
    /** @var array */
    var $showHideLevels;

    /** @var array */
    var $data;
    /** @var array */
    var $childrendata;

    /** @var array */
    var $entityLinks = array(
            'a' => 'stats.php?entity=advertiser&breakdown=history',
            'c' => 'stats.php?entity=campaign&breakdown=history',
            'b' => 'stats.php?entity=banner&breakdown=history',
            'p' => 'stats.php?entity=affiliate&breakdown=history',
            'z' => 'stats.php?entity=zone&breakdown=history'
        );

    /**
     * PHP5-style constructor
     */
    function __construct($params)
    {
        // Get list order and direction
        $this->listOrderField     = MAX_getStoredValue('listorder', 'name');
        $this->listOrderDirection = MAX_getStoredValue('orderdirection', 'up');

        parent::__construct($params);

        // Store the preferences
        $this->pagePrefs['listorder']      = $this->listOrderField;
        $this->pagePrefs['orderdirection'] = $this->listOrderDirection;
    }

    /**
     * PHP4-style constructor
     */
    function StatsByEntityController($params)
    {
        $this->__construct($params);
    }

    /**
     * Output the controller object using the breakdown_by_entity template
     */
    function output()
    { 
        $this->template = 'breakdown_by_entity.html';

        $this->flattenEntities();

        parent::output();
    }

    /**
     * Internal function to convert a tree-style entities array to a flat array
     *
     * @param array Entities array
     * @param int Entities array
     * @param array Reference to the parent entity
     *
     * @return array Flat entities array
     */
    function _flattenEntities($entities, &$cycle_var, $parent = null)
    {
        $ret = array();

        foreach ($entities as $e) {
            if (is_null($parent)) {
                $e['level'] = 0;
                $e['padding'] = 0;

                $e['htmlclass'] = ($cycle_var++ % 2 == 0) ? 'dark' : 'light';
            } else {
                $e['level'] = $parent['level'] + 1;

                $e['htmlclass'] = $parent['htmlclass'];
                $e['padding'] = $parent['padding'] + 16;
            }

            $e['nameclass'] = $e['htmlclass'];

            $sub = null;
            if (isset($e['subentities'])) {
                if (count($e['subentities'])) {
                    $sub = $this->_flattenEntities($e['subentities'], $cycle_var, $e);
                }

                unset($e['subentities']);
            }

            $ret[] = $e;

            if (is_array($sub))
                $ret = array_merge($ret, $sub);
        }

        return $ret;
    }

    /**
     * Recursively convert the tree-style entities array to a flat array
     * suitable for displaying it in a template
     */
    function flattenEntities()
    {
        $i = 0;
        $this->entities = $this->_flattenEntities($this->entities, $i);

        if (count($this->entities))
        {
            $this->entities[count($this->entities) - 1]['htmlclass'] .= ' last';
            $this->entities[count($this->entities) - 1]['nameclass'] .= ' last';

            foreach (array_keys($this->entities) as $k) {
                if ($k && $this->entities[$k]['level'] != 0) {
                    $this->entities[$k-1]['nameclass'] = 'nb'.$this->entities[$k-1]['nameclass'];
                }
            }
        }
    }

    /**
     * Return the appriopriate link for an entity -- helper function for Flexy
     */
    function entityLink($key)
    {
        return empty($this->entityLinks[$key]) ? false : $this->entityLinks[$key];
    }

    /**
     * Internal function to aggregate the stats data
     *
     * @param array Entities array
     * @param array Stats row
     * @param string Key name
     */
    function _prepareDataAdd(&$entity, $row, $key)
    {
        if (!isset($entity[$row[$key]])) {
            $entity[$row[$key]][$key] = $row[$key];
            foreach (array_keys($this->columns) as $s) {
                $entity[$row[$key]][$s] = 0;
            }
        }

        // Use $row keys instead of $this->column to preserve non visible data
        foreach (array_keys($row) as $s) {
            if (isset($row[$s])) {
                if (!isset($entity[$row[$key]][$s])) {
                    $entity[$row[$key]][$s] = 0;
                }
                $entity[$row[$key]][$s] += $row[$s];
            }
        }
    }

    /**
     * Fetch and aggregate stats using the specified parameters
     *
     * @param array Query parameters
     */
    function prepareData($aParams)
    {
        if (is_null($this->data))
        {
            // Get plugin aParams
            $pluginParams = array();
            foreach ($this->plugins as $plugin) {
                $plugin->addQueryParams($pluginParams);
            }

            $aRows = Admin_DA::fromCache('getEntitiesStats', $aParams + $this->aDates + $pluginParams);

            // Merge plugin additional data
            foreach ($this->plugins as $plugin) {
                $plugin->mergeData($aRows, $this->emptyRow, 'getEntitiesStats', $aParams + $this->aDates);
            }

            $this->data = array(
                'advertiser_id' => array(),
                'placement_id'  => array(),
                'ad_id'         => array(),
                'publisher_id'  => array(),
                'zone_id'       => array()
            );

            if (!count($aRows)) {
                $this->noStatsAvailable = true;
                return;
            }

            $aggregates = array('ad_id', 'zone_id');
            if (isset($aParams['exclude'])) {
                $aggregates = array_diff($aggregates, $aParams['exclude']);
            }
            if (isset($aParams['include'])) {
                $aggregates = array_merge($aggregates, $aParams['include']);
            }

            $this->childrendata = array();
            if (array_search('ad_id', $aggregates) !== false) {
                $this->childrendata['ad_id'] = Admin_DA::fromCache('getAds', $aParams);
            }
            if (array_search('placement_id', $aggregates) !== false) {
                $this->childrendata['placement_id'] = Admin_DA::fromCache('getPlacementsChildren', $aParams);

                if (isset($this->childrendata['ad_id'])) {
                    foreach ($this->childrendata['ad_id'] as $key => $item) {
                        $this->childrendata['ad_id'][$key]['advertiser_id'] = $this->childrendata['placement_id'][$item['placement_id']]['advertiser_id'];
                        $this->childrendata['placement_id'][$item['placement_id']]['children'][$key] = &$this->childrendata['ad_id'][$key];
                    }
                }
            }
            if (array_search('advertiser_id', $aggregates) !== false) {
                $this->childrendata['advertiser_id'] = Admin_DA::fromCache('getAdvertisersChildren', $aParams);

                if (isset($this->childrendata['placement_id'])) {
                    foreach ($this->childrendata['placement_id'] as $key => $item) {
                        $this->childrendata['advertiser_id'][$item['advertiser_id']]['children'][$key] = &$this->childrendata['placement_id'][$key];
                    }
                }
            }
            if (array_search('zone_id', $aggregates) !== false) {
                $this->childrendata['zone_id'] = Admin_DA::fromCache('getZones', $aParams);
            }
            if (array_search('publisher_id', $aggregates) !== false) {
                $this->childrendata['publisher_id'] = Admin_DA::fromCache('getPublishersChildren', $aParams);

                if (isset($this->childrendata['zone_id'])) {
                    foreach ($this->childrendata['zone_id'] as $key => $item) {
                        $this->childrendata['publisher_id'][$item['publisher_id']]['children'][$key] = &$this->childrendata['zone_id'][$key];
                    }
                }
            }

            foreach ($aRows as $row) {
                foreach ($aggregates as $agg) {
                    $this->_prepareDataAdd($this->data[$agg], $row, $agg);
                }
            }
        }
    }

    /**
     * Merge aggregate stats with entity properties (name, children, etc)
     *
     * @param array Query parameters
     * @param string Key name
     * @return array Full entity stats with entity data
     */
    function mergeData($aParams, $key)
    {
        $aEntities = array();

        if (isset($this->childrendata[$key])) {
            if ($key == 'placement_id' && !empty($aParams['advertiser_id']) &&
                isset($this->childrendata['advertiser_id'][$aParams['advertiser_id']]['children'])) {
                $aEntities = $this->childrendata['advertiser_id'][$aParams['advertiser_id']]['children'];
            } elseif ($key == 'ad_id' && !empty($aParams['placement_id']) &&
                isset($this->childrendata['placement_id'][$aParams['placement_id']]['children'])) {
                $aEntities = $this->childrendata['placement_id'][$aParams['placement_id']]['children'];
            } elseif ($key == 'zone_id' && !empty($aParams['publisher_id']) &&
                isset($this->childrendata['publisher_id'][$aParams['publisher_id']]['children'])) {
                $aEntities = $this->childrendata['publisher_id'][$aParams['publisher_id']]['children'];
            } else {
                $aEntities = $this->childrendata[$key];
            }
            foreach (array_keys($aEntities) as $entityId) {
                if (isset($this->data[$key][$entityId])) {
                    $aEntities[$entityId] += $this->data[$key][$entityId];
                } else {
                    foreach (array_keys($this->columns) as $s) {
                        $aEntities[$entityId][$s] = 0;
                    }
                }
            }
        }

        return $aEntities;
    }

    /**
     * Get advertiser stats
     *
     * @param array Query parameters
     * @param int Tree level
     * @param string Expand GET parameter, used only when called from other get methods
     * @return Entities array
     */
    function getAdvertisers($aParams, $level, $expand = '')
    {
        $aParams['include'] = array('advertiser_id', 'placement_id');
        $aParams['exclude'] = array('zone_id');
        $this->prepareData($aParams);
        $period_preset = MAX_getStoredValue('period_preset', 'today');
        $aAdvertisers = $this->mergeData($aParams, 'advertiser_id');
        MAX_sortArray(
            $aAdvertisers,
            ($this->listOrderField == 'id' ? 'advertiser_id' : $this->listOrderField),
            $this->listOrderDirection == 'up'
        );

        $entities = array();
        foreach ($aAdvertisers as $advertiserId => $advertiser) {
            $advertiser['active'] = $this->hasActiveStats($advertiser);

            $this->summarizeStats($advertiser);

            if ($this->startLevel > $level || !$this->hideInactive || $advertiser['active']) {
                $advertiser['prefix'] = 'a';
                $advertiser['id'] = $advertiserId;
                $advertiser['linkparams'] = "clientid={$advertiserId}&";                
                if (is_array($aParams) && count($aParams) > 0) {
                    foreach ($aParams as $key => $value) {
                        if ($key != "include" && $key != "exclude") {
                            $advertiser['linkparams'] .= $key . "=" . $value . "&";
                        }
                    }
                } else {
                    $advertiser['linkparams'] .= "&";
                }      
                $advertiser['linkparams'] .= "period_preset={$period_preset}&period_start=" . MAX_getStoredValue('period_start', date('Y-m-d')) 
                                          . "&period_end=" . MAX_getStoredValue('period_end', date('Y-m-d'));
                
                $advertiser['conversionslink'] = "stats.php?entity=conversions&clientid={$advertiserId}";
                $advertiser['expanded'] = MAX_isExpanded($advertiserId, $expand, $this->aNodes, $advertiser['prefix']);
                $advertiser['icon'] = MAX_getEntityIcon('advertiser', $advertiser['active']);

                if ($advertiser['expanded'] || $this->startLevel > $level) {
                    $aParams2 = $aParams + array('advertiser_id' => $advertiserId);
                    $advertiser['subentities'] = $this->getCampaigns($aParams2, $level + 1, $expand);
                }

                $entities[] = $advertiser;
            } elseif ($this->startLevel == $level) {
                $this->hiddenEntities++;
            }
        }

        return $entities;
    }

    /**
     * Get campaign stats
     *
     * @param array Query parameters
     * @param int Tree level
     * @param string Expand GET parameter, used only when called from other get methods
     * @return Entities array
     */
    function getCampaigns($aParams, $level, $expand = '')
    {
        $aParams['include'] = array('placement_id');
        $aParams['exclude'] = array('zone_id');
        $this->prepareData($aParams);        
        $period_preset = MAX_getStoredValue('period_preset', 'today');

        $aPlacements = $this->mergeData($aParams, 'placement_id');
        MAX_sortArray(
            $aPlacements,
            ($this->listOrderField == 'id' ? 'placement_id' : $this->listOrderField),
            $this->listOrderDirection == 'up'
        );

        $entities = array();
        foreach ($aPlacements as $campaignId => $campaign) {
            $campaign['active'] = $this->hasActiveStats($campaign);

            if ($this->startLevel > $level || !$this->hideInactive || $campaign['active']) {

                $this->summarizeStats($campaign);
                // mask anonymous campaigns if advertiser
                if (phpAds_isUser(phpAds_Advertiser)) {
                    // a) mask campaign name
                    $campaign['name'] = MAX_getPlacementName($campaign);
                    // b) mask ad names
                    if ($campaign['anonymous'] == 't') {
                        foreach ($campaign['children'] as $ad_id => $ad) {
                            $campaign['children'][$ad_id]['name'] = MAX_getAdName($ad['name'], null, null, $campaign['anonymous'], $ad_id);
                        }
                    }
                }

                // mask anonymous campaigns
                // a) mask campaign name
                $campaign['name'] = MAX_getPlacementName($campaign);
                // b) mask ad names
                foreach ($campaign['children'] as $ad_id => $ad) {
                    $campaign['children'][$ad_id]['name'] = MAX_getAdName($ad['name'], null, null, $campaign['anonymous'], $ad_id);
                }
                $campaign['prefix'] = 'c';
                $campaign['id'] = $campaignId;
                $campaign['linkparams'] = "clientid={$campaign['advertiser_id']}&campaignid={$campaignId}&";
                if (is_array($aParams) && count($aParams) > 0) {
                    foreach ($aParams as $key => $value) {
                        if ($key != "include" && $key != "exclude") {
                            $campaign['linkparams'] .= $key . "=" . $value . "&";
                        }
                    }
                } else {
                    $campaign['linkparams'] .= "&";
                }                
                $campaign['linkparams'] .= "period_preset={$period_preset}&period_start=" . MAX_getStoredValue('period_start', date('Y-m-d')) 
                                          . "&period_end=" . MAX_getStoredValue('period_end', date('Y-m-d'));
                $campaign['expanded'] = MAX_isExpanded($campaignId, $expand, $this->aNodes, $campaign['prefix']);
                $campaign['icon'] = MAX_getEntityIcon('placement', $campaign['active']);

                if ($campaign['expanded'] || $this->startLevel > $level) {
                    $aParams2 = $aParams + array('placement_id' => $campaignId);
                    $campaign['subentities'] = $this->getBanners($aParams2, $level + 1, $expand);
                }

                $entities[] = $campaign;
            } elseif ($this->startLevel == $level) {
                $this->hiddenEntities++;
            }
        }

        return $entities;
    }

    /**
     * Get banner stats
     *
     * @param array Query parameters
     * @param int Tree level
     * @param string Expand GET parameter, used only when called from other get methods
     * @return Entities array
     */
    function getBanners($aParams, $level, $expand = '')
    {
        $aParams['include'] = array('placement_id'); // Needed to fetch the advertiser_id
        $aParams['exclude'] = array('zone_id');
        $this->prepareData($aParams);
        $period_preset = MAX_getStoredValue('period_preset', 'today');
        
        $aAds = $this->mergeData($aParams, 'ad_id');
        MAX_sortArray(
            $aAds,
            ($this->listOrderField == 'id' ? 'ad_id' : $this->listOrderField),
            $this->listOrderDirection == 'up'
        );

        $entities = array();
        foreach ($aAds as $bannerId => $banner) {
            $banner['active'] = $this->hasActiveStats($banner);

            if ($this->startLevel > $level || !$this->hideInactive || $banner['active']) {

                $this->summarizeStats($banner);
                // mask banner name if anonymous campaign
                $campaign = Admin_DA::getPlacement($banner['placement_id']);
                $campaignAnonymous = $campaign['anonymous'] == 't' ? true : false;
                $banner['name'] = MAX_getAdName($banner['name'], null, null, $campaignAnonymous, $bannerId);
                $banner['prefix'] = 'b';
                $banner['id'] = $bannerId;
                $banner['linkparams'] = "clientid={$banner['advertiser_id']}&campaignid={$banner['placement_id']}&bannerid={$bannerId}&";                        
                if (is_array($aParams) && count($aParams) > 0) {
                    foreach ($aParams as $key => $value) {
                        if ($key != "include" && $key != "exclude") {
                            $banner['linkparams'] .= $key . "=" . $value . "&";
                        }
                    }
                } else {
                    $banner['linkparams'] .= "&";
                }   
                $banner['linkparams'] .= "period_preset={$period_preset}&period_start=" . MAX_getStoredValue('period_start', date('Y-m-d')) 
                                          . "&period_end=" . MAX_getStoredValue('period_end', date('Y-m-d'));
                $banner['expanded'] = false;
                $banner['icon'] = MAX_getEntityIcon('ad', $banner['active'], $banner['type']);

                $entities[] = $banner;
            } elseif ($this->startLevel == $level) {
                $this->hiddenEntities++;
            }
        }

        return $entities;
    }

    /**
     * Get publisher stats
     *
     * @param array Query parameters
     * @param int Tree level
     * @param string Expand GET parameter, used only when called from other get methods
     * @return Entities array
     */
    function getPublishers($aParams, $level, $expand = '')
    {
        $aParams['include'] = array('publisher_id');
        $aParams['exclude'] = array('ad_id');
        $this->prepareData($aParams);
        $period_preset = MAX_getStoredValue('period_preset', 'today');

        $aPublishers = $this->mergeData($aParams, 'publisher_id');
        MAX_sortArray(
            $aPublishers,
            ($this->listOrderField == 'id' ? 'publisher_id' : $this->listOrderField),
            $this->listOrderDirection == 'up'
        );

        $entities = array();
        foreach ($aPublishers as $publisherId => $publisher) {
            $publisher['active'] = $this->hasActiveStats($publisher);

            $this->summarizeStats($publisher);

            if ($this->startLevel > $level || !$this->hideInactive || $publisher['active']) {
                $publisher['prefix'] = 'p';
                $publisher['id'] = $publisherId;
                $publisher['linkparams'] = "affiliateid={$publisherId}&";
                if (is_array($aParams) && count($aParams) > 0) {
                    foreach ($aParams as $key => $value) {
                        if ($key != "include" && $key != "exclude") {
                            $publisher['linkparams'] .= $key . "=" . $value . "&";
                        }
                    }
                } else {
                    $publisher['linkparams'] .= "&";
                }   
                $publisher['linkparams'] .= "period_preset={$period_preset}&period_start=" . MAX_getStoredValue('period_start', date('Y-m-d')) 
                                          . "&period_end=" . MAX_getStoredValue('period_end', date('Y-m-d'));
                $publisher['expanded'] = MAX_isExpanded($publisherId, $expand, $this->aNodes, $publisher['prefix']);
                $publisher['icon'] = MAX_getEntityIcon('publisher', $publisher['active']);

                if ($publisher['expanded'] || $this->startLevel > $level) {
                    $aParams2 = $aParams + array('publisher_id' => $publisherId);
                    $publisher['subentities'] = $this->getZones($aParams2, $level + 1, $expand);
                }

                $entities[] = $publisher;
            } elseif ($this->startLevel == $level) {
                $this->hiddenEntities++;
            }
        }

        return $entities;
    }

    /**
     * Get zone stats
     *
     * @param array Query parameters
     * @param int Tree level
     * @param string Expand GET parameter, used only when called from other get methods
     * @return Entities array
     */
    function getZones($aParams, $level, $expand)
    {
        $aParams['exclude'] = array('ad_id');
        $this->prepareData($aParams);
        $period_preset = MAX_getStoredValue('period_preset', 'today');
        
        $aZones = $this->mergeData($aParams, 'zone_id');
        MAX_sortArray(
            $aZones,
            ($this->listOrderField == 'id' ? 'zone_id' : $this->listOrderField),
            $this->listOrderDirection == 'up'
        );

        $entities = array();
        foreach ($aZones as $zoneId => $zone) {
            $zone['active'] = $this->hasActiveStats($zone);

            if ($this->startLevel > $level || !$this->hideInactive || $zone['active']) {

                $this->summarizeStats($zone);

                $zone['prefix'] = 'z';
                $zone['id'] = $zoneId;
                $zone['linkparams'] = "affiliateid={$zone['publisher_id']}&zoneid={$zoneId}&";
                if (is_array($aParams) && count($aParams) > 0) {
                    foreach ($aParams as $key => $value) {
                        if ($key != "include" && $key != "exclude") {
                            $zone['linkparams'] .= $key . "=" . $value . "&";
                        }
                    }
                } else {
                    $zone['linkparams'] .= "&";
                }   
                $zone['linkparams'] .= "period_preset={$period_preset}&period_start=" . MAX_getStoredValue('period_start', date('Y-m-d')) 
                                          . "&period_end=" . MAX_getStoredValue('period_end', date('Y-m-d'));
                $zone['expanded'] = MAX_isExpanded($zoneId, $expand, $this->aNodes, $zone['prefix']);;
                $zone['icon'] = MAX_getEntityIcon('zone', $zone['active'], $zone['type']);

                $entities[] = $zone;
            } elseif ($this->startLevel == $level) {
                $this->hiddenEntities++;
            }
        }

        return $entities;
    }

    /**
     * Return bool - checks if there are any non empty impresions in object
     *
     * @return bool
     */
    function isEmptyResultArray()
    {
        foreach($this->entities as $record) {

            if($record['sum_requests'] != '-' || $record['sum_views'] != '-' || $record['sum_clicks'] != '-') {
                return false;
            }
        }

        return true;
    }

    /**
     * Exports stats data to an array
     *
     * The array will look like:
     *
     * Array (
     *     'headers' => Array ( 0 => 'Col1', 1 => 'Col2', ... )
     *     'formats' => Array ( 0 => 'text', 1 => 'default', ... )
     *     'data'    => Array (
     *         0 => Array ( 0 => 'Entity 1', 1 => '5', ...),
     *         ...
     *     )
     * )
     *
     * @param array Stats array
     */
    function exportArray()
    {
        $parent = parent::exportArray();

        $headers = array_merge(array($GLOBALS['strName']), $parent['headers']);
        $formats = array_merge(array('text'), $parent['formats']);
        $data    = array();

        foreach ($this->entities as $e) {
            $row = array();
            $row[] = $e['name'];
            foreach (array_keys($this->columns) as $ck) {
                if ($this->showColumn($ck)) {
                    $row[] = $e[$ck];
                }
            }

            $data[] = $row;
        }

        return array(
            'headers' => $headers,
            'formats' => $formats,
            'data'    => $data
        );
    }
}

?>
