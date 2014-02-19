<?php
/*
 * @version $Id: setup.notification.php 20129 2013-02-04 16:53:59Z moyo $
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

include ('../inc/includes.php');

Session::checkSeveralRightsOr(array('notification' => 'r',
                                    'config'       => 'w'));

Html::header(_n('Notification', 'Notifications',2), $_SERVER['PHP_SELF'], "config", "mailing", -1);

if (isset($_POST['activate'])) {
   $config             = new Config();
   $tmp['id']          = $CFG_GLPI['id'];
   $tmp['use_mailing'] = 1;
   $config->update($tmp);
   Html::back();
}

if (!$CFG_GLPI['use_mailing']) {
   if (Session::haveRight("config","w")) {
      echo "<div class='center'>";
      Html::showSimpleForm($_SERVER['PHP_SELF'], 'activate', __('Enable followup via email'));
      echo "</div>";
   }
} else {
   if (!Session::haveRight("config","r")
       && Session::haveRight("notification","r")
       && $CFG_GLPI['use_mailing']) {
      Html::redirect($CFG_GLPI["root_doc"].'/front/notification.php');

   } else {
      echo "<table class='tab_cadre'>";
      echo "<tr><th>" . _n('Notification', 'Notifications',2)."</th></tr>";
      if (Session::haveRight("config","r")) {
         echo "<tr class='tab_bg_1'><td class='center'>".
              "<a href='notificationmailsetting.form.php'>". __('Email followups configuration') .
              "</a></td></tr>";
            echo "<tr class='tab_bg_1'><td class='center'><a href='notificationtemplate.php'>" .
                  _n('Notification template', 'Notification templates', 2) ."</a></td> </tr>";
      }

      if (Session::haveRight("notification","r") && $CFG_GLPI['use_mailing']) {
         echo "<tr class='tab_bg_1'><td class='center'>".
              "<a href='notification.php'>". _n('Notification', 'Notifications',2)."</a></td></tr>";
      } else {
            echo "<tr class='tab_bg_1'><td class='center'>" .
            __('Impossible to configure the notifications: please configure your email followup using the above configuration.') .
                 "</td></tr>";
      }
      echo "</table>";
   }
}

Html::footer();
?>
