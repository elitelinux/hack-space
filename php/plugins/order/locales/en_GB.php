<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

$LANG['plugin_order']['title'][1] = "Orders management";

$LANG['plugin_order'][0] = "Order number";
$LANG['plugin_order'][1] = "Date of order";
$LANG['plugin_order'][2] = "Description";
$LANG['plugin_order'][3] = "Budget";
$LANG['plugin_order'][4] = "Supplier Detail";
$LANG['plugin_order'][5] = "Validation";
$LANG['plugin_order'][6] = "Delivery";
$LANG['plugin_order'][7] = "Order";
$LANG['plugin_order'][8] = "Other item";
$LANG['plugin_order'][9] = "Other type of item";
$LANG['plugin_order'][10] = "Quality";
$LANG['plugin_order'][11] = "Linked orders";
$LANG['plugin_order'][12] = "Budget already used";
$LANG['plugin_order'][13] = "Price tax free";
$LANG['plugin_order'][14] = "Price ATI";
$LANG['plugin_order'][15] = "Price tax free with postage";
$LANG['plugin_order'][25] = "VAT";
$LANG['plugin_order'][26] = "Postage";
$LANG['plugin_order'][28] = "Invoice number";
$LANG['plugin_order'][30] = "Quote number";
$LANG['plugin_order'][31] = "Order number";
$LANG['plugin_order'][32] = "Payment conditions";
$LANG['plugin_order'][39] = "Order name";
$LANG['plugin_order'][40] = "Delivery location";
$LANG['plugin_order'][42] = "Cannot link several items to one detail line";
$LANG['plugin_order'][44] = "An order number is mandatory !";
$LANG['plugin_order'][45] = "Cannot generate items not delivered";
$LANG['plugin_order'][46] = "Cannot link items not delivered";
$LANG['plugin_order'][47] = "Order informations";
$LANG['plugin_order'][48] = "One or several selected rows haven't linked items";
$LANG['plugin_order'][49] = "The order date must be within the dates entered for the selected budget.";
$LANG['plugin_order'][50] = "Estimated due date";
$LANG['plugin_order'][51] = "Due date overtaken";
$LANG['plugin_order'][52] = "Unlink";
$LANG['plugin_order'][53] = "Delivery date";
$LANG['plugin_order'][54] = "Due date overtake";
$LANG['plugin_order'][55] = "Late orders";
$LANG['plugin_order'][56] = "Author";
$LANG['plugin_order'][57] = "Author group";
$LANG['plugin_order'][58] = "Recipient";
$LANG['plugin_order'][59] = "Recipient group";
$LANG['plugin_order'][60] = "Order item";

$LANG['plugin_order']['bill'][0] = "Bill";
$LANG['plugin_order']['bill'][1] = "Bill type";
$LANG['plugin_order']['bill'][2] = "Bill status";
$LANG['plugin_order']['bill'][3] = "A bill number is mandatory";
$LANG['plugin_order']['bill'][4] = "Bills";
$LANG['plugin_order']['bill'][5] = "Payment status";
$LANG['plugin_order']['bill'][6] = "Paid";
$LANG['plugin_order']['bill'][7] = "Being paid";

