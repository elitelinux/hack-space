<?php
/*
 * @version $Id: profession.class.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesProfession extends CommonDropdown {
   
   static function getTypeName($nb=0) {

      return _n('Profession', 'Professions', $nb, 'resources');
   }

   static function canCreate() {
      if (Session::haveRight('entity_dropdown','w')
         && plugin_resources_haveRight('dropdown_public', 'w')){
         return true;
      }
      return false;
   }

   static function canView() {
      if (plugin_resources_haveRight('dropdown_public', 'r')){
         return true;
      }
      return false;
   }

   function getAdditionalFields() {

      return array(array('name'  => 'code',
                         'label' => __('Code', 'resources'),
                         'type'  => 'text',
                         'list'  => true),
                  array('name'  => 'short_name',
                        'label' => __('Short name', 'resources'),
                        'type'  => 'text',
                        'list'  => true),
                  array('name'  => 'plugin_resources_professionlines_id',
                        'label' => __('Profession line', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true),
                  array('name'  => 'plugin_resources_professioncategories_id',
                        'label' => __('Profession category', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true),
                  array('name'  => 'begin_date',
                        'label' => __('Begin date'),
                        'type'  => 'date',
                        'list'  => false),
                  array('name'  => 'end_date',
                        'label' => __('End date'),
                        'type'  => 'date',
                        'list'  => false),
                  array('name'  => 'is_active',
                        'label' => __('Active'),
                        'type'  => 'bool',
                        'list'  => true),
                  );
   }

   /**
    * During resource or employment transfer
    *
    * @static
    * @param $ID
    * @param $entity
    * @return ID|int|the
    */
   static function transfer($ID, $entity) {
      global $DB;

      if ($ID>0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_resources_professions`
                   WHERE `id` = '$ID'";

         if ($result=$DB->query($query)) {
            if ($DB->numrows($result)) {
               $data = $DB->fetch_assoc($result);
               $data = Toolbox::addslashes_deep($data);
               $input['name'] = $data['name'];
               $input['entities_id']  = $entity;
               $temp = new self();
               $newID    = $temp->getID($input);

               if ($newID<0) {
                  $newID = $temp->import($input);
               }

               //transfer of the linked line
               $line = PluginResourcesProfessionLine::transfer($temp->fields["plugin_resources_professionlines_id"], $entity);
               if ($line > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professionlines_id"] = $line;
                  $temp->update($values);
               }

               //transfer of the linked category
               $category = PluginResourcesProfessionCategory::transfer($temp->fields["plugin_resources_professioncategories_id"], $entity);
               if ($category > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professioncategories_id"] = $category;
                  $temp->update($values);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * When a profession is deleted -> deletion of the linked ranks
    *
    * @return nothing|void
    */
   function cleanDBonPurge(){

      $temp = new PluginResourcesRank();
      $temp->deleteByCriteria(array('plugin_resources_professions_id' => $this->fields['id']));

   }

   function getSearchOptions() {

      $tab = parent::getSearchOptions();

      $tab[14]['table']         = $this->getTable();
      $tab[14]['field']         = 'code';
      $tab[14]['name']          = __('Code', 'resources');

      $tab[15]['table']         = $this->getTable();
      $tab[15]['field']         = 'short_name';
      $tab[15]['name']          = __('Short name', 'resources');

      $tab[17]['table']         = 'glpi_plugin_resources_professionlines';
      $tab[17]['field']         = 'name';
      $tab[17]['name']          = __('Profession line', 'resources');
      $tab[17]['datatype']      = 'dropdown';
      
      $tab[18]['table']         = 'glpi_plugin_resources_professioncategories';
      $tab[18]['field']         = 'name';
      $tab[18]['name']          = __('Profession category', 'resources');
      $tab[18]['datatype']      = 'dropdown';

      $tab[19]['table']         = $this->getTable();
      $tab[19]['field']         = 'is_active';
      $tab[19]['name']          = __('Active');
      $tab[19]['datatype']      = 'bool';

      $tab[20]['table']         = $this->getTable();
      $tab[20]['field']         = 'begin_date';
      $tab[20]['name']          = __('Begin date');
      $tab[20]['datatype']      = 'date';

      $tab[21]['table']         = $this->getTable();
      $tab[21]['field']         = 'end_date';
      $tab[21]['name']          = __('End date');
      $tab[21]['datatype']      = 'date';

      return $tab;
   }

   /**
    * is_active = 1 during a creation
    *
    * @return nothing|void
    */
   function post_getEmpty() {

      $this->fields['is_active'] = 1;
   }

}

?>