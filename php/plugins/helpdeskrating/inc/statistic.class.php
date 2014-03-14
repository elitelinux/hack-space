<?php

/************************************************************************************************
 *
 * File: inc/statistic.class.php
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
 * Class for Helpdeskrating Statistics
 *
 * @author Christian Deinert
 */
class PluginHelpdeskratingStatistic extends CommonGLPI
{

    public $table = 'glpi_plugin_helpdeskrating';

    /**
     * Returns Type name
     *
     * @param int $nb
     *
     * @static
     *
     * @return string
     */
    static function getTypeName($nb = 0)
    {
        // No plural
        return 'PluginHelpdeskratingStatistic';
    }

    /**
     * Adds the tabs
     *
     * @param array $options
     *
     * @return array
     */
    function defineTabs($options = array())
    {
        // $tabs = $this->getTabNameForItem($this, 0);
        $tabs = array();
        $this->addStandardTab(__CLASS__, $tabs, $options);
        
        return $tabs;
    }

    /**
     * Defines the names of the available tabs
     *
     * @param CommonGLPI $item
     * @param int $withtemplate
     *
     * @return array
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $tabs = array();
        $tabs[1] = __('Ratings all time', 'helpdeskrating');
        $tabs[2] = __('Ratings over time', 'helpdeskrating');
        $tabs[3] = __('Comments', 'helpdeskrating');
        $tabs[4] = __('Status spreading', 'helpdeskrating');
        
        return $tabs;
    }

    /**
     * Defines the Tabs-actions
     *
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @static
     *
     * @return boolean
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($tabnum) {
            default:
            case 1: // Statistics
                $item->showStatistics();
                break;
            case 2: // Rating over time
                $item->showRatingByTime();
                break;
            case 3: // Comments
                $item->showComments();
                break;
            case 4: // Status-Spreading
                $item->showStatusSpreading();
                break;
        }
        
        return true;
    }

    /**
     * Prints the "Statistics"-Tab
     *
     * @static
     *
     */
    static function showStatistics()
    {
        echo "<table class='tab_cadre_central'><tr>";
        echo "<td class='top'><br>";
        
        echo "<table class='tab_cadre_fixe' width='100%'><thead>";
        echo "<tr style='padding-top:10px;'><th colspan='2'><h1>";
        echo __('Alltime rating', 'helpdeskrating');
        echo "</h1></tr>";
        echo "</thead><tbody>";
        echo "<tr><td align='right'>";
        PluginHelpdeskratingStatistic::printOdometerChart('overall');
        echo "</td><td align='left'>";
        PluginHelpdeskratingStatistic::printOdometerChart('tech');
        echo "</td></tr>";
        echo "<tr><td align='right'>";
        PluginHelpdeskratingStatistic::printOdometerChart('solution');
        echo "</td><td align='left'>";
        PluginHelpdeskratingStatistic::printOdometerChart('time');
        echo "</td></tr>";
        echo "</tbody></table>";
        
        echo "</td></tr></table>";
    }

    /**
     * Prints the "Ratings over Time"-Tab
     *
     * @static
     *
     */
    static function showRatingByTime()
    {
        echo "<table class='tab_cadre_central'><tr>";
        echo "<td class='top'><br>";
        
        echo "<table class='tab_cadre_fixe' width='100%'><thead>";
        echo "<tr style='padding:10px;'><th><h1>";
        echo __('Progress of the ratings over time', 'helpdeskrating');
        echo "</h1></tr>";
        echo "</thead><tbody>";
        echo "<tr><td align='center'>";
        PluginHelpdeskratingStatistic::printAxisChart('overall');
        echo "</td></tr><tr><td align='center'>";
        PluginHelpdeskratingStatistic::printAxisChart('tech');
        echo "</td></tr><tr><td align='center'>";
        PluginHelpdeskratingStatistic::printAxisChart('solution');
        echo "</td></tr><tr><td align='center'>";
        PluginHelpdeskratingStatistic::printAxisChart('time');
        echo "</td></tr>";
        echo "</tbody></table>";
        
        echo "</td></tr></table>";
    }

