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
// Purpose of file: Rack type
// ----------------------------------------------------------------------

class PluginRackviewRack extends CommonDBTM {

    public $dohistory=true;

    /**
     * Build up the HTML of a single Cell inside a rack
     *
     * @param array $row Cell-data
     * @param int $colspan Colspan (for specifying full horizontal stack)
     * @param bool $mini Show a minimized version of the rack
     * @return String the HTML of the cell
     */

    function buildCell($row, $colspan = 1, $mini) {

        $out = "";

        $imagefill = '';

        if (array_key_exists(0, $row)) {

            // Row is full in terms of depth

            $row = $row[0];

            list($rowName, $rowDescription) = $this->buildCellName($row, $mini);

            $out .= '<td colspan="' .
                $colspan .
                '" title="' .
                $rowDescription .
                '">' .
                $imagefill .
                $rowName .
                '</td>';

        } else {

            // One or two depth levels are given

            $out .= '<td colspan="' .
                $colspan .
                '">';
            $out .= '<table class="rackview_depth">';
            $out .= '<tr>';

            if (array_key_exists(2, $row)) {

                list($rowName, $rowDescription) = $this->buildCellName(
                    $row[2],
                    $mini
                );

                $out .= '<td title="' .
                    $rowDescription .
                    '" class="back">' .
                    $imagefill .
                    $rowName .
                    '</td>';

            } else {

                // Empty back

                $out .= '<td class="back">';
                $out .= $imagefill;

                if (!$mini) {

                    $out .= '&nbsp;';

                }

                $out .= '</td>';

            }

            $out .= '</tr>';
            $out .= '<tr>';

            if (array_key_exists(1, $row)) {

                list($rowName, $rowDescription) = $this->buildCellName(
                    $row[1],
                    $mini
                );

                $out .= '<td title="' .
                    $rowDescription .
                    '" class="front">' .
                    $imagefill .
                    $rowName .
                    '</td>';

            } else {

                // Empty front

                $out .= '<td class="front">';
                $out .= $imagefill;

                if (!$mini) {

                    $out .= '&nbsp;';

                }

                $out .= '</td>';

            }

            $out .= '</tr>';
            $out .= '</table>';


            $out .= '</td>';

        }

        return $out;

    }

    /**
     * Build up a unified rack cell description and name
     *
     * @param array $row Row with cell information
     * @param bool $mini Show a minimized version of the rack
     * @return array Array consisting of Name and Description
     */

    function buildCellName($row, $mini) {

        $rowDescription = $row['description'];
        $rowName = '<a href="' .
            Toolbox::getItemTypeFormURL($row['object_type']) .
            '?id=' .
            $row['object_id'] .
            '">' .
            $row['object']['name'] .
            '</a>';

        if ($row['object']['is_deleted'] == 1) {

            $rowName = '<del>' .
                $rowName .
                '</del>';

        }

        if ($mini) {

            if ($rowDescription != "") {

                $rowDescription = sprintf(
                    '%s (%s)',
                    $row['object']['name'],
                    $rowDescription
                );

            } else {

                $rowDescription = $row['object']['name'];

            }

            if ($row['object']['is_deleted'] == 1) {

                $rowDescription = '<del>' .
                    $rowDescription .
                    '</del>';

            }

            $rowName = '';

        }

        return array($rowName, $rowDescription);

    }

    /**
     * Build up the display HTML of a rack
     *
     * @param bool $mini Show a minimized version of the rack
     * @param String $startuField Enable clicking on a row to set a "startu"
     *                            input field. Refers to the id of the input
     *                            field
     * @return bool|String  The HTML to display the rack or false
     */

