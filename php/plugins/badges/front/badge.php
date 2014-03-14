<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Badges plugin for GLPI
 Copyright (C) 2003-2011 by the badges Development Team.

 https://forge.indepnet.net/projects/badges
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of badges.

 Badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$plugin = new Plugin();
$badge=new PluginBadgesBadge();

if ($plugin->isActivated("environment"))
	Html::header(PluginBadgesBadge::getTypeName(),'',"plugins","environment","badges");
else
	Html::header(PluginBadgesBadge::getTypeName(),'',"plugins","badges");

if ($badge->canView() || Session::haveRight("config","w")) {

	Search::show("PluginBadgesBadge");

} else {
	Html::displayRightError();
}

Html::footer();

?>