<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

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
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Frédéric VAN BEVEREN
// Purpose of file:
// ----------------------------------------------------------------------

// Ports d'imprimante

define("PORT_TYPE_UNKNOWN","0");
define("PORT_TYPE_IP","1");
define("PORT_TYPE_LPT","2");
define("PORT_TYPE_COM","3");
define("PORT_TYPE_USB","4");
define("PORT_TYPE_FAX","5");
define("PORT_TYPE_FILE","6");
define("PORT_TYPE_NUL","7");

$EREGNUMIP="(25[0-5]|2[0-4]\d|[01]?\d\d|\d)";		// Expression régulière 0-255
$EREGHOST = "/\/\/[a-z0-9\\-\\_\\.]{1,}\//i";		// Expression régulière nom d'hôte (//xxxx/)

// Expressions régulières pour recherche type de port
$PORTEREG[PORT_TYPE_IP]   = "$EREGNUMIP\\.$EREGNUMIP\\.$EREGNUMIP\\.$EREGNUMIP";
$PORTEREG[PORT_TYPE_LPT]  = "^LPT[1-9]\\:";
$PORTEREG[PORT_TYPE_COM]  = "^COM[1-9]\\:|^COM[1-9]";
$PORTEREG[PORT_TYPE_FAX]  = "^HPFAX\\:|^MSFAX\\:";
$PORTEREG[PORT_TYPE_USB]  = "^USB[0-9]{3}|^DOT4_[0-9]{3}";	// Vérifier pour DOT4_
$PORTEREG[PORT_TYPE_FILE] = "^FILE\\:|^[A-Z]\\:\\\\|^[A-Z]\\:\\/|^PDF|PDF$|^CPW2\\:";
$PORTEREG[PORT_TYPE_NUL]  = "^NUL\\:";


/*
   Objets non traités

   INFOCOM_TYPE
   CONTRACT_TYPE
   CARTRIDGE_TYPE
   TRACKING_TYPE
   CONSUMABLE_TYPE
   CONSUMABLE_ITEM_TYPE
   CARTRIDGE_ITEM_TYPE
   LICENSE_TYPE
   LINK_TYPE
 */
