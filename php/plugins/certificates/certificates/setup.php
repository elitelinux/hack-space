<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_certificates() {
	global $PLUGIN_HOOKS;
   
   $PLUGIN_HOOKS['csrf_compliant']['certificates'] = true;
	$PLUGIN_HOOKS['change_profile']['certificates'] = array('PluginCertificatesProfile','changeProfile');
	$PLUGIN_HOOKS['assign_to_ticket']['certificates'] = true;
   

	if (Session::getLoginUserID()) {
	
      // Params : plugin name - string type - number - attributes
      Plugin::registerClass('PluginCertificatesCertificate', array(
         'linkgroup_tech_types' => true,
         'linkuser_tech_types' => true,
         'document_types' => true,
         'helpdesk_visible_types' => true,
         'ticket_types'         => true,
         'contract_types' => true,
         'notificationtemplates_types' => true
      ));
      
      Plugin::registerClass('PluginCertificatesConfig',
                         array('addtabon' => 'CronTask'));
                         
      Plugin::registerClass('PluginCertificatesProfile',
                         array('addtabon' => 'Profile'));

      if (class_exists('PluginAccountsAccount')) {
         PluginAccountsAccount::registerType('PluginCertificatesCertificate');
      }
   
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1) {

			$_SESSION["glpi_plugin_environment_certificates"]=1;

			// Display a menu entry ?
			if (plugin_certificates_haveRight("certificates","r")) {
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['certificates']['title'] = PluginCertificatesCertificate::getTypeName(2);
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['certificates']['page'] = '/plugins/certificates/front/certificate.php';
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['certificates']['links']['search'] = '/plugins/certificates/front/certificate.php';
			}

			if (plugin_certificates_haveRight("certificates","w")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['certificates']['links']['add'] = '/plugins/certificates/front/certificate.form.php';
				$PLUGIN_HOOKS['use_massive_action']['certificates']=1;

			}
		} else {

			// Display a menu entry ?
			if (plugin_certificates_haveRight("certificates","r")) {
				$PLUGIN_HOOKS['menu_entry']['certificates'] = 'front/certificate.php';
				$PLUGIN_HOOKS['submenu_entry']['certificates']['search'] = 'front/certificate.php';
			}

			if (plugin_certificates_haveRight("certificates","w")) {
				$PLUGIN_HOOKS['submenu_entry']['certificates']['add'] = 'front/certificate.form.php?new=1';
				$PLUGIN_HOOKS['use_massive_action']['certificates']=1;

			}
		}
		
		if (class_exists('PluginCertificatesCertificate_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['certificates'] 
                        = array('Profile'=>array('PluginCertificatesProfile', 'purgeProfiles'));
      }
      
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['certificates'] = 'plugin_certificates_postinit';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_certificates() {

	return array (
		'name' => _n('Certificate', 'Certificates', 2, 'certificates'),
		'version' => '1.9.0',
		'license' => 'GPLv2+',
		'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
		'homepage'=>'https://forge.indepnet.net/projects/show/certificates',
		'minGlpiVersion' => '0.84',// For compatibility / no install in version < 0.80
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_certificates_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'certificates');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_certificates_check_config() {
	return true;
}

function plugin_certificates_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_certificates_profile"][$module])
	&& in_array($_SESSION["glpi_plugin_certificates_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

?>