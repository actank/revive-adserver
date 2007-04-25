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

// Require the initialisation file
require_once '../../init.php';

// Required files
require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-banner.inc.php';
require_once MAX_PATH . '/www/admin/lib-storage.inc.php';

// Security check
MAX_Permission::checkAccess(phpAds_Admin);

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

$doBanners = OA_Dal::factoryDO('banners');
$doBanners->storagetype = 'sql';
$doBanners->find();

while ($doBanners->fetch())
{
	// Get the filename
	$filename = $doBanners->filename;

	// Copy the file
	$buffer = phpAds_ImageRetrieve('sql', $filename);
	$doBanners->filename = phpAds_ImageStore('web', $filename, $buffer);

	// TODO: Would be nice if we gave some indication to the user of success or failure!
	if ($doBanners->filename != false)
	{
		phpAds_ImageDelete ('sql', $filename);

	    $doBannersClone = clone($doBanners);

		$doBannersClone->imageurl = '';
		$doBannersClone->storagetype = 'web';

        $doBannersClone->update();
	}
}

Header("Location: maintenance-storage.php");

?>