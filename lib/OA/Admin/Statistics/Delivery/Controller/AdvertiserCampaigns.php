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
 * Statistics -> Advertisers & Campaigns -> Campaigns
 *
 * @package    OpenXAdmin
 * @subpackage StatisticsDelivery
 */
class OA_Admin_Statistics_Delivery_Controller_AdvertiserCampaigns extends OA_Admin_Statistics_Delivery_CommonEntity
{
    /**
     * @var string[]|int[]
     */
    public $aPageContext;
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
        $this->entity = 'advertiser';
        $this->breakdown = 'campaigns';

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

        // Get parameters
        $advertiserId = $this->_getId('advertiser');

        // Security check
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN, OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADVERTISER);
        $this->_checkAccess(['advertiser' => $advertiserId]);

        // Add standard page parameters
        $this->aPageParams = [
            'clientid' => $advertiserId
        ];

        // Load the period preset and stats breakdown parameters
        $this->_loadPeriodPresetParam();
        $this->_loadStatsBreakdownParam();

        // Load $_GET parameters
        $this->_loadParams();

        // HTML Framework
        if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN) || OA_Permission::isAccount(OA_ACCOUNT_MANAGER)) {
            $this->pageId = '2.1.2';
            $this->aPageSections = ['2.1.1', '2.1.2', '2.1.3'];
        } elseif (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
            $this->pageId = '1.2';
            $this->aPageSections = ['1.1', '1.2', '1.3'];
        }

        // Add breadcrumbs
        $this->_addBreadcrumbs('advertiser', $advertiserId);

        // Add context
        $this->aPageContext = ['advertisers', $advertiserId];

        // Add shortcuts
        if (!OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
            $this->_addShortcut(
                $GLOBALS['strClientProperties'],
                'advertiser-edit.php?clientid=' . $advertiserId,
                'iconAdvertiser'
            );
        }




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
        $aParams['advertiser_id'] = $advertiserId;

        // Limit by publisher
        $publisherId = (int)MAX_getValue('affiliateid', '');
        if (!empty($publisherId)) {
            $aParams['publisher_id'] = $publisherId;
        }

        // Limit by publisher
        $publisherId = (int)MAX_getValue('affiliateid', '');
        if (!empty($publisherId)) {
            $aParams['publisher_id'] = $publisherId;
        }

        switch ($this->startLevel) {
            case 1:
                $this->aEntitiesData = $this->getBanners($aParams, $this->startLevel, $expand);
                break;
            default:
                $this->startLevel = 0;
                $this->aEntitiesData = $this->getCampaigns($aParams, $this->startLevel, $expand);
                break;
        }

        // Summarise the values into a the totals array, & format
        $this->_summariseTotalsAndFormat($this->aEntitiesData);

        $this->showHideLevels = [];
        switch ($this->startLevel) {
            case 1:
                $this->showHideLevels = [
                    0 => ['text' => $GLOBALS['strShowParentCampaigns'], 'icon' => 'images/icon-campaign.gif']
                ];
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveBannersHidden']}";
                break;
            case 0:
                $this->showHideLevels = [
                    1 => ['text' => $GLOBALS['strHideParentCampaigns'], 'icon' => 'images/icon-campaign-d.gif']
                ];
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveCampaignsHidden']}";
                break;
        }


        // Save prefs
        $this->aPagePrefs['startlevel'] = $this->startLevel;
        $this->aPagePrefs['nodes'] = implode(",", $this->aNodes);
        $this->aPagePrefs['hideinactive'] = $this->hideInactive;
    }
}
