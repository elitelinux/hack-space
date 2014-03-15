<?php
/*
 * @version $Id: computervirtualmachine.class.php 340 2013-07-24 14:18:27Z yllen $
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


class PluginPdfComputerVirtualMachine extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {
      $this->obj = ($obj ? $obj : new ComputerVirtualMachine());
   }


   static function pdfForComputer(PluginPdfSimplePDF $pdf, Computer $item) {
      global $DB;

      $ID = $item->getField('id');

      // From ComputerVirtualMachine::showForComputer()
      $virtualmachines = getAllDatasFromTable('glpi_computervirtualmachines',
                                              "`computers_id` = '$ID'");
      $pdf->setColumnsSize(100);
      if (count($virtualmachines)) {
         $pdf->displayTitle("<b>".__('List of virtual machines')."</b>");
         $pdf->setColumnsSize(20,8,8,8,25,8,8,15);
         $pdf->setColumnsAlign('left', 'center', 'center', 'center', 'left', 'right', 'right', 'left');
         $typ = explode(' ', __('Virtualization system'));
         $sys = explode(' ', __('Virtualization model'));
         $sta = explode(' ', __('State of the virtual machine'));
         $pdf->displayTitle(__('Name'), $typ[0], $sys[0], $sta[0], __('UUID'), __('CPU'), __('Mio'),
                            __('Machine'));

         foreach ($virtualmachines as $virtualmachine) {
            $name = '';
            if ($link_computer = ComputerVirtualMachine::findVirtualMachine($virtualmachine)) {
               $computer = new Computer();
               if ($computer->getFromDB($link_computer)) {
                  $name = $computer->getName();
               }
            }
            $pdf->displayLine(
               $virtualmachine['name'],
               Html::clean(Dropdown::getDropdownName('glpi_virtualmachinetypes',
                                                    $virtualmachine['virtualmachinetypes_id'])),
               Html::clean(Dropdown::getDropdownName('glpi_virtualmachinesystems',
                                                     $virtualmachine['virtualmachinesystems_id'])),
               Html::clean(Dropdown::getDropdownName('glpi_virtualmachinestates',
                                                    $virtualmachine['virtualmachinestates_id'])),
               $virtualmachine['uuid'],
               $virtualmachine['vcpu'],

               Html::clean(Html::formatNumber($virtualmachine['ram'],false,0)),
               $name
            );
         }
      } else {
         $pdf->displayTitle("<b>".__('No virtual machine associated with the computer')."</b>");
      }

      // From ComputerVirtualMachine::showForVirtualMachine()
      if ($item->fields['uuid']) {
         $where = "`uuid`".ComputerVirtualMachine::getUUIDRestrictRequest($item->fields['uuid']);
         $hosts = getAllDatasFromTable('glpi_computervirtualmachines', $where);

         if (count($hosts)) {
            $pdf->setColumnsSize(100);
            $pdf->displayTitle("<b>".__('List of host machines')."</b>");

            $pdf->setColumnsSize(26,37,37);
            $pdf->displayTitle(__('Name'), __('Operating system'), __('Entity'));

            $computer = new Computer();
            foreach ($hosts as $host) {
               if ($computer->getFromDB($host['computers_id'])) {
                  $pdf->displayLine(
                     $computer->getName(),
                     Html::clean(Dropdown::getDropdownName('glpi_operatingsystems',
                                                           $computer->getField('operatingsystems_id'))),
                     Html::clean(Dropdown::getDropdownName('glpi_entities',
                                                           $computer->getEntityID()))
                  );
               }
            }
         }
      }
      $pdf->displaySpace();
   }
}