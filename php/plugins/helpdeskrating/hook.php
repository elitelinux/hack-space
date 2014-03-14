<?php

/************************************************************************************************
 *
 * File: hook.php
 *
 ************************************************************************************************
 *
 * Helpdeskrating - A Plugin for GLPI Software
 * Copyright (c) 2010-2013 Christian Deinert
 *
 * http://sourceforge.net/projects/helpdeskrating/
 *
 ************************************************************************************************
 *
 * LICENSE
 *
 *     This file is part of the GLPI Plugin Helpdeskrating.
 *
 *     The GLPI Plugin Helpdeskrating is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Lesser Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     The GLPI Plugin Helpdeskrating is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Lesser Public License for more details.
 *
 *     You should have received a copy of the GNU Lesser Public License
 *     along with the GLPI Plugin Helpdeskrating.  If not, see <http://www.gnu.org/licenses/>.
 *
 ************************************************************************************************/

/**
 * Hook called on profile change
 *
 * Good place to evaluate the user right on this plugin
 * and to save it in the session
 */
function plugin_change_profile_helpdeskrating()
{
    // For example : same right of computer
    if (Session::haveRight('update_ticket', 'w')) {
        $_SESSION["glpi_plugin_helpdeskrating_profile"] = array(
            'helpdeskrating' => 'w'
        );
    } elseif (! Session::haveRight('update_ticket', 'w')) {
        $_SESSION["glpi_plugin_helpdeskrating_profile"] = array(
            'helpdeskrating' => 'r'
        );
    } else {
        unset($_SESSION["glpi_plugin_helpdeskrating_profile"]);
    }
}

/**
 * Install process for plugin
 *
 * @return bool true if succeeded
 */
