<?php
/*
 * @version $Id: notificationtemplatetranslation.class.php 20129 2013-02-04 16:53:59Z moyo $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class Notification
class NotificationTemplateTranslation extends CommonDBChild {

   // From CommonDBChild
   static public $itemtype  = 'NotificationTemplate';
   static public $items_id  = 'notificationtemplates_id';

   public $dohistory = true;


   static function getTypeName($nb=0) {
      return _n('Template translation', 'Template translations', $nb);
   }


   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   /**
    * @see CommonDBTM::getName()
   **/
   function getName($options=array()) {
      global $CFG_GLPI;

      if ($this->getField('language') != '') {
         $toadd = $CFG_GLPI['languages'][$this->getField('language')][0];
      } else {
         $toadd = self::getTypeName(1);
      }

      return $toadd;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $ong['empty'] = $this->getTypeName(1); // History as single tab seems "strange"
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   function showForm($ID, $options) {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("config", "w")) {
         return false;
      }
      $notificationtemplates_id = -1;
      if (isset($options['notificationtemplates_id'])) {
         $notificationtemplates_id = $options['notificationtemplates_id'];
      }

      if ($this->getFromDB($ID)) {
         $notificationtemplates_id = $this->getField('notificationtemplates_id');
      }

      $this->initForm($ID, $options);
      $template = new NotificationTemplate();
      $template->getFromDB($notificationtemplates_id);

      Html::initEditorSystem('content_html');

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".NotificationTemplate::getTypeName()."</td>";
      echo "<td colspan='2'><a href='".Toolbox::getItemTypeFormURL('NotificationTemplate').
                           "?id=".$notificationtemplates_id."'>".$template->getField('name')."</a>";
      echo "</td><td>".
           "<a class='vsubmit' href='#' onClick=\"var w=window.open('".$CFG_GLPI["root_doc"].
             "/front/popup.php?popup=list_notificationtags&amp;sub_type=".
             $template->getField('itemtype')."' ,'glpipopup', 'height=400, width=1000, top=100, ".
             "left=100, scrollbars=yes' );w.focus();\">".__('Show list of available tags')."</a>".
           "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Language') . "</td><td colspan='3'>";

      //Get all used languages
      $used = self::getAllUsedLanguages($notificationtemplates_id);
      if ($ID > 0) {
         if (isset($used[$this->getField('language')])) {
            unset($used[$this->getField('language')]);
         }
      }
      Dropdown::showLanguages("language", array('display_emptychoice' => true,
                                                'value'               => $this->fields['language'],
                                                'used'                => $used));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Subject') . "</td>";
      echo "<td colspan='3'>";
      echo "<input type='text' name='subject'size='100' value='".$this->fields["subject"]."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>";
      _e('Email text body');
      echo "<br>".__('(leave the field empty for a generation from HTML)');
      echo "</td><td colspan='3'>";
      echo "<textarea cols='100' rows='15' name='content_text' >".$this->fields["content_text"];
      echo "</textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" ;
      _e('Email HTML body');
      echo "</td><td colspan='3'>";
      echo "<textarea cols='100' rows='15' name='content_html'>".$this->fields["content_html"];
      echo "</textarea>";
      echo "<input type='hidden' name='notificationtemplates_id' value='".
             $template->getField('id')."'>";
      echo "</td></tr>";
      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;
   }


   /**
    * @param $template        NotificationTemplate object
    * @param $options   array
   **/
   function showSummary(NotificationTemplate $template, $options=array()) {
      global $DB, $CFG_GLPI;

      $nID     = $template->getField('id');
      $canedit = Session::haveRight("config", "w");

      if ($canedit) {
         echo "<div class='center'>".
              "<a class='vsubmit' href='".Toolbox::getItemTypeFormURL('NotificationTemplateTranslation').
                "?notificationtemplates_id=".$nID."'>". __('Add a new translation')."</a></div><br>";
      }

      echo "<div class='center' id='tabsbody'>";

      Session::initNavigateListItems('NotificationTemplateTranslation',
            //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                     sprintf(__('%1$s = %2$s'),
                                             NotificationTemplate::getTypeName(1),
                                             $template->getName()));

      if ($canedit) {
         $rand = mt_rand();
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $paramsma = array();
         Html::showMassiveActions(__CLASS__, $paramsma);
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      if ($canedit) {
         echo "<th width='10'>";
         Html::checkAllAsCheckbox('mass'.__CLASS__.$rand);
         echo "</th>";
      }
      echo "<th>".__('Language')."</th></tr>";

      foreach ($DB->request('glpi_notificationtemplatetranslations',
                            array('notificationtemplates_id' => $nID)) as $data) {

         if ($this->getFromDB($data['id'])) {
            Session::addToNavigateListItems('NotificationTemplateTranslation',$data['id']);
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td class='center'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }
            echo "<td class='center'>";
            echo "<a href='".Toolbox::getItemTypeFormURL('NotificationTemplateTranslation').
                  "?id=".$data['id']."&notificationtemplates_id=".$nID."'>";

            if ($data['language'] != '') {
               echo $CFG_GLPI['languages'][$data['language']][0];

            } else {
               _e('Default translation');
            }

            echo "</a></td></tr>";
         }
      }
      echo "</table>";

      if ($canedit) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }


   /**
    * @param $input  array
   */
   static function cleanContentHtml(array $input) {

      $txt = Html::clean(Toolbox::unclean_cross_side_scripting_deep($input['content_html']));
      $txt = trim(html_entity_decode($txt, 0, 'UTF-8'));

      if (!$txt) {
         // No HTML (nothing to display)
         $input['content_html'] = '';

      } else if (!$input['content_text']) {
         // Use cleaned HTML
         $input['content_text'] = $txt;
      }
      return $input;
   }


   function prepareInputForAdd($input) {
      return parent::prepareInputForAdd(self::cleanContentHtml($input));
   }


   function prepareInputForUpdate($input) {
      return parent::prepareInputForUpdate(self::cleanContentHtml($input));
   }


   function getSearchOptions() {

      $tab                     = array();
      $tab['common']           = __('Characteristics');

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'language';
      $tab[1]['name']          = __('Language');
      $tab[1]['datatype']      = 'language';
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'subject';
      $tab[2]['name']          = __('Subject');
      $tab[2]['massiveaction'] = false;
      $tab[2]['datatype']      = 'string';

      $tab[3]['table']         = $this->getTable();
      $tab[3]['field']         = 'content_html';
      $tab[3]['name']          = __('Email HTML body');
      $tab[3]['datatype']      = 'text';
      $tab[3]['htmltext']      = 'true';
      $tab[3]['massiveaction'] = false;

      $tab[4]['table']         = $this->getTable();
      $tab[4]['field']         = 'content_text';
      $tab[4]['name']          = __('Email text body');
      $tab[4]['datatype']      = 'text';
      $tab[4]['massiveaction'] = false;

      return $tab;
   }


   /**
    * @param $language_id
   **/
   static function getAllUsedLanguages($language_id) {

      $used_languages = getAllDatasFromTable('glpi_notificationtemplatetranslations',
                                             'notificationtemplates_id='.$language_id);
      $used           = array();

      foreach ($used_languages as $used_language) {
         $used[$used_language['language']] = $used_language['language'];
      }

      return $used;
   }


   /**
    * @param $itemtype
   **/
   static function showAvailableTags($itemtype) {

      $target = NotificationTarget::getInstanceByType($itemtype);
      $target->getTags();

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>".__('Tag')."</th>
                <th>".__('Label')."</th>
                <th>"._n('Event', 'Events', 1)."</th>
                <th>".__('Type')."</th>
                <th>".__('Possible values')."</th>
            </tr>";

      $tags = array();

      foreach ($target->tag_descriptions as $tag_type => $infos) {
         foreach ($infos as $key => $val) {
            $infos[$key]['type'] = $tag_type;
         }
         $tags = array_merge($tags,$infos);
      }
      ksort($tags);
      foreach ($tags as $tag => $values) {

         if ($values['events'] == NotificationTarget::TAG_FOR_ALL_EVENTS) {
            $event = __('All');
         } else {
            $event = implode(', ',$values['events']);
         }

         $action = '';

         if ($values['foreach']) {
            $action = __('List of values');
         } else {
            $action = __('Single value');
         }

         if (!empty($values['allowed_values'])) {
            $allowed_values = implode(',',$values['allowed_values']);
         } else {
            $allowed_values = '';
         }

         echo "<tr class='tab_bg_1'><td>".$tag."</td>".
              "<td>";
         if ($values['type'] == NotificationTarget::TAG_LANGUAGE) {
            printf(__('%1$s: %2$s'), __('Label'), $values['label']);
         } else {
               echo $values['label'];
         }
         echo "</td><td>".$event."</td>".
              "<td>".$action."</td>".
              "<td>".$allowed_values."</td>".
              "</tr>";
      }
      echo "</table></div>";
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'NotificationTemplate' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(self::getTypeName(2),
                                              countElementsInTable($this->getTable(),
                                                                   "notificationtemplates_id
                                                                        = '".$item->getID()."'"));
               }
               return self::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'NotificationTemplate') {
         $temp = new self();
         $temp->showSummary($item);
      }
      return true;
   }


   /**
    * Display debug information for current object
    * NotificationTemplateTranslation => translation preview
    *
    * @since version 0.84
   **/
   function showDebug() {

      $template = new NotificationTemplate();
      if (!$template->getFromDB($this->fields['notificationtemplates_id'])) {
         return;
      }

      $itemtype = $template->getField('itemtype');
      if (!($item = getItemForItemtype($itemtype))) {
         return;
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Preview')."</th></tr>";

      $oktypes = array('CartridgeItem', 'ConsumableItem', 'Contract', 'Crontask',
                       'Problem', 'Ticket', 'User');

      if (!in_array($itemtype, $oktypes)) {
         // this itemtype doesn't work, need to be fixed
         echo "<tr class='tab_bg_2 center'><td>".NOT_AVAILABLE."</td>";
         echo "</table></div>";
         return;
      }

      // Criteria Form
      $key   = getForeignKeyFieldForItemType($item->getType());
      $id    = Session::getSavedOption(__CLASS__, $key, 0);
      $event = Session::getSavedOption(__CLASS__, $key.'_event', '');

      echo "<tr class='tab_bg_2'><td>".$item->getTypeName(1)."&nbsp;";
      $item->dropdown(array('value'     => $id,
                            'on_change' => 'reloadTab("'.$key.'="+this.value)'));
      echo "</td><td>".NotificationEvent::getTypeName(1)."&nbsp;";
      NotificationEvent::dropdownEvents($item->getType(),
                                        array('value'     => $event,
                                              'on_change' => 'reloadTab("'.$key.'_event="+this.value)'));
      echo "</td>";

      // Preview
      if ($event
          && $item->getFromDB($id)) {
         $options = array('_debug' => true);

         // TODO Awfull Hack waiting for https://forge.indepnet.net/issues/3439
         $multi   = array('alert', 'alertnotclosed', 'end', 'notice',
                          'periodicity', 'periodicitynotice');
         if (in_array($event, $multi)) {
            // Won't work for Cardridge and Consumable
            $options['entities_id'] = $item->getEntityID();
            $options['items']       = array($item->getID() => $item->fields);
         }
         $target = NotificationTarget::getInstance($item, $event, $options);
         $infos  = array('language'=> $_SESSION['glpilanguage']);

         $template->resetComputedTemplates();
         $template->setSignature(Notification::getMailingSignature($_SESSION['glpiactive_entity']));
         if ($tid = $template->getTemplateByLanguage($target, $infos, $event, $options)) {

            $data = $template->templates_by_languages[$tid];

            echo "<tr><th colspan='2'>".__('Subject')."</th></tr>";
            echo "<tr class='tab_bg_2 b'><td colspan='2'>".$data['subject']."</td></tr>";

            echo "<tr><th>".__('Email text body')."</th>";
            echo "<th>".__('Email HTML body')."</th></tr>";
            echo "<tr class='tab_bg_2'><td>".nl2br($data['content_text'])."</td>";
            echo "<td>".$data['content_html']."</td></tr>";
         }
      }
      echo "</table></div>";
   }
}
?>
