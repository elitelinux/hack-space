<?php
/*
 * @version $Id: ruledictionnaryphonemodel.class.php 20129 2013-02-04 16:53:59Z moyo $
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

class RuleDictionnaryPhoneModel extends RuleDictionnaryDropdown {


   /**
    * Constructor
   **/
   function __construct() {
      parent::__construct('RuleDictionnaryPhoneModel');
   }


   /**
    * @see Rule::getCriterias()
   **/
   function getCriterias() {

      static $criterias = array();

      if (count($criterias)) {
         return $criterias;
      }

      $criterias['name']['field']         = 'name';
      $criterias['name']['name']          = __('Model');
      $criterias['name']['table']         = 'glpi_phonemodels';

      $criterias['manufacturer']['field'] = 'name';
      $criterias['manufacturer']['name']  = __('Manufacturer');
      $criterias['manufacturer']['table'] = 'glpi_manufacturers';

      return $criterias;
   }


   /**
    * @see Rule::getActions()
   **/
   function getActions() {

      $actions                          = array();
      $actions['name']['name']          = __('Model');
      $actions['name']['force_actions'] = array('append_regex_result', 'assign', 'regex_result');

      return $actions;
   }

}
?>
