<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file: Dennis Ploeger <dennis.ploeger@getit.de>
// Purpose of file: RackView en_US localization
// ----------------------------------------------------------------------

// Title

$LANG['plugin_rackview']['title'] = 'RackView';

// Errors

$LANG['plugin_rackview']['error_inserting'] = 'Can not insert object data';
$LANG['plugin_rackview']['error_invaliddepth'] = 'Invalid depth specified';
$LANG['plugin_rackview']['error_invalidhorizontal'] =
    'Invalid horizontal specified';
$LANG['plugin_rackview']['error_invalidid'] = 'Invalid object ID specified';
$LANG['plugin_rackview']['error_invalidmountaction'] =
    'Invalid mount action specified';
$LANG['plugin_rackview']['error_invalidmountid'] =
    'Invalid mount id specified';
$LANG['plugin_rackview']['error_invalidmountsize'] =
    'Invalid mount size specified';
$LANG['plugin_rackview']['error_invalidrackid'] = 'Invalid rack ID specified';
$LANG['plugin_rackview']['error_invalidstartu'] =
    'Invalid starting Unit specified';
$LANG['plugin_rackview']['error_invalidtype'] = 'Invalid object type specified';
$LANG['plugin_rackview']['error_mounting'] = 'Can not mount object';
$LANG['plugin_rackview']['error_remounting'] = 'Can not remount object';
$LANG['plugin_rackview']['error_sizenotnumeric'] = 'Size not numeric!';
$LANG['plugin_rackview']['error_sizetoolarge'] = 'The size is too large!';
$LANG['plugin_rackview']['error_unmounting'] = 'Can not unmount object';
$LANG['plugin_rackview']['error_updating'] = 'Can not update object data';

// Labels

$LANG['plugin_rackview']['label_addmount'] = 'Add mount';
$LANG['plugin_rackview']['label_data'] = 'Data';
$LANG['plugin_rackview']['label_delete'] = 'Unmount';
$LANG['plugin_rackview']['label_mount'] = 'Mounted in rack %s';
$LANG['plugin_rackview']['label_mounted'] = 'Mounted in';
$LANG['plugin_rackview']['label_notmounted'] = 'Not currently mounted';
$LANG['plugin_rackview']['label_noracks'] = 'No racks currently defined';
$LANG['plugin_rackview']['label_selectarack'] = 'Please select a rack';
$LANG['plugin_rackview']['label_summary_title'] = 'Rack summary';
$LANG['plugin_rackview']['label_summary_locations'] = 'Locations';
$LANG['plugin_rackview']['label_update'] = 'Update';
$LANG['plugin_rackview']['label_usedefault'] = 'Use default';

// Fields

$LANG['plugin_rackview']['help_depth'] = 'Depth of object in rack';
$LANG['plugin_rackview']['field_depth'] = 'Depth';

$LANG['plugin_rackview']['help_description'] = 'Decriptive text';
$LANG['plugin_rackview']['field_description'] = 'Description';

$LANG['plugin_rackview']['help_horizontal'] = 'Horizontal location in rack';
$LANG['plugin_rackview']['field_horizontal'] = 'Horizontal';

$LANG['plugin_rackview']['help_location'] = 'Location of rack';
$LANG['plugin_rackview']['field_location'] = 'Location';

$LANG['plugin_rackview']['help_mount_size'] =
    'Size ob object in rack (in rack units)';
$LANG['plugin_rackview']['field_mount_size'] = 'Size';

$LANG['plugin_rackview']['help_name'] = 'Name (short description) of object';
$LANG['plugin_rackview']['field_name'] = 'Name';

$LANG['plugin_rackview']['help_rack'] = 'Rack the object is located in';
$LANG['plugin_rackview']['field_rack'] = 'Rack';

$LANG['plugin_rackview']['help_size'] = 'Size of object in rack units';
$LANG['plugin_rackview']['field_size'] = 'Size';

$LANG['plugin_rackview']['help_startu'] = 'Starting (lower) rack unit of mount';
$LANG['plugin_rackview']['field_startu'] = 'Starting unit';

// Options

$LANG['plugin_rackview']['option_depth_back'] = 'Back';
$LANG['plugin_rackview']['option_depth_front'] = 'Front';
$LANG['plugin_rackview']['option_depth_full'] = 'Full';

$LANG['plugin_rackview']['option_horizontal_center'] = 'Center';
$LANG['plugin_rackview']['option_horizontal_full'] = 'Full';
$LANG['plugin_rackview']['option_horizontal_left'] = 'Left';
$LANG['plugin_rackview']['option_horizontal_right'] = 'Right';

// Types

$LANG['plugin_rackview']['type_rack'] = 'Rack';

// Massive Actions

$LANG['plugin_rackview']['massiveaction_print'] = 'Print';