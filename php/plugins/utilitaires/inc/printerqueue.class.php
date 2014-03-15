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


// *********************************************************
// File d'impression

class PluginUtilitairesPrinterQueue
    {
    var $host = '';	// Hôte de la file d'impression
    var $server = '';	// Serveur de la file d'impression
    var $qName = '';	// Nom        "        "
    var $driver = '';	// identifiant OCS de l'hote
    var $glpi_id = 0;	// identifiant GLPI de l'hote

    var $error = 0;

    function PluginUtilitairesPrinterQueue ($host, $server, $qName, $driver)
		{
		$this->update ($host, $server, $qName, $driver);
		}

    function update($host, $server, $qName, $driver)
		{
		global $EREGHOST, $UC_NAMES;
	
		if (isset($server)) $this->server = strtoupper ($server);
		if (isset($qName)) $this->qName = preg_replace($EREGHOST, '', $qName);
		if (isset($driver)) $this->driver = $driver;
		if (isset($host))
			{
			$this->host = strtoupper ($host);
			if (array_key_exists($this->host, $UC_NAMES))
				{
				$this->glpi_id = $UC_NAMES[$this->host]["glpi_id"];
				$this->error = $this->error & ~0X0001;
				}
			else
				$this->error |= 0X0001;
			}
			
	
		if (array_key_exists($this->server, $UC_NAMES))
			$this->error = $this->error & ~0X0002;
		else
			$this->error |= 0X0002;
		}

    function compare($queue)
		{
		return ($this->getName() === $queue->getName());
		}


    function getName()
		{
		return '//'.$this->server.'/'.$this->qName;
		}

    function display($fgServer)
		{
		if ($fgServer)
			{
			$bgColor1 = ' style="background-color:#adffad" ';
			$bgColor2 = $bgColor1;
			}
		else
			{
			$bgColor1 = $this->error & 0X0001 ? ' style="background-color:#ffadad" ' : '';
			$bgColor2 = $this->error & 0X0002 ? ' style="background-color:#ffadad" ' : '';
			}
	
		if ($this->host === $this->server)	// C'est un serveur
			echo '<tr><td align=left width=100px'.$bgColor1.'>'.$this->host.'</td><td align=left'.$bgColor2
			.'>'.$this->qName.'</td></tr>';
		else
			echo '<tr><td align=left width=100px'.$bgColor1.'>'.$this->host.'</td><td align=left'.$bgColor2
			.'>'.$this->getName().'</td></tr>';
		}

    // Connexion à l'UC - Spécifique : pas de mise à jour du lieu ni du contact
    function connectToUC($sID)
		{
		if (!$this->error)
			{
			$connect = new Connection;
			$connect->end1 = $sID;
			$connect->end2 = $this->glpi_id;
			$connect->type = PRINTER_TYPE;
			$connID = $connect->addtoDB();
			}
		}

    function connectToServer($sID)
		{
		if (!$this->error)
			$connID = Connect($sID, $this->glpi_id, PRINTER_TYPE);
		}
    }

?>