    /**
     * Prints the "Comments"-Tab
     *
     * @static
     *
     */
    static function showComments()
    {
        echo "<table class='tab_cadre_central'><tr>";
        echo "<td class='top'><br>";
        
        echo "<table class='tab_cadre_fixe' width='100%'><thead>";
        echo "<tr style='padding-top:10px;'><th width='20%'><h1>";
        echo __('Overall satisfaction', 'helpdeskrating');
        echo "</h1></th><th width='40%'><h1>";
        echo __('Comment by user', 'helpdeskrating');
        echo "</h1></th><th width='40%'><h1>";
        echo __('Comment by technician', 'helpdeskrating');
        echo "</h1></th></tr>";
        echo "</thead><tbody>";
        PluginHelpdeskratingStatistic::printCommentTable();
        echo "</tbody></table>";
        
        echo "</td></tr></table>";
    }

    /**
     * Prints the "Status-spreading"-Tab
     *
     * @static
     *
     */
    static function showStatusSpreading()
    {
        echo "<table class='tab_cadre_central'><tr>";
        echo "<td class='top'><br>";
        
        echo "<table class='tab_cadre_fixe' width='100%'><thead>";
        echo "<tr style='padding-top:10px;'><th colspan='2'><h1>";
        echo __('Spreading of the statuses', 'helpdeskrating');
        echo "</h1></th></tr>";
        echo "</thead><tbody>";
        echo "<tr><td align='right'>";
        PluginHelpdeskratingStatistic::printPieChart('tech');
        echo "</td><td align='left'>";
        PluginHelpdeskratingStatistic::printPieChart('user');
        echo "</td></tr>";
        echo "</tbody></table>";
        
        echo "</td></tr></table>";
    }

    /**
     * Returns the logged in User ID in any GLPI Version
     *
     * @static
     *
     * @return int
     */
    static function getUserID()
    {
        return Session::getLoginUserID(false);
    }

    /**
     * Creates and prints the odometer chart in the statistic
     *
     * @param String $type
     *
     * @static
     *
     */
    static function printOdometerChart($type = 'overall')
    {
        global $CFG_GLPI;
        
        // Definition of ChartLabels
        $label = array(
            'overall' => __('Overall satisfaction', 'helpdeskrating'),
            'solution' => __('Satisfaction with the solution', 'helpdeskrating'),
            'tech' => __('Satisfaction with the technician', 'helpdeskrating'),
            'time' => __('Satisfaction with the chronological sequence', 'helpdeskrating')
        );
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        
        if ($uid) {
            $graph = new ezcGraphOdometerChart();
            $graph->title = $label[$type];
            
            // Graph Data
            $data = PluginHelpdeskratingStatistic::getAlltimeData($type);
            $graph->data['data'] = new ezcGraphArrayDataSet(array(
                $data
            ));
            $graph->data['data']->color[0] = '#00000000';
            
            // Graph Display Options
            $graph->options->startColor = '#00FF00';
            $graph->options->endColor = '#FF0000';
            $graph->options->borderWidth = 1;
            $graph->options->borderColor = '#BABDB6';
            $graph->options->markerWidth = 5;
            $graph->options->odometerHeight = .5;
            $graph->axis->min = 1;
            $graph->axis->max = 6;
            $graph->axis->label = $label[$type] . ": " . number_format($data, 2, '.', '');
            
            // Graph Output
            $filename = $uid . '_' . mt_rand() . '.svg';
            $graph->render(400, 150, GLPI_GRAPH_DIR . '/' . $filename);
            echo "<object data='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'
                      type='image/svg+xml' width='400' height='150'>
                      <param name='src' value='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'>
                      You need a browser capeable of SVG to display this image.
                     </object> ";
        }
    }

    /**
     * Reads the average rating for the odometer charts
     *
     * @param String $datatype
     *
     * @static
     *
     * @return int
     */
    static function getAlltimeData($datatype = 'overall')
    {
        global $DB;
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        
        $sql = "select sum(a.rating_tech) as tech, sum(a.rating_solution) as solution, sum(a.rating_time) as time,
                       sum(a.rating_overall) as overall, count(a.id) as count
                from glpi_plugin_helpdeskrating a, glpi_tickets b, glpi_tickets_users c
                where a.tickets_id = b.id
                and b.id = c.tickets_id
                and c.type = 2
                and c.users_id = " . $uid . "
                and b.status = '" . CommonITILObject::CLOSED . "'
                and a.type = 'user'";
        $result = $DB->query($sql);
        $data = $DB->fetch_assoc($result);
        
        if ($data['count'] != 0) {
            $rating = $data[$datatype] / $data['count'];
        } else {
            $rating = 0;
        }
        
        return $rating;
    }

