<?php
/*
 * @version $Id: contact.class.php 20129 2013-02-04 16:53:59Z moyo $
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
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Contact class
**/
class Contact extends CommonDBTM{

   // From CommonDBTM
   public $dohistory = true;


   static function getTypeName($nb=0) {
      return _n('Contact', 'Contacts', $nb);
   }


   static function canCreate() {
      return Session::haveRight('contact_enterprise', 'w');
   }

   static function canView() {
      return Session::haveRight('contact_enterprise', 'r');
   }


   function cleanDBonPurge() {

      $cs = new Contact_Supplier();
      $cs->cleanDBonItemDelete($this->getType(), $this->fields['id']);
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('Contact_Supplier', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   /**
    * Get address of the contact (company one)
    *
    *@return string containing the address
   **/
   function GetAddress() {
      global $DB;

      $query = "SELECT `glpi_suppliers`.`name`, `glpi_suppliers`.`address`,
                       `glpi_suppliers`.`postcode`, `glpi_suppliers`.`town`,
                       `glpi_suppliers`.`state`, `glpi_suppliers`.`country`
                FROM `glpi_suppliers`, `glpi_contacts_suppliers`
                WHERE `glpi_contacts_suppliers`.`contacts_id` = '".$this->fields["id"]."'
                      AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            if ($data = $DB->fetch_assoc($result)) {
               return $data;
            }
         }
      }
      return "";
   }


