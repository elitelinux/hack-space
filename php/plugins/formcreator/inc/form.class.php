<?php

class PluginFormcreatorForm extends CommonDBTM {

   static function canCreate() {
      return Session::haveRight('config', 'w');
   }

   static function canView() {
      return Session::haveRight('config', 'r');
   }
   
   static function getTypeName($nb = 0) {
      global $LANG;

      return $LANG['plugin_formcreator']["name"];
   }

   function defineTabs($options=array()) {
      global $LANG,$CFG_GLPI;

      $ong = array();

      $this->addStandardTab('PluginFormcreatorQuestion', $ong, $options);
      $this->addStandardTab('PluginFormcreatorTarget', $ong, $options);
      $this->addStandardTab('PluginFormcreatorSection', $ong, $options);
	  $this->addStandardTab('PluginFormcreatorCat', $ong, $options);
	  $this->addStandardTab('PluginFormcreatorTitle', $ong, $options);
      
      return $ong;
   }   
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $LANG;

      return true;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      return $LANG['plugin_formcreator']["headings"][0];
   }
         
   function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      
      $this->showTabs($options);
      $this->showFormHeader($options);
	  
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
	  echo __('Name');
	  echo "&nbsp;:</td>";
      echo "<td>";
      echo '<input type="text" name="name" value="'.$this->fields["name"].'" size="54"/>';
      echo "</td>";
      echo "</td><td>";
	  echo __('Active');
	  echo "&nbsp;:</td><td>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td></tr>";
      
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
	  echo __('Description');
	  echo "&nbsp;:</td>";
      echo "<td>";
      echo "<textarea name='content' cols='55' rows='6'>";
      echo $this->fields["content"];
      echo "</textarea>";
      echo "</td></td>";
      echo "<td>";
	  echo __('Select Language');
	  echo "</td><td>";
	  if ($this->fields["language"])
		Dropdown::showLanguages("language", array('value' => $this->fields["language"]));
	  else
		Dropdown::showLanguages("language", array('value' => $_SESSION['glpilanguage']));
      echo "</td></tr>";
	  echo '<tr><td>'.$LANG['plugin_formcreator']["cat"][3].'</td><td>';
	  PluginFormcreatorCat::getSelectCat($ID, $this->fields["cat"]);
	  echo '</td></tr>';
	       
      $this->showFormButtons($options);
      $this->addDivForTabs();
      
   }

   function prepareInputForAdd($input) {
      global $CFG_GLPI, $LANG;
      
      if (empty($input['name'])) {
         
         Session::addMessageAfterRedirect($LANG['plugin_formcreator']["error_form"][0], false, ERROR);
         return false;
      }
      
      return $input;
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['plugin_formcreator']["search"][0];
      
      $tab[1]['table'] = 'glpi_plugin_formcreator_forms';
      $tab[1]['field'] = 'name';
      $tab[1]['name']  = __('Name');
      $tab[1]['datatype']  = 'itemlink';
      
      $tab[0]['table'] = 'glpi_plugin_formcreator_forms';
      $tab[0]['field'] = 'content';
      $tab[0]['name']  = __('Description');;
      $tab[0]['datatype']  = 'itemlink';

      $tab[2]['table'] = 'glpi_plugin_formcreator_forms';
      $tab[2]['field'] = 'is_active';
      $tab[2]['name']  = __('Active');
      $tab[2]['datatype'] = 'bool';

      $tab[3]['table'] = 'glpi_plugin_formcreator_forms';
      $tab[3]['field'] = 'is_recursive';
      $tab[3]['name']  = __('Recursive');
      $tab[3]['datatype'] = 'bool';
	  
	  $tab[4]['table'] = 'glpi_plugin_formcreator_forms';
      $tab[4]['field'] = 'language';
      $tab[4]['name']  = __('Select Language');
      $tab[4]['datatype'] = 'text';
            
      return $tab;
   }
   
   static function getHelpdeskListForm() {
      global $LANG, $CFG_GLPI;
	  $language = $_SESSION["glpilanguage"];
	  $title = PluginFormcreatorTitle::getSelectTitle($language);
	  if (!empty($title)) {
		  echo '<table class="curvedEdges">';
		  echo '<td>'.PluginFormcreatorTitle::getSelectTitle($language).'</td>';
		  echo '</tr></table>';
		  echo "<br/><br/>";
	  }
      
      echo '<div class="center">';
      $form = new PluginFormcreatorForm;
      $listForm = $form->find("is_active = '1' ORDER BY `name` ASC");
      
	  $cat = PluginFormcreatorCat::getListing();
	  
	  
      $nbForm = 0;
      
      if(!empty($listForm)) {
         echo"<table class='tab_cadre_fixe fix_tab_height colspan='3''>";
			
			foreach ($cat as $cat_id => $cat_name) {
				$listForm_cat = $form->find("is_active = '1' AND cat = '$cat_id'ORDER BY `name` ASC");
				
				if (!empty($listForm_cat)) {
					if (count($cat) != 1) {
						echo '<tr style="cursor:pointer" onclick="Suite(\''.$cat_id.'\');">';
						echo "<td colspan='3' class='classcat'><img id='bas_".$cat_id."' src=\"../pics/deroulebas.jpg\"/><img id='haut_".$cat_id."' style=\"display:none\" src=\"../pics/deroulehaut.jpg\"/>&nbsp;&nbsp;".$cat_name."</td>";
						echo "</tr>";
						echo '<tbody id="cat_'.$cat_id.'" style="display:none">';
						echo "<tr>";
					} else {
						echo '<tr><td colspan="3" class="classcat">'.$cat_name.'</td></tr><tr>';
					}
					echo "<th style='width:25px;'></th>";
					echo "<th style='width:300px;'>".$LANG['plugin_formcreator']["headings"][0]."</th>";
					echo "<th style='width:605px;'>";
					echo __('Description');
					echo "</th>";
					echo "</tr>";
				}
				
				foreach ($listForm_cat as $form_id => $value) {
				   $question = new PluginFormcreatorQuestion;
				   $listQuestion = $question->find("plugin_formcreator_forms_id = '".$form_id."'");

				   if(!empty($listQuestion)) {
					if(Session::haveAccessToEntity($value['entities_id'],$value['is_recursive'])) {
						$link = $CFG_GLPI["root_doc"]."/plugins/formcreator/front/form.helpdesk.php";
				   
						if ($value['language'] == $_SESSION["glpilanguage"])
						{
							echo "<tr>";
								echo '<td><a href='.$link.'?form='.$form_id.'><img src="../pics/link.png"></a></td>';
								echo '<td><a href='.$link.'?form='.$form_id.'>'.$value['name'].'</a></td>';
								echo "<td>".$value['content']."</td>";
							echo "</tr>";

							$nbForm++;
						}
					}
				   }
				}
				if (!empty($listForm_cat) && (count($cat) != 1)) {
					echo '</tbody>';
				}
			}
            
            if(!$nbForm) {
               echo '<tr>';
               echo '<td class="center" colspan="3">'.$LANG['plugin_formcreator']["helpdesk"][1].'</td>';
               echo '</tr>';
            }
            
         echo "</table>";
         
      } else {
         echo $LANG['plugin_formcreator']["helpdesk"][1]; 
      }
         
      echo "</div>";
      
   }
   
   static function getHomeHelpdeskListForm() {
      global $LANG, $CFG_GLPI;
      
      $form = new PluginFormcreatorForm;
      $listForm = $form->find("is_active = '1'");
      
      echo "<table style='float: right;'>";
      echo "<tr><td class='top' width='450px'>";
         echo '<table class="tab_cadrehov">';
            echo '<tr>';
               echo '<th>';
                  echo '<div class="relative">'.$LANG['plugin_formcreator']["headings"][6].'</div>';
                echo '</th>';
            echo '</tr>';
      
      $nbForm = 0;
      
      foreach ($listForm as $form_id => $value) {
         
         $question = new PluginFormcreatorQuestion;
         $listQuestion = $question->find("plugin_formcreator_forms_id = '".$form_id."'");

         if(!empty($listQuestion)) {
            
            if(Session::haveAccessToEntity($value['entities_id'],$value['is_recursive'])) {
               $link = $CFG_GLPI["root_doc"]."/plugins/formcreator/front/form.helpdesk.php";
            
               echo "<tr>";
                  echo '<td><a href='.$link.'?form='.$form_id.'>'.$value['name'].'</a></td>';
               echo "</tr>";
               
               $nbForm++;
            }

         }
            
      }
      
      if(!$nbForm) {
         echo '<tr>';
         echo '<td class="center" colspan="3">'.$LANG['plugin_formcreator']["helpdesk"][1].'</td>';
         echo '</tr>';
      }      
         
         echo '</table>';
      echo "</td></tr>";
      echo "</table>";
      
   }
   
   function createDefaultTarget($formID) {
      global $LANG;

      $target = new PluginFormcreatorTarget;

      $defaultTarget['name'] = $LANG['plugin_formcreator']["default"]["target"][0];
      $defaultTarget['content'] = $LANG['plugin_formcreator']["default"]["target"][1];
      $defaultTarget['plugin_formcreator_forms_id'] = $formID;

      $targetID = $target->add($defaultTarget);

      return $targetID;

   }

   function createDefaultSection($formID,$targetID) {
      global $LANG;

      $section = new PluginFormcreatorSection;

      $defaultSection['name'] = $LANG['plugin_formcreator']["default"]["section"][0];
      $defaultSection['content'] = $LANG['plugin_formcreator']["default"]["section"][1];
      $defaultSection['plugin_formcreator_forms_id'] = $formID;
      $defaultSection['plugin_formcreator_targets_id'] = $targetID;

      $section->add($defaultSection);

   }
   
}

?>