    /**
     * Creates and prints the pie chart in the statistic
     *
     * @param String $type
     *
     * @static
     *
     */
    static function printPieChart($type = 'tech')
    {
        global $CFG_GLPI;
        
        // Definition of Chart Labels
        $label = array(
            'tech' => __('Rating by the user', 'helpdeskrating'),
            'user' => __('Rating by the technician', 'helpdeskrating')
        );
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        
        if ($uid) {
            // Get Graph Data
            $data = PluginHelpdeskratingStatistic::getSpreadingData($type);
            
            if ($data[__('in progress', 'helpdeskrating')] == 0 && $data[__('closed', 'helpdeskrating')] == 0 && $data[__('rated', 'helpdeskrating')] == 0) {
                
                echo "<h1>$label[$type]</h1>";
                echo __('no data available', 'helpdeskrating');
            } else {
                // Create Graph
                $graph = new ezcGraphPieChart();
                $graph->title = $label[$type];
                
                // Set Graph Data
                $graph->data['data'] = new ezcGraphArrayDataSet($data);
                
                // Graph Legend
                $graph->legend->position = ezcGraph::BOTTOM;
                $graph->data['data']->color[__('in progress', 'helpdeskrating')] = '#55575388';
                $graph->data['data']->highlight[__('in progress', 'helpdeskrating')] = true;
                $graph->data['data']->color[__('closed', 'helpdeskrating')] = '#F5900080';
                $graph->data['data']->color[__('rated', 'helpdeskrating')] = '#4E9A0680';
                
                // Graph Output
                $filename = $uid . '_' . mt_rand() . '.svg';
                $graph->render(400, 300, GLPI_GRAPH_DIR . '/' . $filename);
                echo "<object data='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'
                        type='image/svg+xml' width='400' height='300'>
                        <param name='src' value='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'>
                        You need a browser capeable of SVG to display this image.
                        </object> ";
            }
        }
    }

    /**
     * Reads the spreading of ticket statuses for the pie chart
     *
     * @param String $type
     *
     * @return array
     * @static
     *
     */
    static function getSpreadingData($type = 'tech')
    {
        global $DB;
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        $sql_all = "select count(a.id) as `all`
                    from glpi_tickets a, glpi_tickets_users b
                    where a.id = b.tickets_id
                    and b.type = 2
                    and b.users_id = " . $uid;
        $sql_solved = "select count(a.id) as solved
                       from glpi_tickets a, glpi_tickets_users b
                       where a.status = '" . CommonITILObject::CLOSED . "'
                       and a.id = b.tickets_id
                       and b.type = 2
                       and b.users_id = " . $uid;
        
        if ($type == 'tech') {
            $sql_rated = "select count(a.id) as rated
                          from glpi_tickets a, glpi_plugin_helpdeskrating b, glpi_tickets_users c
                          where a.id = b.tickets_id
                          and a.id = c.tickets_id
                          and c.type = 2
                          and c.users_id = " . $uid . "
                          and a.status = '" . CommonITILObject::CLOSED . "'
                          and b.type = 'user'";
        } else {
            $sql_rated = "select count(a.id) as rated
                          from glpi_tickets a, glpi_plugin_helpdeskrating b, glpi_tickets_users c
                          where a.id = b.tickets_id
                          and a.id = c.tickets_id
                          and c.type = 2
                          and c.users_id = " . $uid . "
                          and a.status = '" . CommonITILObject::CLOSED . "'
                          and b.type = 'tech'";
        }
        
        // All
        $result = $DB->query($sql_all);
        $data_all = $DB->fetch_assoc($result);
        
        // Solved
        $result = $DB->query($sql_solved);
        $data_solved = $DB->fetch_assoc($result);
        
        // Rated
        $result = $DB->query($sql_rated);
        $data_rated = $DB->fetch_assoc($result);
        
        return array(
            __('in progress', 'helpdeskrating') => ($data_all['all'] - $data_solved['solved']),
            __('closed', 'helpdeskrating') => ($data_solved['solved'] - $data_rated['rated']),
            __('rated', 'helpdeskrating') => $data_rated['rated']
        );
    }

