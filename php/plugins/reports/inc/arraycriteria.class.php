<?php
/*
 * @version $Id: arraycriteria.class.php 236 2013-03-14 16:24:19Z yllen $
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
class PluginReportsArrayCriteria extends PluginReportsDropdownCriteria {
   private $choice = array();

   function __construct($report, $name, $label='', $options=array()) {

      parent::__construct($report, $name, NOT_AVAILABLE,
                          ($label ? $label : _n('Criterion', 'Criteria', 2)));
      $this->choice = $options;
   }


   function getSubName() {

      $val = $this->getParameterValue();
      if (empty($val) || $val=='all') {
         return '';
      }
      return " " . sprintf(__('%1$s: %2$s'), $this->getCriteriaLabel(), $this->choice[$val]);
   }


   public function displayDropdownCriteria() {

      Dropdown::showFromArray($this->getName(), $this->choice,
                              array('value' => $this->getParameterValue()));
   }


   /**
    * Get SQL code associated with the criteria
    */
   public function getSqlCriteriasRestriction($link = 'AND') {

      $val = $this->getParameterValue();
      if (empty($val) || ($val == 'all')) {
         return '';
      }
      return $link . " " . $this->getSqlField() . "='$val' ";
   }
}
?>