$LANG['plugin_order']['config'][0] = "Plugin configuration";
$LANG['plugin_order']['config'][1] = "Default VAT";
$LANG['plugin_order']['config'][2] = "Use validation process";
$LANG['plugin_order']['config'][3] = "Automatic actions when delivery";
$LANG['plugin_order']['config'][4] = "Enable automatic generation";
$LANG['plugin_order']['config'][5] = "Default name";
$LANG['plugin_order']['config'][6] = "Default serial number";
$LANG['plugin_order']['config'][7] = "Default inventory number";
$LANG['plugin_order']['config'][8] = "Default entity";
$LANG['plugin_order']['config'][9] = "Default category";
$LANG['plugin_order']['config'][10] = "Default title";
$LANG['plugin_order']['config'][11] = "Default description";
$LANG['plugin_order']['config'][12] = "Default state";
$LANG['plugin_order']['config'][13] = "Order lifecycle";
$LANG['plugin_order']['config'][14] = "State before validation";
$LANG['plugin_order']['config'][15] = "Waiting for validation state";
$LANG['plugin_order']['config'][16] = "Validated order state";
$LANG['plugin_order']['config'][17] = "Order being delivered state";
$LANG['plugin_order']['config'][18] = "Order delivered state";
$LANG['plugin_order']['config'][19] = "Canceled order state";
$LANG['plugin_order']['config'][20] = "No VAT";
$LANG['plugin_order']['config'][21] = "Order paied state";
$LANG['plugin_order']['config'][22] = "Order generation in ODT";
$LANG['plugin_order']['config'][23] = "Activate suppliers quality satisfaction";
$LANG['plugin_order']['config'][24] = "Display order's suppliers informations";
$LANG['plugin_order']['config'][25] = "Color to be displayed when order due date is overtaken";
$LANG['plugin_order']['config'][26] = "Copy order documents when a new item is created";
$LANG['plugin_order']['config'][27] = "Default heading when adding a document to an order";

$LANG['plugin_order']['delivery'][1] = "Item delivered";
$LANG['plugin_order']['delivery'][2] = "Take item delivery";
$LANG['plugin_order']['delivery'][3] = "Generate item";
$LANG['plugin_order']['delivery'][4] = "Take item delivery (bulk)";
$LANG['plugin_order']['delivery'][5] = "Delivered items";
$LANG['plugin_order']['delivery'][6] = "Number to deliver";
$LANG['plugin_order']['delivery'][9] = "Generate";
$LANG['plugin_order']['delivery'][11] = "Link to an existing item";
$LANG['plugin_order']['delivery'][12] = "Delete item link";
$LANG['plugin_order']['delivery'][13] = "Item generated by using order";
$LANG['plugin_order']['delivery'][14] = "Item linked to order";
$LANG['plugin_order']['delivery'][15] = "Item unlink form order";
$LANG['plugin_order']['delivery'][16] = "Item already linked to another one";
$LANG['plugin_order']['delivery'][17] = "No item to generate";

$LANG['plugin_order']['detail'][1] = "Equipment";
$LANG['plugin_order']['detail'][2] = "Reference";
$LANG['plugin_order']['detail'][4] = "Unit price tax free";
$LANG['plugin_order']['detail'][5] = "Add to the order";
$LANG['plugin_order']['detail'][6] = "Type";
$LANG['plugin_order']['detail'][7] = "Quantity";
$LANG['plugin_order']['detail'][18] = "Discounted price tax free";
$LANG['plugin_order']['detail'][19] = "Status";
$LANG['plugin_order']['detail'][20] = "No item to take delivery of";
$LANG['plugin_order']['detail'][21] = "Delivery date";
$LANG['plugin_order']['detail'][25] = "Discount (%)";
$LANG['plugin_order']['detail'][27] = "Please select a supplier";
$LANG['plugin_order']['detail'][29] = "No item selected";
$LANG['plugin_order']['detail'][30] = "Item successfully selected";
$LANG['plugin_order']['detail'][31] = "Item successfully taken delivery";
$LANG['plugin_order']['detail'][32] = "Item already taken delivery";
$LANG['plugin_order']['detail'][33] = "The discount pourcentage must be between 0 and 100";
$LANG['plugin_order']['detail'][34] = "Add reference";
$LANG['plugin_order']['detail'][35] = "Remove reference";
$LANG['plugin_order']['detail'][36] = "Do you really want to delete these details ? Delivered items will not be linked to order !";
$LANG['plugin_order']['detail'][37] = "Not enough items to deliver";
$LANG['plugin_order']['detail'][38] = "Do you really want to cancel this order ? This option is irreversible !";
$LANG['plugin_order']['detail'][39] = "Do you want to cancel the validation approval ?";
$LANG['plugin_order']['detail'][40] = "Do you really want to edit the order ?";
$LANG['plugin_order']['detail'][41] = "Do you really want to update this item ?";

