<?php
/*
 * @version $Id: software.class.php 349 2013-07-30 13:46:01Z yllen $
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


class PluginPdfSoftware extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {
      $this->obj = ($obj ? $obj : new Software());
   }


   static function pdfMain(PluginPdfSimplePDF $pdf, Software $software) {

      PluginPdfCommon::mainTitle($pdf, $software);

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Name').'</i></b>', $software->fields['name']),
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Publisher').'</i></b>',
                          Html::clean(Dropdown::getDropdownName('glpi_manufacturers',
                                                                $software->fields['manufacturers_id']))));

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Location').'</i></b>',
                          Html::clean(Dropdown::getDropdownName('glpi_locations',
                                                                $software->fields['locations_id']))),
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Category').'</i></b>',
                          Html::clean(Dropdown::getDropdownName('glpi_softwarecategories',
                                                                $software->fields['softwarecategories_id']))));

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Technician in charge of the hardware').'</i></b>',
                          getUserName($software->fields['users_id_tech'])),
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Associable to a ticket').'</i></b>',
                          ($software->fields['is_helpdesk_visible'] ?__('Yes'):__('No'))));

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Group in charge of the hardware').'</i></b>',
                          Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                $software->fields['groups_id_tech']))),
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('User').'</i></b>',
                          getUserName($software->fields['users_id'])));

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Group').'</i></b>',
                          Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                $software->fields['groups_id']))));

      $pdf->displayLine(
         '<b><i>'.sprintf(__('Last update on %s'),
                          Html::convDateTime($software->fields['date_mod'])));


      if ($software->fields['softwares_id'] > 0) {
         $col2 = '<b><i> '.__('from').' </i></b> '.
                  Html::clean(Dropdown::getDropdownName('glpi_softwares',
                                                       $software->fields['softwares_id']));
      } else {
         $col2 = '';
      }

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Upgrade').'</i></b>',
                          ($software->fields['is_update']?__('Yes'):__('No')), $col2));


      $pdf->setColumnsSize(100);
      PluginPdfCommon::mainLine($pdf, $software, 'comment');

      $pdf->displaySpace();
   }


    function defineAllTabs($options=array()) {

      $onglets = parent::defineAllTabs($options);
      unset($onglets['Software$1']); // Merge tab can't be exported
      unset($onglets['Item_Problem$1']); // TODO add method to print linked Problems
      return $onglets;
   }


   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      switch ($tab) {
         case 'SoftwareVersion$1' :
            PluginPdfSoftwareVersion::pdfForSoftware($pdf, $item);
            break;

         case 'SoftwareLicense$1' :
            $infocom = isset($_REQUEST['item']['Infocom$1']);
            PluginPdfSoftwareLicense::pdfForSoftware($pdf, $item, $infocom);
            break;

         case 'Computer_SoftwareVersion$1' :
            PluginPdfComputer_SoftwareVersion::pdfForItem($pdf, $item);
            break;

         default :
            return false;
      }
      return true;
   }
}