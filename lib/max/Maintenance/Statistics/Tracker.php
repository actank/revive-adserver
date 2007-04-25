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

require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Common.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Tracker/Task/SetUpdateRequirements.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Tracker/Task/SummariseIntermediate.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Tracker/Task/SummariseFinal.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Tracker/Task/DeleteOldData.php';
require_once MAX_PATH . '/lib/max/Maintenance/Statistics/Tracker/Task/LogCompletion.php';

/**
 * A class for defining and running the maintenance statistics tasks for the
 * 'Tracker' module.
 *
 * @package    MaxMaintenance
 * @subpackage Statistics
 * @author     Andrew Hill <andrew@m3.net>
 */
class MAX_Maintenance_Statistics_Tracker extends MAX_Maintenance_Statistics_Common
{

    /**
     * The constructor method.
     */
    function MAX_Maintenance_Statistics_Tracker()
    {
        parent::MAX_Maintenance_Statistics_Common();
        // This is the Tracker module
        $this->module = 'Tracker';
        // Register this object as the controlling class for the process
        $oServiceLocator = &ServiceLocator::instance();
        $oServiceLocator->register('Maintenance_Statistics_Controller', $this);
        // Add a task to set the update requirements
        $oSetUpdateRequirements = new MAX_Maintenance_Statistics_Tracker_Task_SetUpdateRequirements();
        $this->oTaskRunner->addTask($oSetUpdateRequirements);
        // Add a task to summarise the raw statistics into intermediate form
        $oSummariseIntermediate = new MAX_Maintenance_Statistics_Tracker_Task_SummariseIntermediate();
        $this->oTaskRunner->addTask($oSummariseIntermediate);
        // Add a task to summarise the intermediate statistics into final form
        $oSummariseFinal = new MAX_Maintenance_Statistics_Tracker_Task_SummariseFinal();
        $this->oTaskRunner->addTask($oSummariseFinal);
        // Add a task to delete old data
        $oDeleteOldData = new MAX_Maintenance_Statistics_Tracker_Task_DeleteOldData();
        $this->oTaskRunner->addTask($oDeleteOldData);
        // Add a task to log the completion of the task
        $oLogCompletion = new MAX_Maintenance_Statistics_Tracker_Task_LogCompletion();
        $this->oTaskRunner->addTask($oLogCompletion);
    }

}

?>