    public function buildRack($mini, $startuField = "") {

        global $DB;

        $imagefill = '';

        if ($this->isNewItem()) {

            // Don't graph a undefined new rack

            return false;

        }

        // Get all mounts regarding this rack sorted by starting unit

        $mounts = $DB->request(
            "SELECT * " .
                "FROM glpi_plugin_rackview_mount " .
                "WHERE rack_id = " .
                $this->fields['id'] .
                " ORDER BY startu desc"
        );

        $mountedUnits = array();

        while ($mount = $mounts->next()) {

            // Get object

            $object = $DB->request(
                getTableForItemType($mount['object_type']),
                array(
                    'id' => $mount['object_id']
                )
            );

            if ($object->numRows() == 0) {

                Html::displayErrorAndDie(
                    'Cannot find object with type ' .
                        $mount['object_type'] .
                        ' and id ' .
                        $mount['object_id']
                );

            }

            $object = $object->next();

            if (
                (isset($mount['mount_size'])) &&
                (!is_null($mount['mount_size']))
            ) {

                $object['rackview_size'] = $mount['mount_size'];

            } else {

                // Get size from object

                $objectSize = $DB->request(
                    'glpi_plugin_rackview_object',
                    array(
                        'object_type' => $mount['object_type'],
                        'object_id' => $mount['object_id']
                    )
                );

                if ($objectSize->numrows() == 0) {

                    // Object has no size specified. Skip.

                    continue;

                }

                $objectSize = $objectSize->next();

                $object['rackview_size'] = $objectSize['size'];

            }

            if ($object['rackview_size'] == 0) {

                // Object has no size specified. Skip.

                continue;

            }

            $mount['object'] = $object;

            for (
                $currentUnit = $mount['startu'];
                $currentUnit < $mount['startu'] + $object['rackview_size'];
                $currentUnit = $currentUnit + 1
            ) {

                if (!isset($mountedUnits[$currentUnit])) {

                    $mountedUnits[$currentUnit] = array();

                }

                if (!isset($mountedUnits[$currentUnit][$mount['horizontal']])) {

                    $mountedUnits[$currentUnit][$mount['horizontal']] = array();

                }

                $mountedUnits[
                $currentUnit
                ][
                $mount['horizontal']
                ][
                $mount['depth']
                ] = $mount;

            }

        }

        // Build rack

        $out = "";

        $out .= '<table class="rackview_rack';

        if ($mini) {

            $out .= ' rackview_mini';

        }

        $out .= '">';
        $out .= '<tr>';
        $out .= '<th colspan="4" title="' .
            $this->fields['description'] .
            '">' .
            $this->fields['name'] .
            '</th>';
        $out .= '</tr>';

        for (
            $currentUnit = $this->fields['size'];
            $currentUnit > 0;
            $currentUnit = $currentUnit -1
        ) {

            $out .= '<tr';

            if ($startuField != "") {

                $out .= ' onClick="' .
                    'document.getElementById(\'' .
                    $startuField .
                    '\').value = \'' .
                    $currentUnit .
                    '\'" style="cursor: pointer"';

            }

            $out .= '>';

            $out .= '<td class="rackview_rownumber">' .
                $currentUnit .
                '</td>';

            if (array_key_exists($currentUnit, $mountedUnits)) {

                $row = $mountedUnits[$currentUnit];

                if (array_key_exists(0, $row)) {

                    // Row is full horizontally

                    $row = $row[0];

                    $out .= $this->buildCell($row, 3, $mini);

                } else {

                    if (array_key_exists(1, $row)) {

                        $out .= $this->buildCell($row[1], 1, $mini);

                    } else {

                        // Empty left

                        $out .= '<td class="rackview_empty">' .
                                $imagefill .
                                '&nbsp;</td>';

                    }

                    if (array_key_exists(2, $row)) {

                        $out .= $this->buildCell($row[2], 1, $mini);

                    } else {

                        // Empty center

                        $out .= '<td class="rackview_empty">' .
                                $imagefill .
                                '&nbsp;</td>';

                    }

                    if (array_key_exists(3, $row)) {

                        $out .= $this->buildCell($row[3], 1, $mini);

                    } else {

                        // Empty right

                        $out .= '<td class="rackview_empty">' .
                                $imagefill .
                                '&nbsp;</td>';

                    }

                }

            } else {

                // Empty row

                for ($i = 0; $i < 3; $i++) {

                    $out .= '<td class="rackview_empty">' .
		        $imagefill .
		        '&nbsp;</td>';
       
                }

            }

            $out .= '</tr>';

        }

        $out .= '</table>';

        return $out;

    }

    /**
     * Can the user create a rack?
     *
     * @return bool|booleen Yes/No
     */

    static function canCreate() {
        return plugin_rackview_haveRight('rack', 'w');
    }