function plugin_helpdeskrating_install()
{
    global $DB;
    
    if (! TableExists("glpi_plugin_helpdeskrating")) {
        $query = "CREATE TABLE if not exists `glpi_plugin_helpdeskrating` (
				`id` int(11) NOT NULL auto_increment,
				`tickets_id` int(11) NOT NULL,
				`date` DATETIME,
				`type` varchar(5) NOT NULL default 'user',
				`rating_tech` int(1),
				`rating_solution` int(1),
				`rating_overall` int(1),
				`rating_time` int(1),
				`rating_tech_communication` int(1),
				`rating_tech_guideline` int(1),
				`rating_tech_cooperative` int(1),
				`user_cansee` int(1) default 0,
				`comment` varchar(255) collate utf8_unicode_ci default NULL,
				PRIMARY KEY  (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
        $DB->query($query) or die("error creating glpi_plugin_helpdeskrating " . $DB->error());
    }
    
    $DB->query("delete from `glpi_notificationtemplates` where `name`='Ticketrated' and `itemtype`='PluginHelpdeskratingHelpdeskrating'");
    $DB->query("delete from `glpi_notificationtemplatetranslations` where `subject`='##lang.helpdeskrating.event## ##helpdeskrating.ticketname##'");
    $DB->query("delete from `glpi_notifications` where `itemtype`='PluginHelpdeskratingHelpdeskrating'");
    $insert = "insert into `glpi_notificationtemplates`
				(`name`, `itemtype`, `date_mod`)
				values
				('Ticketrated', 'PluginHelpdeskratingHelpdeskrating', '" . date("Y-m-d H:i:s") . "')";
    $DB->query($insert) or die($DB->error());
    
    $query = "select `id` from `glpi_notificationtemplates` where `itemtype` = 'PluginHelpdeskratingHelpdeskrating' and `name` = 'Ticketrated'";
    $result = $DB->query($query) or die($DB->error());
    $notitempid = $DB->result($result, 0, 'id');
    
    if ($notitempid != '' || ! empty($notitempid)) {
        
        // adding notificationtranslation
        $insert = "insert into `glpi_notificationtemplatetranslations`
					(`notificationtemplates_id`, `subject`, `content_text`, `content_html`)
					values
					($notitempid, '##lang.helpdeskrating.event## ##helpdeskrating.ticketname##', 'URL: ##helpdeskrating.url##

Name: ##helpdeskrating.ticketname##

##lang.helpdeskrating.text_rating0##
##IFhelpdeskrating.event=userrating##
##lang.helpdeskrating.ratekat1##: ##helpdeskrating.ratekat1##
##lang.helpdeskrating.ratekat2##: ##helpdeskrating.ratekat2##
##lang.helpdeskrating.ratekat3##: ##helpdeskrating.ratekat3##
##lang.helpdeskrating.ratekat4##: ##helpdeskrating.ratekat4##
##lang.helpdeskrating.comment##: ##helpdeskrating.comment##
##ENDIFhelpdeskrating.event##
##IFhelpdeskrating.event=techrating##
##lang.helpdeskrating.ratetech0##: ##helpdeskrating.ratetech0##
 ##lang.helpdeskrating.ratetech1##: ##helpdeskrating.ratetech1##
 ##lang.helpdeskrating.ratetech2##: ##helpdeskrating.ratetech2##
 ##lang.helpdeskrating.comment##: ##helpdeskrating.comment##
 ##ENDIFhelpdeskrating.event##', '&lt;p&gt;&lt;strong&gt;URL&lt;/strong&gt;: &lt;a href=\"##helpdeskrating.url##\"&gt;##helpdeskrating.url##&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;Name&lt;/strong&gt;: ##helpdeskrating.ticketname##&lt;/p&gt;
&lt;p&gt;##lang.helpdeskrating.text_rating0##&lt;br /&gt;##IFhelpdeskrating.event=userrating##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.ratekat1##&lt;/strong&gt;: ##helpdeskrating.ratekat1##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.ratekat2##&lt;/strong&gt;: ##helpdeskrating.ratekat2##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.ratekat3##&lt;/strong&gt;: ##helpdeskrating.ratekat3##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.ratekat4##&lt;/strong&gt;: ##helpdeskrating.ratekat4##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.comment##&lt;/strong&gt;: ##helpdeskrating.comment##&lt;br /&gt;##ENDIFhelpdeskrating.event##&lt;br /&gt;##IFhelpdeskrating.event=techrating##&lt;br /&gt;&lt;strong&gt;##lang.helpdeskrating.ratetech0##&lt;/strong&gt;: ##helpdeskrating.ratetech0##&lt;br /&gt; &lt;strong&gt;##lang.helpdeskrating.ratetech1##&lt;/strong&gt;: ##helpdeskrating.ratetech1##&lt;br /&gt; &lt;strong&gt;##lang.helpdeskrating.ratetech2##&lt;/strong&gt;: ##helpdeskrating.ratetech2##&lt;br /&gt; &lt;strong&gt;##lang.helpdeskrating.comment##&lt;/strong&gt;: ##helpdeskrating.comment##&lt;br /&gt; ##ENDIFhelpdeskrating.event##&lt;/p&gt;')";
        $DB->query($insert);
        
        // adding user rating notification
        $insert = "insert into `glpi_notifications`
					(`name`, `entities_id`, `itemtype`, `event`, `mode`, `notificationtemplates_id`, `comment`, `is_recursive`, `is_active`, `date_mod`)
					values
					('Ticket User Rating', 0, 'PluginHelpdeskratingHelpdeskrating', 'userrating', 'mail', $notitempid, '', 1, 1, '2010-07-12 10:52:00')";
        $DB->query($insert);
        $query = "select `id`
					from `glpi_notifications`
					where `name` = 'Ticket User Rating'
					and   `event` = 'userrating'
					and   `notificationtemplates_id` = $notitempid";
        $result = $DB->query($query) or die($DB->error());
        $notiid = $DB->result($result, 0, 'id');
        $insert = "insert into `glpi_notificationtargets`
					(`items_id`, `type`, `notifications_id`)
					values
					(2, 1, $notiid)";
        $DB->query($insert);
        
        // adding tech rating notification
        $insert = "insert into `glpi_notifications`
					(`name`, `entities_id`, `itemtype`, `event`, `mode`, `notificationtemplates_id`, `comment`, `is_recursive`, `is_active`, `date_mod`)
					values
					('Ticket Tech Rating', 0, 'PluginHelpdeskratingHelpdeskrating', 'techrating', 'mail', $notitempid, '', 1, 1, '2010-07-12 10:52:00')";
        $DB->query($insert);
        $query = "select `id`
				  from `glpi_notifications`
				  where `name` = 'Ticket Tech Rating'
				  and   `event` = 'techrating'
				  and   `notificationtemplates_id` = $notitempid";
        $result = $DB->query($query) or die($DB->error());
        $notiid = $DB->result($result, 0, 'id');
        $insert = "insert into `glpi_notificationtargets`
					(`items_id`, `type`, `notifications_id`)
					values
					(3, 1, $notiid)";
        $DB->query($insert);
    }
    
    return true;
}

/**
 * Uninstall process for plugin
 *
 * @return bool true if succeeded
 */
function plugin_Helpdeskrating_uninstall()
{
    global $DB;
    
    if (TableExists("glpi_plugin_helpdeskrating")) {
        $query = "DROP TABLE `glpi_plugin_helpdeskrating`;";
        $DB->query($query) or die("error deleting glpi_plugin_helpdeskrating");
    }
    
    $query = "select `id` from `glpi_notificationtemplates` where `itemtype` = 'PluginHelpdeskratingHelpdeskrating' and `name` = 'Ticketrated'";
    $result = $DB->query($query) or die($DB->error());
    $notitempid = $DB->result($result, 0, 'id');
    
    if (! empty($notitempid)) {
        $del = "delete from `glpi_notificationtemplates` where `id` = $notitempid";
        $DB->query($del);
        $query = "select `id` from `glpi_notificationtemplatetranslations` where `notificationtemplates_id` = $notitempid";
        $res = $DB->query($query);
        $notitemptransid = $DB->result($res, 0, 'id');
        $del = "delete from `glpi_notificationtemplatetranslations` where `id` = $notitemptransid";
        $DB->query($del);
        $query = "select `id` from `glpi_notifications` where `name` = 'Ticket User Rating' and `event` = 'userrating'";
        $res = $DB->query($query);
        $notiid = $DB->result($res, 0, 'id');
        $del = "delete from `glpi_notifications` where `id` = $notiid";
        $DB->query($del);
        $query = "select `id` from `glpi_notificationtargets` where `notifications_id` = $notiid";
        $res = $DB->query($query);
        $notitarget = $DB->result($res, 0, 'id');
        $del = "delete from `glpi_notificationtargets` where `id` = $notitarget";
        $DB->query($del);
        $query = "select `id` from `glpi_notifications` where `name` = 'Ticket Tech Rating' and `event` = 'techrating'";
        $res = $DB->query($query);
        $notiid = $DB->result($res, 0, 'id');
        $del = "delete from `glpi_notifications` where `id` = $notiid";
        $DB->query($del);
        $query = "select `id` from `glpi_notificationtargets` where `notifications_id` = $notiid";
        $res = $DB->query($query);
        $notitarget = $DB->result($res, 0, 'id');
        $del = "delete from `glpi_notificationtargets` where `id` = $notitarget";
        $DB->query($del);
    }
    
    return true;
}

?>
