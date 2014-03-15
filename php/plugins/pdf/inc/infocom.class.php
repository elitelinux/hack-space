<?php
/*
 * @version $Id: infocom.class.php 346 2013-07-29 09:42:39Z yllen $
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


class PluginPdfInfocom extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {
      $this->obj = ($obj ? $obj : new Infocom());
   }


   static function pdfForItem(PluginPdfSimplePDF $pdf, CommonDBTM $item){
      global $CFG_GLPI;

      $ID = $item->getField('id');

      if (!Session::haveRight("infocom","r")) {
         return false;
      }

      $ic = new Infocom();

      $pdf->setColumnsSize(100);
      if ($ic->getFromDBforDevice(get_class($item),$ID)) {
         $pdf->displayTitle("<b>".__('Financial and administrative information')."</b>");

         $pdf->setColumnsSize(50,50);

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Supplier')."</i></b>",
                             Html::clean(Dropdown::getDropdownName("glpi_suppliers",
                                                                   $ic->fields["suppliers_id"]))),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Budget')."</i></b>",
                             Html::clean(Dropdown::getDropdownName("glpi_budgets",
                                                                   $ic->fields["budgets_id"]))));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Order number')."</i></b>",
                             $ic->fields["order_number"]),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Order date')."</i></b>",
                             Html::convDate($ic->fields["order_date"])));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Immobilization number')."</i></b>",
                             $ic->fields["immo_number"]),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Date of purchase')."</i></b>",
                             Html::convDate($ic->fields["buy_date"])));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Invoice number')."</i></b>",
                             $ic->fields["bill"]),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Delivery date')."</i></b>",
                             Html::convDate($ic->fields["delivery_date"])));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Delivery form')."</i></b>",
                             $ic->fields["delivery_number"]),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Startup date')."</i></b>",
                             Html::convDate($ic->fields["use_date"])));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Value')."</i></b>",
                             Html::clean(Html::formatNumber($ic->fields["value"]))),
            "<b><i>".sprintf(__('%1$s: %2$s'), _('Date of last physical inventory')."</i></b>",
                             Html::convDate($ic->fields["inventory_date"])));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Warranty extension value')."</i></b>",
                             Html::clean(Html::formatNumber($ic->fields["warranty_value"]))),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Amortization duration')."</i></b>",
                             sprintf(__('%1$s (%2$s)'),
                                     sprintf(_n('%d year', '%d years', $ic->fields["sink_time"]),
                                             $ic->fields["sink_time"]),
                                     Infocom::getAmortTypeName($ic->fields["sink_type"]))));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Account net value')."</i></b>",
                             Infocom::Amort($ic->fields["sink_type"], $ic->fields["value"],
                                            $ic->fields["sink_time"], $ic->fields["sink_coeff"],
                                            $ic->fields["warranty_date"], $ic->fields["use_date"],
                                            $CFG_GLPI['date_tax'],"n")),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Amortization coefficient')."</i></b>",
                             $ic->fields["sink_coeff"]));

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('TCO (value + tracking cost)')."</i></b>",
                             Html::clean(Infocom::showTco($item->getField('ticket_tco'),
                                                          $ic->fields["value"]))),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Monthly TCO')."</i></b>",
                             Html::clean(Infocom::showTco($item->getField('ticket_tco'),
                                                          $ic->fields["value"],
                                                          $ic->fields["warranty_date"]))));

         PluginPdfCommon::mainLine($pdf, $ic, 'comment');

         $pdf->setColumnsSize(100);
         $pdf->displayTitle("<b>".__('Warranty information')."</b>");

         $pdf->setColumnsSize(50,50);

         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Start date of warranty')."</i></b>",
                             Html::convDate($ic->fields["warranty_date"])),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Warranty duration')."</i></b>",
                             sprintf(__('%1$s - %2$s'),
                                     sprintf(_n('%d month', '%d months',
                                                $ic->fields["warranty_duration"]),
                                             $ic->fields["warranty_duration"]),
                                     sprintf(__('Valid to %s'),
                                             Infocom::getWarrantyExpir($ic->fields["buy_date"],
                                                                       $ic->fields["warranty_duration"])))));

         $col1 = "<b><i>".__('Alarms on financial and administrative information')."</i></b>";
         if ($ic->fields["alert"] == 0) {
            $col1 = sprintf(__('%1$s: %2$s'), $col1, __('No'));
         } else if ($ic->fields["alert"] == 4) {
            $col1 = sprintf(__('%1$s: %2$s'), $col1, __('Warranty expiration date'));
         }
         $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Warranty information')."</i></b>",
                             $ic->fields["warranty_info"]),
            $col1);
      } else {
         $pdf->displayTitle("<b>".__('No financial information', 'pdf')."</b>");
      }

      $pdf->displaySpace();
   }
}