$LANG['plugin_order']['generation'][0] = "Generation";
$LANG['plugin_order']['generation'][1] = "Order Generation";
$LANG['plugin_order']['generation'][2] = "Order";
$LANG['plugin_order']['generation'][3] = "Invoice address";
$LANG['plugin_order']['generation'][4] = "Delivery address";
$LANG['plugin_order']['generation'][5] = "The";
$LANG['plugin_order']['generation'][6] = "Quantity";
$LANG['plugin_order']['generation'][7] = "Designation";
$LANG['plugin_order']['generation'][8] = "Unit price";
$LANG['plugin_order']['generation'][9] = "Sum tax free";
$LANG['plugin_order']['generation'][10] = "Issuer order";
$LANG['plugin_order']['generation'][11] = "Recipient";
$LANG['plugin_order']['generation'][12] = "Order number";
$LANG['plugin_order']['generation'][13] = "Discount rate";
$LANG['plugin_order']['generation'][14] = "TOTAL tax free";
$LANG['plugin_order']['generation'][15] = "TOTAL with taxes";
$LANG['plugin_order']['generation'][16] = "Signature of issuing order";
$LANG['plugin_order']['generation'][17] = "€";

$LANG['plugin_order']['history'][2] = "Add";
$LANG['plugin_order']['history'][4] = "Delete";

$LANG['plugin_order']['infocom'][1] = "Some fields cannont be modified because they belong to an order";

$LANG['plugin_order']['item'][0] = "Associated items";
$LANG['plugin_order']['item'][2] = "No associated item";

$LANG['plugin_order']['mailing'][2] = "by";

$LANG['plugin_order']['menu'][0] = "Menu";
$LANG['plugin_order']['menu'][1] = "Orders";
$LANG['plugin_order']['menu'][2] = "Products references";
$LANG['plugin_order']['menu'][4] = "Orders";
$LANG['plugin_order']['menu'][5] = "References";
$LANG['plugin_order']['menu'][6] = "Bills";

$LANG['plugin_order']['parser'][1] = "Use this model";
$LANG['plugin_order']['parser'][2] = "No file found into the folder";
$LANG['plugin_order']['parser'][3] = "Use this sign";
$LANG['plugin_order']['parser'][4] = "Thanks to select a model into your preferences";

$LANG['plugin_order']['profile'][0] = "Rights management";
$LANG['plugin_order']['profile'][1] = "Order validation";
$LANG['plugin_order']['profile'][2] = "Cancel order";
$LANG['plugin_order']['profile'][3] = "Edit a validated order";
$LANG['plugin_order']['profile'][4] = "Link order to a ticket";

$LANG['plugin_order']['reference'][1] = "Product reference";
$LANG['plugin_order']['reference'][2] = "Add a supplier";
$LANG['plugin_order']['reference'][3] = "List references";
$LANG['plugin_order']['reference'][5] = "Supplier for the reference";
$LANG['plugin_order']['reference'][6] = "A reference with the same name still exists";
$LANG['plugin_order']['reference'][7] = "Reference(s) in use";
$LANG['plugin_order']['reference'][8] = "Cannot create reference without a name";
$LANG['plugin_order']['reference'][9] = "Cannot create reference without a type";
$LANG['plugin_order']['reference'][10] = "Manufacturer's product reference";
$LANG['plugin_order']['reference'][11] = "View by item type";
$LANG['plugin_order']['reference'][12] = "Select the wanted item type";
$LANG['plugin_order']['reference'][13] = "Copy reference";
$LANG['plugin_order']['reference'][14] = "Copy of";