/*
function getStructure($objectId, $actionId)
	{
	global $LINK_ID_TABLE, $LANG;

	$computer = $LINK_ID_TABLE[COMPUTER_TYPE];
	$printer = $LINK_ID_TABLE[PRINTER_TYPE];
	$monitor = $LINK_ID_TABLE[MONITOR_TYPE];
	$peripheral = $LINK_ID_TABLE[PERIPHERAL_TYPE];
	$infoCom = $LINK_ID_TABLE[INFOCOM_TYPE];
	$tracking = $LINK_ID_TABLE[TRACKING_TYPE];
	//$software = $LINK_ID_TABLE[SOFTWARE_TYPE];
	$monitor = $LINK_ID_TABLE[PHONE_TYPE];

	$masterTable = $LINK_ID_TABLE[$objectId];
	$masterCondition = "$masterTable.is_template = '0'";

	if ($actionId == PURGE_ACTION)
		$masterCondition .= " and $masterTable.deleted = '1'";

	switch ($objectId)
		{
		case COMPUTER_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			$obj1 = new glpiuObject('glpi_ocs_link', null, 'ID', 'glpi_id', "", $object);
			$obj1 = new glpiuObject('glpi_connect_wire', null, 'ID', 'end2', "", $object);
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".COMPUTER_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".COMPUTER_TYPE, $object);
			$obj1 = new glpiuObject('glpi_inst_software', null, 'ID', 'cID', "", $object);
			$obj1 = new glpiuObject('glpi_computer_device', null, 'ID', 'FK_device', "", $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".COMPUTER_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".COMPUTER_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_networking_ports', null, 'ID', 'on_device', "glpi_networking_ports.device_type = ".COMPUTER_TYPE, $object);
			$obj2 = new glpiuObject('glpi_networking_wire', null, 'ID', array('end1', 'end2'), "", $obj1,"ID");
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".COMPUTER_TYPE, $object);
			break;

		case PRINTER_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			if ($actionId == DELETE_UNLINKED_ACTION)
				$obj1 = new glpiuObject('glpi_connect_wire', 'B', 'ID', 'end1', "B.type = ".PRINTER_TYPE, $object, null, true, true);
			else
				$obj1 = new glpiuObject('glpi_connect_wire', null, 'ID', 'end1', "glpi_connect_wire.type = ".PRINTER_TYPE, $object);
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".PRINTER_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".PRINTER_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".PRINTER_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".PRINTER_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_networking_ports', null, 'ID', 'on_device', "glpi_networking_ports.device_type = ".PRINTER_TYPE, $object);
			$obj2 = new glpiuObject('glpi_networking_wire', null, 'ID', array('end1', 'end2'), "", $obj1,"ID");
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".PRINTER_TYPE, $object);
			break;

		case MONITOR_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			if ($actionId == DELETE_UNLINKED_ACTION)
				$obj1 = new glpiuObject('glpi_connect_wire', 'B', 'ID', 'end1', "B.type = ".MONITOR_TYPE, $object, null, true, true);
			else
				$obj1 = new glpiuObject('glpi_connect_wire', null, 'ID', 'end1', "glpi_connect_wire.type = ".MONITOR_TYPE, $object);
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".MONITOR_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".MONITOR_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".MONITOR_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".MONITOR_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".MONITOR_TYPE, $object);
			break;

		case PERIPHERAL_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			if ($actionId == DELETE_UNLINKED_ACTION)
				$obj1 = new glpiuObject('glpi_connect_wire', 'B', 'ID', 'end1', "B.type = ".PERIPHERAL_TYPE, $object, null, true, true);
			else
				$obj1 = new glpiuObject('glpi_connect_wire', null, 'ID', 'end1', "glpi_connect_wire.type = ".PERIPHERAL_TYPE, $object);
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".PERIPHERAL_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".PERIPHERAL_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".PERIPHERAL_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".PERIPHERAL_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_networking_ports', null, 'ID', 'on_device', "glpi_networking_ports.device_type = ".PERIPHERAL_TYPE, $object);
			$obj2 = new glpiuObject('glpi_networking_wire', null, 'ID', array('end1', 'end2'), "", $obj1,"ID");
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".PERIPHERAL_TYPE, $object);
			break;

		case SOFTWARE_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			if ($actionId == DELETE_UNLINKED_ACTION)
				$obj1 = new glpiuObject('glpi_licenses', 'B', 'ID', 'sID', "", $object, null, true, true);
			else
			{
				$obj1 = new glpiuObject('glpi_licenses', null, 'ID', 'sID', "", $object);
				$obj2 = new glpiuObject('glpi_inst_software', null, 'ID', 'license', "", $obj1);
			}
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".SOFTWARE_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".SOFTWARE_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".SOFTWARE_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".SOFTWARE_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".SOFTWARE_TYPE, $object);
			break;

		case NETWORKING_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];

			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".NETWORKING_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".NETWORKING_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".NETWORKING_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".NETWORKING_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_networking_ports', null, 'ID', 'on_device', "glpi_networking_ports.device_type = ".NETWORKING_TYPE, $object);
			$obj2 = new glpiuObject('glpi_networking_wire', null, 'ID', array('end1', 'end2'), "", $obj1,"ID");
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".NETWORKING_TYPE, $object);
			break;

		case PHONE_TYPE :
			$object = new glpiuObject($masterTable, null, 'ID', '', $masterCondition);
			$object->title = $LANG['plugin_utilitaires']["menu"][$objectId];
			if ($actionId == DELETE_UNLINKED_ACTION)
				$obj1 = new glpiuObject('glpi_connect_wire', 'B', 'ID', 'end1', "B.type = ".PHONE_TYPE, $object, null, true, true);
			else
				$obj1 = new glpiuObject('glpi_connect_wire', null, 'ID', 'end1', "glpi_connect_wire.type = ".PHONE_TYPE, $object);
			$obj1 = new glpiuObject($infoCom, null, 'ID', 'FK_device', "$infoCom.device_type = ".PHONE_TYPE, $object);
			$obj1 = new glpiuObject($tracking, null, 'ID', 'computer', "$tracking.device_type = ".PHONE_TYPE, $object);
			$obj1 = new glpiuObject('glpi_contract_device', null, 'ID', 'FK_device', "glpi_contract_device.device_type = ".PHONE_TYPE, $object);
			$obj1 = new glpiuObject('glpi_reservation_item', null, 'ID', 'id_device', "glpi_reservation_item.device_type = ".PHONE_TYPE, $object);
			$obj2 = new glpiuObject('glpi_reservation_resa', null, 'ID', 'id_item', "", $obj1, 'ID');
			$obj1 = new glpiuObject('glpi_networking_ports', null, 'ID', 'on_device', "glpi_networking_ports.device_type = ".PHONE_TYPE, $object);
			$obj2 = new glpiuObject('glpi_networking_wire', null, 'ID', array('end1', 'end2'), "", $obj1,"ID");
			$obj1 = new glpiuObject('glpi_history', null, 'ID', 'FK_glpi_device', "glpi_history.device_type = ".PHONE_TYPE, $object);
			break;
		}
	return $object;
	}


function countObjectsToProcess($objectId, $actionId, $start = 0)
    {
    switch ($actionId)
		{
		case CREATE_MODEL_ACTION:
		case LINK_MODEL_ACTION:
		case RESET_OCS_ACTION:
		    $nb = 1;
		    break;
		case IMPORT_PRINTERS_ACTION:
		    $nb = glpiuImportPrinters(0, $start);
		    break;
		default :
		    $object = getStructure($objectId, $actionId);
		    $nb = $object->process(0);
		}

    return $nb;
    }


function processObjects($objectId, $actionId, $start = 0)
    {
    switch ($actionId)
		{
		case CREATE_MODEL_ACTION:
		    addModels($objectId);
		    break;
		case LINK_MODEL_ACTION:
		    linkModels($objectId);
		    break;
		case RESET_OCS_ACTION:
		    resetOCS($objectId);
		    break;
		case IMPORT_PRINTERS_ACTION:
		    $nb = glpiuImportPrinters(1, $start);
		    break;
		default :
		    $object = getStructure($objectId, $actionId);
		    $object->process(1);
		}
    return true;
    }*/

