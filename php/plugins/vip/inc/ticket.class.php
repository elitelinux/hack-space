<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginVipTicket extends CommonDBTM {

	static function canCreate() {

	      return plugin_vip_haveRight('config', 'w');
	}
	
	static function canView() {

	      return plugin_vip_haveRight('config', 'r');
	}

	static function plugin_vip_item_add(Ticket $ticket) {
		global $DB;

		$ticketid = $ticket->getField('id');
		$vipticket = self::isTicketVip($ticketid);
		$vipdbticket = "";

		$ticketquery = "SELECT isvip
                          FROM glpi_plugin_vip_tickets
                         WHERE id = ".$ticketid;

        $vipticketres = $DB->query($ticketquery);
        $vipdbticket = mysqli_fetch_object($vipticketres);

        if ($vipticket) {
			$vipdbquery = "INSERT INTO glpi_plugin_vip_tickets
                           VALUES " . $ticketid . ",1";
        } else {
            $vipdbquery = "INSERT INTO glpi_plugin_vip_tickets
                           VALUES " . $ticketid . ",0";
        }
        $updatevipdb = $DB->query($vipdbquery);
	}

	static function plugin_vip_item_update(Ticket $ticket) {
		global $DB;

		$ticketid = $ticket->getField('id');
		$vipticket = self::isTicketVip($ticketid);
		$vipdbticket = "";

		$ticketquery = "SELECT isvip
						  FROM glpi_plugin_vip_tickets
				   		 WHERE id = ".$ticketid;

		$vipticketres = $DB->query($ticketquery);
		$vipdbticket = mysqli_fetch_object($vipticketres);

		if ($vipticket) {
            $vipdbquery = "INSERT INTO glpi_plugin_vip_tickets
                           		VALUES (".$ticketid.",1)
               ON DUPLICATE KEY UPDATE isvip = 1";
		} else {
        	$vipdbquery = "INSERT INTO glpi_plugin_vip_tickets
                                VALUES (".$ticketid.",0)
               ON DUPLICATE KEY UPDATE isvip = 0";
		}
		$updatevipdb = $DB->query($vipdbquery);
	}

	static function plugin_vip_item_update_user(Ticket_User $ticket) {
		global $DB;

		$ticketid = $ticket->getField('tickets_id');
		$vipticket = self::isTicketVip($ticketid);
		$vipdbticket = "";

		$ticketquery = "SELECT isvip
						  FROM glpi_plugin_vip_tickets
				   		 WHERE id = ".$ticketid;

		$vipticketres = $DB->query($ticketquery);
		$vipdbticket = mysqli_fetch_object($vipticketres);

		if ($vipticket) {
            $vipdbquery = "UPDATE glpi_plugin_vip_tickets
                              SET isvip = 1
                            WHERE id = ".$ticketid;
		} else {
        	$vipdbquery = "UPDATE glpi_plugin_vip_tickets
                              SET isvip = 0
                            WHERE id = ".$ticketid;
		}
		$updatevipdb = $DB->query($vipdbquery);
	}

	static function plugin_vip_item_delete(Ticket $ticket) {
		global $DB;

		$ticketid = $ticket->getField('id');

		$delticketquery = "DELETE FROM glpi_plugin_vip_tickets
								 WHERE id = ". $ticketid;

		$delvipticket = $DB->query($delticketquery);

	}

	static function isUserVip($uid) {
		global $DB;

		$vipquery = "SELECT count(*) AS nb
					   FROM glpi_groups_users
					   JOIN glpi_plugin_vip_groups
					     ON glpi_plugin_vip_groups.id = glpi_groups_users.groups_id
				      WHERE glpi_plugin_vip_groups.isvip = 1
				        AND glpi_groups_users.users_id = ".$uid;

		$vipresult = $DB->query($vipquery);
		$isvip = mysqli_fetch_object($vipresult)->nb;

		if ($isvip) {
			return true;
        }   
    	return false;
	}

	static function isTicketVip($ticketid) {
		global $DB;

		$isvip = false;

		$userquery  = "SELECT users_id
						 FROM glpi_tickets_users
						WHERE type = 1
						  AND tickets_id = ".$ticketid;
		$userresult = $DB->query($userquery);

		while ($uids = mysqli_fetch_object($userresult)) {
			foreach ($uids as $uid) {
				$isuservip = self::isUserVip($uid);
				if ($isuservip) {
            		$isvip = true;
        		}         
			}
		}
        return $isvip;
	}

	static function updateVipDb() {
		global $DB;

		$query = "SELECT id FROM glpi_plugin_vip_tickets";
	    $res = $DB->query($query);

		$count = 0;

	    while ($ticketids = mysqli_fetch_object($res)) {
	        foreach ($ticketids as $ticketid) {
  				$vipticket = self::isTicketVip($ticketid);
		        $vipdbticket = ""; 
		
		        $ticketquery = "SELECT isvip
		                          FROM glpi_plugin_vip_tickets
		                         WHERE id = ".$ticketid;
		
		        $vipticketres = $DB->query($ticketquery);
		        $vipdbticket = mysqli_fetch_object($vipticketres);
		
		        if ($vipticket) {
					if (!$vipdbticket->isvip) {
		            	$vipdbquery = "UPDATE glpi_plugin_vip_tickets
										  SET isvip = 1
		            	                WHERE id = ".$ticketid;
		       			$updatevipdb = $DB->query($vipdbquery);
						$count++;
					}
				}
				else {
					if ($vipdbticket->isvip) {
						$vipdbquery = "UPDATE glpi_plugin_vip_tickets
										  SET isvip = 0
										WHERE id = ".$ticketid;
						$updatevipdb = $DB->query($vipdbquery);
						$count++;
					}
				}
	        }
	    }
		Session::addMessageAfterRedirect("VIP: ". $count . ' tickets updated.');
	}
}
?>
