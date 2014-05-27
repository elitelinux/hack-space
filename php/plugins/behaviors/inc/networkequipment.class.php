<?php
/**
 * @version $Id: networkequipment.class.php 140 2013-02-27 15:59:09Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet
 @copyright Copyright (c) 2010-2013 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsNetworkEquipment extends PluginBehaviorsCommon {

   static function afterAdd(NetworkEquipment $network) {
      // Toolbox::logDebug("PluginBehaviorsNetworkEquipment::afterAdd(), NetworkEquipment=", $network);
   }


   static function afterUpdate(NetworkEquipment $network) {
      // Toolbox::logDebug("PluginBehaviorsNetworkEquipment::afterUpdate(), NetworkEquipment=", $network);
   }
}