/*
function addModels($objectId)
    {
    global $LINK_ID_TABLE, $GLPIUMODELTABLE;

    $objectTable = $LINK_ID_TABLE[$objectId];
    $dropdown = $GLPIUMODELTABLE[$objectId];
    $DB = new DB;
    $query = "insert into $dropdown (name) "
	."SELECT distinct $objectTable.name FROM $objectTable "
	."left outer join $dropdown "
	."on $dropdown.name = $objectTable.name "
	."where $dropdown.id is null and $objectTable.is_template = '0'";
    $result = $DB->query($query) or die($DB->error());
    }

function linkModels($objectId)
    {
    global $LINK_ID_TABLE, $GLPIUMODELTABLE;

    $objectTable = $LINK_ID_TABLE[$objectId];
    $dropdown = $GLPIUMODELTABLE[$objectId];
    $DB = new DB;
    $query = "update $objectTable "
	."inner join $dropdown on $dropdown.name = $objectTable.name "
	."set $objectTable.model = $dropdown.ID where $objectTable.is_template = '0'";
    $result = $DB->query($query) or die($DB->error());
    }

function resetOCS($objectId)
    {
    $DBocs = new DBocs($_SESSION["ocs_server_id"]);
    $query = "update HARDWARE set CHECKSUM=".MAX_OCS_CHECKSUM;
    $result = $DBocs->query($query) or die($DB->error());

    $DB = new DB;
    $query = "delete from glpi_ocs_link";
    $result = $DB->query($query) or die($DB->error());
    }
*/
$UC_NAMES=array();		// Noms d'UC / ID GLPI
$PRINTERSLIST = array();

