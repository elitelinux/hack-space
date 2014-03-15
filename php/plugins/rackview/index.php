<?php
/*
 * @version $Id: index.php 69 2010-12-08 08:13:41Z bogucool $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Dennis Plöger <dennis.ploeger@getit.de>
// Purpose of file: Index page for menu
// ----------------------------------------------------------------------


$NEEDED_ITEMS=array('search');

include ('../../inc/includes.php');

plugin_rackview_haveRight('rackview',"r");

Html::header(
    $LANG['plugin_rackview']['title'],
    $_SERVER['PHP_SELF'],
    'plugins',
    'rackview'
);

Search::show('PluginRackviewRack');

Html::footer();

?>