    /**
     * Can the user view a rack?
     *
     * @return bool|booleen Yes/No
     */

    static function canView() {
        return plugin_rackview_haveRight('rack', 'r');
    }

    function defineTabs($options=array()) {
        global $LANG, $CFG_GLPI;

        $ong = array();
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Note', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    static function displayTabContentForItem(
        $item,
        $tabnum=1,
        $withtemplate=0
    ) {

        if (in_array($item->getType(), rackview_get_supported_types())) {

            PluginRackviewRack::rackview_data($item, $item->getId() , $withtemplate);

        }

    }


    /**
     * Return the search options for a rack
     *
     * @return array Search Options
     */

    function getSearchOptions() {
        global $LANG;

        $tab = array();

        $tab['common']=$LANG['plugin_rackview']["title"];

        $tab[1]['table']=$this->getTable();
        $tab[1]['field']='name';
        $tab[1]['linkfield']='name';
        $tab[1]['name']=$LANG['plugin_rackview']['field_name'];
        $tab[1]['datatype']='itemlink';
        $tab[1]['itemlink_type']=$this->getType();

        $tab[2]['table']=$this->getTable();
        $tab[2]['field']='description';
        $tab[2]['linkfield']='description';
        $tab[2]['name']=$LANG['plugin_rackview']['field_description'];

        $tab[3]['table']='glpi_locations';
        $tab[3]['field']='completename';
        $tab[3]['linkfield']='locations_id';
        $tab[3]['name']=$LANG["common"][15];

        $tab[4]['table']=$this->getTable();
        $tab[4]['field']='size';
        $tab[4]['linkfield']='size';
        $tab[4]['name']=$LANG['plugin_rackview']['field_size'];

        $tab[5]['table']=$this->getTable();
        $tab[5]['field']='notepad';
        $tab[5]['name']=$LANG["title"][37];

        return $tab;
    }

    function getTabNameForItem($item) {

        global $LANG;

        if (in_array($item->getType(), rackview_get_supported_types())) {

            if (!$item->getId()) {

                // Don't react on new objects

                return false;
            }

            if (!plugin_rackview_haveRight('object', 'r')) {

                // Security check

                return false;

            }

            return array(

                1 => $LANG['plugin_rackview']['title']

            );

        }

        return false;

    }

    /**
     * Return type description
     *
     * @return string Description
     */

    static function getTypeName() {
        global $LANG;

        return $LANG['plugin_rackview']['type_rack'];
    }

    /**
     * Main frontend data for object modification
     *
     * @param CommonDBTM $item Item to Display
     * @param int $ID
     * @param int $withtemplate
     */

