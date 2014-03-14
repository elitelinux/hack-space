<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ideabox plugin for GLPI
 Copyright (C) 2003-2011 by the Ideabox Development Team.

 https://forge.indepnet.net/projects/ideabox
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Ideabox.

 Ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_ideabox_install() {
	global $DB;
	
	include_once (GLPI_ROOT."/plugins/ideabox/inc/profile.class.php");
	
   $install=false;
   $update78=false;
   $update80=false;
	if (!TableExists("glpi_plugin_ideabox_profiles") && !TableExists("glpi_plugin_ideabox_ideaboxes")) {
      
      $install=true;
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/empty-1.8.0.sql");
	
	} else if (TableExists("glpi_plugin_ideabox_mailing") && !FieldExists("glpi_plugin_ideabox","begin_date")) {
      
      $update78=true;
      $update80=true;
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.5.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.6.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.1.sql");

	} else if (TableExists("glpi_plugin_ideabox_profiles") && FieldExists("glpi_plugin_ideabox_profiles","interface")) {
      
      $update78=true;
      $update80=true;
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.6.0.sql");
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.1.sql");

	} else if (!TableExists("glpi_plugin_ideabox_ideaboxs") && !FieldExists("glpi_plugin_ideabox_profiles","profiles_id")) {
      
      $update78=true;
      $update80=true;
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.1.sql");
      
	} else if (!TableExists("glpi_plugin_ideabox_ideaboxes")) {
      
      $update80=true;
		$DB->runFile(GLPI_ROOT ."/plugins/ideabox/sql/update-1.7.1.sql");
	
	}
   
   if ($install || $update78) {
      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginIdeaboxIdeabox' AND `name` = 'Idea'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');
      
      $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##lang.ideabox.title##',
                        '##lang.ideabox.url## : ##ideabox.url##
   ##lang.ideabox.entity## : ##ideabox.entity##
   ##IFideabox.name####lang.ideabox.name## : ##ideabox.name##
   ##ENDIFideabox.name##
   ##IFideabox.comment####lang.ideabox.comment## : ##ideabox.comment##
   ##ENDIFideabox.comment##

   ##FOREACHupdates##----------
   ##lang.update.title##:
   ##IFupdate.name####lang.ideabox.name## : ##update.name####ENDIFupdate.name##
   ##IFupdate.comment##
   ##lang.ideabox.comment## : ##update.comment##
   ##ENDIFupdate.comment##
   ----------##ENDFOREACHupdates##

   ##lang.comment.title##
   ----------
   ##FOREACHcomments##
   ##IFcomment.name####lang.comment.name## : ##comment.name####ENDIFcomment.name##
   ##IFcomment.author####lang.comment.author## : ##comment.author####ENDIFcomment.author##
   ##IFcomment.datecomment####lang.comment.datecomment## : ##comment.datecomment####ENDIFcomment.datecomment##
   ##IFcomment.comment####lang.comment.comment## : ##comment.comment####ENDIFcomment.comment##
   -------
   ##ENDFOREACHcomments##',
                        '&lt;p&gt;&lt;strong&gt;##lang.ideabox.url##&lt;/strong&gt; : &lt;a href=\"##ideabox.url##\"&gt;##ideabox.url##&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.ideabox.entity##&lt;/strong&gt; : ##ideabox.entity##&lt;br /&gt; ##IFideabox.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##ideabox.name####ENDIFideabox.name##&lt;br /&gt;&lt;br /&gt; ##IFideabox.comment##&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##ideabox.comment####ENDIFideabox.comment##&lt;br /&gt;&lt;br /&gt;##FOREACHupdates##----------&lt;br /&gt;&lt;strong&gt;##lang.update.title## :&lt;/strong&gt;&lt;br /&gt;##IFupdate.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##update.name####ENDIFupdate.name##&lt;br /&gt;##IFupdate.comment##&lt;br /&gt;&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##update.comment##&lt;br /&gt;##ENDIFupdate.comment##&lt;br /&gt;----------##ENDFOREACHupdates##&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.comment.title## :&lt;/strong&gt;&lt;br /&gt;----------&lt;br /&gt;##FOREACHcomments####IFcomment.name##&lt;strong&gt;##lang.comment.name##&lt;/strong&gt; : ##comment.name####ENDIFcomment.name##&lt;br /&gt;##IFcomment.author##&lt;strong&gt;##lang.comment.author##&lt;/strong&gt; : ##comment.author####ENDIFcomment.author##&lt;br /&gt;##IFcomment.datecomment##&lt;strong&gt;##lang.comment.datecomment##&lt;/strong&gt; : ##comment.datecomment####ENDIFcomment.datecomment##&lt;br /&gt;##IFcomment.comment##&lt;strong&gt;##lang.comment.comment##&lt;/strong&gt; : ##comment.comment####ENDIFcomment.comment##&lt;br /&gt;----------&lt;br /&gt;##ENDFOREACHcomments##&lt;/p&gt;');";
      $result=$DB->query($query);
      
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'New Idea', 0, 'PluginIdeaboxIdeabox', 'new',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Update Idea', 0, 'PluginIdeaboxIdeabox', 'update',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Delete Idea', 0, 'PluginIdeaboxIdeabox', 'delete',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'New Idea Comment', 0, 'PluginIdeaboxIdeabox', 'newcomment',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Update Idea Comment', 0, 'PluginIdeaboxIdeabox', 'updatecomment',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Delete Idea Comment', 0, 'PluginIdeaboxIdeabox', 'deletecomment',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
   }
   if ($update78) {
      
      $query_="SELECT *
            FROM `glpi_plugin_ideabox_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_ideabox_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_ideabox_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
      
      Plugin::migrateItemType(
         array(4900=>'PluginIdeaboxIdeabox',4901=>'PluginIdeaboxIdeabox'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"));

	}
	
   PluginIdeaboxProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
	return true;
}

function plugin_ideabox_uninstall() {
	global $DB;
	
	$tables = array("glpi_plugin_ideabox_ideaboxs",
               "glpi_plugin_ideabox_ideaboxes",
					"glpi_plugin_ideabox_comments",
					"glpi_plugin_ideabox_profiles");

   foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
	
	//old tables
   $tables = array("glpi_plugin_ideabox",
					"glpi_plugin_ideabox_mailing");

  foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   $notif = new Notification();
   
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'new',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'update',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'delete',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'newcomment',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'updatecomment',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'event'    => 'deletecomment',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginIdeaboxIdeabox',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
      $template->delete($data);
   }
   
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs",
               "glpi_tickets");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginIdeaboxIdeabox' OR `itemtype` = 'PluginIdeaboxComment';");
	
	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginIdeaboxIdeabox'));
   }

	return true;
}

function plugin_ideabox_AssignToTicket($types) {

	if (plugin_ideabox_haveRight("open_ticket","1"))
		$types['PluginIdeaboxIdeabox']=PluginIdeaboxIdeabox::getTypeName(2);
	return $types;
}

// Define dropdown relations
function plugin_ideabox_getDatabaseRelations() {
	$plugin = new Plugin();
	if ($plugin->isActivated("ideabox"))
		return array("glpi_entities"=>array("glpi_plugin_ideabox_ideaboxes"=>"entities_id"),
                     "glpi_users"=>array("glpi_plugin_ideabox_ideaboxes"=>"users_id",
                                          "glpi_plugin_ideabox_comments"=>"users_id"),
                     "glpi_plugin_ideabox_ideaboxes"=>array("glpi_plugin_ideabox_comments"=>"plugin_ideabox_ideaboxes_id"));
	else
		return array();
}


function plugin_datainjection_populate_ideabox() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginIdeaboxIdeaboxInjection'] = 'ideabox';
}

?>