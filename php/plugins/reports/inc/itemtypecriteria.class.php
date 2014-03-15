<?php
/*
 * @version $Id: itemtypecriteria.class.php 237 2013-04-02 15:44:45Z yllen $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


/**
 * Ticket status selection criteria
 */
class PluginReportsItemTypeCriteria extends PluginReportsDropdownCriteria {

   private $types = array();


   /**
    * @param $report
    * @param $name            (default 'itemtype')
    * @param $label           (default '')
    * @param $types     array
    * @param $ignored   array
   **/
   function __construct($report, $name='itemtype', $label='', $types=array(), $ignored=array()) {
      global $CFG_GLPI;

      parent::__construct($report, $name, NOT_AVAILABLE, ($label ? $label : __('Item type')));

      if (is_array($types) && count($types)) {
         // $types is an hashtable of itemtype => display name
         $this->types = $types;

      } else if (is_string($types) && isset($CFG_GLPI[$types])) {
         // $types is the name of an configured type hashtable (infocom_types, doc_types, ...)
         foreach($CFG_GLPI[$types] as $itemtype) {
            if (($item = getItemForItemtype($itemtype)) && !in_array($itemtype, $ignored)) {
               $this->types[$itemtype] = $item->getTypeName();
            }
         }
         $this->types[''] = __('All');

      } else {
         // No types, use helpdesk_types
         $this->types     = Ticket::getAllTypesForHelpdesk();
         $this->types[''] = __('All');
      }
   }


   function getSubName() {

      $itemtype = $this->getParameterValue();
      if ($itemtype && ($item = getItemForItemtype($itemtype))) {
         $name = $item->getTypeName();
      } else {
         // All
         return '';
      }
      return " " . $this->getCriteriaLabel() . " : " . $name;
   }


   public function displayDropdownCriteria() {

      Dropdown::showFromArray($this->getName(), $this->types,
                              array('value'=> $this->getParameterValue()));
   }

}
?>