<?php
/************************************************************************************************
 *
 * File: inc/notificationtargethelpdeskrating.php
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
if (! defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * NotificationTarget class for Helpdeskrating
 */
class PluginHelpdeskratingNotificationTargetHelpdeskrating extends NotificationTarget
{

    /**
     * Get avaiable Event names
     *
     * @return array
     */
    function getEvents()
    {
        return array(
            'userrating' => __('Ticket was rated by the User', 'helpdeskrating'),
            'techrating' => __('Ticket was rated by the Technician', 'helpdeskrating')
        );
    }

    /**
     * Gets the data for the Notification text
     *
     * @param string $event
     * @param array $options
     */
    function getDatasForTemplate($event, $options = array())
    {
        global $DB, $CFG_GLPI;
        
        // static datas for the template
        $this->datas['##lang.helpdeskrating.name##'] = __('Helpdeskrating', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.text_rating0##'] = __('Rating of the Ticket (1=best, 6=worst)', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.event##'] = __('Ticket was rated', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.userrating##'] = __('Ticket was rated by the User', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.techrating##'] = __('Ticket was rated by the Technician', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.comment##'] = __('Comment', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratekat1##'] = __('Overall satisfaction', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratekat2##'] = __('Satisfaction with the solution', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratekat3##'] = __('Satisfaction with the technician', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratekat4##'] = __('Satisfaction with the chronological sequence', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratetech0##'] = __('Satisfaction with the communication', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratetech1##'] = __('Satisfaction with the guidelines/specifications by the user', 'helpdeskrating');
        $this->datas['##lang.helpdeskrating.ratetech2##'] = __('How cooperative was the user', 'helpdeskrating');
        
        // dynamic datas for the template
        $this->datas['##helpdeskrating.event##'] = $event;
        
        if ($event == 'userrating') {
            $sql = "select `rating_overall`, `rating_solution`, `rating_tech`, `rating_time`, `comment`
					from `glpi_plugin_helpdeskrating`
					where `tickets_id` = " . $this->obj->fields["tickets_id"] . "
					and   `type` = 'user'";
            $result = $DB->query($sql) or die($sql . ' ' . $DB->error());
            $data = $DB->fetch_assoc($result);
            $this->datas['##helpdeskrating.comment##'] = $data["comment"];
            $this->datas['##helpdeskrating.ratekat1##'] = $data["rating_overall"];
            $this->datas['##helpdeskrating.ratekat2##'] = $data["rating_solution"];
            $this->datas['##helpdeskrating.ratekat3##'] = $data["rating_tech"];
            $this->datas['##helpdeskrating.ratekat4##'] = $data["rating_time"];
            $this->datas['##helpdeskrating.ratetech0##'] = '';
            $this->datas['##helpdeskrating.ratetech1##'] = '';
            $this->datas['##helpdeskrating.ratetech2##'] = '';
        } elseif ($event == 'techrating') {
            $sql = "select `rating_tech_communication`, `rating_tech_guideline`, `rating_tech_cooperative`, `comment`
					from `glpi_plugin_helpdeskrating`
					where `tickets_id` = " . $this->obj->fields["tickets_id"] . "
					and   `type` = 'tech'
					and   `user_cansee` = 1";
            $result = $DB->query($sql) or die($sql . ' ' . $DB->error());
            $data = $DB->fetch_assoc($result);
            $this->datas['##helpdeskrating.comment##'] = $data["comment"];
            $this->datas['##helpdeskrating.ratekat1##'] = '';
            $this->datas['##helpdeskrating.ratekat2##'] = '';
            $this->datas['##helpdeskrating.ratekat3##'] = '';
            $this->datas['##helpdeskrating.ratekat4##'] = '';
            $this->datas['##helpdeskrating.ratetech0##'] = $data["rating_tech_communication"];
            $this->datas['##helpdeskrating.ratetech1##'] = $data["rating_tech_guideline"];
            $this->datas['##helpdeskrating.ratetech2##'] = $data["rating_tech_cooperative"];
        }
        
        $tickets_id = $this->obj->fields['tickets_id'];
        $sql = "select name, content from `glpi_tickets` where `id` = " . $this->obj->fields["tickets_id"] . "";
        $result = $DB->query($sql) or die($sql . ' ' . $DB->error());
        $data = $DB->fetch_assoc($result);
        $name = $data['name'];
        $this->datas['##helpdeskrating.ticketname##'] = $name;
        $this->datas['##helpdeskrating.ticketcontent##'] = $data['content'];
        $this->datas['##helpdeskrating.url##'] = urldecode($CFG_GLPI["url_base"] . "/front/ticket.form.php?id=" . $this->obj->fields["tickets_id"] . "&forcetab=helpdeskrating_1");
    }

    /**
     * Get the Prefix for the mailing subject
     *
     * Gives the Ticket id in Form of the standard GLPI Subject Prefix,
     * if the event is not 'alertnotclosed'
     *
     * @param
     *            String the Event that occured
     * @return String the Prefix for the mail
     */
    function getSubjectPrefix($event = '')
    {
        if ($event != 'alertnotclosed') {
            return sprintf("[GLPI #%07d] ", $this->obj->getField('tickets_id'));
        }
        
        parent::getSubjectPrefix();
    }

    /**
     * Fetches the target Address
     *
     * @param array $data
     * @param array $options
     */
    function getAddressesByTarget($data, $options = array())
    {
        $_TechInCharge = Notification::ASSIGN_TECH;
        
        // Look for all targets whose type is Notification::ITEM_USER
        switch ($data['type']) {
            case Notification::USER_TYPE:
                switch ($data['items_id']) {
                    case $_TechInCharge:
                        // Go shure, that the correct ticket and rating datas are delivered
                        $this->obj->fields['type'] = 'userrating';
                        $this->obj->fields['tickets_id'] = $options['tickets_id'];
                        $this->obj->fields['rating_tech'] = $options['rating_tech'];
                        $this->obj->fields['rating_solution'] = $options['rating_solution'];
                        $this->obj->fields['rating_time'] = $options['rating_time'];
                        $this->obj->fields['rating_overall'] = $options['rating_overall'];
                        $this->obj->fields['comment'] = $options['comment'];
                        
                        $this->getTicketAssignTechnicianAddress();
                        break;
                    
                    // Send to the user who's got the issue
                    case Notification::AUTHOR:
                        // Go shure, that the correct ticket and rating datas are delivered
                        $this->obj->fields['type'] = 'techrating';
                        $this->obj->fields['tickets_id'] = $options['tickets_id'];
                        
                        $this->getRecipientAddress();
                        break;
                }
        }
    }

    /**
     * Reads the email address of the assigned technician
     */
    function getTicketAssignTechnicianAddress()
    {
        global $DB;
        
        if (isset($this->obj->fields["tickets_id"])) {
            $query = "SELECT `glpi_useremails`.`email` as email, `glpi_users`.`language` as lang, `glpi_users`.`id` as id
                      FROM `glpi_tickets_users`, `glpi_users`, `glpi_useremails`
                      WHERE `glpi_tickets_users`.`tickets_id` = " . $this->obj->fields['tickets_id'] . "
                      and   `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                      and   `glpi_tickets_users`.`type` = 2
                      and   `glpi_users`.`id` = `glpi_useremails`.`users_id`";
            
            foreach ($DB->request($query) as $data) {
                $this->addToAddressesList($data);
            }
        }
    }

    /**
     * Reads the email address of the user
     */
    function getRecipientAddress()
    {
        global $DB;
        
        if (isset($this->obj->fields["tickets_id"])) {
            $query = "SELECT `glpi_useremails`.`email` as email, `glpi_users`.`language` as lang, `glpi_users`.`id` as id
                      FROM `glpi_tickets_users`, `glpi_users`, `glpi_useremails`
                      WHERE `glpi_tickets_users`.`tickets_id` = " . $this->obj->fields['tickets_id'] . "
                      and   `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                      and   `glpi_tickets_users`.`type` = 1
                      and   `glpi_users`.`id` = `glpi_useremails`.`users_id`";
            
            foreach ($DB->request($query) as $data) {
                $this->addToAddressesList($data);
            }
        }
    }
}

?>
