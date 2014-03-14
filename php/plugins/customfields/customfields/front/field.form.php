<?php
/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org/
----------------------------------------------------------------------

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
------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Thierry Bugier
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Update custom data posted from a form in the 
// customfield's tab
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

if (isset($_POST['update_customfield'])) {
   if (isset($_POST['customfielditemtype']) && isset($_POST['id'])) {

      // Update custom field

      $customFieldsItemType = $_POST['customfielditemtype'];
      $customFieldsItem     = new $customFieldsItemType();
      $customFieldsItem->getFromDB($_POST['id']);
      $customFieldsItem->update($_POST);

   }
}

Html::back();