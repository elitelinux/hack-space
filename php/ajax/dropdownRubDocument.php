<?php
/*
 * @version $Id: dropdownRubDocument.php 21179 2013-06-25 07:08:30Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

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

/** @file
* @brief
*/

if (strpos($_SERVER['PHP_SELF'],"dropdownRubDocument.php")) {
   $AJAX_INCLUDE = 1;
   include ('../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["rubdoc"])) {
   if (!is_array($_POST['used'])) {
      $_POST['used'] = Toolbox::decodeArrayFromInput($_POST['used']);
   }
   $used = array();

   // Clean used array
   if (is_array($_POST['used']) && count($_POST['used'])>0) {
      $query = "SELECT `id`
                FROM `glpi_documents`
                WHERE `id` IN (".implode(',',$_POST['used']).")
                      AND `documentcategories_id` = '".$_POST["rubdoc"]."'";

      foreach ($DB->request($query) AS $data) {
         $used[$data['id']] = $data['id'];
      }
   }

   Dropdown::show('Document',
                  array('name'      => $_POST['myname'],
                        'used'      => $used,
                        'entity'    => $_POST['entity'],
                        'rand'      => $_POST['rand'],
                        'condition' => "glpi_documents.documentcategories_id='".$_POST["rubdoc"]."'"));

}
?>
