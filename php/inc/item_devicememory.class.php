<?php
/*
 * @version $Id: item_devicememory.class.php 20129 2013-02-04 16:53:59Z moyo $
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
* @since version 0.84
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Relation between item and devices
**/
class Item_DeviceMemory extends Item_Devices {

   static public $itemtype_2 = 'DeviceMemory';
   static public $items_id_2 = 'devicememories_id';

   static protected $notable = false;

   static function getSpecificities() {
      return array('size'   => array('long name'  => sprintf(__('%1$s (%2$s)'), __('Size'),
                                                             __('Mio')),
                                     'short name' => __('Size'),
                                     'size'       => 10),
                   'serial' => array('long name'  => __('Serial number'),
                                     'short name' => __('Serial number'),
                                     'size'       => 20));
   }
}
?>
