<?php
/*
 * @version $Id: ticket.class.php 358 2014-01-15 15:59:21Z yllen $
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


class PluginPdfTicket extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {
      $this->obj = ($obj ? $obj : new Ticket());
   }


   static function pdfMain(PluginPdfSimplePDF $pdf, Ticket $job) {
      global $CFG_GLPI, $DB;

      $ID = $job->getField('id');
      if (!$job->can($ID, 'r')) {
         return false;
      }

      $pdf->setColumnsSize(100);

      $pdf->displayTitle('<b>'.
               (empty($job->fields["name"])?__('Without title'):$name=$job->fields["name"]).'</b>');

      if (count($_SESSION['glpiactiveentities'])>1) {
         $entity = " (".Dropdown::getDropdownName("glpi_entities",$job->fields["entities_id"]).")";
      } else {
         $entity = '';
      }

      $pdf->setColumnsSize(50,50);
      $recipient_name='';
      if ($job->fields["users_id_recipient"]) {
         $recipient      = new User();
         $recipient->getFromDB($job->fields["users_id_recipient"]);
         $recipient_name = $recipient->getName();
      }

      $sla = $due = $commentsla = '';
      if ($job->fields['due_date']) {
         $due = "<b><i>".sprintf(__('%1$s: %2$s'), __('Due date')."</b></i>",
                                 Html::convDateTime($job->fields['due_date']));
      }
      $pdf->displayLine(
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Opening date')."</i></b>",
                          Html::convDateTime($job->fields["date"])), $due);

      $pdf->setColumnsSize(100);
      if ($job->fields["slas_id"] > 0) {
         $sla = "<b><i>".sprintf(__('%1$s: %2$s'), __('SLA')."</b></i>",
                                 Html::clean(Dropdown::getDropdownName("glpi_slas",
                                                                       $job->fields["slas_id"])));

         $slalevel = new SlaLevel();
         if ($slalevel->getFromDB($job->fields['slalevels_id'])) {
            $commentsla = "<b><i>".sprintf(__('%1$s: %2$s'), __('Escalation level')."</b></i>",
                                           $slalevel->getName());
         }

         $nextaction = new SlaLevel_Ticket();
         if ($nextaction->getFromDBForTicket($job->fields["id"])) {
            $commentsla .= " <b><i>".sprintf(__('Next escalation: %s')."</b></i>",
                                             Html::convDateTime($nextaction->fields['date']));
            if ($slalevel->getFromDB($nextaction->fields['slalevels_id'])) {
               $commentsla .= " <b><i>".sprintf(__('%1$s: %2$s'), __('Escalation level'),
                                                $slalevel->getName());
            }
         }
         $pdf->displayText($sla, $commentsla, 1);
      }

      $pdf->setColumnsSize(50,50);
      $lastupdate = Html::convDateTime($job->fields["date_mod"]);
      if ($job->fields['users_id_lastupdater'] > 0) {
         $lastupdate = sprintf(__('%1$s by %2$s'), $lastupdate,
                               getUserName($job->fields["users_id_lastupdater"]));
      }

      $pdf->displayLine(
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('By')."</i></b>", $recipient_name),
         '<b><i>'.sprintf(__('%1$s: %2$s'), __('Last update').'</i></b>', $lastupdate));

      $pdf->displayLine(
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Type')."</i></b>",
                          Html::clean(Ticket::getTicketTypeName($job->fields["type"]))),
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Category')."</i></b>",
                          Html::clean(Dropdown::getDropdownName("glpi_itilcategories",
                                                                $job->fields["itilcategories_id"]))));

      $status = '';
      if (in_array($job->fields["status"], $job->getSolvedStatusArray())
          || in_array($job->fields["status"], $job->getClosedStatusArray())) {
         $status = sprintf(__('%1$s %2$s'), '-', Html::convDateTime($job->fields["solvedate"]));
      }
      if (in_array($job->fields["status"], $job->getClosedStatusArray())) {
         $status = sprintf(__('%1$s %2$s'), '-', Html::convDateTime($job->fields["closedate"]));
      }

      if ($job->fields["status"] == Ticket::WAITING) {
         $status = sprintf(__('%1$s %2$s'), '-',
                           Html::convDateTime($job->fields['begin_waiting_date']));
      }

      $pdf->displayLine(
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Status')."</i></b>",
                          Html::clean($job->getStatus($job->fields["status"])). $status),
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Request source')."</i></b>",
                          Html::clean(Dropdown::getDropdownName('glpi_requesttypes',
                                                                $job->fields['requesttypes_id']))));

      $pdf->displayLine(
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Urgency')."</i></b>",
                          Html::clean($job->getUrgencyName($job->fields["urgency"]))),
         "<b><i>".sprintf(__('%1$s: %2$s'), __('Approval')."</i></b>",
                          TicketValidation::getStatus($job->fields['global_validation'])));

      $pdf->displayLine(
            "<b><i>". sprintf(__('%1$s: %2$s'), __('Impact')."</i></b>",
                  Html::clean($job->getImpactName($job->fields["impact"]))));

      $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Priority')."</i></b>",
                             Html::clean($job->getPriorityName($job->fields["priority"]))),
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Location')."</i></b>",
                             Html::clean(Dropdown::getDropdownName("glpi_locations",
                                                                   $job->fields["locations_id"]))));

      // Item
      $name        = "<b><i>".sprintf(__('%1$s: %2$s'), __('Associated element')."</i></b>", '');
      $commentitem = '';
      $pdf->setColumnsSize(100);
      if ($job->fields['itemtype']
          && ($item = getItemForItemtype($job->fields['itemtype']))) {
         if ($item->getFromDB($job->fields["items_id"])) {
            $name = "<b><i>".sprintf(__('%1$s: %2$s'), __('Associated element')."</i></b>",
                                     $item->getNameID());
            if (isset($item->fields["serial"])) {
               $commentitem =
                  ", <b><i>".sprintf(__('%1$s: %2$s'),__('Serial number')."</i></b>",
                                     Html::clean($item->fields["serial"]));
               Html::clean($item->fields["serial"]);
            }
            if (isset($item->fields["otherserial"])) {
                $commentitem .=
                  ", <b><i>".sprintf(__('%1$s: %2$s'),__('Inventory number')."</i></b>",
                                     Html::clean($item->fields["otherserial"]));
            }
            if (isset($item->fields["locations_id"])) {
                $commentitem .=
                  ", <b><i>".sprintf(__('%1$s: %2$s'),__('Location')."</i></b>",
                                     Html::clean(Dropdown::getDropdownName("glpi_locations",
                                                                           $item->fields["locations_id"])));
            }
         }
      }
      $pdf->displayText($name, $commentitem, 1);

      $pdf->setColumnsSize(50,50);

      // Requester
      $users     = array();
      $listusers = '';
      $requester = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Requester'), $listusers);
      foreach ($job->getUsers(CommonITILActor::REQUESTER) as $d) {
         if ($d['users_id']) {
            $tmp = Html::clean(getUserName($d['users_id']));
            if ($d['alternative_email']) {
               $tmp .= ' ('.$d['alternative_email'].')';
            }
         } else {
            $tmp = $d['alternative_email'];
         }
         $users[] = $tmp;
      }
      if (count($users)) {
         $listusers = implode(', ', $users);
      }
      $pdf->displayText($requester, $listusers, 1);

      $groups         = array();
      $listgroups     = '';
      $requestergroup = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Requester group'),
                                         $listgroups);
      foreach ($job->getGroups(CommonITILActor::REQUESTER) as $d) {
         $groups[] = Html::clean(Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
      }
      if (count($groups)) {
      $listgroups = implode(', ', $groups);
      }
      $pdf->displayText($requestergroup, $listgroups, 1);

      // Observer
      $users     = array();
      $listusers = '';
      $watcher   = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Watcher'), $listusers);
      foreach ($job->getUsers(CommonITILActor::OBSERVER) as $d) {
         if ($d['users_id']) {
            $tmp = Html::clean(getUserName($d['users_id']));
            if ($d['alternative_email']) {
               $tmp .= ' ('.$d['alternative_email'].')';
            }
         } else {
            $tmp = $d['alternative_email'];
         }
         $users[] = $tmp;
      }
      if (count($users)) {
         $listusers = implode(', ', $users);
      }
      $pdf->displayText($watcher, $listusers, 1);

      $groups       = array();
      $listgroups   = '';
      $watchergroup = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Watcher group'),
                                         $listgroups);
      foreach ($job->getGroups(CommonITILActor::OBSERVER) as $d) {
         $groups[] = Html::clean(Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
      }
      if (count($groups)) {
         $listgroups = implode(', ', $groups);
      }
      $pdf->displayText($watchergroup, $listgroups, 1);

      // Assign to
      $users = array();
      $listusers = '';
      $assign    = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('User assigned'), $listusers);
      foreach ($job->getUsers(CommonITILActor::ASSIGN) as $d) {
         if ($d['users_id']) {
            $tmp = Html::clean(getUserName($d['users_id']));
            if ($d['alternative_email']) {
               $tmp .= ' ('.$d['alternative_email'].')';
            }
         } else {
            $tmp = $d['alternative_email'];
         }
         $users[] = $tmp;
      }
      if (count($users)) {
         $listusers = implode(', ', $users);
      }
      $pdf->displayText($assign, $listusers, 1);

      $groups     = array();
      $listgroups  = '';
      $assigngroup = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Group assigned'),
                                         $listgroups);
      foreach ($job->getGroups(CommonITILActor::ASSIGN) as $d) {
         $groups[] = Html::clean(Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
      }
      if (count($groups)) {
         $listgroups = implode(', ', $groups);
      }
      $pdf->displayText($assigngroup, $listgroups, 1);

     // Supplier
      $suppliers      = array();
      $listsuppliers  = '';
      $assignsupplier = '<b><i>'.sprintf(__('%1$s: %2$s')."</i></b>", __('Supplier assigned'),
                                         $listsuppliers);
      foreach ($job->getSuppliers(CommonITILActor::ASSIGN) as $d) {
         $suppliers[] = Html::clean(Dropdown::getDropdownName("glpi_suppliers", $d['suppliers_id']));
      }
      if (count($suppliers)) {
         $listsuppliers = implode(', ', $suppliers);
      }
      $pdf->displayText($assignsupplier, $listsuppliers, 1);

      $pdf->setColumnsSize(100);
      $pdf->displayLine(
            "<b><i>".sprintf(__('%1$s: %2$s'), __('Title')."</i></b>", $job->fields["name"]));

      $pdf->displayText("<b><i>".sprintf(__('%1$s: %2$s'), __('Description')."</i></b>",
                        $job->fields['content']));

      // Linked tickets
      $tickets   = Ticket_Ticket::getLinkedTicketsTo($ID);
      if (is_array($tickets) && count($tickets)) {
         $ticket = new Ticket();
         foreach ($tickets as $linkID => $data) {
            $tmp = sprintf(__('%1$s %2$s'), Ticket_Ticket::getLinkName($data['link']),
                           sprintf(__('%1$s %2$s'), __('ID'), $data['tickets_id']));
            if ($ticket->getFromDB($data['tickets_id'])) {
               $tmp = sprintf(__('%1$s: %2$s'), $tmp, $ticket->getName());
            }
            $jobs[] = $tmp;
            $jobs = implode("\n", $jobs);
         }
         $linked = "<b><i>".sprintf(__('%1$s: %2$s')."</i></b>", __('Linked tickets'), '');
         $pdf->displayText($linked, $jobs, 1);
      }

      $pdf->displaySpace();
   }



   static function pdfForItem(PluginPdfSimplePDF $pdf, CommonDBTM $item, $tree=false) {
      global $DB,$CFG_GLPI;

      $ID   = $item->getField('id');
      $type = $item->getType();

      if (!Session::haveRight("show_all_ticket","1")) {
         return;
      }

      switch ($item->getType()) {
         case 'User' :
            $restrict   = "(`glpi_tickets_users`.`users_id` = '".$item->getID()."'
                            AND `glpi_tickets_users`.`type` = ".CommonITILActor::REQUESTER.")";
            $order      = '`glpi_tickets`.`date_mod` DESC';
            break;

         case 'SLA' :
            $restrict  = "(`slas_id` = '".$item->getID()."')";
            $order     = '`glpi_tickets`.`due_date` DESC';
            break;

         case 'Supplier' :
            $restrict  = "(`glpi_suppliers_tickets`.`suppliers_id` = '".$item->getID()."'
                           AND `glpi_suppliers_tickets`.`type` = ".CommonITILActor::ASSIGN.")";
            $order     = '`glpi_tickets`.`date_mod` DESC';
            break;

         case 'Group' :
            if ($tree) {
               $restrict = "IN (".implode(',', getSonsOf('glpi_groups', $item->getID())).")";
            } else {
               $restrict = "='".$item->getID()."'";
            }
            $restrict   = "(`glpi_groups_tickets`.`groups_id` $restrict
                            AND `glpi_groups_tickets`.`type` = ".CommonITILActor::REQUESTER.")";
            $order      = '`glpi_tickets`.`date_mod` DESC';
            break;

         default :
            $restrict   = "(`items_id` = '".$item->getID()."'  AND `itemtype` = '$type')";
            $order      = '`glpi_tickets`.`date_mod` DESC';
      }

      $query = "SELECT ".Ticket::getCommonSelect()."
                FROM glpi_tickets ".
                Ticket::getCommonLeftJoin()."
                WHERE $restrict ".
                  getEntitiesRestrictRequest("AND","glpi_tickets")."
                ORDER BY $order
                LIMIT ".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $pdf->setColumnsSize(100);
      if (!$number) {
         $pdf->displayTitle('<b>'.__('Last tickets').'</b>');
      } else {
         $pdf->displayTitle("<b>".sprintf(__('Last %d ticket')."</b>", $number));

         $job = new Ticket();
         while ($data = $DB->fetch_assoc($result)) {
            if (!$job->getFromDB($data["id"])) {
               continue;
            }
            $pdf->setColumnsAlign('center');
            $col = '<b><i>ID '.$job->fields["id"].'</i></b>, '.
                    sprintf(__('%1$s: %2$s'), __('Status'),
                            Ticket::getStatus($job->fields["status"]));

            if (count($_SESSION["glpiactiveentities"]) > 1) {
               if ($job->fields['entities_id'] == 0) {
                  $col = sprintf(__('%1$s (%2$s)'), $col, __('Root entity'));
               } else {
                  $col = sprintf(__('%1$s (%2$s)'), $col,
                                 Dropdown::getDropdownName("glpi_entities",
                                                           $job->fields['entities_id']));
               }
            }
            $pdf->displayLine($col);

            $pdf->setColumnsAlign('left');

            $col = '<b><i>'.sprintf(__('Opened on %s').'</i></b>',
                                    Html::convDateTime($job->fields['date']));
            if ($job->fields['begin_waiting_date']) {
               $col = sprintf(__('%1$s, %2$s'), $col,
                              '<b><i>'.sprintf(__('Put on hold on %s').'</i></b>',
                                               Html::convDateTime($job->fields['begin_waiting_date'])));
            }
            if (in_array($job->fields["status"], $job->getSolvedStatusArray())
                || in_array($job->fields["status"], $job->getClosedStatusArray())) {
               $col = sprintf(__('%1$s, %2$s'), $col,
                              '<b><i>'.sprintf(__('Solved on %s').'</i></b>',
                                               Html::convDateTime($job->fields['solvedate'])));
            }
            if (in_array($job->fields["status"], $job->getClosedStatusArray())) {
               $col = sprintf(__('%1$s, %2$s'), $col,
                              '<b><i>'.sprintf(__('Closed on %s').'</i></b>',
                                               Html::convDateTime($job->fields['closedate'])));
            }
            if ($job->fields['due_date']) {
               $col = sprintf(__('%1$s, %2$s'), $col,
                              '<b><i>'.sprintf(__('%1$s: %2$s').'</i></b>', __('Due date'),
                                               Html::convDateTime($job->fields['due_date'])));
            }
            $pdf->displayLine($col);

            $col = '<b><i>'.sprintf(__('%1$s: %2$s'), __('Priority').'</i></b>',
                                    Ticket::getPriorityName($job->fields["priority"]));
            if ($job->fields["itilcategories_id"]) {
               $col = sprintf(__('%1$s - %2$s'), $col,
                              '<b><i>'.sprintf(__('%1$s: %2$s').'</i></b>', __('Category'),
                                               Dropdown::getDropdownName('glpi_itilcategories',
                                                                         $job->fields["itilcategories_id"])));
            }
            $pdf->displayLine($col);

            $col   = '';
            $users = $job->getUsers(CommonITILActor::REQUESTER);
            if (count($users)) {
               foreach ($users as $d) {
                  if (empty($col)) {
                     $col = getUserName($d['users_id']);
                  } else {
                     $col = sprintf(__('%1$s, %2$s'), $col, getUserName($d['users_id']));
                  }
               }
            }
            $grps = $job->getGroups(CommonITILActor::REQUESTER);
            if (count($grps)) {
               if (empty($col)) {
                  $col = sprintf(__('%1$s %2$s'), $col, _n('Group', 'Groups', 2).' </i></b>');
               } else {
                  $col = sprintf(__('%1$s - %2$s'), $col, _n('Group', 'Groups', 2).' </i></b>');
               }
               $first = true;
               foreach ($grps as $d) {
                  if ($first) {
                     $col = sprintf(__('%1$s  %2$s'), $col,
                                    Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
                  } else {
                     $col = sprintf(__('%1$s, %2$s'), $col,
                           Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
                  }
                  $first = false;
               }
            }
            if ($col) {
               $texte = '<b><i>'.sprintf(__('%1$s: %2$s'), __('Requester').'</i></b>', '');
               $pdf->displayText($texte, $col, 1);
            }

            $col   = '';
            $users = $job->getUsers(CommonITILActor::ASSIGN);
            if (count($users)) {
               foreach ($users as $d) {
                  if (empty($col)) {
                     $col = getUserName($d['users_id']);
                  } else {
                     $col = sprintf(__('%1$s, %2$s'), $col, getUserName($d['users_id']));
                  }
               }
            }
            $grps = $job->getGroups(CommonITILActor::ASSIGN);
            if (count($grps)) {
               if (empty($col)) {
                  $col = sprintf(__('%1$s %2$s'), $col, _n('Group', 'Groups', 2).' </i></b>');
               } else {
                  $col = sprintf(__('%1$s - %2$s'), $col, _n('Group', 'Groups', 2).' </i></b>');
               }
               $first = true;
               foreach ($grps as $d) {
                  if ($first) {
                     $col = sprintf(__('%1$s  %2$s'), $col,
                                    Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
                  } else {
                     $col = sprintf(__('%1$s, %2$s'), $col,
                                    Dropdown::getDropdownName("glpi_groups", $d['groups_id']));
                  }
                  $first = false;
               }
            }
            if ($col) {
               $texte = '<b><i>'.sprintf(__('%1$s: %2$s').'</i></b>', ('Assigned to'), '');
               $pdf->displayText($texte, $col, 1);
            }

            $texte = '<b><i>'.sprintf(__('%1$s: %2$s').'</i></b>', ('Title'), '');
            $pdf->displayText($texte, $job->fields["name"], 1);
         }
      }
      $pdf->displaySpace();
   }


   static function pdfSolution(PluginPdfSimplePDF $pdf, Ticket $job) {
      global $CFG_GLPI, $DB;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>".__('Solution')."</b>");

      if ($job->fields['solutiontypes_id'] || !empty($job->fields['solution'])) {
         if ($job->fields['solutiontypes_id']) {
            $title = Html::clean(Dropdown::getDropdownName('glpi_solutiontypes',
                                           $job->getField('solutiontypes_id')));
         } else {
            $title = __('Solution');
         }
         $sol = Html::clean(Toolbox::unclean_cross_side_scripting_deep(
                           html_entity_decode($job->getField('solution'),
                                              ENT_QUOTES, "UTF-8")));
         $pdf->displayText("<b><i>$title</i></b> : ", $sol);
      } else {
         $pdf->displayLine(__('None'));
      }

      $pdf->displaySpace();
   }


   static function pdfStat(PluginPdfSimplePDF $pdf, Ticket $job) {

      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>"._n('Date', 'Dates', 2)."</b>");

      $pdf->setColumnsSize(50, 50);
      $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Opening date'),
                                Html::convDateTime($job->fields['date'])));
      $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Due date'),
                                Html::convDateTime($job->fields['due_date'])));
      if (in_array($job->fields["status"], $job->getSolvedStatusArray())
          || in_array($job->fields["status"], $job->getClosedStatusArray())) {
         $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Solution date'),
                                   Html::convDateTime($job->fields['solvedate'])));
      }
      if (in_array($job->fields["status"], $job->getClosedStatusArray())) {
         $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Closing date'),
                                   Html::convDateTime($job->fields['closedate'])));
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>"._n('Time', 'Times', 2)."</b>");

      $pdf->setColumnsSize(50, 50);
      if ($job->fields['takeintoaccount_delay_stat'] > 0) {
         $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Take into account'),
                                   Html::clean(Html::timestampToString($job->fields['takeintoaccount_delay_stat'],0))));
      }

      if (in_array($job->fields["status"], $job->getSolvedStatusArray())
          || in_array($job->fields["status"], $job->getClosedStatusArray())) {
               if ($job->fields['solve_delay_stat'] > 0) {
            $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Solution'),
                                      Html::clean(Html::timestampToString($job->fields['solve_delay_stat'],0))));
         }
      }
      if (in_array($job->fields["status"], $job->getClosedStatusArray())) {
         if ($job->fields['close_delay_stat'] > 0) {
            $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Closing'),
                                      Html::clean(Html::timestampToString($job->fields['close_delay_stat'],0))));
         }
      }
      if ($job->fields['waiting_duration'] > 0) {
         $pdf->displayLine(sprintf(__('%1$s: %2$s'), __('Pending'),
                                   Html::clean(Html::timestampToString($job->fields['waiting_duration'],0))));
      }

      $pdf->displaySpace();
   }


   function defineAllTabs($options=array()) {

      $onglets = parent::defineAllTabs($options);

      if (Session::haveRight("show_full_ticket","1")) {
         $onglets['_private_'] = __('Private');
      }
 //     unset($onglets['Problem$1']); // TODO add method to print linked Problems
 //     unset($onglets['Change$1']);  // TODO add method to print linked Changes

      return $onglets;
   }


   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      $private = isset($_REQUEST['item']['_private_']);

      switch ($tab) {
         case '_private_' :
            // nothing to export, just a flag
            break;

         case 'TicketFollowup$1' :
            PluginPdfTicketFollowup::pdfForTicket($pdf, $item, $private);
            break;

         case 'TicketTask$1' :
            PluginPdfTicketTask::pdfForTicket($pdf, $item, $private);
            break;

         case 'TicketValidation$1' :
            PluginPdfTicketValidation::pdfForTicket($pdf, $item);
            break;

         case 'TicketCost$1' :
            PluginPdfTicketCost::pdfForTicket($pdf, $item);
            break;

         case 'Ticket$2' :
            self::pdfSolution($pdf, $item);
            break;

         case 'Ticket$3' :
            PluginPdfTicketSatisfaction::pdfForTicket($pdf, $item);
            break;

         case 'Ticket$4' :
            self::pdfStat($pdf, $item);
            break;

         default :
            return false;
      }
      return true;
   }
}