$LANG['plugin_order']['status'][0] = "Order status";
$LANG['plugin_order']['status'][1] = "Being delivered";
$LANG['plugin_order']['status'][2] = "Delivered";
$LANG['plugin_order']['status'][3] = "Delivery status";
$LANG['plugin_order']['status'][4] = "No specified status";
$LANG['plugin_order']['status'][7] = "Waiting for approval";
$LANG['plugin_order']['status'][8] = "Taken delivery";
$LANG['plugin_order']['status'][9] = "Draft";
$LANG['plugin_order']['status'][10] = "Canceled";
$LANG['plugin_order']['status'][11] = "Waiting for delivery";
$LANG['plugin_order']['status'][12] = "Validated";
$LANG['plugin_order']['status'][13] = "Delivery statistics";
$LANG['plugin_order']['status'][14] = "the order is validated, any update is forbidden";
$LANG['plugin_order']['status'][15] = "You cannot remove this status";
$LANG['plugin_order']['status'][16] = "Paid";
$LANG['plugin_order']['status'][17] = "Not paid";
$LANG['plugin_order']['status'][18] = "Paid value";
$LANG['plugin_order']['status'][19] = "Billing summary";
$LANG['plugin_order']['status'][20] = "Order is late";

$LANG['plugin_order']['survey'][0] = "Supplier quality";
$LANG['plugin_order']['survey'][1] = "Administrative followup quality (contracts, bills, mail, etc.)";
$LANG['plugin_order']['survey'][2] = "Commercial followup quality, visits, responseness";
$LANG['plugin_order']['survey'][3] = "Contacts availability";
$LANG['plugin_order']['survey'][4] = "Quality of supplier intervention";
$LANG['plugin_order']['survey'][5] = "Reliability about annouced delays";
$LANG['plugin_order']['survey'][6] = "Really unsatisfied";
$LANG['plugin_order']['survey'][7] = "Really satisfied";
$LANG['plugin_order']['survey'][8] = "Average mark up to 10 (X points / 5)";
$LANG['plugin_order']['survey'][9] = "Final supplier note";
$LANG['plugin_order']['survey'][10] = "Note";
$LANG['plugin_order']['survey'][11] = "Comment on survey";

$LANG['plugin_order']['validation'][0] = "Thanks to add at least one equipment on your order.";
$LANG['plugin_order']['validation'][1] = "Request order validation";
$LANG['plugin_order']['validation'][2] = "Order validated";
$LANG['plugin_order']['validation'][3] = "Order being taken delivery";
$LANG['plugin_order']['validation'][4] = "Order delivered";
$LANG['plugin_order']['validation'][5] = "Order canceled";
$LANG['plugin_order']['validation'][6] = "Validation process";
$LANG['plugin_order']['validation'][7] = "Order validation successfully requested";
$LANG['plugin_order']['validation'][8] = "Order currently edited";
$LANG['plugin_order']['validation'][9] = "Validate order";
$LANG['plugin_order']['validation'][10] = "Order is validated";
$LANG['plugin_order']['validation'][11] = "Ask for validation";
$LANG['plugin_order']['validation'][12] = "Cancel order";
$LANG['plugin_order']['validation'][13] = "Cancel ask for validation";
$LANG['plugin_order']['validation'][14] = "Validation query is now canceled";
$LANG['plugin_order']['validation'][15] = "Order is in draft state";
$LANG['plugin_order']['validation'][16] = "Validation canceled successfully";
$LANG['plugin_order']['validation'][17] = "Edit order";
$LANG['plugin_order']['validation'][18] = "Comment of validation";
$LANG['plugin_order']['validation'][19] = "Editor of validation";

$LANG['plugin_order']['budget_over'][0] = "Total orders related with this budget is greater than its value.";
$LANG['plugin_order']['budget_over'][1] = "Total orders related with this budget is equal to its value.";

$LANG['plugin_order']['install'][0] = "Plugin installation or upgrade";

?>
