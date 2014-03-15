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


//************************************************
// Imprimante

class PluginUtilitairesPrinter extends Printer
    {
    var $printNo = 0;
    var $portType = PORT_TYPE_UNKNOWN;
    var $port = '';
    var $portName = '';
    var $IP = '';
    var $fgLPT = 0;
    var $fgCOM = 0;
    var $fgUSB = 0;
    var $error = '';

    var $name = '';
    var $server = '';
    var $driver = '';
    var $id = 0;

    var $queues = array();		// Files d'impression liées à l'imprimante
    var $servers = array();		// Serveurs d'impression
    var $linked = array();		// Ordinateurs liés à l'imprimante

    // Constructeur
    function PluginUtilitairesPrinter($port, $name, $UCname, $driver, $printNo)
		{
		parent::Printer ();
		$this->port = $port;
		$this->explode($name, $UCname, $driver);
		$this->printNo = $printNo;
		}

    // Interprétation des informations d'OCS
    function explode($name, $UCname, $driver)
		{
		global $PORTEREG, $EREGHOST;
	
		$portType = 0;
		$i = 0;
		$match = '';
	
		// Informations de PORT
		foreach ($PORTEREG as $t => $ereg)
			{
			if (preg_match( "/".$ereg."/i", $this->port, $match))
				{
				$portType = $t;
				$this->portType = $portType;
				switch ($portType)
					{
					case PORT_TYPE_IP:
						$this->IP = $match[0];
						if (preg_match( "/".$ereg."\\:/", $this->port, $match))
							$this->portName = preg_replace( "/".$ereg."\\:/", '', $this->port);
						else if (preg_match( "/".$ereg."\\@/", $this->port, $match))
							$this->portName = preg_replace( "/".$ereg."\\@/", '', $this->port);
						else if (preg_match( "/".$ereg."\\ /", $this->port, $match))
							$this->portName = preg_replace( "/".$ereg."\\ /", '', $this->port);
						break;
					case PORT_TYPE_LPT:
						$this->fgLPT = 1;
						break;
					case PORT_TYPE_COM:
						$this->fgCOM = 1;
						break;
					case PORT_TYPE_USB:
						$this->fgUSB = 1;
						break;		
					}
				}
			if ($portType)
				break;
			}
	
		// Cherche un nom d'hôte dans le nom d'imprimante, sinon le port, sinon le port est le nom
	
		$UCname = strtoupper ($UCname);
	
		// Création des files d'impression et serveurs
		// Pas une imprimante réseau et nom d'ordinateur dans le port : infos du port
		if ($this->IP == '' && ($server = $this->extractUC($this->port)) != '')
			$this->addQueue($UCname, $server, $this->port, $driver);
	
		// Nom d'ordinateur dans la file : infos de la file
		else if (($server = $this->extractUC($name)) != '')
			$this->addQueue($UCname, $server, $name, $driver);
	
		// Autres cas : par exemple, serveur d'impression : pas d'UC dans la file
		else
			{
			//$server = strtoupper ($UCname);
			$this->addQueue($UCname, $UCname, $name, $driver);
			}
	
		}

    // Ajout d'une file
    function addQueue($host, $server, $qName, $driver)
		{
		$fgServer = 0;
		if (!array_key_exists($host, $this->queues))
			{
			$this->queues[$host] = new glpiuPrinterQueue($host, $server, $qName, $driver);
			if ($host === $server)
				{
				if (array_key_exists($server, $this->servers))
					$this->servers[$server]->driver = $driver;
				else
					$this->servers[$server] = new glpiuPrinterQueue($server, $server, $qName, $driver);
				$fgServer = 1;
				}
			}
		if (!$fgServer && !array_key_exists($server, $this->servers))
			$this->servers[$server] = new glpiuPrinterQueue($server, $server, $qName, $driver);
		}


    // Compte les files valides
    function countQueues(&$queues)
		{
		$nb = 0;
		foreach($queues as $host=>$queue)
			{
			if (!$queue->error) $nb ++;
			}
		return $nb;
		}

    // Compte les serveurs valides
    function countServers()
		{
		return $this->countQueues(&$this->servers);
		}

    // Compare cette imprimante à une autre
    function compare(&$printer)
		{
		$ret = 0;
		if ($printer->portType === $this->portType)	// pas nécessairement
			{
			// Même adresse IP
			if ($printer->IP != '' && $printer->IP === $this->IP)
			$ret = 1;
	
			// Même serveur et même file d'impression
			if (!$ret && $this->countServers() > 0)
				{
				reset($printer->servers);
				$server = key($printer->servers);
		
				if (array_key_exists($server, $this->servers) 
					&& $printer->servers[$server]->getName() === $this->servers[$server]->getName())
				   $ret = 1;
				}
			}
		return $ret;
		}

    // Met à jour cette imprimante à partir d'une autre
    function update(&$printer)
		{
		foreach($printer->servers as $server=>$queue)
			{
			if (!array_key_exists($server, $this->servers))
				$this->servers[$server] = $queue;
			}
		foreach($printer->queues as $host=>$queue)
			{
			if (!array_key_exists($host, $this->queues))
				$this->queues[$host] = $queue;
			}
		}

	
    // Extrait le nom d'UC du nom de file d'impression
    function extractUC($queue)
		{
		global $EREGHOST;
	
		$match = '';
		$UCname = '';
		if (preg_match($EREGHOST, $queue, $match))
			{
			$UCname = preg_replace("/\/{1,2}/", '', strtoupper ($match[0]));
			$pos = strpos($UCname, '.');
			if ($pos !== FALSE)
				$UCname = substr($UCname, 0, $pos);
			}
		return $UCname;
		}

    // Sélection du serveur ayant le plus de files d'impression de l'imprimante
    function bestServer()
		{
		$bestServer = '';
		$max = 0;
	
		foreach($this->servers as $server=>$queue)
			{
			$nb = 0;
			if (!$queue->error)
				{
				$qn = $queue->getName();
				foreach($this->queues as $host=>$queue1)
					{
					if (!$queue1->error)
						{
						$q1n = $queue1->getName();
						
						if ($qn === $q1n) $nb++;
						}
					}
		
				if ($nb > $max)
					{
					$max = $nb;
					$bestServer = $server;
					}
				}
			}
		$this->server = $bestServer;
		if ($bestServer != '' && ($driver = $this->servers[$bestServer]->driver) != '')
			$this->driver = $driver;
	
		return $bestServer;
		}


    // Crée le nom de l'imprimante
    function getName($force = 0) 
		{
		if ((!$this->name || $force) && $this->countServers())
			{
			$server = $this->bestServer();
			if ($server == '')
				$this->error = 'Pas de serveur';
			else
				$this->name = $this->servers[$server]->getName();
			}
		return $this->name;
		}


    // Affichage
    function display()
		{
		$style = $this->error == '' ? '' : 'style="background-color:red"';
		$border = 'valign=top style="border: 1px solid silver"';
		$check ="all";
		echo '<tr class="tab_bg_2" '.$style.' >';
		echo "<td><input type='checkbox' name='toimport[$this->printNo]' ".($check=="all"?"checked":"")."></td>";
		echo '<td align=center '.$border.'>'
			.$this->portType.'</td><td '.$border.'>&nbsp;'.$this->IP.'</td><td '.$border.'>&nbsp;'.$this->portName
			.'</td><td '.$border.'>&nbsp;'.$this->port.'</td><td '.$border.'>';
	
		// Serveurs
		$i = 0;
		foreach($this->servers as $server=>$queue)
			{
			if (!$i ++) echo '<table width="100%">';
	
			$queue->display($server === $this->server);
			}
		echo $i ? '</table>' : '&nbsp;';
	
		echo '</td><td '.$border.'>';
	
		// Files d'impression
		$i = 0;
		foreach($this->queues as $host=>$queue)
			{
			if (!$i ++) echo '<table width="100%">';
	
			$queue->display($host === $this->server);
			}
		echo $i ? '</table>' : '&nbsp;';
	
		echo '</td><td>'.($this->error == '' ? '&nbsp;' : $this->error).'</td></tr>'.chr(10);
		}


    // Ajoute l'imprimante à la base
    function addToDB() 
		{
		if ($this->getName())
			{
		  $cfg_ocs = getOcsConf($_SESSION["ocs_server_id"]);
	
			parent::getEmpty ();
		
			$this->fields = array();
			$this->fields["name"] = $this->getName();
			$this->fields["comments"] = $this->port.'\n'.$this->error;
			$this->fields["date_mod"] = date("Y-m-d H:i:s");
			$this->fields["is_global"] = 0;
			$this->fields["model"] = ocsImportDropdown("glpi_dropdown_model_printers","name",$this->driver);
	
			if ($this->fgLPT) $this->fields["flags_par"] = 1;
			if ($this->fgCOM) $this->fields["flags_serial"] = 1;
			if ($this->fgUSB) $this->fields["flags_usb"] = 1;
		
			if ($this->id = parent::addToDB())
				{
				if ($cfg_ocs["default_state"])
					updateState(PRINTER_TYPE, $this->id, $cfg_ocs["default_state"],0,0);
		
				// Si connexion directe : connexion avec lien du lieu et du contact
				foreach($this->servers as $server=>&$queue)
					{
					// Si serveur : le lieu et le contact seront ceux du serveur
					if ($server === $this->server)
						$queue->connectToServer($this->id);
					else
						$queue->connectToUC($this->id);
					}
					
				// Connexion des files d'impression
				foreach($this->queues as $host=>&$queue)
					{
					if ($host != $queue->server)
						$queue->connectToUC($this->id);
					}
		
				// Connexion IP
				if ($this->IP != '')
					{
					$netport = array();
					$netport["ifaddr"]=$this->IP;
					$netport["ifmac"]= ''; //$line2["MACADDR"];
					$netport["iface"]=ocsImportDropdown("glpi_dropdown_iface","name","Ethernet");
					$netport["name"]="Ethernet";
					$netport["on_device"]=$this->id;
					$netport["logical_number"]=0;
					$netport["device_type"]=PRINTER_TYPE;
						
					$np = new Netport;
					$np->add($netport);
					}
				}
			}
		return $this->id;
		}
    
    }

?>
