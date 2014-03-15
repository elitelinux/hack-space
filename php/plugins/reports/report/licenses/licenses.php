<?php
/*
 * @version $Id: licenses.php 246 2013-05-02 13:03:33Z yllen $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Detailed license report
$report = new PluginReportsAutoReport(__('licenses_report_title', 'reports'));

$license = new PluginReportsSoftwareWithLicenseCriteria($report);

$license->setSqlField("`glpi_softwarelicenses`.`softwares_id`");

$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()
    && $license->getParameterValue() >0) {

   $report->setSubNameAuto();

   $report->setColumns(array("license" => _n('License', 'Licenses', 2),
                             "serial"  => __('Serial number'),
                             "nombre"  => _x('Quantity', 'Number'),
                             "type"    => __('Type'),
                             "buy"     => __('Purchase version'),
                             "used"    => __('Used version', 'reports'),
                             "expire"  => __('Expiration'),
                             "comment" => __('Comments'),
                             "name"    => __('Computer')));

   $query = "SELECT `glpi_softwarelicenses`.`name` AS license,
                    `glpi_softwarelicenses`.`serial`,
                    `glpi_softwarelicenses`.`number` AS nombre,
                    `glpi_softwarelicensetypes`.`name` AS type,
                    buyversion.`name` AS buy,
                    useversion.`name` AS used,
                    `glpi_softwarelicenses`.`expire`,
                    `glpi_softwarelicenses`.`comment`,
                    `glpi_computers`.`name`
             FROM `glpi_softwarelicenses`
             LEFT JOIN `glpi_softwares`
                  ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
             LEFT JOIN `glpi_computers_softwarelicenses`
                  ON (`glpi_softwarelicenses`.`id`
                        = `glpi_computers_softwarelicenses`.`softwarelicenses_id`)
             LEFT JOIN `glpi_computers`
                  ON (`glpi_computers`.`id` = `glpi_computers_softwarelicenses`.`computers_id`)
             LEFT JOIN `glpi_softwareversions` AS buyversion
                  ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`)
             LEFT JOIN `glpi_softwareversions` AS useversion
                  ON (useversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_use`)
             LEFT JOIN `glpi_softwarelicensetypes`
                  ON (`glpi_softwarelicensetypes`.`id`
                        =`glpi_softwarelicenses`.`softwarelicensetypes_id`)
             LEFT JOIN `glpi_entities`
                  ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)".
             $report->addSqlCriteriasRestriction("WHERE")."
                   AND `glpi_softwares`.`is_deleted` = '0'
                   AND `glpi_softwares`.`is_template` = '0' " .
                   getEntitiesRestrictRequest(' AND ', 'glpi_softwares') ."
             ORDER BY license";

   $report->setGroupBy("license");
   $report->setSqlRequest($query);
   $report->execute();

}
?>