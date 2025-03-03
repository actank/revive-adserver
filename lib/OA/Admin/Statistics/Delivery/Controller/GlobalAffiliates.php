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

require_once MAX_PATH . '/lib/OA/Admin/Statistics/Delivery/CommonEntity.php';

/**
 * The class to display the delivery statistcs for the page:
 *
 * Statistics -> Publishers & Zones
 *
 * @package    OpenXAdmin
 * @subpackage StatisticsDelivery
 */
class OA_Admin_Statistics_Delivery_Controller_GlobalAffiliates extends OA_Admin_Statistics_Delivery_CommonEntity
{
    public $aNodes;
    /**
     * @var mixed
     */
    public $coreParams;
    public $hiddenEntitiesText;
    /**
     * The final "child" implementation of the PHP5-style constructor.
     *
     * @param array $aParams An array of parameters. The array should
     *                       be indexed by the name of object variables,
     *                       with the values that those variables should
     *                       be set to. For example, the parameter:
     *                       $aParams = array('foo' => 'bar')
     *                       would result in $this->foo = bar.
     */
    public function __construct($aParams)
    {
        // Set this page's entity/breakdown values
        $this->entity = 'global';
        $this->breakdown = 'affiliates';

        // This page uses the day span selector element
        $this->showDaySpanSelector = true;

        parent::__construct($aParams);
    }

    /**
     * The final "child" implementation of the parental abstract method.
     *
     * @see OA_Admin_Statistics_Common::start()
     */
    public function start()
    {
        // Get the preferences
        $aPref = $GLOBALS['_MAX']['PREF'];

        // Security check
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN, OA_ACCOUNT_MANAGER);

        // HTML Framework
        $this->pageId = '2.4';
        $this->aPageSections = ['2.1', '2.4', '2.2'];

        $this->hideInactive = MAX_getStoredValue('hideinactive', ($aPref['ui_hide_inactive'] == true), null, true);
        $this->showHideInactive = true;

        $this->startLevel = MAX_getStoredValue('startlevel', 0, null, true);

        // Init nodes
        $this->aNodes = MAX_getStoredArray('nodes', []);
        $expand = MAX_getValue('expand', '');
        $collapse = MAX_getValue('collapse');

        // Adjust which nodes are opened closed...
        MAX_adjustNodes($this->aNodes, $expand, $collapse);

        $aParams = $this->coreParams;
        if (!OA_Permission::isAccount(OA_ACCOUNT_ADMIN)) {
            $aParams['agency_id'] = OA_Permission::getAgencyId();
        }

        // Load the period preset and stats breakdown parameters
        $this->_loadPeriodPresetParam();
        $this->_loadStatsBreakdownParam();

        $this->_loadParams();

        switch ($this->startLevel) {
            case 1:
                $this->aEntitiesData = $this->getZones($aParams, $this->startLevel, $expand);
                break;
            default:
                $this->startLevel = 0;
                $this->aEntitiesData = $this->getPublishers($aParams, $this->startLevel, $expand);
                break;
        }

        // Summarise the values into a the totals array, & format
        $this->_summariseTotalsAndFormat($this->aEntitiesData);

        $this->showHideLevels = [];
        switch ($this->startLevel) {
            case 1:
                $this->showHideLevels = [
                    0 => ['text' => $GLOBALS['strShowParentAffiliates'], 'icon' => 'images/icon-affiliate.gif'],
                ];
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveZonesHidden']}";
                break;
            case 0:
                $this->showHideLevels = [
                    1 => ['text' => $GLOBALS['strHideParentAffiliates'], 'icon' => 'images/icon-affiliate-d.gif'],
                ];
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveAffiliatesHidden']}";
                break;
        }


        // Save prefs
        $this->aPagePrefs['startlevel'] = $this->startLevel;
        $this->aPagePrefs['nodes'] = implode(",", $this->aNodes);
        $this->aPagePrefs['hideinactive'] = $this->hideInactive;
        $this->aPagePrefs['startlevel'] = $this->startLevel;
    }
}