    static function rackview_data($item, $ID, $withtemplate = 0) {
        global $CFG_GLPI, $DB, $LANG;

        if (!$withtemplate) {

            print '<table class="tab_cadre_fixe">';
            print '<tr class="tab_bg_2">';

            print '<th colspan="2">' .
                $LANG['plugin_rackview']['title'] .
                '</th>';

            print '</tr>';

            print '<tr class="tab_bg_2" valign="top">';

            // Show rack, if all informations have been correctly set

            $result = $DB->request(
                "glpi_plugin_rackview_mount",
                array(
                    "object_type" => $item->getType(),
                    "object_id" => $item->getID()
                )
            );

            if ($result->numrows() > 0) {

                // Display rack(s)

                print '<td width="50%">';

                while ($mount = $result->next()) {

                    print '<table>';
                    print '<tr valign="top">';
                    print '<td>';

                    // Show rack

                    $rack = new PluginRackviewRack();

                    $rack->getFromDB($mount['rack_id']);

                    print '<div id="rackview_rack_' .
                        $mount['id'] .
                        '" class="CSSTableGenerator">';
                    $rack->showRack(
                        true,
                        'rackview_startu_' . $mount['id']
                    );
                    print '</div>';

                    print '</td>';
                    print '<td>';

                    // Show mount toolbox

                    PluginRackviewRack::rackview_mount_toolbox(
                        $item,
                        'remount_' . $mount['id'],
                        'remount',
                        $mount,
                        true
                    );

                    print '</td>';
                    print '</tr>';
                    print '</table>';

                }

            } else {

                print '<td width="50%">';
                print '<div>' .
                    $LANG['plugin_rackview']['label_notmounted'] .
                    '</div>';

            }

            // Show racks to mount

            $result = $DB->request(
                "glpi_plugin_rackview_racks"
            );

            if ($result->numrows() == 0) {

                // no racks have been defined

                print '<div>' .
                    $LANG['plugin_rackview']['label_noracks'] .
                    '</div>';

            } else {

                // Build "mount" toolbox

                print '<table>';
                print '<tr>';
                print '<td>';
                print '<div id="rackview_rack_new" class="CSSTableGenerator">' .
                    $LANG['plugin_rackview']['label_selectarack'] .
                    '</div>';
                print '</td>';
                print '<td>';

                PluginRackviewRack::rackview_mount_toolbox($item);

                print '</td>';
                print '</tr>';
                print '</table>';

            }

            print '</td>';

            // Display rack informations

            $result = $DB->request(
                "glpi_plugin_rackview_object",
                array(
                    "object_type" => $item->getType(),
                    "object_id" => $item->getID()
                )
            );

            $rack_object = array(
                "size" => "",
                "object_type" => $item->getType(),
                "object_id" => $item->getID()
            );

            if ($result->numrows() > 0) {

                $rack_object = $result->next();

            }

            print '<td width="50%">';

            // Form

            print '<form name="form" method="post" action="' .
                $CFG_GLPI["root_doc"] .
                '/plugins/rackview/front/plugin_rackview_setobject.php">';

            print '<input type="hidden" name="object_type" '.
                'value="' .
                $rack_object['object_type'] .
                '" />';

            print '<input type="hidden" name="object_id" '.
                'value="' .
                $rack_object['object_id'] .
                '" />';

            print '<table class="tab_cadre_fixe" style="width: 100%">';
            print '<tr class="tab_bg_1">';
            print '<th colspan="2">' .
                $LANG['plugin_rackview']['label_data'] .
                '</th>';

            foreach (array("size") as $field) {

                print '<tr class="tab_bg_1">';

                print '<td title="' .
                    $LANG['plugin_rackview']['help_' . $field] .
                    '">' .
                    $LANG['plugin_rackview']['field_' . $field] .
                    '</td>';

                print '<td>';

                print '<input type="text" name="rackview_size" size="2" ' .
                    'value="' .
                    $rack_object[$field] .
                    '" />';

                print '</td>';

                print '</tr>';

            }

            print '<tr>';
            print '<td colspan="2" align="center">' .
                '<input type="submit" class="submit" value="' .
                $LANG['plugin_rackview']['label_update'] .
                '" /></td>';
            print '</tr>';

            print '</table>';

            Html::closeForm();

            print '</td>';

            print '</tr>';
            print '</table>';

        }

    }

    /**
     * Display the mount toolbox
     *
     * @param CommonDBTM $item        Item referenced with the mount
     * @param string     $formName    Name of the form
     * @param string     $mountAction Action to perform
     * @param array      $data        Default data (if modified)
     * @param bool       $showDelete  Display "Delete" action button
     */

