<?php
/*
 * @version $Id: rank.class.php 480 2012-11-09 tynet $
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

class PluginResourcesRank extends CommonDropdown {
   
   static function getTypeName($nb=0) {

      return _n('Rank', 'Ranks', $nb, 'resources');
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
                  array('name'  => 'plugin_resources_professions_id',
                        'label' => __('Profession', 'resources'),
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

   function getSearchOptions() {

      $tab = parent::getSearchOptions();

      $tab[14]['table']         = $this->getTable();
      $tab[14]['field']         = 'code';
      $tab[14]['name']          = __('Code', 'resources');

      $tab[15]['table']         = $this->getTable();
      $tab[15]['field']         = __('Short name', 'resources');

      $tab[17]['table']         = 'glpi_plugin_resources_professions';
      $tab[17]['field']         = 'name';
      $tab[17]['name']          = __('Profession', 'resources');
      $tab[17]['datatype']      = 'dropdown';
      
      $tab[18]['table']         = $this->getTable();
      $tab[18]['field']         = 'is_active';
      $tab[18]['name']          = __('Active');
      $tab[18]['datatype']      = 'bool';

      $tab[19]['table']         = $this->getTable();
      $tab[19]['field']         = 'begin_date';
      $tab[19]['name']          = __('Begin date');
      $tab[19]['datatype']      = 'date';

      $tab[20]['table']         = $this->getTable();
      $tab[20]['field']         = 'end_date';
      $tab[20]['name']          = __('End date');
      $tab[20]['datatype']      = 'date';

      return $tab;
   }


   /**
    * Display a rank's list depending on profession
    *
    * @static
    * @param $options
    */
   static function showRank($options){
      global $DB;

      $professionId = $options['plugin_resources_professions_id'];
      $entity = $options['entity'];
      $rand = $options['rand'];
      $sort = $options['sort'];

      if ($professionId>0) {

         if ($sort) {
            $query = "SELECT `glpi_plugin_resources_ranks`.*
                     FROM `glpi_plugin_resources_ranks`
                     INNER JOIN `glpi_plugin_resources_costs`
                        ON (`glpi_plugin_resources_ranks`.`id`
                            = `glpi_plugin_resources_costs`.`plugin_resources_ranks_id`)
                     WHERE `glpi_plugin_resources_ranks`.`plugin_resources_professions_id` = '".$professionId."'
                     AND `glpi_plugin_resources_costs`.`cost` <> '0.00'";

            $values[0] = Dropdown::EMPTY_VALUE;
            if($result = $DB->query($query)){
               while ($data = $DB->fetch_array($result)) {
                  $values[$data['id']] = $data['name'];
               }
            }
            Dropdown::showFromArray('plugin_resources_ranks_id', $values);

         } else {
            $condition = " `plugin_resources_professions_id` = '".$professionId."'";

            Dropdown::show('PluginResourcesRank', array('entity' => $entity,
               'condition' => $condition));
         }

      } else {
         echo "<select name='plugin_resources_ranks_id'
                        id='dropdown_plugin_resources_ranks_id$rand'>";
         echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
      }
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
                   FROM `glpi_plugin_resources_ranks`
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

               //transfert of the linked profession
               $profession = PluginResourcesProfession::transfer($temp->fields["plugin_resources_professions_id"], $entity);
               if ($profession > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professions_id"] = $profession;
                  $temp->update($values);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * when a rank is deleted -> deletion of the linked specialities
    *
    * @return nothing|void
    */
   function cleanDBonPurge(){

      $temp = new PluginResourcesResourceSpeciality();
      $temp->deleteByCriteria(array('plugin_resources_ranks_id' => $this->fields['id']));

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