function glpiuFindPrinter($port)
    {
    global $PRINTERSLIST;

    $printer = new PluginUtilitairesPrinter($port);
    }

function glpiuInsertPrinter($port, $name, $UCname, $driver)
    {
    global $PRINTERSLIST;
    $found = 0;
    $printer = new PluginUtilitairesPrinter($port, $name, $UCname, $driver, count($PRINTERSLIST));

    foreach($PRINTERSLIST as &$p)
		{
		if ($p->compare($printer))
		    {
		    $found = 1;
		    $p->update($printer);
		    break;
		    }
		}

    if (!$found)
	$PRINTERSLIST[$printer->printNo] = $printer;
    }


// importation des imprimantes
function glpiuImportPrinters($mode, $start = 0)
    {
    global $PRINTERSLIST, $UC_OCSIDS, $UC_NAMES, $CFG_GLPI, $LANG;
    $DBocs = new DBocs($_SESSION["ocsservers_id"]);
    $DB = new DB;
    $noPort = 0;
    $numrows = 0;
    $UC_OCSIDS=array();		// Correspondance UC ocs/glpi

    // Recherche les ports
    $query_ports = "select distinct hardware.NAME as UC_Name, hardware.DEVICEID, printers.NAME, printers.PORT, printers.DRIVER from printers "
	."inner join hardware on hardware.ID = printers.HARDWARE_ID  where  printers.DRIVER <> '' order by hardware.DEVICEID, printers.NAME, printers.PORT, printers.DRIVER";
    $result_ports = $DBocs->query($query_ports) or die($DBocs->error());

    if ($DBocs->numrows($result_ports)>0)
		{
	
		// Récupère les UC dans GLPI
		$query_UC = "select distinct glpi_id, ocs_id from glpi_ocs_link order by glpi_id";
		$result_UC = $DB->query($query_UC) or die($DB->error());
	
		if ($DB->numrows($result_UC)>0)
			{
			while($data = $DB->fetch_array($result_UC))
				$UC_OCSIDS[$data["ocs_id"]] = array('glpi_id' => $data["glpi_id"], 'UC_Name' => '', 'driver' => '');
			}
	
		unset($result_UC);
	
	
		// Recherche les UC liés à une imprimante dans OCS
		$query_UC = "select distinct hardware.NAME as UC_Name, hardware.ID as ocs_id from hardware "
			."inner join printers on printers.HARDWARE_ID = hardware.ID order by hardware.DEVICEID, hardware.NAME";
		$result_UC = $DBocs->query($query_UC) or die($DBocs->error());
	
		if ($DBocs->numrows($result_UC)>0)
			{
			while($data=$DBocs->fetch_array($result_UC))
				{
				if (array_key_exists($data["ocs_id"], $UC_OCSIDS))
					$UC_OCSIDS[$data["ocs_id"]]['UC_Name'] = strtoupper($data["UC_Name"]);
				}
			}
	
		unset($result_UC);
	
		// Suppression des UC sans imprimante
		// création d'une table des UC avec nom pour clé
	
		foreach($UC_OCSIDS as $ocs_id => &$item)
			{
			if ($item['UC_Name'] === '')
				unset($UC_OCSIDS[$ocs_id]);
			else
				$UC_NAMES[$item['UC_Name']] = array('glpi_id' => $item["glpi_id"], 'ocs_id' => $ocs_id);
			}
	
		unset($UC_OCSIDS);
	
	
		// Création des imprimantes
		while($data = $DBocs->fetch_array($result_ports))
			glpiuInsertPrinter($data["PORT"], $data["NAME"], $data["UC_Name"], $data["DRIVER"]);
	
		unset($result_ports);
		unset($UC_NAMES);
	
		// Si en mode action, suppression des imprimantes
		/*if ($mode == 1 && count($PRINTERSLIST) > 0)
			{
			processObjects(PRINTER_TYPE, DELETE_ACTION);
			}*/
	
		// Récupère les imprimantes dans GLPI
		$printer_names =array();
		if (count($PRINTERSLIST) > 0)
			{
			$query_PRN = "select distinct ID, name from glpi_printers where deleted='0' ";
			$result_PRN = $DB->query($query_PRN) or die($DB->error());
			if ($DB->numrows($result_PRN) > 0)
				{
				while($data = $DB->fetch_array($result_PRN))
					$printer_names[$data["name"]] = $data["ID"];
				}
			}
	
		// Complétion des imprimantes et suppression de celles à ne pas importer 
		$numrows = 0;
		foreach($PRINTERSLIST as $prn_no => &$printer)
			{
			if ($printer->portType <= PORT_TYPE_USB)
				{
				$printer->getName();
				
				if ($printer->server != '' && !isset($printer_names[$printer->name]))
					$numrows ++;
				else
					unset($PRINTERSLIST[$prn_no]);
				
				}
			else
				unset($PRINTERSLIST[$prn_no]);
			}
	
	
		unset($printer_names);
	
		// Importation
		if ($mode == 1 && isset($_POST['toimport']))
			{
			foreach ($_POST['toimport'] as $prn_no => $val)
				{
				if ($val == "on")
					{
					$PRINTERSLIST[$prn_no]->addToDB();
					unset($PRINTERSLIST[$prn_no]);
					$numrows --;
					}
				}
			}
	
		unset ($_POST['toimport']);
	
		// Affichage
		echo "<div align='center'>";
		if ($numrows)
			{
			$readOnly = !haveRight("ocsng","w");
			$parameters = "objectType=".PRINTER_TYPE."&amp;actionId=".IMPORT_PRINTERS_ACTION;
			printPager($start,$numrows,$_SERVER["PHP_SELF"],$parameters);
	
			// delete end 
			array_splice($PRINTERSLIST,$start + $_SESSION["glpilist_limit"]);
			// delete begin
			if ($start > 0)
			array_splice($PRINTERSLIST,0,$start);
	
			echo "<form method='post' name='printer_form' id='printer_form' action='".$_SERVER["PHP_SELF"]."'>";
			echo "<a href='".$_SERVER["PHP_SELF"]."' onclick= \"if ( markAllRows('printer_form') ) return false;\">".$LANG["buttons"][18]."</a>&nbsp;/&nbsp;<a href='".$_SERVER["PHP_SELF"]."' onclick= \"if ( unMarkAllRows('printer_form') ) return false;\">".$LANG["buttons"][19]."</a>";
			echo "<input type='hidden' name='objectType' value='".PRINTER_TYPE."'>";
			echo "<input type='hidden' name='actionId' value='".IMPORT_PRINTERS_ACTION."'>";
			echo "<table class='tab_cadre'>";
			if (!$readOnly)
			echo "<tr class='tab_bg_1'><th colspan=8><input class='submit' type='submit' name='do_action' value='".$LANG["buttons"][37]."'></th></tr>".chr(10);
			echo '<tr><th></th><th>Type</th><th>IP</th><th>Nom</th><th width="150px">Port</th><th>Serveurs</th><th>Files d\'impression</th><th>Erreurs</th></tr>'.chr(10);
	
			foreach($PRINTERSLIST as &$printer)
			$printer->display();
	
			if (!$readOnly)
			echo "<tr class='tab_bg_1'><th colspan=8><input  class='submit' type='submit' name='do_action' value='".$LANG["buttons"][37]."'></th></tr>".chr(10);
			echo "</table>";
			//echo '<br>'.$numrows.'<br>';
			echo "</form>";
	
			printPager($start,$numrows,$_SERVER["PHP_SELF"],$parameters);
			}
		else 
			{
			echo "<strong>".$LANG["ocsng"][9]."<br>";
			echo "<a href='../index.php'>".$LANG["buttons"][13]."</a></strong>";
			}
		echo "</div>";
		}
    return $numrows;
    }

?>