   /**
    * Get website of the contact (company one)
    *
    *@return string containing the website
   **/
   function GetWebsite() {
      global $DB;

      $query = "SELECT `glpi_suppliers`.`website` as website
                FROM `glpi_suppliers`, `glpi_contacts_suppliers`
                WHERE `glpi_contacts_suppliers`.`contacts_id` = '".$this->fields["id"]."'
                      AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            return $DB->result($result, 0, "website");
         }
         return "";
      }
   }


   /**
    * Print the contact form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
   **/
   function showForm($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Surname')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td rowspan='4' class='middle right'>".__('Comments')."</td>";
      echo "<td class='middle' rowspan='4'>";
      echo "<textarea cols='45' rows='7' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('First name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "firstname");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Phone')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "phone");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Phone 2')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "phone2");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Mobile phone')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "mobile");
      echo "</td>";
      echo "<td class='middle'>".__('Address')."</td>";
      echo "<td class='middle'>";
      echo "<textarea cols='37' rows='3' name='address'>".$this->fields["address"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Fax')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "fax");
      echo "</td>";
      echo "<td>".__('Postal code')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "postcode", array('size' => 10));
      echo "&nbsp;&nbsp;". __('City'). "&nbsp;";
      Html::autocompletionTextField($this, "town", array('size' => 23));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Email', 'Emails', 1)."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "email");
      echo "</td>";
      echo "<td>".__('State')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "state");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type')."</td>";
      echo "<td>";
      ContactType::dropdown(array('value' => $this->fields["contacttypes_id"]));
      echo "</td>";
      echo "<td>".__('Country')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "country");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . _x('person','Title') . "</td><td>";
      UserTitle::dropdown(array('value' => $this->fields["usertitles_id"]));
      echo "<td>&nbsp;</td><td class='center'>";
      if ($ID > 0) {
         echo "<a target=''_blank' href='".$this->getFormURL().
                "?getvcard=1&amp;id=$ID'>".__('Vcard')."</a>";
      }
      echo "</td></tr>";


      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   /**
    * @see CommonDBTM::doSpecificMassiveActions()
   **/
   function doSpecificMassiveActions($input=array()) {

      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      switch ($input['action']) {
         case "add_contact_supplier" :
            $contactsupplier = new Contact_Supplier();
            return $contactsupplier->doSpecificMassiveActions($input);

         default :
            return parent::doSpecificMassiveActions($input);
      }
      return false;
   }


   /**
    * @see CommonDBTM::showSpecificMassiveActionsParameters()
    **/
   function showSpecificMassiveActionsParameters($input=array()) {

      switch ($input['action']) {
         case "add_contact_supplier" :
            $contactsupplier = new Contact_Supplier();
            return $contactsupplier->showSpecificMassiveActionsParameters($input);

         default :
            return parent::showSpecificMassiveActionsParameters($input);
      }
      return false;
   }


   /**
    * @see CommonDBTM::getSpecificMassiveActions()
    **/
   function getSpecificMassiveActions($checkitem=NULL) {

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['add_contact_supplier'] = _x('button', 'Add a supplier');
      }
      if (Session::haveRight('transfer','r')
          && Session::isMultiEntitiesMode()
          && $isadmin) {
         $actions['add_transfer_list'] = _x('button', 'Add to transfer list');
      }
      return $actions;
   }


   function getSearchOptions() {

      $tab                      = array();
      $tab['common']            = __('Characteristics');

      $tab[1]['table']          = $this->getTable();
      $tab[1]['field']          = 'name';
      $tab[1]['name']           = __('Surname');
      $tab[1]['datatype']       = 'itemlink';
      $tab[1]['massiveaction']  = false;

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'firstname';
      $tab[11]['name']          = __('First name');
      $tab[11]['datatype']      = 'string';

      $tab[2]['table']          = $this->getTable();
      $tab[2]['field']          = 'id';
      $tab[2]['name']           = __('ID');
      $tab[2]['massiveaction']  = false;
      $tab[2]['datatype']       = 'number';

      $tab[3]['table']          = $this->getTable();
      $tab[3]['field']          = 'phone';
      $tab[3]['name']           = __('Phone');
      $tab[3]['datatype']       = 'string';

      $tab[4]['table']          = $this->getTable();
      $tab[4]['field']          = 'phone2';
      $tab[4]['name']           = __('Phone 2');
      $tab[4]['datatype']       = 'string';

      $tab[10]['table']         = $this->getTable();
      $tab[10]['field']         = 'mobile';
      $tab[10]['name']          = __('Mobile phone');
      $tab[10]['datatype']      = 'string';

      $tab[5]['table']          = $this->getTable();
      $tab[5]['field']          = 'fax';
      $tab[5]['name']           = __('Fax');
      $tab[5]['datatype']       = 'string';

      $tab[6]['table']          = $this->getTable();
      $tab[6]['field']          = 'email';
      $tab[6]['name']           = _n('Email', 'Emails', 1);
      $tab[6]['datatype']       = 'email';

      $tab[82]['table']         = $this->getTable();
      $tab[82]['field']         = 'address';
      $tab[82]['name']          = __('Address');
      $tab[83]['datatype']      = 'text';

      $tab[84]['table']         = $this->getTable();
      $tab[84]['field']         = 'town';
      $tab[84]['name']          = __('City');
      $tab[84]['datatype']      = 'string';

      $tab[83]['table']         = $this->getTable();
      $tab[83]['field']         = 'postcode';
      $tab[83]['name']          = __('Postal code');
      $tab[83]['datatype']      = 'string';

      $tab[85]['table']         = $this->getTable();
      $tab[85]['field']         = 'state';
      $tab[85]['name']          = __('State');
      $tab[85]['datatype']      = 'string';

      $tab[87]['table']         = $this->getTable();
      $tab[87]['field']         = 'country';
      $tab[87]['name']          = __('Country');
      $tab[87]['datatype']      = 'string';

      $tab[9]['table']          = 'glpi_contacttypes';
      $tab[9]['field']          = 'name';
      $tab[9]['name']           = __('Type');
      $tab[9]['datatype']       = 'dropdown';

      $tab[81]['table']         = 'glpi_usertitles';
      $tab[81]['field']         = 'name';
      $tab[81]['name']          = _x('person','Title');
      $tab[81]['datatype']      = 'dropdown';

      $tab[8]['table']          = 'glpi_suppliers';
      $tab[8]['field']          = 'name';
      $tab[8]['name']           = __('Associated suppliers');
      $tab[8]['forcegroupby']   = true;
      $tab[8]['datatype']       = 'itemlink';
      $tab[8]['joinparams']     = array('beforejoin'
                                         => array('table'      => 'glpi_contacts_suppliers',
                                                  'joinparams' => array('jointype' => 'child')));

      $tab[16]['table']         = $this->getTable();
      $tab[16]['field']         = 'comment';
      $tab[16]['name']          = __('Comments');
      $tab[16]['datatype']      = 'text';

      $tab[90]['table']         = $this->getTable();
      $tab[90]['field']         = 'notepad';
      $tab[90]['name']          = __('Notes');
      $tab[90]['massiveaction'] = false;
      $tab[90]['datatype']      = 'text';


      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = __('Entity');
      $tab[80]['massiveaction'] = false;
      $tab[80]['datatype']      = 'dropdown';

      $tab[86]['table']         = $this->getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = __('Child entities');
      $tab[86]['datatype']      = 'bool';

      return $tab;
   }





   /**
    * Generate the Vcard for the current Contact
    *
    *@return Nothing (display)
   **/
   function generateVcard() {

      include (GLPI_ROOT . "/lib/vcardclass/classes-vcard.php");

      if (!$this->can($this->fields['id'],'r')) {
         return false;
      }

      // build the Vcard
      $vcard = new vCard();

      $vcard->setName($this->fields["name"], $this->fields["firstname"], "", "");

      $vcard->setPhoneNumber($this->fields["phone"], "PREF;WORK;VOICE");
      $vcard->setPhoneNumber($this->fields["phone2"], "HOME;VOICE");
      $vcard->setPhoneNumber($this->fields["mobile"], "WORK;CELL");

      $addr = $this->GetAddress();
      if (is_array($addr)) {
         $vcard->setAddress($addr["name"], "", $addr["address"], $addr["town"], $addr["state"],
                            $addr["postcode"], $addr["country"], "WORK;POSTAL");
      }
      $vcard->setEmail($this->fields["email"]);
      $vcard->setNote($this->fields["comment"]);
      $vcard->setURL($this->GetWebsite(), "WORK");

      // send the  VCard
      $output   = $vcard->getVCard();
      $filename = $vcard->getFileName();      // "xxx xxx.vcf"

      @Header("Content-Disposition: attachment; filename=\"$filename\"");
      @Header("Content-Length: ".Toolbox::strlen($output));
      @Header("Connection: close");
      @Header("content-type: text/x-vcard; charset=UTF-8");

      echo $output;
   }

}
?>