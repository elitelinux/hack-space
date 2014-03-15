<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Dennis Ploeger <dennis.ploeger@getit.de>
// Purpose of file: Ajax method of displaying a rack
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_GET['id'])) {

    $rackId = $_GET['id'];

    $rack = new PluginRackviewRack();

    if ($rack->getFromDB($rackId)) {

        $mini = false;

        if (
            (isset($_GET['mini'])) &&
            ($_GET['mini'] == 'true')
        ) {

            $mini = true;

        }

        if (isset($_GET['startu'])) {

            $rack->showRack($mini, $_GET['startu']);

        } else {

            $rack->showRack($mini);

        }

    }

}