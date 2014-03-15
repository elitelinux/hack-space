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
// Purpose of file: Rackview Javascript code
// ----------------------------------------------------------------------

/**
 * Display a specific rack in a div
 *
 * @param url URL for the backend AJAX
 * @param idField append this field in the URL to specify the rack id
 * @param formName get the rack ID from this form
 * @param inputName get the rack ID from this input field
 * @param mountId the ID of the mount (or -1) we're looking at
 * @param additionalParams Additional GET-parameters to add to the URL
 * @param emptyString String to display, when no rack was selected
 */


function rackview_display_rack(
    url,
    idField,
    formName,
    inputName,
    mountId,
    additionalParams,
    emptyString
) {

    var rackElement,
        rackId;

    if (mountId === -1) {
        mountId = "new";
    }

    rackElement = document.getElementById(
        'rackview_rack_' + mountId
    );

    rackId =
        document.forms[formName].elements[inputName].selectedOptions[0].value;

    if (rackId > 0) {

        if (url.search(/\?/) !== -1) {

            url = url + "&" + idField + "=" + rackId;

        } else {

            url = url + "?" + idField + "=" + rackId;

        }

        if (additionalParams !== null) {

            url = url + "&" + additionalParams;

        }

        Ext.get(rackElement).load({
            url: url
        });

    } else {

        Ext.get(rackElement).update(emptyString);

    }

}

/**
 * Toggle the "enabled" state of a destination input field based on
 * the checked state of a checkbox.
 *
 * @param checkbox      Checkbox in the DOM
 * @param destinationId The ID of the destination field
 */

function rackview_toggle(checkbox, destinationId) {

    document.getElementById(destinationId).disabled = checkbox.checked;

}

/**
 * Add the rackview print css to the header. (Ugly hack, because glpi doesn't
 * support print css)
 */

function rackview_print_css() {

    Ext.getHead().
        createChild({tag: 'link'}).
        set({
            rel:'stylesheet',
            href:'/glpi/plugins/rackview/css/rackview.print.css',
            media:'print'
        });

}

rackview_print_css();