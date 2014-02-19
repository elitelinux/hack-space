<?php
/*
 * @version $Id: networkportinstantiation.class.php 20322 2013-02-28 08:56:11Z moyo $
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

/// NetworkPortInstantiation class
/// Represents the type of a given network port. As such, its ID field is the same one than the ID
/// of the network port it instantiates. This class don't have any table associated. It just
/// provides usefull and default methods for the instantiations.
/// Several kind of instanciations are available for a given port :
///   - NetworkPortLocal
///   - NetworkPortEthernet
///   - NetworkPortWifi
///   - NetworkPortAggregate
///   - NetworkPortAlias
/// @since 0.84
class NetworkPortInstantiation extends CommonDBChild {

   // From CommonDBTM
   var $auto_message_on_action = false;

   // From CommonDBChild
   static public $itemtype       = 'NetworkPort';
   static public $items_id       = 'networkports_id';
   public $dohistory             = false;

   // Instantiation properties
   public $canHaveVLAN           = true;
   public $canHaveVirtualPort    = true;
   public $haveMAC               = true;



   static function getIndexName() {
      return 'networkports_id';
   }


   /**
    * Show the instanciation element for the form of the NetworkPort
    * By default, just print that there is no parameter for this type of NetworkPort
    *
    * @param $netport               the port that owns this instantiation
    *                               (usefull, for instance to get network port attributs
    * @param $options         array of options given to NetworkPort::showForm
    * @param $recursiveItems        list of the items on which this port is attached
   **/
   function showInstantiationForm(NetworkPort $netport, $options=array(), $recursiveItems) {

      echo "<tr><td colspan='4' class='center'>".__('No options available for this port type.').
           "</td></tr>";
   }


   function prepareInput($input) {

      // Try to get mac address from the instantiation ...
      if (!empty($input['mac'])) {
         $input['mac'] = strtolower($input['mac']) ;
      }
      return $input;
   }


   function prepareInputForAdd($input) {
      return parent::prepareInputForAdd($this->prepareInput($input));
   }


   function prepareInputForUpdate($input) {
      return parent::prepareInputForUpdate($this->prepareInput($input));
   }


   /**
    * Get all the instantiation specific options to display
    *
    * @return array containing the options
   **/
   static function getInstantiationNetworkPortDisplayOptions() {
      return array();
   }


   /**
    * Get the instantiation specific options to display that applies for all instantiations
    *
    * @return array containing the options
   **/
   static function getGlobalInstantiationNetworkPortDisplayOptions() {
      return array('mac'           => array('name'    => __('MAC'),
                                            'default' => true),
                   'vlans'         => array('name'    => __('VLAN'),
                                            'default' => false),
                   'virtual_ports' => array('name'    => __('Virtual ports'),
                                            'default' => false),
                   'port_opposite' => array('name'    => __('Opposite link'),
                                            'default' => false));
   }


   /**
    * Get HTMLTable columns headers for a given item type
    * Beware : the internet informations are "sons" of each instantiation ...
    *
    * @param $group           HTMLTableGroup object
    * @param $super           HTMLTableSuperHeader object
    * @param $internet_super  HTMLTableSuperHeader object for the internet sub part (default NULL)
    * @param $father          HTMLTableHeader object (default NULL)
    * @param $options   array of possible options:
    *       - 'dont_display' : array of the columns that must not be display
    *
    * @return the father group for the Internet Informations ...
   **/
   function getInstantiationHTMLTableHeaders(HTMLTableGroup $group, HTMLTableSuperHeader $super,
                                             HTMLTableSuperHeader $internet_super=NULL,
                                             HTMLTableHeader $father=NULL,
                                             array $options=array()) {

      $display_options = &$options['display_options'];

      if (($this->canHaveVirtualPort) && ($display_options['virtual_ports'])) {
         $father = $group->addHeader('VirtualPorts', '<i>'.__('Virtual ports').'</i>',
                                     $super, $father);
      }

      if (($this->canHaveVLAN) && ($display_options['vlans'])) {
         NetworkPort_Vlan::getHTMLTableHeader('NetworkPort', $group, $super, $father, $options);
      }

      if (($this->haveMAC) && ($display_options['mac'])) {
         $group->addHeader('MAC', __('MAC'), $super, $father);
      }

      if (($internet_super !== NULL) && ($display_options['internet'])) {
         NetworkName::getHTMLTableHeader('NetworkPort', $group, $internet_super, $father, $options);
      }

      return NULL;
   }


   /**
    * Get HTMLTable row for a given network port and a given extremity when two ports are
    * existing on a link (NetworkPort_NetworkPort).
    *
    * @param $netport         NetworkPort object (contains item)
    * @param $row             HTMLTableRow object
    * @param $father          HTMLTableHeader object (default NULL)
    * @param $options   array of possible options:
    *       - 'dont_display' : array of the elements that must not be display
    *       - 'withtemplate' : integer withtemplate param
    *
    * @return the father cell for the Internet Informations ...
   **/
   protected function getPeerInstantiationHTMLTable(NetworkPort $netport, HTMLTableRow $row,
                                                    HTMLTableCell $father=NULL,
                                                    array $options=array()) {

      self::getInstantiationHTMLTable($netport, $row, $father, $options);
      return NULL;

   }


   /**
    * Replacement of NetworkPortInstantiation::getInstantiationHTMLTable() method when two ports
    * share the same link (NetworkPort_NetworkPort). Used, for instance by Dialup and Ethernet.
    *
    * @see NetworkPortInstantiation::getInstantiationHTMLTable()
    *
    * @param $netport         NetworkPort object (contains item)
    * @param $row             HTMLTableRow object
    * @param $father          HTMLTableHeader object (default NULL)
    * @param $options   array of possible options:
    *       - 'dont_display' : array of the elements that must not be display
    *       - 'withtemplate' : integer withtemplate param
    *
    * @return the father cell for the Internet Informations ...
   **/
   function getInstantiationHTMLTableWithPeer(NetworkPort $netport, HTMLTableRow $row,
                                              HTMLTableCell $father=NULL, array $options=array()) {

      $connect_cell_value = array(array('function'   => array(__CLASS__, 'showConnection'),
                                        'parameters' => array(clone $netport)));

      $oppositePort = NetworkPort_NetworkPort::getOpposite($netport);
      if ($oppositePort !== false) {

         $opposite_options            = $options;
         $opposite_options['canedit'] = false;
         $display_options             = $options['display_options'];

         if ($display_options['port_opposite']) {
            $cell          = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                           __('Local network port'));

            $opposite_cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                           $connect_cell_value);
            $opposite_cell->setAttributForTheRow(array('class' => 'htmltable_upper_separation_cell'));

            $oppositeInstantiationPort = $oppositePort->getInstantiation();
            if ($oppositeInstantiationPort !== false) {
               $oppositeInstantiationPort->getPeerInstantiationHTMLTable($oppositePort, $row,
                                                                         $opposite_cell,
                                                                         $opposite_options);
            }

         } else {
            $cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                                  $connect_cell_value);
          }

      } else {
         $cell = $row->addCell($row->getHeaderByName('Instantiation', 'Connected'),
                               $connect_cell_value);
      }

      $this->getPeerInstantiationHTMLTable($netport, $row, $cell, $options);
      return $cell;
   }


   /**
    * Get HTMLTable row for a given item
    *
    * @param $netport         NetworkPort object (contains item)
    * @param $row             HTMLTableRow object
    * @param $father          HTMLTableHeader object (default NULL)
    * @param $options   array of possible options:
    *       - 'dont_display' : array of the elements that must not be display
    *       - 'withtemplate' : integer withtemplate param
    *
    * @return the father cell for the Internet Informations ...
   **/
   function getInstantiationHTMLTable(NetworkPort $netport, HTMLTableRow $row,
                                      HTMLTableCell $father=NULL, array $options=array()) {
      global $DB;

      $display_options = $options['display_options'];

      if (($this->canHaveVirtualPort) && ($display_options['virtual_ports'])) {

         $virtual_header = $row->getHeaderByName('Instantiation', 'VirtualPorts');

         $query = "(SELECT `networkports_id`
                    FROM `glpi_networkportaliases`
                    WHERE `networkports_id_alias`='".$netport->getID()."')
                   UNION
                   (SELECT `networkports_id`
                    FROM `glpi_networkportaggregates`
                    WHERE `networkports_id_list` LIKE '%\"".$netport->getID()."\"%')";

         $iterator = $DB->request($query);

         if ($iterator->numrows() > 0) {
            $new_father = $row->addCell($virtual_header, __('this port'), $father);
         } else {
            $new_father = $row->addCell($virtual_header, '', $father);
         }

         foreach ($iterator as $networkports_ids) {

            $virtualPort = new NetworkPort();

            if ($virtualPort->getFromDB($networkports_ids['networkports_id'])) {

               $cell_value = '<i>'.$virtualPort->getLink().'</i>';

               $virtual_cell = $row->addCell($virtual_header, $cell_value, $father);
               $virtual_cell->setAttributForTheRow(array('class' => 'htmltable_upper_separation_cell'));

               if (($this->canHaveVLAN) && ($display_options['vlans'])) {
                  NetworkPort_Vlan::getHTMLTableCellsForItem($row, $virtualPort, $virtual_cell,
                                                             $options);
               }

               if ($display_options['internet']) {
                  NetworkName::getHTMLTableCellsForItem($row, $virtualPort, $virtual_cell,
                                                        $options);
               }
            }
            unset($virtualPort);
         }

         $father = $new_father;
      }

      if (($this->canHaveVLAN) && ($display_options['vlans'])) {
         NetworkPort_Vlan::getHTMLTableCellsForItem($row, $netport, $father, $options);
      }

      if (($this->haveMAC) && ($display_options['mac']) && (!empty($netport->fields["mac"]))) {
         $row->addCell($row->getHeaderByName('Instantiation', 'MAC'),
                       $netport->fields["mac"], $father);
      }

      if ($display_options['internet']) {
         NetworkName::getHTMLTableCellsForItem($row, $netport, $father, $options);
      }


      return NULL;
   }


  /**
    * Get all NetworkPort and NetworkEquipments that have a specific MAC address
    *
    * @param $mac                      address to search
    * @param $wildcard_search boolean  true if we search with wildcard (false by default)
    *
    * @return (array) each value of the array (corresponding to one NetworkPort) is an array of the
    *                 items from the master item to the NetworkPort
   **/
   static function getItemsByMac($mac, $wildcard_search=false) {
      global $DB;

      $mac = strtolower($mac);
      if ($wildcard_search) {
         $count = 0;
         $mac = str_replace('*', '%', $mac, $count);
         if ($count == 0) {
            $mac = '%'.$mac.'%';
         }
         $relation = "LIKE '$mac'";
      } else {
         $relation = "= '$mac'";
      }

      $macItemWithItems = array();

      foreach (array('NetworkPort', 'NetworkEquipment') as $netporttype) {
         $netport = new $netporttype();

         $query = "SELECT `id`
                   FROM `".$netport->getTable()."`
                   WHERE `mac` $relation ";

         foreach ($DB->request($query) as $element) {
            if ($netport->getFromDB($element['id'])) {

               if ($netport instanceof CommonDBChild) {
                  $macItemWithItems[] = array_merge(array_reverse($netport->recursivelyGetItems()),
                                                    array(clone $netport));
               } else {
                  $macItemWithItems[] = array(clone $netport);
               }
            }
         }
      }

      return $macItemWithItems;
   }


   /**
    * Get an Object ID by its MAC address (only if one result is found in the entity)
    *
    * @param $value  the mac address
    * @param $entity the entity to look for
    *
    * @return an array containing the object ID
    *         or an empty array is no value of serverals ID where found
   **/
   static function getUniqueItemByMac($value, $entity) {

      $macs_with_items = self::getItemsByMac($value);

      if (count($macs_with_items) == 1) {
         $mac_with_items = $macs_with_items[0];
         $item           = $mac_with_items[0];

         if ($item->getEntityID() == $entity) {
            $result = array("id"       => $item->getID(),
                            "itemtype" => $item->getType());
            unset($macs_with_items);
            return $result;
         }
      }

      return array();
   }


   /**
    * In case of NetworkPort attached to a network card, list the fields that must be duplicate
    * from the network card to the network port (mac address, port type, ...)
    *
    * @return an array with SQL field (for instance : device.`type`) => form field (type)
   **/
   function getNetworkCardInterestingFields() {
      return array();
   }


   /**
    * Select which network card to attach to the current NetworkPort (for the moment, only ethernet
    * and wifi ports). Whenever a card is attached, its information (mac, type, ...) are
    * autmatically set to the required field.
    *
    * @param $netport               NetworkPort object :the port that owns this instantiation
    *                               (usefull, for instance to get network port attributs
    * @param $options         array of options given to NetworkPort::showForm
    * @param $recursiveItems        list of the items on which this port is attached
   **/
   function showNetworkCardField(NetworkPort $netport, $options=array(), $recursiveItems) {
      global $DB;

      echo "<td>" . __('Network card') . "</td>\n</td>";
      echo "<td>";

      if (count($recursiveItems)  > 0) {

         $lastItem = $recursiveItems[count($recursiveItems) - 1];

         // Network card association is only available for computers
         if (($lastItem->getType() == 'Computer')
             && !$options['several']) {

            // Query each link to network cards
            $query = "SELECT link.`id` AS link_id,
                             device.`designation` AS name";

            // $deviceFields contains the list of fields to update
            $deviceFields = array();
            foreach ($this->getNetworkCardInterestingFields() as $SQL_field => $form_field) {
               $deviceFields[] = $form_field;
               $query         .= ", $SQL_field AS $form_field";
            }
            $query .= " FROM `glpi_devicenetworkcards` AS device,
                             `glpi_items_devicenetworkcards` AS link
                        WHERE link.`items_id` = '".$lastItem->getID()."'
                              AND link.`itemtype` = '".$lastItem->getType()."'
                              AND device.`id` = link.`devicenetworkcards_id`";
            // TODO : add checking the type of network card !

            // Add the javascript to update each field
            echo "\n<script type=\"text/javascript\">
   var deviceAttributs = [];\n";

            $deviceNames = array(0 => ""); // First option : no network card
            foreach ($DB->request($query) as $availableDevice) {
               $linkID               = $availableDevice['link_id'];
               $deviceNames[$linkID] = $availableDevice['name'];
               if (isset($availableDevice['mac'])) {
                  $deviceNames[$linkID] = sprintf(__('%1$s - %2$s'), $deviceNames[$linkID],
                                                  $availableDevice['mac']);
               }

               // get fields that must be copied from those of the network card
               $deviceInformations = array();
               foreach ($deviceFields as $field) {
                  // No gettext here
                  $deviceInformations[] = "$field: '".$availableDevice[$field]."'";
               }
               //addslashes_deep($deviceInformations);
               // Fill the javascript array
               echo "  deviceAttributs[$linkID] = {".implode(', ', $deviceInformations)."};\n";
            }

            // And add the javascript function that updates the other fields
            echo "
   function updateNetworkPortForm(devID) {
      for (var fieldName in deviceAttributs[devID]) {
         var field=document.getElementsByName(fieldName)[0];
         if ((field == undefined) || (deviceAttributs[devID][fieldName] == undefined))
            continue;
         field.value = deviceAttributs[devID][fieldName];
      }
   }
</script>\n";

            if (count($deviceNames) > 0) {
               $options = array('value'
                                 => $this->fields['items_devicenetworkcards_id'],
                                'on_change'
                                 => 'updateNetworkPortForm(this.options[this.selectedIndex].value)');
               Dropdown::showFromArray('items_devicenetworkcards_id', $deviceNames, $options);
            } else {
                _e('No network card available');
            }
         } else {
            _e('Equipment without network card');
         }
      } else {
         _e('Item not linked to an object');
      }
      echo "</td>";
   }


   /**
    * Display the MAC field. Used by Ethernet, Wifi, Aggregate and alias NetworkPorts
    *
    * @param $netport         NetworkPort object : the port that owns this instantiation
    *                         (usefull, for instance to get network port attributs
    * @param $options   array of options given to NetworkPort::showForm
   **/
   function showMacField(NetworkPort $netport, $options=array()) {

      // Show device MAC adresses
      echo "<td>" . __('MAC') ."</td>\n<td>";
      Html::autocompletionTextField($netport, "mac");
      echo "</td>\n";
   }


   /**
    * Display the Netpoint field. Used by Ethernet, and Migration
    *
    * @param $netport               NetworkPort object :the port that owns this instantiation
    *                               (usefull, for instance to get network port attributs
    * @param $options         array of options given to NetworkPort::showForm
    * @param $recursiveItems        list of the items on which this port is attached
   **/
   function showNetpointField(NetworkPort $netport, $options=array(), $recursiveItems) {

      echo "<td>" . __('Network outlet') . "</td>\n";
      echo "<td>";
      if (count($recursiveItems) > 0) {
         $lastItem = $recursiveItems[count($recursiveItems) - 1];
         Netpoint::dropdownNetpoint("netpoints_id", $this->fields["netpoints_id"],
                                    $lastItem->fields['locations_id'], 1, $lastItem->getEntityID(),
                                    $netport->fields["itemtype"]);
      } else {
         _e('item not linked to an object');
      }
      echo "</td>";
   }


   /**
    * \brief display the attached NetworkPort
    *
    * NetworkPortAlias and NetworkPortAggregate are based on other physical network ports
    * (Ethernet or Wifi). This method displays the physical network ports.
   **/
   function getInstantiationNetworkPortHTMLTable() {

      $netports = array();

      // Manage alias
      if (isset($this->fields['networkports_id_alias'])) {
         $links_id = $this->fields['networkports_id_alias'];
         $netport  = new NetworkPort();
         if ($netport->getFromDB($links_id)) {
            $netports[] = $netport->getLink();
         }
      }
      // Manage aggregate
      if (isset($this->fields['networkports_id_list'])) {
         $links_id = $this->fields['networkports_id_list'];
         $netport  = new NetworkPort();
         foreach ($links_id as $id) {
            if ($netport->getFromDB($id)) {
               $netports[] = $netport->getLink();
            }
         }
      }

      if (count($netports) > 0) {
         return implode('<br>', $netports);
      }

      return "&nbsp;";
   }


   /**
    * \brief select which NetworkPort to attach
    *
    * NetworkPortAlias and NetworkPortAggregate ara based on other physical network ports
    * (Ethernet or Wifi). This method Allows us to select which one to select.
    *
    * @param $recursiveItems
    * @param $origin          NetworkPortAlias are based on one NetworkPort wherever
    *                         NetworkPortAggregate are based on several NetworkPort.
   **/
   function showNetworkPortSelector($recursiveItems, $origin) {
      global $DB;

      if (count($recursiveItems) == 0) {
         return;
      }

      $lastItem = $recursiveItems[count($recursiveItems) - 1];

      echo "<td>" . __('Origin port') . "</td><td>\n";
      $links_id      = array();
      $netport_types = array('NetworkPortEthernet', 'NetworkPortWifi');
      $selectOptions = array();
      $possible_ports = array();

      switch ($origin) {
         case 'NetworkPortAlias' :
            $possible_ports[-1]         = Dropdown::EMPTY_VALUE;
            $field_name                 = 'networkports_id_alias';
            $selectOptions['multiple']  = false;
            $selectOptions['on_change'] = 'updateForm(this.options[this.selectedIndex].value)';
            $netport_types[]            = 'NetworkPortAggregate';
            break;

         case 'NetworkPortAggregate' :
            $field_name                       = 'networkports_id_list';
            $selectOptions['multiple']        = true;
            $selectOptions['size']            = 4;
            $selectOptions['mark_unmark_all'] = true;
            $netport_types[]                  = 'NetworkPortAlias';
            break;
      }

      if (isset($this->fields[$field_name])) {
         if (is_array($this->fields[$field_name])) {
            $selectOptions['values'] = $this->fields[$field_name];
         } else {
            $selectOptions['values'] = array($this->fields[$field_name]);
         }
      }

      $macAddresses = array();
      foreach ($netport_types as $netport_type) {
         $instantiationTable = getTableForItemType($netport_type);
         $query = "SELECT port.`id`, port.`name`, port.`mac`
                   FROM `glpi_networkports` AS port
                   WHERE `items_id` = '".$lastItem->getID()."'
                         AND `itemtype` = '".$lastItem->getType()."'
                         AND `instantiation_type` = '$netport_type'
                   ORDER BY `logical_number`, `name`";

         $result = $DB->query($query);

         if ($DB->numrows($result) > 0) {
            $array_element_name = call_user_func(array($netport_type, 'getTypeName'),
                                                 $DB->numrows($result));
            $possible_ports[$array_element_name] = array();

            while ($portEntry = $DB->fetch_assoc($result)) {
               $macAddresses[$portEntry['id']] = $portEntry['mac'];
               if (!empty($portEntry['mac'])) {
                  $portEntry['name'] = sprintf(__('%1$s - %2$s'), $portEntry['name'],
                                               $portEntry['mac']);
               }
               $possible_ports[$array_element_name][$portEntry['id']] = $portEntry['name'];
            }
         }
      }

      if (!$selectOptions['multiple']) {
         echo "\n<script type=\"text/javascript\">
        var device_mac_addresses = [];\n";
         foreach ($macAddresses as $port_id => $macAddress) {
            echo "  device_mac_addresses[$port_id] = '$macAddress'\n";
         }
         echo "   function updateForm(devID) {
      var field=document.getElementsByName('mac')[0];
      if ((field != undefined) && (device_mac_addresses[devID] != undefined))
         field.value = device_mac_addresses[devID];
   }
</script>\n";
      }

      Dropdown::showFromArray($field_name, $possible_ports, $selectOptions);
      echo "</td>\n";
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == "NetworkPort") {
         $instantiation = $item->getInstantiation();
         if ($instantiation !== false) {
            $log = new Log();
            //TRANS: %1$s is a type, %2$s is a table
            return sprintf(__('%1$s - %2$s'), $instantiation->getTypeName(),
                           $log->getTabNameForItem($instantiation));
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == "NetworkPort") {
         $instantiation = $item->getInstantiation();
         if ($instantiation !== false) {
            return Log::displayTabContentForItem($instantiation, $tabnum, $withtemplate);
         }
      }
   }


   /**
    * @param $tab          array
    * @param $joinparams   array
    * @param $itemtype
    **/
   static function getSearchOptionsToAddForInstantiation(array &$tab, array $joinparams,
                                                         $itemtype) {
   }


   /**
    * Display a connection of a networking port
    *
    * @param $netport      to be displayed
    * @param $edit         boolean permit to edit ? (false by default)
   **/
   static function showConnection($netport, $edit=false) {

      $ID = $netport->fields["id"];
      if (empty($ID)) {
         return false;
      }

      $device1 = $netport->getItem();

      if (!$device1->can($device1->getID(), 'r')) {
         return false;
      }
      $canedit      = $device1->can($device1->fields["id"], 'w');
      $relations_id = 0;
      $oppositePort = NetworkPort_NetworkPort::getOpposite($netport, $relations_id);

      if ($oppositePort !== false) {
         $device2 = $oppositePort->getItem();

         if ($device2->can($device2->fields["id"], 'r')) {
            $networklink = $oppositePort->getLink();
            $tooltip     = Html::showToolTip($oppositePort->fields['comment'],
                                             array('display' => false));
            $netlink     = sprintf(__('%1$s %2$s'),
                                   "<span class='b'>".$networklink."</span>\n", $tooltip);
            //TRANS: %1$s and %2$s are links
            echo "&nbsp;". sprintf(__('%1$s on %2$s'), $netlink,
                                   "<span class='b'>".$device2->getLink()."</span>");
            if ($device1->fields["entities_id"] != $device2->fields["entities_id"]) {
               echo "<br>(". Dropdown::getDropdownName("glpi_entities",
                                                       $device2->getEntityID()) .")";
            }

            // 'w' on dev1 + 'r' on dev2 OR 'r' on dev1 + 'w' on dev2
            if ($canedit
                || $device2->can($device2->fields["id"], 'w')) {
               echo "&nbsp;";
               Html::showSimpleForm($oppositePort->getFormURL(), 'disconnect', __('Disconnect'),
                                    array('id' => $relations_id));
            }

         } else {
            if (rtrim($oppositePort->fields["name"]) != "") {
               $netname = $oppositePort->fields["name"];
            } else {
               $netname = __('Without name');
            }
            printf(__('%1$s on %2$s'), "<span class='b'>".$netname."</span>",
                   "<span class='b'>".$device2->getName()."</span>");
            echo "<br>(" .Dropdown::getDropdownName("glpi_entities",
                                                    $device2->getEntityID()) .")";
         }

      } else {
         echo "<div id='not_connected_display$ID'>" . __('Not connected.') . "</div>";
         if ($canedit) {
            if (!$device1->isTemplate()) {
               if ($edit) {
                  self::dropdownConnect($ID,
                                        array('name'        => 'NetworkPortConnect_networkports_id_2',
                                              'entity'      => $device1->fields["entities_id"],
                                              'entity_sons' => $device1->isRecursive()));
               } else {
                  echo "<a href=\"".$netport->getFormURL()."?id=$ID\">". __('Connect')."</a>";
               }
            } else {
               echo "&nbsp;";
            }
         }
      }
   }


   /**
    * Make a select box for  connected port
    *
    * @param $ID                 ID of the current port to connect
    * @param $options   array    of possible options:
    *    - name : string / name of the select (default is networkports_id)
    *    - comments : boolean / is the comments displayed near the dropdown (default true)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - entity_sons : boolean / if entity restrict specified auto select its sons
    *                   only available if entity is a single value not an array (default false)
    *
    * @return nothing (print out an HTML select box)
   **/
   static function dropdownConnect($ID, $options=array()) {
      global $CFG_GLPI;

      $p['name']        = 'networkports_id';
      $p['comments']    = 1;
      $p['entity']      = -1;
      $p['entity_sons'] = false;

     if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      // Manage entity_sons
      if (!($p['entity'] < 0) && $p['entity_sons']) {
         if (is_array($p['entity'])) {
            echo "entity_sons options is not available with entity option as array";
         } else {
            $p['entity'] = getSonsOf('glpi_entities', $p['entity']);
         }
      }

      $rand = mt_rand();
      echo "<input type='hidden' name='NetworkPortConnect_networkports_id_1'value='$ID'>";
      echo "<select name='NetworkPortConnect_itemtype' id='itemtype$rand'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";

      foreach ($CFG_GLPI["networkport_types"] as $key => $itemtype) {
         if ($item = getItemForItemtype($itemtype)) {
            echo "<option value='".$itemtype."'>".$item->getTypeName(1)."</option>";
         } else {
            unset($CFG_GLPI["networkport_types"][$key]);
         }
      }
      echo "</select>";

      $params = array('itemtype'           => '__VALUE__',
                      'entity_restrict'    => $p['entity'],
                      'networkports_id'    => $ID,
                      'comments'           => $p['comments'],
                      'myname'             => $p['name'],
                      'instantiation_type' => get_called_class());

      Ajax::updateItemOnSelectEvent("itemtype$rand", "show_".$p['name']."$rand",
                                    $CFG_GLPI["root_doc"].
                                       "/ajax/dropdownConnectNetworkPortDeviceType.php",
                                    $params);

      echo "<span id='show_".$p['name']."$rand'>&nbsp;</span>\n";

      return $rand;
   }
}
?>
