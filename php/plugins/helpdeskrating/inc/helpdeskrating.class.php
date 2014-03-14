<?php

/************************************************************************************************
 *
 * File: inc/helpdeskrating.class.php
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
 * Class of the defined type
 *
 * @author Christian Deinert
 */
class PluginHelpdeskratingHelpdeskrating extends CommonDBTM
{
    
    // From CommonGLPI
    public static $table = 'glpi_plugin_helpdeskrating';

    public $type = 'PluginHelpdeskratingHelpdeskrating';

    /**
     * returns the localized name of the type
     *
     * @static
     *
     * @return string
     */
    static function getTypeName($nb = 0)
    {
        return 'PluginHelpdeskratingHelpdeskrating';
    }

    /**
     * returns if the user has the permission to write
     *
     * @return Boolean
     */
    static function canCreate()
    {
        return plugin_helpdeskrating_haveRight('helpdeskrating', 'w');
    }

    /**
     * returns if the user has the permission to read
     *
     * @return Bboolean
     */
    static function canView()
    {
        return plugin_helpdeskrating_haveRight('helpdeskrating', 'r');
    }

    /**
     * Define headings added by the plugin
     *
     * @param CommonGLPI $item
     * @param number $withtemplate
     * @return string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (! $withtemplate) {
            switch ($item->getType()) {
                case 'Ticket':
                    return array(
                        1 => __('Helpdeskrating', 'helpdeskrating')
                    );
                    break;
            }
        }
        
        return '';
    }

    /**
     * Define headings actions added by the plugin
     *
     * @param CommonGLPI $item
     * @param number $tabnum
     * @param number $withtemplate
     * @return boolean
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (! $withtemplate) {
            echo '<div class="center">';
            
            switch ($item->getType()) {
                case 'Ticket':
                    echo '<div align="center">';
                    // echo $this->showRatingForm($item->getField('id'), $item->getField('status'));
                    echo self::showRatingForm($item->getField('id'), $item->getField('status'));
                    echo '</div>';
                    break;
            }
            
            echo '</div>';
        }
        
        return true;
    }

    /**
     * Prints the rating form or the rating table
     *
     * @param int $tickets_id
     * @param string $status
     * @param string $withtemplate
     */
    public static function showRatingForm($tickets_id, $status, $withtemplate = '')
    {
        global $DB, $CFG_GLPI;
        
        // Show the rating form for the user
        $sql = "select users_id
				from `glpi_tickets_users`
				where tickets_id = '$tickets_id'
				and type = 1";
        $result = $DB->query($sql);
        $show_form_user = FALSE;
        
        while ($data = $DB->fetch_assoc($result)) {
            if ($data['users_id'] == Session::getLoginUserID()) {
                $show_form_user = TRUE;
            }
        }
        
        // Show the rating form for the technician
        $sql = "select users_id
				from `glpi_tickets_users`
				where tickets_id = '$tickets_id'
				and type = 2";
        $result = $DB->query($sql);
        $show_form_tech = FALSE;
        
        while ($data = $DB->fetch_assoc($result)) {
            if ($data['users_id'] == Session::getLoginUserID()) {
                $show_form_tech = TRUE;
            }
        }
        
        // $_SessionHaveRight_update = Session::haveRight('update_ticket', 1);
        // $_SessionHaveRight_assign = Session::haveRight('assign_ticket', 1);
        
        $sql = "select * from " . self::$table . " where tickets_id = '$tickets_id' and type='user';";
        $result = $DB->query($sql);
        $data = $DB->fetch_assoc($result);
        $id1 = $data['id'];
        $sql = "select * from " . self::$table . " where tickets_id = '$tickets_id' and type='tech';";
        $result = $DB->query($sql);
        $data2 = $DB->fetch_assoc($result);
        $id2 = $data2['id'];
        
        if ($status == CommonITILObject::SOLVED || $status == CommonITILObject::CLOSED) { // Rating is avaiable
            
            if ($show_form_user && $id1 == '') { // User's rating form is avaiable
                                                 // create Form and Table
                echo '<form action="' . self::getFormURL() . '" method="post">';
                echo '<input type="hidden" name="tickets_id" value="' . $tickets_id . '"/>';
                echo '<input type="hidden" name="func" value="add"/>';
                echo '<input type="hidden" name="type" value="user"/>';
                echo '<table class="tab_cadre_fixe">';
                echo '<tr><th colspan="7">' . __('Rating of the Ticket (1=best, 6=worst)', 'helpdeskrating') . '</th></tr>';
                echo '<tr class="tab_bg_1">';
                echo '<th></th>';
                echo '<th>1<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_plus_2.png"></th>';
                echo '<th>2<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"></th>';
                echo '<th>3<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"></th>';
                echo '<th>4<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>5<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>6<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_minus_2.png"></th>';
                echo '</tr>';
                echo '<tr class="tab_bg_1">';
                echo '<td>' . __('Overall satisfaction', 'helpdeskrating') . ':</td>';
                echo '<td align="center"><input type="radio" name="overall" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="overall" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="overall" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="overall" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="overall" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="overall" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the solution', 'helpdeskrating') . ':</td>';
                echo '<td align="center"><input type="radio" name="solution" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="solution" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="solution" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="solution" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="solution" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="solution" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the technician', 'helpdeskrating') . '</td>';
                echo '<td align="center"><input type="radio" name="tech" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="tech" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="tech" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="tech" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="tech" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="tech" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the chronological sequence<br />(e.g. adherence to a appointed time-limit)', 'helpdeskrating') . '</td>';
                echo '<td align="center"><input type="radio" name="time" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="time" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="time" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="time" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="time" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="time" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Comment (optional)', 'helpdeskrating') . ':</td>';
                echo '<td colspan="7"><textarea name="comment_user" cols="50" rows="5"></textarea></td>';
                echo '</tr>';
                echo '</table>';
                echo '<div align="center"><input type="submit" value=" ' . __('Save', 'helpdeskrating') . ' "></div>';
                Html::closeForm();
            } elseif ($id1 != '') { // User has rated, show the ratings
                $rate_overall = $data['rating_overall'];
                $rate_solution = $data['rating_solution'];
                $rate_tech = $data['rating_tech'];
                $rate_termin = $data['rating_time'];
                
                echo '<table class="tab_cadre_fixe">';
                echo '<tr><th colspan="7">' . __('Rating of the Ticket (1=best, 6=worst)', 'helpdeskrating') . '</th></tr>';
                echo '<tr class="tab_bg_1">';
                echo '<th width="40%"></th>';
                echo '<th>1<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_plus_2.png"></th>';
                echo '<th>2<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"></th>';
                echo '<th>3<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"></th>';
                echo '<th>4<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>5<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>6<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_minus_2.png"></th>';
                echo '</tr>';
                echo '<tr class="tab_bg_1">';
                echo '<td>' . __('Overall satisfaction', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_overall == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_overall == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_overall == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_overall == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_overall == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_overall == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the solution', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_solution == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_solution == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_solution == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_solution == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_solution == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_solution == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the technician', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_tech == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_tech == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_tech == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_tech == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_tech == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_tech == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the chronological sequence<br />(e.g. adherence to a appointed time-limit)', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_termin == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_termin == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_termin == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_termin == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_termin == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_termin == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Comment (optional)', 'helpdeskrating') . '</td>';
                echo '<td colspan="7"><textarea name="comment_user" cols="50" rows="5" readonly="readonly">' . $data['comment'] . '</textarea></td>';
                echo '</tr>';
                echo '<tr class="tab_bg_1"><td colspan="7" align="center">';
                echo __('Date of rating: ', 'helpdeskrating') . substr($data['date'], 8, 2) . '.' . substr($data['date'], 5, 2) . '.' . substr($data['date'], 0, 4) . '  ' . substr($data['date'], 11);
                echo '</td></tr>';
                echo '</table>';
            }
            
            if ($show_form_tech && $id2 == '') { // Tech's rating form is avaiable
                                                 // create Form and Table
                echo '<form action="' . self::getFormURL() . '" method="post">';
                echo '<input type="hidden" name="tickets_id" value="' . $tickets_id . '"/>';
                echo '<input type="hidden" name="func" value="add"/>';
                echo '<input type="hidden" name="type" value="tech"/>';
                echo '<table class="tab_cadre_fixe">';
                echo '<tr><th colspan="7">' . __('Rating of the Ticket (1=best, 6=worst)', 'helpdeskrating') . '</th></tr>';
                echo '<tr class="tab_bg_1">';
                echo '<th></th>';
                echo '<th>1<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_plus_2.png"></th>';
                echo '<th>2<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"></th>';
                echo '<th>3<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"></th>';
                echo '<th>4<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>5<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>6<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_minus_2.png"></th>';
                echo '</tr>';
                echo '<tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the communication', 'helpdeskrating') . ':</td>';
                echo '<td align="center"><input type="radio" name="communication" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="communication" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="communication" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="communication" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="communication" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="communication" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the guidelines/specifications by the user', 'helpdeskrating') . ':</td>';
                echo '<td align="center"><input type="radio" name="guideline" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="guideline" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="guideline" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="guideline" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="guideline" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="guideline" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('How cooperative was the user', 'helpdeskrating') . '</td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="1"/></td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="2"/></td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="3"/></td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="4"/></td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="5"/></td>';
                echo '<td align="center"><input type="radio" name="cooperative" value="6"/></td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Comment (optional)', 'helpdeskrating') . ':</td>';
                echo '<td colspan="7"><textarea name="comment_tech" cols="50" rows="5"></textarea></td>';
                echo '</tr>';
                echo '</table>';
                echo '<div align="center"><input type="submit" value=" ' . __('Save', 'helpdeskrating') . ' " /></div>';
                Html::closeForm();
            } elseif (($show_form_tech && $id2 != '') || (! $show_form_tech && $id2 != '' && $data2['user_cansee'] == 1)) { // Tech has rated, show the ratings (if user shall see the ratings)
                $rate_comm = $data2['rating_tech_communication'];
                $rate_guide = $data2['rating_tech_guideline'];
                $rate_coop = $data2['rating_tech_cooperative'];
                
                echo '<table class="tab_cadre_fixe">';
                echo '<tr><th colspan="7">' . __('Rating of the Ticket (1=best, 6=worst)', 'helpdeskrating') . '</th></tr>';
                echo '<tr class="tab_bg_1">';
                echo '<th width="40%"></th>';
                echo '<th>1<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_plus_2.png"></th>';
                echo '<th>2<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"></th>';
                echo '<th>3<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/plus_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"></th>';
                echo '<th>4<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/neutral_2.png"><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>5<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_2.png"></th>';
                echo '<th>6<br /><img src="' . $CFG_GLPI["root_doc"] . '/plugins/helpdeskrating/pics/minus_minus_2.png"></th>';
                echo '</tr>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the communication', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_comm == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_comm == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_comm == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_comm == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_comm == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_comm == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Satisfaction with the guidelines/specifications by the user', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_guide == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_guide == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_guide == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_guide == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_guide == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_guide == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('How cooperative was the user', 'helpdeskrating') . ':</td>';
                echo '<td align="center">';
                if ($rate_coop == 1) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_coop == 2) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_coop == 3) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_coop == 4) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_coop == 5) {
                    echo 'X';
                }
                echo '</td>';
                echo '<td align="center">';
                if ($rate_coop == 6) {
                    echo 'X';
                }
                echo '</td>';
                echo '</tr>';
                echo '</tr><tr class="tab_bg_1">';
                echo '<td>' . __('Comment (optional)', 'helpdeskrating') . '</td>';
                echo '<td colspan="7"><textarea name="comment_tech" cols="50" rows="5" readonly="readonly">' . $data2["comment"] . '</textarea></td>';
                echo '<tr class="tab_bg_1"><td colspan="7" align="center">';
                echo __('Date of rating: ', 'helpdeskrating') . substr($data['date'], 8, 2) . '.' . substr($data['date'], 5, 2) . '.' . substr($data['date'], 0, 4) . '  ' . substr($data['date'], 11);
                echo '</td>';
                
                if ($show_form_tech) {
                    echo '</tr><tr class="tab_bg_1">';
                    echo '<td>' . __('Is the user allowed to see the rating?', 'helpdeskrating') . '</td>';
                    echo '<td colspan="7">';
                    
                    if ($data2['user_cansee'] != 1) {
                        echo __('No', 'helpdeskrating');
                        echo '<form action="' . self::getFormURL() . '" method="post">';
                        echo '<input type="hidden" name="tickets_id" value="' . $tickets_id . '"/>';
                        echo '<input type="hidden" name="func" value="cansee"/>';
                        echo '<input type="hidden" name="cansee" value="1"/>';
                        echo '<input type="submit" value=" ' . __('change', 'helpdeskrating') . ' " />';
                        Html::closeForm();
                    } else {
                        echo __('Yes', 'helpdeskrating');
                        echo '<form action="' . self::getFormURL() . '" method="post">';
                        echo '<input type="hidden" name="tickets_id" value="' . $tickets_id . '"/>';
                        echo '<input type="hidden" name="func" value="cansee"/>';
                        echo '<input type="hidden" name="cansee" value="0"/>';
                        echo '<input type="submit" value=" ' . __('change', 'helpdeskrating') . ' " />';
                        Html::closeForm();
                    }
                    
                    echo '</td>';
                }
                
                echo '</tr>';
                echo '</table>';
            }
        } else { // Rating not possible now
            echo __('Rating is possible if the ticket is solved or closed', 'helpdeskrating');
        }
    }

    /**
     * Writes the Rating into the database and raises a Notification event
     *
     * @param Array $items
     */
    function addRating($items)
    {
        global $DB, $CFG_GLPI;
        
        $_SessionHaveRight_update = Session::haveRight('update_ticket', 1);
        $_SessionHaveRight_assign = Session::haveRight('assign_ticket', 1);
        
        $type = 'user';
        $this->fields = array(
            'tickets_id' => $items['tickets_id']
        );
        
        if ($_SessionHaveRight_update || $_SessionHaveRight_assign) {
            $type = 'tech';
            $insert = "insert into glpi_plugin_helpdeskrating
						(
							tickets_id,
							date,
							rating_tech_communication,
							rating_tech_guideline,
							rating_tech_cooperative,
							comment,
							type
						)
						values
						(
							" . $items['tickets_id'] . ",
							'" . date('Y-m-d H:i:s') . "',
							" . $items['communication'] . ",
							" . $items['guideline'] . ",
							" . $items['cooperative'] . ",
							'" . $items['comment_tech'] . "',
							'$type'
						);";
            
            if (! empty($items['communication']) && ! empty($items['guideline']) && ! empty($items['cooperative'])) {
                $DB->query($insert) or die($insert . "<br />" . $DB->error());
            }
        } else {
            $type = 'user';
            $insert = "insert into glpi_plugin_helpdeskrating
						(
							tickets_id,
							date,
							rating_tech,
							rating_solution,
							rating_overall,
							rating_time,
							comment,
							type
						)
						values
						(
							" . $items['tickets_id'] . ",
							'" . date('Y-m-d H:i:s') . "',
							" . $items['tech'] . ",
							" . $items['solution'] . ",
							" . $items['overall'] . ",
							" . $items['time'] . ",
							'" . $items['comment_user'] . "',
							'$type'
						);";
            
            if (! empty($items['tech']) && ! empty($items['solution']) && ! empty($items['overall']) && ! empty($items['time'])) {
                $DB->query($insert) or die($insert . "<br />" . $DB->error());
                $DB->query("update glpi_tickets set status = " . CommonITILObject::CLOSED . " where id = " . $items['tickets_id'] . " ");
                
                if ($CFG_GLPI["use_mailing"]) {
                    // Mailing
                    NotificationEvent::raiseEvent("userrating", $this, array(
                        'tickets_id' => $items['tickets_id'],
                        'rating_tech' => $items['tech'],
                        'rating_solution' => $items['solution'],
                        'rating_time' => $items['time'],
                        'rating_overall' => $items['overall'],
                        'comment' => $items['comment_user']
                    ));
                }
            }
        }
        
        return true;
    }

    /**
     * Switches whether the user can see a rating of the technician or not
     * and writes this information into the database.
     * If the permission to see the rating is given, it also raises a Notification event
     *
     * @param Array $items
     */
    function switchUserSee($items)
    {
        global $DB, $CFG_GLPI;
        
        $this->fields = array(
            'tickets_id' => $items['tickets_id']
        );
        $update = "update `glpi_plugin_helpdeskrating`
					set `user_cansee` = " . $items['cansee'] . "
					where `tickets_id` = " . $items['tickets_id'] . "
					and   `type` = 'tech' ";
        
        if (($items['cansee'] == 1 || $items['cansee'] == 0) && ! empty($items['tickets_id'])) {
            $DB->query($update) or die($update . "<br />" . $DB->error());
            
            if ($CFG_GLPI["use_mailing"] && $items['cansee'] == 1) {
                NotificationEvent::raiseEvent("techrating", $this, array(
                    'tickets_id' => $items['tickets_id']
                ));
            }
        }
        
        return true;
    }

    /**
     * Return the table used to stor this object
     *
     * @return string
     *
     */
    static function getTable()
    {
        return self::$table;
    }
}

?>
