<?php
/*
 * @version $Id: computer_item.class.php 346 2013-07-29 09:42:39Z yllen $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2013 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/


class PluginPdfComputer_Item extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {
      $this->obj = ($obj ? $obj : new Computer_Item());
   }


   static function pdfForComputer(PluginPdfSimplePDF $pdf, Computer $comp) {
      global $DB;

      $ID = $comp->getField('id');

      $items = array('Printer'    => _n('Printer', 'Printers', 2),
                     'Monitor'    => _n('Monitor', 'Monitors', 2),
                     'Peripheral' => _n('Device', 'Devices', 2),
                     'Phone'      => _n('Phone', 'Phones', 2));

      $info = new InfoCom();

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.__('Direct connections').'</b>');

      foreach ($items as $type => $title) {
         if (!($item = getItemForItemtype($type))) {
            continue;
         }
         if (!$item->canView()) {
            continue;
         }
         $query = "SELECT *
                   FROM `glpi_computers_items`
                   WHERE `computers_id` = '".$ID."'
                         AND `itemtype` = '".$type."'";

         if ($result = $DB->query($query)) {
            $resultnum = $DB->numrows($result);
            if ($resultnum > 0) {
               for ($j=0 ; $j < $resultnum ; $j++) {
                  $tID    = $DB->result($result, $j, "items_id");
                  $connID = $DB->result($result, $j, "id");
                  $item->getFromDB($tID);
                  $info->getFromDBforDevice($type,$tID) || $info->getEmpty();

                  $line1 = $item->getName();
                  if ($item->getField("serial") != null) {
                     $line1 = sprintf(__('%1$s - %2$s'), $line1,
                                       sprintf(__('%1$s: %2$s'), __('Serial number'),
                                               $item->getField("serial")));
                  }

                  $line1 = sprintf(__('%1$s - %2$s'), $line1,
                                   Html::clean(Dropdown::getDropdownName("glpi_states",
                                                                         $item->getField('states_id'))));

                  $line2 = "";
                  if ($item->getField("otherserial") != null) {
                     $line2 = sprintf(__('%1$s: %2$s'), __('Inventory number'),
                                      $item->getField("otherserial"));
                  }
                  if ($info->fields["immo_number"]) {
                     $line2 = sprintf(__('%1$s - %2$s'), $line2,
                                      sprintf(__('%1$s: %2$s'), __('Immobilization number'),
                                              $info->fields["immo_number"]));
                  }
                  if ($line2) {
                     $pdf->displayText('<b>'.sprintf(__('%1$s: %2$s'), $item->getTypeName().'</b>',
                                                     $line1 . "\n" . $line2), 2);
                  } else {
                     $pdf->displayText('<b>'.sprintf(__('%1$s: %2$s'), $item->getTypeName().'</b>',
                                                     $line1), 1);
                  }
               }// each device   of current type

            } else { // No row
               switch ($type) {
                  case 'Printer' :
                     $pdf->displayLine(sprintf(__('No printer', 'pdf')));
                     break;

                  case 'Monitor' :
                     $pdf->displayLine(sprintf(__('No monitor', 'pdf')));
                     break;

                  case 'Peripheral' :
                     $pdf->displayLine(sprintf(__('No peripheral', 'pdf')));
                     break;

                  case 'Phone' :
                     $pdf->displayLine(sprintf(__('No phone', 'pdf')));
                     break;
               }
            } // No row
         } // Result
      } // each type
      $pdf->displaySpace();
   }


   static function pdfForItem(PluginPdfSimplePDF $pdf, CommonDBTM $item){
      global $DB;

      $ID   = $item->getField('id');
      $type = $item->getType();

      $info = new InfoCom();
      $comp = new Computer();

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.__('Direct connections').'</b>');

      $query = "SELECT *
                FROM `glpi_computers_items`
                WHERE `items_id` = '".$ID."'
                      AND `itemtype` = '".$type."'";

      if ($result = $DB->query($query)) {
         $resultnum = $DB->numrows($result);
         if ($resultnum > 0) {
            for ($j=0 ; $j < $resultnum ; $j++) {
               $tID    = $DB->result($result, $j, "computers_id");
               $connID = $DB->result($result, $j, "id");
               $comp->getFromDB($tID);
               $info->getFromDBforDevice('Computer',$tID) || $info->getEmpty();

               $line1 = ($comp->fields['name']?$comp->fields['name']:"(".$comp->fields['id'].")");
               if ($comp->fields['serial']) {
                  $line1 = sprintf(__('%1$s - %2$s'), $line1,
                                   sprintf(__('%1$s: %2$s'), __('Serial number'),
                                           $comp->fields['serial']));
               }
               $line1 = sprintf(__('%1$s - %2$s'), $line1,
                                Html::clean(Dropdown::getDropdownName("glpi_states",
                                                                      $comp->fields['states_id'])));

               $line2 = "";
               if ($comp->fields['otherserial']) {
                  $line2 = sprintf(__('%1$s: %2$s'), __('Inventory number'),
                                   $item->getField("otherserial"));
               }
               if ($info->fields['immo_number']) {
                  $line2 = sprintf(__('%1$s - %2$s'), $line2,
                                   sprintf(__('%1$s: %2$s'), __('Immobilization number'),
                                           $info->fields["immo_number"]));
               }
               if ($line2) {
                  $pdf->displayText('<b>'.sprintf(__('%1$s: %2$s'), __('Computer').'/b>',
                                                  $line1 . "\n" . $line2), 2);
               } else {
                  $pdf->displayText('<b>'.sprintf(__('%1$s: %2$s'), __('Computer').'/b>',
                                                  $line1), 1);
               }
            }// each device   of current type

         } else { // No row
            $pdf->displayLine(__('Not connected.'));
         } // No row
      } // Result
      $pdf->displaySpace();
   }
}