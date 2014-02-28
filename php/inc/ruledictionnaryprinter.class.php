<?php
/*
 * @version $Id: ruledictionnaryprinter.class.php 22657 2014-02-12 16:17:54Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

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
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
* Rule class store all information about a GLPI rule :
*   - description
*   - criterias
*   - actions
**/
class RuleDictionnaryPrinter extends Rule {

   // From Rule
   static public $right    = 'rule_dictionnary_printer';
   public $can_sort        = true;


   function getTitle() {
      return __('Dictionnary of printers');
   }


   /**
    * @see Rule::maxActionsCount()
   **/
   function maxActionsCount() {
      return 4;
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
      $criterias['name']['name']          = __('Name');
      $criterias['name']['table']         = 'glpi_printers';

      $criterias['manufacturer']['field'] = 'name';
      $criterias['manufacturer']['name']  = __('Manufacturer');
      $criterias['manufacturer']['table'] = '';

      $criterias['comment']['field']      = 'comment';
      $criterias['comment']['name']       = __('Comments');
      $criterias['comment']['table']      = '';

      return $criterias;
   }


   /**
    * @see Rule::getActions()
   **/
   function getActions() {

      $actions                               = array();

      $actions['name']['name']               = __('Name');
      $actions['name']['force_actions']      = array('assign', 'regex_result');

      $actions['_ignore_import']['name']     = __('To be unaware of import');
      $actions['_ignore_import']['type']     = 'yesonly';

      $actions['manufacturer']['name']       = __('Manufacturer');
      $actions['manufacturer']['table']      = 'glpi_manufacturers';
      $actions['manufacturer']['type']       = 'dropdown';

      $actions['is_global']['name']          = __('Management type');
      $actions['is_global']['type']          = 'dropdown_management';

      return $actions;
   }

}
?>