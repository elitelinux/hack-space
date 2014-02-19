<?php
/*
 * @version $Id: ticketsatisfaction.class.php 20129 2013-02-04 16:53:59Z moyo $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class TicketSatisfaction extends CommonDBTM {

   public $dohistory         = true;
   public $history_blacklist = array('date_answered');


   static function getTypeName($nb=0) {
      return __('Satisfaction');
   }


   /**
    * for use showFormHeader
   **/
   static function getIndexName() {
      return 'tickets_id';
   }


   function getLogTypeID() {
      return array('Ticket', $this->fields['tickets_id']);
   }


   static function canUpdate() {
      return (Session::haveRight('create_ticket', 1));
   }


   /**
    * Is the current user have right to update the current satisfaction
    *
    * @return boolean
   **/
   function canUpdateItem() {

      $ticket = new Ticket();
      if (!$ticket->getFromDB($this->fields['tickets_id'])) {
         return false;
      }

      // you can't change if your answer > 12h
      if (!is_null($this->fields['date_answered'])
          && ((strtotime("now") - strtotime($this->fields['date_answered'])) > (12*HOUR_TIMESTAMP))) {
         return false;
      }

      if ($ticket->isUser(CommonITILActor::REQUESTER,Session::getLoginUserID())
          || ($ticket->fields["users_id_recipient"] === Session::getLoginUserID())
          || (isset($_SESSION["glpigroups"])
              && $ticket->haveAGroup(CommonITILActor::REQUESTER, $_SESSION["glpigroups"]))) {
         return true;
      }
      return false;
   }


   /**
    * form for satisfaction
    *
    * @param $ticket Object : the ticket
   **/
   function showSatisfactionForm($ticket) {

      $tid                 = $ticket->fields['id'];
      $options             = array();
      $options['colspan']  = 1;

      // for external inquest => link
      if ($this->fields["type"] == 2) {
         $url = Entity::generateLinkSatisfaction($ticket);
         echo "<div class='center spaced'>".
              "<a href='$url'>".__('External survey')."</a><br>($url)</div>";

      // for internal inquest => form
      } else {
         $this->showFormHeader($options);

         // Set default satisfaction to 3 if not set
         if (is_null($this->fields["satisfaction"])) {
            $this->fields["satisfaction"] = 3;
         }
         echo "<tr class='tab_bg_2'>";
         echo "<td>".__('Satisfaction with the resolution of the ticket')."</td>";
         echo "<td>";
         echo "<input type='hidden' name='tickets_id' value='$tid'>";
         echo "<input type='hidden' id='satisfaction' name='satisfaction' value='".
                $this->fields["satisfaction"]."'>";

         echo  "<script type='text/javascript'>\n
            Ext.onReady(function() {
            var md = new Ext.form.StarRate({
                       hiddenName: 'satisfaction',
                       starConfig: {
                       	minValue: 0,
                       	maxValue: 5,
                        value:".$this->fields["satisfaction"]."
                       },
                       applyTo : 'satisfaction'
            });
            })
            </script>";

         echo "</td></tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td rowspan='1'>".__('Comments')."</td>";
         echo "<td rowspan='1' class='middle'>";
         echo "<textarea cols='45' rows='7' name='comment' >".$this->fields["comment"]."</textarea>";
         echo "</td></tr>\n";

         if ($this->fields["date_answered"] > 0) {
            echo "<tr class='tab_bg_2'>";
            echo "<td>".__('Response date to the satisfaction survey')."</td><td>";
            echo Html::convDateTime($this->fields["date_answered"])."</td></tr>\n";
         }

         $options['candel'] = false;
         $this->showFormButtons($options);
      }
   }


   function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      if ($input['satisfaction'] >= 0) {
         $input["date_answered"] = $_SESSION["glpi_currenttime"];
      }

      return $input;
   }


   function post_addItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         $ticket = new Ticket();
         if ($ticket->getFromDB($this->fields['tickets_id'])) {
            NotificationEvent::raiseEvent("satisfaction", $ticket);
         }
      }
   }


   /**
    * display satisfaction value
    *
    * @param $value decimal between 0 and 5
   **/
   static function displaySatisfaction($value) {

      if ($value < 0) {
         $value = 0;
      }
      if ($value > 5) {
         $value = 5;
      }
      $out = '<div style="width: 81px;"  class="x-starslider-horz">';
      $out .= '<div  class="x-starslider-end">';
      $out .= '<div style="width: 81px;" class="x-starslider-inner">';
      $out .= "<div style='width: ".intval($value*16)."px;' class='x-starslider-thumb'>";
      // display for export
      $out .= '<span class="invisible">'.$value.'</span>';
      $out .= '</div></div></div></div>';
      return $out;
   }


   /**
    * Get name of inquest type
    *
    * @param $value status ID
   **/
   static function getTypeInquestName($value) {

      switch ($value) {
         case 1 :
            return __('Internal survey');

         case 2 :
            return __('External survey');

         default :
            // Get value if not defined
            return $value;
      }
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
   **/
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'type':
            return self::getTypeInquestName($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name                  (default '')
    * @param $values                (default '')
    * @param $options   array
   **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'type' :
            $options['value'] = $values[$field];
            $typeinquest = array(1 => __('Internal survey'),
                                 2 => __('External survey'));
            return Dropdown::showFromArray($name, $typeinquest, $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

}
?>