    static function rackview_mount_toolbox(
        $item,
        $formName = "mountForm",
        $mountAction = "mount",
        $data = array(),
        $showDelete = false
    ) {

        global $CFG_GLPI,
               $DB,
               $LANG;

        print '<div>';
        print '<form name="' .
            $formName .
            '" method="post" action="' .
            $CFG_GLPI['root_doc'] .
            '/plugins/rackview/front/plugin_rackview_mount.php">';

        print '<table class="tab_cadre_fixe" style="width: 100%">';

        print '<input type="hidden" name="mount_action" value="' .
            $mountAction .
            '" />';
        print '<input type="hidden" name="object_type" '.
            'value="' .
            $item->getType() .
            '" />';

        print '<input type="hidden" name="object_id" '.
            'value="' .
            $item->getID() .
            '" />';

        if (isset($data['id'])) {

            print '<input type="hidden" name="mount_id" '.
                'value="'.
                $data['id'] .
                '" />';

        }

        print '<tr class="tab_bg_1">';

        $title = $LANG['plugin_rackview']['label_addmount'];

        if (isset($data['rack_id'])) {

            // Get rack name

            $table = getTableForItemType('PluginRackviewRack');

            $rack = $DB->request(
                $table,
                array(
                    'id' => $data['rack_id']
                )
            );

            if ($rack->numrows() == 0) {

                Html::displayErrorAndDie(
                    $LANG['plugin_rackview']['error_invalidrackid']
                );

            }

            $rack = $rack->next();

            $rackLink = sprintf(
                '<a href="%s?id=%s">%s</a>',
                Toolbox::getItemTypeFormURL('PluginRackviewRack'),
                $data['rack_id'],
                $rack['name']
            );

            $title = sprintf(
                $LANG['plugin_rackview']['label_mount'],
                $rackLink
            );

        }

        print '<th colspan="2">' .
            $title .
            '</th>';
        print '</tr>';

        // Rack

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_rack'] .
            '">' .
            $LANG['plugin_rackview']['field_rack'] .
            '</td>';
        print '<td>';

        $value = -1;

        if (isset($data['rack_id'])) {

            $value = $data['rack_id'];

        }

        $mount_id = -1;

        if (isset($data['id'])) {

            $mount_id = $data['id'];

        }

        $startuCode = 'rackview_startu_';

        if (isset($data['id'])) {

            $startuCode .= $data['id'];

        } else {

            $startuCode .= 'new';

        }

        $onChangeCode = sprintf(
            'rackview_display_rack("%s", "id", "%s", "plugin_rackview_racks_id", '.
            '%d, "mini=true&startu=%s", "%s")',
            $CFG_GLPI["root_doc"] .
            '/plugins/rackview/ajax/ajax_show_rack.php',
            $formName,
            $mount_id,
            $startuCode,
            $LANG['plugin_rackview']['label_selectarack']
        );

        Dropdown::show(
            'PluginRackviewRack',
            array(
                'value' => $value,
                'on_change' => $onChangeCode
            )
        );

        print '</td>';
        print '</tr>';

        // Start-U

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_startu'] .
            '">' .
            $LANG['plugin_rackview']['field_startu'] .
            '</td>';
        print '<td>';

        $startuId = 'new';

        if (isset($data['id'])) {

            $startuId = $data['id'];

        }

        print '<input type="text" name="startu" id="' .
            'rackview_startu_' . $startuId .
            '" maxlength="4" ';

        if (isset($data['startu'])) {

            print 'value="' .
                $data['startu'] .
                '"';

        }

        print '/>';
        print '</td>';
        print '</tr>';

        // Mount-Size

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_mount_size'] .
            '">' .
            $LANG['plugin_rackview']['field_mount_size'] .
            '</td>';
        print '<td>';

        print '<input type="checkbox" name="use_default" value="true"';

        if (
            (!isset($data['mount_size'])) ||
            (is_null($data['mount_size']))
        ) {

            print ' checked="checked"';

        }

        $mount_size_id = 'new';

        if (isset($data['id'])) {

            $mount_size_id = $data['id'];

        }

        print sprintf(
            'onClick = "rackview_toggle(this, \'rackview_mount_size_%s\')"',
            $mount_size_id
        );

        print '/> ' .
            $LANG['plugin_rackview']['label_usedefault'] .
            '&nbsp;';

        print sprintf(
            '<input type="text" name="mount_size" id="rackview_mount_size_%s" ' .
            'maxlength="4" ',
            $mount_size_id
        );

        if (
            (isset($data['mount_size'])) &&
            (!is_null($data['mount_size']))
        ) {

            print 'value="' .
                $data['mount_size'] .
                '"';

        } else {

            print 'disabled="disabled"';

        }

        print '/>';
        print '</td>';
        print '</tr>';

        // Depth

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_depth'] .
            '">' .
            $LANG['plugin_rackview']['field_depth'] .
            '</td>';
        print '<td>';
        print '<select name="depth">';

        $value = 0;

        if (isset($data['depth'])) {

            $value = $data['depth'];

        }

        print '<option value="0"';

        if ($value == 0) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_depth_full'] .
            '</option>';
        print '<option value="1"';

        if ($value == 1) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_depth_front'] .
            '</option>';

        print '<option value="2"';

        if ($value == 2) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_depth_back'] .
            '</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        // Horizontal

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_horizontal'] .
            '">' .
            $LANG['plugin_rackview']['field_horizontal'] .
            '</td>';
        print '<td>';
        print '<select name="horizontal">';

        $value = 0;

        if (isset($data['horizontal'])) {

            $value = $data['horizontal'];

        }

        print '<option value="0"';

        if ($value == 0) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_horizontal_full'] .
            '</option>';
        print '<option value="1"';

        if ($value == 1) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_horizontal_left'] .
            '</option>';

        print '<option value="2"';

        if ($value == 2) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_horizontal_center'] .
            '</option>';

        print '<option value="3"';

        if ($value == 3) {
            print 'selected="selected"';
        }

        print '>' .
            $LANG['plugin_rackview']['option_horizontal_right'] .
            '</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        // Description

        print '<tr>';
        print '<td title="' .
            $LANG['plugin_rackview']['help_description'] .
            '">' .
            $LANG['plugin_rackview']['field_description'] .
            '</td>';
        print '<td>';

        $value = '&nbsp;';

        if (isset($data['description'])) {

            $value = $data['description'];

        }

        print '<textarea name="description" cols="40" rows="4">' .
            $value .
            '</textarea>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td colspan="2" align="center">';

        // Submit

        print '<input type="submit" class="submit" value="' .
            $LANG['plugin_rackview']['label_update'] .
            '" />';

        if ($showDelete) {

            // Delete

            print '&nbsp;<input type="button" onClick="';

            print 'document.' .
                $formName .
                '.mount_action.value = \'unmount\'; ' .
                'document.' .
                $formName .
                '.submit();';

            print '" class="submit" value="' .
                $LANG['plugin_rackview']['label_delete'] .
                '" />';

        }

        print '</td>';
        print '</tr>';
        print '</table>';

        Html::closeForm();

        print '</div>';

    }