    /**
     * Prints the Comments about and from the Technician
     *
     * @static
     *
     */
    static function printCommentTable()
    {
        global $DB, $CFG_GLPI;
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        $sql = "select a.rating_overall as rating_overall_user, a.`comment` as comment_user, b.`comment` as comment_tech
                from glpi_plugin_helpdeskrating a
                        left outer join glpi_plugin_helpdeskrating b on a.tickets_id = b.tickets_id and b.type = 'tech',
                     glpi_tickets_users c
                where a.tickets_id = c.tickets_id
                and   a.type = 'user'
                and   a.comment != ''
                and   c.type = 2
                and   c.users_id = " . $uid;
        
        if ($uid) {
            $result = $DB->query($sql);
            
            while ($data = $DB->fetch_assoc($result)) {
                echo "<tr><td align='center'>";
                echo $data['rating_overall_user'];
                echo "</td><td>";
                echo $data['comment_user'];
                echo "</td><td>";
                echo $data['comment_tech'];
                echo "</td></tr>";
            }
        }
    }

    /**
     * Creates and prints the Axis Chart
     *
     * @static
     *
     */
    static function printAxisChart($type = 'overall')
    {
        global $CFG_GLPI, $LANG;
        
        // Definition of Graph Labels
        $label = array(
            'overall' => __('Overall satisfaction', 'helpdeskrating'),
            'solution' => __('Satisfaction with the solution', 'helpdeskrating'),
            'tech' => __('Satisfaction with the technician', 'helpdeskrating'),
            'time' => __('Satisfaction with the chronological sequence', 'helpdeskrating')
        );
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        
        if ($uid) {
            // Get Graph Data
            $data = PluginHelpdeskratingStatistic::getTimedRatings($type);
            
            if (empty($data)) {
                echo "<h1>$label[$type]</h1>";
                echo __('no data available', 'helpdeskrating');
            } else {
                // Create Graph
                $graph = new ezcGraphLineChart();
                $graph->title = $label[$type];
                
                // Set Graph Data
                foreach ($data as $key => $val) {
                    $graph->data[$key] = new ezcGraphArrayDataSet($val);
                }
                
                // Graph Display options
                
                $graph->yAxis->min = 0;
                $graph->yAxis->max = 6;
                $graph->yAxis->majorStep = 1;
                $graph->xAxis = new ezcGraphChartElementNumericAxis();
                $graph->xAxis->min = 1;
                $graph->xAxis->max = 12;
                $graph->xAxis->majorStep = 1;
                
                // Graph Output
                $filename = $uid . '_' . mt_rand() . '.svg';
                $graph->render(800, 300, GLPI_GRAPH_DIR . '/' . $filename);
                echo "<object data='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'
                        type='image/svg+xml' width='800' height='300'>
                        <param name='src' value='" . $CFG_GLPI['root_doc'] . "/front/graph.send.php?file=$filename'>
                        You need a browser capeable of SVG to display this image.
                        </object> "; // */
            }
        }
    }

    /**
     * Reads the ratings grouped by year and month and creates an array
     *
     * @return array
     * @static
     *
     */
    static function getTimedRatings($type = 'overall')
    {
        global $DB, $CFG_GLPI;
        
        $uid = PluginHelpdeskratingStatistic::getUserID();
        $sql = "select year(a.date) as year, month(a.date) as month, count(a.id) as anz, sum(a.rating_tech) as tech,
                        sum(a.rating_solution) as solution, sum(a.rating_overall) as overall, sum(a.rating_time) as time
                from glpi_plugin_helpdeskrating a, glpi_tickets b, glpi_tickets_users c
                where a.tickets_id = b.id
                and b.id = c.tickets_id
                and c.type = 2
                and c.users_id = " . $uid . "
                and b.status = '" . CommonITILObject::CLOSED . "'
                and a.type = 'user'
                group by year(a.date), month(a.date)
                order by 1, 2";
        $rating_data = array();
        
        if ($uid) {
            $result = $DB->query($sql);
            $year = 1979;
            
            while ($data = $DB->fetch_assoc($result)) {
                if ($year != $data['year']) {
                    $year = $data['year'];
                    
                    for ($i = 1; $i <= 12; $i ++) {
                        $rating_data[$data['year']][$i] = 0;
                    }
                }
                
                $rating_data[$data['year']][$data['month']] = ($data[$type] / $data['anz']);
            }
        }
        
        return $rating_data;
    }
}

?>
