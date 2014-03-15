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
// Purpose of file: RackView de_DE localization
// ----------------------------------------------------------------------

// Title

$LANG['plugin_rackview']['title'] = 'RackView';

// Errors

$LANG['plugin_rackview']['error_inserting'] = 'Konnte Daten nicht einfügen';
$LANG['plugin_rackview']['error_invaliddepth'] = 'Ungültige Tiefe angegeben';
$LANG['plugin_rackview']['error_invalidhorizontal'] =
    'Ungültige horizontale Ausrichtung angegeben';
$LANG['plugin_rackview']['error_invalidid'] = 'Ungültige Object-ID';
$LANG['plugin_rackview']['error_invalidmountaction'] =
    'Ungültige Einbau-Aktion angegeben';
$LANG['plugin_rackview']['error_invalidmountid'] =
    'Ungültige Einbau-ID';
$LANG['plugin_rackview']['error_invalidmountsize'] =
    'Ungültige Größe angegeben';
$LANG['plugin_rackview']['error_invalidrackid'] = 'Ungültige Rack-ID angegeben';
$LANG['plugin_rackview']['error_invalidstartu'] =
    'Ungültige Starthöhe angegeben';
$LANG['plugin_rackview']['error_invalidtype'] =
    'Ungültigen Objekttyp angegeben';
$LANG['plugin_rackview']['error_mounting'] = 'Konnte Objekt nicht einbauen';
$LANG['plugin_rackview']['error_remounting'] = 'Konnte Objekt nicht umbauen';
$LANG['plugin_rackview']['error_sizenotnumeric'] = 'Größe nicht numerisch!';
$LANG['plugin_rackview']['error_sizetoolarge'] = 'Größe zu viel!';
$LANG['plugin_rackview']['error_unmounting'] = 'Konnte Objekt nicht ausbauen';
$LANG['plugin_rackview']['error_updating'] =
    'Konnte Objektdaten nicht aktualisieren';

// Labels

$LANG['plugin_rackview']['label_addmount'] = 'Objekt einbauen';
$LANG['plugin_rackview']['label_data'] = 'Daten';
$LANG['plugin_rackview']['label_delete'] = 'Ausbauen';
$LANG['plugin_rackview']['label_mount'] = 'Eingebaut in Rack %s';
$LANG['plugin_rackview']['label_mounted'] = 'Eingebaut in';
$LANG['plugin_rackview']['label_notmounted'] = 'Zur Zeit nicht eingebaut';
$LANG['plugin_rackview']['label_noracks'] = 'Keine Racks vorhanden';
$LANG['plugin_rackview']['label_selectarack'] = 'Bitte ein Rack angeben';
$LANG['plugin_rackview']['label_summary_title'] = 'Rack Übersicht';
$LANG['plugin_rackview']['label_summary_locations'] = 'Standorte';
$LANG['plugin_rackview']['label_update'] = 'Aktualisieren';
$LANG['plugin_rackview']['label_usedefault'] = 'Standard';

// Fields

$LANG['plugin_rackview']['help_depth'] = 'Tiefe des Objektes im Rack';
$LANG['plugin_rackview']['field_depth'] = 'Tiefe';

$LANG['plugin_rackview']['help_description'] = 'Bemerkungen';
$LANG['plugin_rackview']['field_description'] = 'Bemerkungen';

$LANG['plugin_rackview']['help_horizontal'] = 'Horizontale Position im Rack';
$LANG['plugin_rackview']['field_horizontal'] = 'Horizontal';

$LANG['plugin_rackview']['help_location'] = 'Standort des Racks';
$LANG['plugin_rackview']['field_location'] = 'Standort';

$LANG['plugin_rackview']['help_mount_size'] =
    'Größe des Objets im Rack (in Höheneinheiten)';
$LANG['plugin_rackview']['field_mount_size'] = 'Größe';

$LANG['plugin_rackview']['help_name'] = 'Name (Kurzbeschreibung) des Objekts';
$LANG['plugin_rackview']['field_name'] = 'Name';

$LANG['plugin_rackview']['help_rack'] =
    'Rack, in dem das Objekt eingebaut ist oder eingebaut werden soll';
$LANG['plugin_rackview']['field_rack'] = 'Rack';

$LANG['plugin_rackview']['help_size'] = 'Größe des Objekts in Höheneinheiten';
$LANG['plugin_rackview']['field_size'] = 'Größe';

$LANG['plugin_rackview']['help_startu'] = 'Starteinheit (unterste) des Objekts';
$LANG['plugin_rackview']['field_startu'] = 'Starteinheit';

// Options

$LANG['plugin_rackview']['option_depth_back'] = 'Hinten';
$LANG['plugin_rackview']['option_depth_front'] = 'Vorne';
$LANG['plugin_rackview']['option_depth_full'] = 'Komplett';

$LANG['plugin_rackview']['option_horizontal_center'] = 'Mitte';
$LANG['plugin_rackview']['option_horizontal_full'] = 'Komplett';
$LANG['plugin_rackview']['option_horizontal_left'] = 'Links';
$LANG['plugin_rackview']['option_horizontal_right'] = 'Rechts';

// Types

$LANG['plugin_rackview']['type_rack'] = 'Rack';

// Massive Actions

$LANG['plugin_rackview']['massiveaction_print'] = 'Drucken';