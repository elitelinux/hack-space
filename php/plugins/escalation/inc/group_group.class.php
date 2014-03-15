<?php

/*
   ------------------------------------------------------------------------
   Plugin Escalation for GLPI
   Copyright (C) 2012-2012 by the Plugin Escalation for GLPI Development Team.

   https://forge.indepnet.net/projects/escalation/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Escalation project.

   Plugin Escalation for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Escalation for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Escalation. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Escalation for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2012 Plugin Escalation for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/escalation/
   @since     2012

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginEscalationGroup_Group extends CommonDBRelation {


   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Ticket'
              && $item->getID() > 0
              && $_SESSION['glpiactiveprofile']['interface'] == 'central') {

         $peConfig = new PluginEscalationConfig();
         if ($peConfig->getValue('workflow', $item->fields['entities_id']) == '1') {
            $peGroup_group = new PluginEscalationGroup_Group();
            if (PluginEscalationProfile::haveRight("bypassworkflow", 1)
                    OR $peGroup_group->is_user_tech($item->getID())) {

               return "Escalade";
            }
         }
      } else if ($item->getType() == 'Group'
              && $item->getID() > 0
              && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $peConfig = new PluginEscalationConfig();
         if ($peConfig->getValue('workflow', $item->fields['entities_id']) == '1') {
            return "Escalade";
         }
      }
      return '';
   }



   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean TRUE
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Ticket') {
         $peGroup_Group = new PluginEscalationGroup_Group();
         $peGroup_Group->showGroups($item->getID());
      } else if ($item->getType()=='Group') {
         $peGroup_Group = new PluginEscalationGroup_Group();
         $peGroup_Group->manageGroup($item->getID());
      }
      return TRUE;
   }



   function showGroups($tickets_id) {
      global $DB,$CFG_GLPI;

      $group_Ticket = new Group_Ticket();
      $group = new Group();
      $ticket = new Ticket();
      $ticket->getFromDB($tickets_id);

      $createticketRight = 0;
      if (PluginEscalationProfile::haveRight("copyticketonworkflow", 1)) {
         $createticketRight = 1;
      }

      $groups_id = 0;
      $query = "SELECT * FROM `".$group_Ticket->getTable()."`
              WHERE `tickets_id`='".$tickets_id."'
               AND `type`='2'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $groups_id = $data['groups_id'];
      }

      echo "<form method='post' name='' id=''  action=\"".$CFG_GLPI['root_doc'] .
         "/plugins/escalation/front/group_group.form.php\">";
      echo "<table width='950' class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th colspan='4'>";
      echo "Escalade";
      echo "</th>";
      echo "</tr>";

      $a_groups = array();
      $a_groups['0'] = Dropdown::EMPTY_VALUE;
      if ($groups_id != '0'
              OR PluginEscalationProfile::haveRight("bypassworkflow", 1)) {

         $query = "SELECT * FROM `".$this->getTable()."`
            WHERE `groups_id_source` = '".$groups_id."' ";
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $group->getFromDB($data['groups_id_destination']);
            $a_groups[$data['groups_id_destination']] = $group->getName();
         }

         echo "<tr class='tab_bg_1' id='groupadd'>";
         echo "<td width='200'>";
         echo "Escalade vers le groupe ";
         echo "</td>";
         echo "<td>";
         if (PluginEscalationProfile::haveRight("bypassworkflow", 1)) {
            Dropdown::show('Group', array('name'      => 'group_assign',
                                          'entity'    => $ticket->fields['entities_id']));

            while ($data=$DB->fetch_array($result)) {
               $group->getFromDB($data['groups_id_destination']);
               $a_groups[$data['groups_id_destination']] = $group->getName();
            }
         } else {
            $rand = Dropdown::showFromArray('group_assign', $a_groups, array('on_change' => 'Ext.get("useradd").hide();Ext.get("or").hide();'));
         }
         echo "</td>";
         if ($createticketRight) {
            echo "<th colspan='2'>";
            echo "Créer sous-ticket";
            echo "</th>";
         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1' id='or'>";
         echo "<td>";
         echo "</td>";
         echo "<td>";
         echo "ou";
         echo "</td>";
         if ($createticketRight) {
            echo "<td>";
            echo "Créer sous-ticket";
            echo "</td>";
            echo "<td>";
            Dropdown::showYesNo("createsubticket");
            echo "</td>";
         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1' id='useradd'>";
         echo "<td>";
         echo "Escalade vers le technicien";
         echo "</td>";
         echo "<td>";
         $user = new User();
         $elements = array('0' => Dropdown::EMPTY_VALUE);
         $query = "SELECT * FROM `glpi_groups_users`
            WHERE `groups_id`='".$groups_id."'";
         $result = $DB->query($query);
         while ($data = $DB->fetch_assoc($result)) {
            $user->getFromDB($data['users_id']);
            $elements[$data['users_id']] = $user->getName();
         }
         $rand = Dropdown::showFromArray("_users_id_assign", $elements, array('on_change' => 'Ext.get("groupadd").hide();Ext.get("or").hide();'));
         echo "</td>";
         if ($createticketRight) {
            echo "<td>";
            echo "SLA à appliquer";
            echo "</td>";
            echo "<td>";
            Dropdown::show('Sla',array('entity' => $ticket->fields["entities_id"]));
            echo "</td>";
         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>";
         echo "<span id='show_assignuser$rand'></span>";
         echo "</td>";
         if ($createticketRight) {
            echo "<td>";
            echo "Groupe de technicien assigné";
            echo "</td>";
            echo "<td>";
            if (PluginEscalationProfile::haveRight("bypassworkflow", 1)) {
               Dropdown::show('Group', array('name'      => 'groupsubticket',
                                                      'entity'    => $ticket->fields['entities_id']));
            } else {
               $rand = Dropdown::showFromArray('groupsubticket', $a_groups);
            }
            echo "</td>";

         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' align='center'>";
         echo "<input type='hidden' name='tickets_id' value='".$tickets_id."'/>";
         echo "<input type='submit' class='submit' name='update' value='".__('Update')."'/>";
         echo "</td>";
         echo "</tr>";
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         echo "Escalade vers le groupe ";
         echo "</td>";
         echo "<td>";
         $a_groups = array();
         foreach($_SESSION['glpigroups'] as $groups_id) {
            $group->getFromDB($groups_id);
            $a_groups[$groups_id] = $group->getName();
         }
         $rand = Dropdown::showFromArray("group_assign", $a_groups);

         $params = array('groups_id'   => '__VALUE__',
                         'entity'      => $ticket->fields['entities_id'],
                         'rand'        => $rand);
         Ajax::updateItemOnSelectEvent("dropdown_group_assign".$rand, "show_assignuser$rand",
                                     $CFG_GLPI["root_doc"]."/plugins/escalation/ajax/dropdownUserassign.php",
                                     $params);

         if ($createticketRight) {

         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>";
         echo "<span id='show_assignuser$rand'></span>";
         echo "</td>";
         if ($createticketRight) {

         } else {
            echo "<td colspan='2'>";
            echo "</td>";
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' align='center'>";
         echo "<input type='hidden' name='tickets_id' value='".$tickets_id."'/>";
         echo "<input type='submit' class='submit' name='update' value='".__('Update')."'/>";
         echo "</td>";
         echo "</tr>";

      }
      echo "</table>";
      Html::closeForm();
   }



   function manageGroup($groups_id) {
      global $DB,$CFG_GLPI;

      $group = new Group();
      $a_groups_tmp = $group->find('', '`name`');
      $a_groups = array();
      foreach ($a_groups_tmp as $data) {
         $a_groups[$data['id']] = $data['name'];
      }
      unset($a_groups[$groups_id]);

      echo "<form method='post' name='' id=''  action=\"".$CFG_GLPI['root_doc'] .
         "/plugins/escalation/front/group_group.form.php\">";

      echo "<table width='950' class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th colspan='2'>";
      echo "Escalade";
      echo "</th>";
      echo "</tr>";

      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `groups_id_source`='".$groups_id."'";
      $result = $DB->query($query);

      echo "<tr>";
      echo "<td colspan='2' align='center'>";
      while ($data=$DB->fetch_array($result)) {
         unset($a_groups[$data['groups_id_destination']]);
      }
      Dropdown::showFromArray("groups_id_destination", $a_groups);
      echo "<input type='hidden' name='groups_id_source' value='".$groups_id."' />";
      echo "&nbsp;<input type='submit' class='submit' name='addgroup' value='".__('Add')."'/>";

      echo "</td>";
      echo "</tr>";

      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $group->getFromDB($data['groups_id_destination']);
         echo "<tr class='tab_bg_1'>";
         echo "<td width='30'>";
         echo "<input type='checkbox' name='delgroup[]' value='".$data['id']."' />";
         echo "</td>";
         echo "<td>";
         echo $group->getName();
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      Html::openArrowMassives("delgroup", true);
      Html::closeArrowMassives(array('deleteitem' => __('Delete permanently')));
      Html::closeForm();
   }



   static function selectGroupOnAdd($item) {
      global $CFG_GLPI,$DB;

      if (isset($item->input['_auto_import'])
              || isset($item->input['bypassgrouponadd'])) {
         return;
      }

      $peGroup_group = new self();

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $peConfig = new PluginEscalationConfig();
         if ($peConfig->getValue('workflow', $item->fields['entities_id']) == '1') {
            if (isset($_POST['_groups_id_assign'])
                  && $_POST['_groups_id_assign'] > 0) {

               if (isset($_SESSION['plugin_escalation_files'])) {
                  $_FILES = $_SESSION['plugin_escalation_files'];
               }

               return;
            } else {
               $group= new Group();
               Html::header(__('Administration'),'',"maintain","ticket");

               if (isset($_POST['dropdown__groups_id_requester'])
                       && $_POST['dropdown__groups_id_requester'] > 0) {
                  $_SESSION['plugin_escalation_groups_id_requester'] = $_POST['dropdown__groups_id_requester'];
               }

               if (isset($_FILES)) {
                  foreach ($_FILES['filename']['tmp_name'] as $numfile=>$datafile) {
                     if ($datafile != '') {
                        $split = explode("/", $datafile);

                        Document::renameForce($datafile,
                                GLPI_DOC_DIR."/_tmp/".end($split));
                        $_FILES['filename']['tmp_name'][$numfile] = GLPI_DOC_DIR."/_tmp/".end($split);
                     }
                  }
                  $_SESSION['plugin_escalation_files'] = $_FILES;
               }
               echo '<form action="'.$CFG_GLPI['root_doc'].'/front/ticket.form.php"
                  enctype="multipart/form-data" name="form_ticket" method="post">';
               echo "<table class='tab_cadre_fixe'>";

               echo "<tr class='tab_bg_1'>";
               echo "<th colspan='2'>Sélection du groupe de techniciens</th>";
               echo "</tr>";

               echo "<tr class='tab_bg_1'>";
               echo "<td>";
               echo __('Group in charge of the ticket')."&nbsp;:";
               echo "</td>";
               echo "<td>";
               $a_groups = array();
               foreach($_SESSION['glpigroups'] as $groups_id) {
                  $group->getFromDB($groups_id);
                  $a_groups[$groups_id] = $group->getName();

                  $queryg = "SELECT * FROM `".$peGroup_group->getTable()."`
                     WHERE `groups_id_source` = '".$groups_id."' ";
                  $resultg = $DB->query($queryg);
                  while ($datag=$DB->fetch_array($resultg)) {
                     $group->getFromDB($datag['groups_id_destination']);
                     $a_groups[$groups_id."_".$datag['groups_id_destination']] = "&nbsp;&nbsp;&nbsp;> ".$group->getName();
                  }
               }

               $rand = Dropdown::showFromArray("_groups_id_assign_escalation", $a_groups);

               $params = array('groups_id'   => '__VALUE__',
                               'entity'      => $_POST['entities_id'],
                               'rand'        => $rand);
               Ajax::updateItemOnSelectEvent("dropdown__groups_id_assign".$rand, "show_assignuser$rand",
                                           $CFG_GLPI["root_doc"]."/plugins/escalation/ajax/dropdownUserassign.php",
                                           $params);
               echo "</tr>";

               echo "<tr class='tab_bg_1'>";
               echo "<td colspan='2'>";
               foreach ($_POST as $key=>$value) {
                  if (is_array($value)) {
                     foreach ($value as $keyy=>$valuee) {
                        echo '<input type="hidden" name="'.$key.'['.$keyy.']" value="'.$valuee.'" />';
                     }
                  } else if ($key == 'content') {
                     $value = Html::cleanPostForTextArea(Toolbox::clean_cross_side_scripting_deep($value));
                     echo '<textarea name="'.$key.'" style="display:none;">'.$value.'</textarea>';
                  } else if ($key == 'dropdown__groups_id_requester') {
                     echo '<input type="hidden" name="_groups_id_requester" value="'.$value.'" />';
                  } else {
                     $value = Html::cleanInputText(Toolbox::clean_cross_side_scripting_deep(stripslashes($value)));
                     echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
                  }
               }
               if (!isset($_POST['_users_id_assign'])
                       OR $_POST['_users_id_assign'] == '0') {
                  echo "<span id='show_assignuser$rand'></span>";
               }
               echo "</td>";
               echo "</tr>";

               echo "<tr class='tab_bg_1'>";
               echo "<td colspan='2' align='center'>";
               echo "<input type='submit' name='add' value=\"".__('Add')."\" class='submit'>";
               echo "</td>";
               echo "</tr>";

               echo "</table>";
               Html::closeForm();

               Html::footer();

               exit;
            }
         }
      }
   }


   function is_user_tech($tickets_id) {
      $group_User = new Group_User();

      $tech = false;

      if (countElementsInTable("glpi_tickets_users",
              "`tickets_id`='".$tickets_id."'
               AND `type`='2'
               AND `users_id`='".$_SESSION['glpiID']."'") > 0) {
         $tech = true;
      }
      $a_groups = $group_User->getUserGroups($_SESSION['glpiID']);
      foreach ($a_groups as $data) {
         if (countElementsInTable("glpi_groups_tickets",
                 "`tickets_id`='".$tickets_id."'
                  AND `type`='2'
                  AND `groups_id`='".$data['id']."'") > 0) {
            $tech = true;
         }
      }

      return $tech;
   }


//   static function allowAssignRight($item) {
//      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
//         $_SESSION['pluginescalation']['assign_ticket'] = $_SESSION['glpiactiveprofile']['assign_ticket'];
//         $_SESSION['glpiactiveprofile']['assign_ticket'] = 1;
//      }
//   }
//
//
//
//   static function restoreAssignRight($item) {
//      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
//         $_SESSION['glpiactiveprofile']['assign_ticket'] = $_SESSION['pluginescalation']['assign_ticket'];
//      }
//   }


   static function notMultiple($item) {
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {

         $peConfig = new PluginEscalationConfig();
         $unique_assigned = $peConfig->getValue("unique_assigned", $item->fields['entities_id']);
         if ($unique_assigned == '1') {
            $ticket_User = new Ticket_User();
            $group_Ticket = new Group_Ticket();
            $group_User = new Group_User();


            if (isset($item->input['_itil_assign'])) {
               if ($item->input['_itil_assign']['_type'] == 'user') {
                  $in_group = 0;
                  $a_groups = $group_Ticket->find("`type`='2'
                     AND `tickets_id`='".$item->fields['id']."'");
                  $groups = Group_User::getUserGroups($item->input['_itil_assign']['users_id']);
                  if (count($a_groups) > 0) {
                     foreach ($a_groups as $data) {
                        foreach($groups as $dat) {
                           if ($dat['id'] == $data['groups_id']) {
                              $in_group = 1;
                           }
                        }
                     }
                  }
                  //if ($in_group == '0') {
                  //   unset($item->input['_itil_assign']['users_id']);
                  //}
               } else if ($item->input['_itil_assign']['_type'] == 'group') {
                  $a_groups = $group_Ticket->find("`type`='2'
                     AND `tickets_id`='".$item->getID()."'");
                  if (count($a_groups) > 0) {
                     foreach ($a_groups as $data) {
                        $group_Ticket->delete($data);
                     }
                  }
                  $a_users = $ticket_User->find("`type`='2'
                     AND `tickets_id`='".$item->getID()."'");
                  foreach ($a_users as $data) {
                     if (countElementsInTable($group_User->getTable(),
                             "`users_id`='".$data['users_id']."'
                             AND `groups_id`='".$item->input['_itil_assign']['groups_id']."'") == '0') {
                        $ticket_User->delete($data);
                     }
                  }
               }
            }
         }
      }
   }



   static function convertNewTicket() {
      if (isset($_POST['_groups_id_assign_escalation'])) {
         $split = explode('_', $_POST['_groups_id_assign_escalation']);
         if (isset($split[1])) {
            $_POST['_groups_id_assign'] = $split[1];
         } else {
            $_POST['_groups_id_assign'] = $split[0];
         }
      }
   }
}

?>