    /**
     * Show rack UI form
     *
     * @param       $ID      ID of object
     * @param array $options additional options
     * @return bool If an error occurs
     */

    function showForm($ID, $options = array()) {
        global $CFG_GLPI, $LANG;

        if (!$this->canView()) {
            return false;
        }

        if ($ID > 0) {
            $this->check($ID, 'r');
        } else {
            $this->check(-1, 'w');
            $this->getEmpty();
        }

        $this->showTabs($options);

        $this->showFormHeader($options);

        print "<tr class='tab_bg_1'>";

        // Name

        print '<td title="' .
            $LANG['plugin_rackview']['help_name'].
            '">' .
            $LANG['plugin_rackview']['field_name'] .
            ':</td>';

        print '<td>';
        Html::autocompletionTextField($this, 'name');
        print '</td>';

        // Location

        print '<td title="' .
            $LANG['plugin_rackview']['help_location'].
            '">' .
            $LANG['plugin_rackview']['field_location'] .
            ':</td>';

        print '<td>';
        Dropdown::show(
            'Location',
            array(
                'value' => $this->fields['locations_id']
            )
        );
        print '</td>';

        print '</tr>';
        print "<tr class='tab_bg_1'>";

        // Size

        print '<td title="' .
            $LANG['plugin_rackview']['help_size'].
            '">' .
            $LANG['plugin_rackview']['field_size'] .
            ':</td>';

        print '<td>';
        Html::autocompletionTextField($this, 'size');
        print '</td>';

        print '<td colspan="2">&nbsp;</td>';

        print '</tr>';
        print "<tr class='tab_bg_1'>";

        print '<td title="' .
            $LANG['plugin_rackview']['help_description'].
            '">' .
            $LANG['plugin_rackview']['field_description'] .
            ':</td>';

        print '<td colspan="3">';
        print '<textarea cols="70" rows="4" name="description">' .
            $this->fields['description'] .
            '</textarea>';
        print '</td>';

        print '</tr>';

        // Description

        $this->showFormButtons($options);
        $this->addDivForTabs();

        return true;

    }

    /**
     * Display a rack
     *
     * @param bool $mini Show a minimized version of the rack
     * @param String $startuField Enable clicking on a row to set a "startu"
     *                            input field. Refers to the id of the input
     *                            field
     */

    public function showRack($mini, $startuField = "") {

        print $this->buildRack($mini, $startuField);

    }

}
