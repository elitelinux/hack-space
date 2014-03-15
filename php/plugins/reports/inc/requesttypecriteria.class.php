<?php
/*
 * @version $Id: requesttypecriteria.class.php 237 2013-04-02 15:44:45Z yllen $
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
 * User titles selection criteria
**/
class PluginReportsRequestTypeCriteria extends PluginReportsDropdownCriteria {


   /**
    * @param $report
    * @param $name      (default 'requesttypes_id')
    * @param $label     (default '')
   **/
   function __construct($report, $name='requesttypes_id', $label='') {

      parent::__construct($report, $name, NOT_AVAILABLE,
                          ($label ? $label : _n('Request source', 'Request sources', 1)));
   }


   //Dropdown priorities is not a generic dropdown, so the function needs to be overwritten
   public function displayDropdownCriteria() {

      Dropdown::show('RequestType', array('name'  => $this->getName(),
                                          'value' => $this->getParameterValue()));
   }


   function getSubName() {

      if ($this->getParameterValue() > 0) {
         return " ".$this->getCriteriaLabel()." : ".getRequestTypeName($this->getParameterValue());
      }
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);
   }

}
?>