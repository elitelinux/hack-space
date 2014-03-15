<?php
/*
 * @version $Id: employment.class.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesEmployment extends CommonDBTM {

   static public $itemtype = 'PluginResourcesResource';
   static public $items_id = 'plugin_resources_resources_id';

   // From CommonDBTM
	public $dohistory=true;
   
   static function getTypeName($nb=0) {

      return _n('Employment', 'Employments', $nb, 'resources');
   }
   
   static function canCreate() {
      return plugin_resources_haveRight('employment', 'w');
   }

   static function canView() {
      return plugin_resources_haveRight('employment', 'r');
   }

   /**
   * Display tab for each emplyment
   **/
   function defineTabs($options=array()) {

      $ong = array();

      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);

      return $ong;
   }

   /**
   * Display employment's tab for each resource except template
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='PluginResourcesResource' && $this->canView() && $withtemplate==0) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2),
               countElementsInTable($this->getTable(),
               "`plugin_resources_resources_id` = '".$item->getID()."'"));
         }
         return self::getTypeName(2);

      }
      return '';
   }

   /**
    * display tab's content for each resource
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      if ($item->getType()=='PluginResourcesResource') {
         if (plugin_resources_haveRight('employment', 'w')) {
            self::addNewEmployments($item);
         }
         if (plugin_resources_haveRight('employment', 'r')) {
            self::showMinimalList();
         }
      }
      return true;
   }

   /**
   * Actions done when an employment is deleted from the database
   *
   * @return nothing
   **/
	function cleanDBonPurge() {

   }

   /**
    * allow search management
    */
   function getSearchOptions() {

      $tab = array();
      $tab['common']           = self::getTypeName(2);

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['datatype']        = 'number';
      $tab[2]['massiveaction']   = false;

      $tab[3]['table']           = 'glpi_plugin_resources_resources';
      $tab[3]['field']           = 'name';
      $tab[3]['name']            = __('Human resource', 'resources');
      $tab[3]['massiveaction']   = false;
      $tab[3]['datatype']        = 'dropdown';
      
      $tab[4]['table']           = 'glpi_plugin_resources_ranks';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('Rank', 'resources');
      $tab[4]['massiveaction']   = false;
      $tab[4]['datatype']        = 'dropdown';
      
      $tab[5]['table']           = 'glpi_plugin_resources_professions';
      $tab[5]['field']           = 'name';
      $tab[5]['name']            = __('Profession', 'resources');
      $tab[5]['massiveaction']   = false;
      $tab[5]['datatype']        = 'dropdown';
      
      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'begin_date';
      $tab[6]['name']            = __('Begin date');
      $tab[6]['datatype']        = 'date';

      $tab[7]['table']           = $this->getTable();
      $tab[7]['field']           = 'end_date';
      $tab[7]['name']            = __('End date');
      $tab[7]['datatype']        = 'date';

      $tab[8]['table']           = 'glpi_plugin_resources_employmentstates';
      $tab[8]['field']           = 'name';
      $tab[8]['name']            = __('Employment state', 'resources');
      $tab[8]['datatype']        = 'dropdown';
      
      $tab[9]['table']           = 'glpi_plugin_resources_employers';
      $tab[9]['field']           = 'completename';
      $tab[9]['name']            = __('Employer', 'resources');
      $tab[9]['datatype']        = 'dropdown';
      
      $tab[10]['table']          = $this->getTable();
      $tab[10]['field']          = 'ratio_employment_budget';
      $tab[10]['name']           = __('Ratio Employment / Budget', 'resources');
      $tab[10]['datatype']       = 'decimal';

      $tab[13]['table']          = 'glpi_plugin_resources_resources';
      $tab[13]['field']          = 'id';
      $tab[13]['name']           = __('Human resource', 'resources').__('ID');
      $tab[13]['massiveaction']  = false;

      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'date_mod';
      $tab[14]['name']           = __('Last update');
      $tab[14]['datatype']       = 'datetime';
      $tab[14]['massiveaction']  = false;

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';

      return $tab;
   }

   /**
    * Display the employment form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    *@return boolean item found
    **/
   function showForm($ID, $options=array("")) {
      global $CFG_GLPI;

      //validation des droits
      if (!$this->canView()) return false;

      $plugin_resources_resources_id = 0;
      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_resources_resources_id'=>$plugin_resources_resources_id);
         $this->check(-1,'w',$input);
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      if ($ID > 0) {
         $resource=$this->fields["plugin_resources_resources_id"];
      } else {
         $resource=$plugin_resources_resources_id;
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('value' => $this->fields["name"]));
      echo "</td>";

      echo "<td>".__('Employer', 'resources')."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesEmployer',
         array('value'  => $this->fields["plugin_resources_employers_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Profession', 'resources')."</td>";
      echo "<td>";
      $params = array('name' => 'plugin_resources_professions_id',
                    'value' => $this->fields['plugin_resources_professions_id'],
                    'entity' => $this->fields["entities_id"],
                    'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownRank.php",
                    'span' => 'span_rank',
                    'sort' => true
                  );
      PluginResourcesResource::showGenericDropdown('PluginResourcesProfession',$params);
      echo "</td>";
      echo "<td>".__('Rank', 'resources')."</td><td>";
      echo "<span id='span_rank' name='span_rank'>";
      if ($this->fields["plugin_resources_ranks_id"]>0) {
         echo Dropdown::getDropdownName('glpi_plugin_resources_ranks',
            $this->fields["plugin_resources_ranks_id"]);
      } else {
         _e('None');
      }
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Employment state', 'resources')."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesEmploymentState',
         array('value' => $this->fields["plugin_resources_employmentstates_id"],
               'entity'=> $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__('Ratio Employment / Budget', 'resources')."</td><td>";
      echo "<input type='text' name='ratio_employment_budget' value='".
         Html::formatNumber($this->fields["ratio_employment_budget"], true).
         "' size='14'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Begin date')."</td>";
      echo "<td>";
      Html::showDateFormItem("begin_date",$this->fields["begin_date"],true,true);
      echo "</td>";
      echo "<td>".__('End date')."</td>";
      echo "<td>";
      Html::showDateFormItem("end_date",$this->fields["end_date"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Human resource', 'resources')."</td>";
      echo "<td>";

      PluginResourcesResource::dropdown(array('name'   => 'plugin_resources_resources_id',
                              'value'  => $resource,
                              'entity' => $this->fields["entities_id"]));
                              
      echo "</td>";
      echo "<td>".__('Comments')."</td>";
      echo "<td><textarea cols='45' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center' colspan='6'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";
      echo "</tr>";


      if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
         $options['candel'] = false;
      }
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;

   }

   /**
    * adding of an employment in resource side
    *
    * @static
    * @param CommonGLPI $item
    */
   static function addNewEmployments(CommonGLPI $item){
      global $CFG_GLPI;

      $ID = $item->getField('id');

      $canedit = $item->can($ID, 'w');
      if (plugin_resources_haveRight('employment', 'w') && $canedit) {

         echo "<div align='center'>";
         echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/employment.form.php?plugin_resources_resources_id=".
            $ID."' >".__('Declare a new employment', 'resources')."</a></div>";
         echo "</div>";
      }

      echo "<div align='center'>";
      echo "<form method='post' name='addemployment' id='addemployment' action='".
         $CFG_GLPI["root_doc"]."/plugins/resources/front/employment.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='2'>";
      echo __('To affect an employment', 'resources')."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<input type='hidden' name='items_id' value='".$ID."'>";
      echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
      echo "<td class='center' class='tab_bg_2'>";
      echo self::getTypeName(1);
      $restrict = " `plugin_resources_resources_id`='0'";
      Dropdown::show('PluginResourcesEmployment',
         array('condition' => $restrict,
               'entity' => $item->getField("entities_id")));
      echo "</td><td class='center' class='tab_bg_2'>";
      echo "<input type='submit' name='add_item' value=\"".
         _sx('button','Add')."\" class='submit'></td></tr></table>";

      Html::closeForm();
      echo "</div>";

   }

   /**
    * Display the employments list of a resource
    *
    * @static
    * @param CommonGLPI $item
    */
   static function showMinimalList(){
      global $DB,$CFG_GLPI;

      $item = new self();
      $itemtype = $item->getType();
      $itemtable = $item->getTable();

      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();//
      $p['contains']    = array();//
      $p['searchtype']  = array();//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['id']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';

      foreach ($_POST as $key => $val) {
         $p[$key]=$val;
      }

      $PluginResourcesResource = new PluginResourcesResource();
      $PluginResourcesResource->getFromDB($p['id']);
      $canedit = $PluginResourcesResource->can($p['id'], 'w');

      if ($p['export_all']) {
         $p['start']=0;
      }

      // Manage defautll seachtype value : for bookmark compatibility
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key]='contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key]='contains';
            }
         }
      }

      $target= Toolbox::getItemTypeSearchURL($itemtype);

      $limitsearchopt=Search::getCleanedOptions($itemtype);

      $LIST_LIMIT=$_SESSION['glpilist_limit'];

      // Set display type for export if define
      $output_type=Search::HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type=$_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==Search::GLOBAL_SEARCH) {
            $LIST_LIMIT=Search::GLOBAL_DISPLAY_COUNT;
         }
      }

      $entity_restrict = $item->isEntityAssign();

      // Get the items to display
      $toview=Search::addDefaultToView($itemtype);

      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser($itemtype,Session::getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview,$val);
         }
      }

      // Add searched items
      if (count($p['field'])>0) {
         foreach($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview,$val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview,$p['sort']);
      }

      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count=count($toview);

      //// 1 - SELECT
      $query = "SELECT ".Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $query.= Search::addSelect($itemtype,$val,$key,0);
      }

      $query .= "`".$itemtable."`.`id` AS id ";

      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $query.= " FROM `".$itemtable."`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype,$itemtable,$already_link_tables);
      $query .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt[$itemtype]=&Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
            $searchopt[$itemtype][$val]["table"],
            $searchopt[$itemtype][$val]["linkfield"], 0, 0,
            $searchopt[$itemtype][$val]["joinparams"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                  $searchopt[$itemtype][$key]["table"],
                  $searchopt[$itemtype][$key]["linkfield"], 0, 0,
                  $searchopt[$itemtype][$key]["joinparams"]);
            }
         }
      }

      $query.= " WHERE `".$itemtable."`.`plugin_resources_resources_id` = '".$p['id']."'";



      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if (in_array('all',$p['field'])) {
         $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
            }
         }
      }
      $query.=$GROUPBY;
      //// 4 - ORDER
      $ORDER=" ORDER BY `id` ";
      foreach($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER= Search::addOrderBy($itemtype,$p['sort'],$p['order'],$key);
         }
      }
      $query.=$ORDER;

      // Get it from database

      if ($result = $DB->query($query)) {
         $numrows =  $DB->numrows($result);

         $globallinkto = Search::getArrayUrlLink("field",$p['field']).
            Search::getArrayUrlLink("link",$p['link']).
            Search::getArrayUrlLink("contains",$p['contains']).
            Search::getArrayUrlLink("field2",$p['field2']).
            Search::getArrayUrlLink("contains2",$p['contains2']).
            Search::getArrayUrlLink("itemtype2",$p['itemtype2']).
            Search::getArrayUrlLink("link2",$p['link2']);

         $parameters = "sort=".$p['sort']."&amp;order=".$p['order'].$globallinkto;

         if ($output_type==Search::GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>".$item->getTypeName();
               // More items
               if ($numrows>$p['start']+Search::GLOBAL_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters'>".__('All')."</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }

         if ($p['start']<$numrows) {

            if ($output_type==Search::HTML_OUTPUT && !$p['withtemplate']) {
               echo "<div align='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/employment.php?contains%5B0%5D=".
                  $p['id']."&field%5B0%5D=13&sort=1&start=0'>"._x('button','Search')."</a><br>";
               echo "</div>";
            }

            // Pager
            
            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $search_config="";
               if ($item->canCreate() && $canedit) {
                  $tmp = " class='pointer' onClick=\"var w = window.open('".$CFG_GLPI["root_doc"].
                        "/front/popup.php?popup=search_config&amp;itemtype=".$itemtype."' ,'glpipopup', ".
                        "'height=400, width=1000, top=100, left=100, scrollbars=yes' ); w.focus();\"";

                  $search_config = "<img alt='".__('Select default items to show')."' title='".__('Select default items to show').
                                    "' src='".$CFG_GLPI["root_doc"]."/pics/options_search.png' ";
                  $search_config .= $tmp.">";
               }
               //echo Search::showHeaderItem($output_type,$search_config,$header_num,"",0,$p['order']);
            }
            
            // Pager
            if ($output_type==Search::HTML_OUTPUT) {
               Html::printAjaxPager("",$p['start'],$numrows,$search_config);
               echo "<br>";
            }
           
            // Define begin and end var for loop
            // Search case
            $begin_display=$p['start'];
            $end_display=$p['start']+$LIST_LIMIT;

            // Export All case
            if ($p['export_all']) {
               $begin_display=0;
               $end_display=$numrows;
            }
            
            //massive action
            $sel="";
            if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";

            if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
               Html::openMassiveActionsForm('massform'.$itemtype);
               $massiveactionparams = array('num_displayed' => $end_display-$begin_display,
                                               'fixed'         => true,
                                               'is_deleted'    => $p['is_deleted']);
               Html::showMassiveActions($itemtype, $massiveactionparams);
            }
            // Add toview elements
            $nbcols=$toview_count;

            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }
            
            // Display List Header
            echo Search::showHeader($output_type,$end_display-$begin_display+1,$nbcols,1);
            
            $header_num=1;
            // Display column Headers for toview items
            $headers_line        = '';
            $headers_line_top    = '';
            $headers_line_bottom = '';
            echo Search::showNewLine($output_type);

            
            if (($output_type == Search::HTML_OUTPUT)
                && $item->canCreate() && $canedit) { // HTML display - massive modif
               
               $headers_line_top .= Search::showHeaderItem($output_type,
                                         Html::getCheckAllAsCheckbox('massform'.$itemtype),
                                         $header_num, "", 0, $p['order']);
               $headers_line_bottom .= Search::showHeaderItem($output_type,
                                         Html::getCheckAllAsCheckbox('massform'.$itemtype),
                                         $header_num, "", 0, $p['order']);
            }
           
            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto='';
               if (!isset($searchopt[$itemtype][$val]['nosort'])
                     || !$searchopt[$itemtype][$val]['nosort']) {
                  $linkto = "javascript:reloadTab('sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                           "&amp;start=".$p['start'].$globallinkto."')";
               }
               $headers_line .= Search::showHeaderItem($output_type,$searchopt[$itemtype][$val]["name"],
                                          $header_num,$linkto,$p['sort']==$val,$p['order']);
            }
            
            // End Line for column headers
            $headers_line .= Search::showEndLine($output_type);
            
            $headers_line_top    .= $headers_line;
            $headers_line_bottom .= $headers_line;

            echo $headers_line_top;

            $DB->data_seek($result,$p['start']);

            // Define begin and end var for loop
            // Search case
            $i=$begin_display;

            // Init list of items displayed
            if ($output_type==Search::HTML_OUTPUT) {

               Session::initNavigateListItems($itemtype, PluginResourcesResource::getTypeName(1)." = ".
                  (empty($PluginResourcesResource->fields['name']) ? "(".$p['id'].")" : $PluginResourcesResource->fields['name']));
            }

            // Num of the row (1=header_line)
            $row_num=1;
            // Display Loop
            while ($i < $numrows && $i<($end_display)) {

               $item_num=1;
               $data=$DB->fetch_array($result);
               $i++;
               $row_num++;

               echo Search::showNewLine($output_type,($i%2));

               Session::addToNavigateListItems($itemtype,$data['id']);

               $tmpcheck="";
               if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
                  $sel="";
                  $tmpcheck="<input type='checkbox' name='item[".$data["id"]."]' value='1' $sel>";

               }
               echo Search::showItem($output_type,$tmpcheck,$item_num,$row_num,"width='10'");

               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type,Search::giveItem($itemtype,$val,$data,$key),$item_num,
                     $row_num,
                     Search::displayConfigItem($itemtype,$val,$data,$key));
               }

               echo Search::showEndLine($output_type);
            }
            // Close Table
            $title="";
            // Create title
            if ($output_type==Search::PDF_OUTPUT_PORTRAIT 
                  || $output_type==Search::PDF_OUTPUT_LANDSCAPE) {
               $title.=__('List of employments', 'resources');
            }

            // Display footer
            echo Search::showFooter($output_type,$title);

            //massive action
            if ($item->canCreate() && $canedit && $output_type==Search::HTML_OUTPUT && $p['withtemplate']!=2) {
               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($itemtype, $massiveactionparams);
               Html::closeForm();
            } else {
               echo "</table></div>";
            }

            // Pager
            if ($output_type==Search::HTML_OUTPUT) {
               echo "<br>";
               Html::printPager($p['start'],$numrows,$target,$parameters);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
   }
   ////// CRON FUNCTIONS ///////
   //Cron action
   static function cronInfo($name){

      switch ($name) {
         case 'ResourcesLeaving':
            return array (
               'description' =>__('Updating leaving resources (declaring leaving, state of employment)', 'resources'));   // Optional
            break;
      }
      return array();
   }

   function queryLeavingResources() {

      $date=date("Y-m-d H:i:s");
      $query = "SELECT *
            FROM `glpi_plugin_resources_resources`
            WHERE `date_end` IS NOT NULL
            AND `date_end` < '".$date."'
            AND `is_leaving` = 0
            AND `is_template` = 0
            AND `is_deleted` = 0";

      return $query;

   }

   /**
    * Cron action on tasks : LeavingResources
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronResourcesLeaving($task=NULL) {
      global $DB;


      $cron_status = 0;
      $message=array();

      $PluginResourcesEmployment= new PluginResourcesEmployment();
      $query_expired = $PluginResourcesEmployment->queryLeavingResources();

      $querys = array(Alert::END=>$query_expired);

      $task_infos = array();
      $task_messages = array();

      foreach ($querys as $type => $query) {
         $task_infos[$type] = array();
         foreach ($DB->request($query) as $data) {

            //when a resource is leaving, current employment get default state
            $default = PluginResourcesEmploymentState::getDefault();
            // only current employment
            $restrict = "`plugin_resources_resources_id` = '".$data["id"]."'
                     AND ((`begin_date` < '".$data['date_end']."'
                           OR `begin_date` IS NULL)
                           AND (`end_date` > '".$data['date_end']."'
                                 OR `end_date` IS NULL)) ";
            $employments = getAllDatasFromTable("glpi_plugin_resources_employments",$restrict);
            if (!empty($employments)) {
               foreach ($employments as $employment) {
                  $values = array('plugin_resources_employmentstates_id'=> $default,
                               'end_date' => $data['date_end'],
                              'id'=> $employment['id']
                  );
                  $PluginResourcesEmployment->update($values);
               }
            }
            $resource = new PluginResourcesResource();
            $resource->getFromDB($data["id"]);
            $resource->update(array('is_leaving'=>1, 'id'=>$data["id"], 'date_end'=>$data['date_end']));
            $entity = $data['entities_id'];
            if(!isset($message[$entity])){
               $message=array($entity => '');
            }
            $message[$entity].= $data["name"]." ".$data["firstname"]." : ".
               Html::convDate($data["date_end"])."<br>\n";
            $task_infos[$type][$entity][] = $data;

            if (!isset($tasks_infos[$type][$entity])) {
               $task_messages[$type][$entity] = __('These resources left the company, linked current employment have been updated', 'resources')."<br />";
            }
            $task_messages[$type][$entity] .= $message[$entity];

         }
      }

      foreach ($querys as $type => $query) {

         foreach ($task_infos[$type] as $entity => $resources) {
            Plugin::loadLang('resources');

               $message = $task_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                     $entity).":  $message\n");
                  $task->addVolume(count($resources));
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                     $entity).":  $message");
               }
         }
      }

      return $cron_status;
   }
   
   //Massive action
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin 
            && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if (strpos($_SERVER['PHP_SELF'],"employment.php")) {
            $actions['Delete'] = __('Delete permanently');
         }
         if (Session::haveRight('transfer', 'r')
            && Session::isMultiEntitiesMode()) {
            $actions['Transfert'] = __('Transfer');
         }
      } elseif ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if (strpos($_SERVER['PHP_SELF'],"employment.php")) {
            $actions['Delete_Item'] = __('Delete permanently');
         }
      }
      return $actions;
   }

   function showSpecificMassiveActionsParameters($input = array()) {

      switch ($input['action']) {
         case "Delete" :
         case "Delete_Item" :
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;
         case "Transfert" :
            Dropdown::show('Entity');
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value='" . _sx('button', 'Post') . "'>";
            return true;
            break;

         default :
            return parent::showSpecificMassiveActionsParameters($input);
            break;
      }
      return false;
   }

   function doSpecificMassiveActions($input = array()) {

      $res = array('ok' => 0,
         'ko' => 0,
         'noright' => 0);

      $task_item = new PluginResourcesTask_Item();

      switch ($input['action']) {
         case "Transfert" :
            if ($input['itemtype'] == 'PluginResourcesEmployment') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $this->getFromDB($key);
                     $rank = PluginResourcesRank::transfer($PluginResourcesEmployment->fields["plugin_resources_ranks_id"], $data['entities_id']);
                     if ($rank > 0) {
                        $values["id"] = $key;
                        $values["plugin_resources_ranks_id"] = $rank;
                        $this->update($values);
                     }

                     $profession = PluginResourcesProfession::transfer($PluginResourcesEmployment->fields["plugin_resources_professions_id"],
                        $data['entities_id']);
                     if ($profession > 0) {
                        $values["id"] = $key;
                        $values["plugin_resources_professions_id"] = $profession;
                        $this->update($values);
                     }

                     unset($values);
                     $values["id"] = $key;
                     $values["entities_id"] = $input['entities_id'];

                     if ($this->update($values)) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               }
            }
            break;
         case "Delete" :
            if ($input['itemtype'] == 'PluginResourcesEmployment') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     if ($this->can(-1,'w',$input)){
                        if ($this->delete(array("id" => $key))) {
                           $nbok++;
                        } else {
                           $nbko++;
                        }
                     } else {
                        $nbnoright++;
                     }
                  }
               }
            }
            break;
         case "Delete_Item" :
            if ($input['itemtype'] == 'PluginResourcesEmployment') {
               foreach ($input["item"] as $key => $val) {
                  if ($val == 1) {
                     $values = array('id' => $key,
                                    'plugin_resources_resources_id' =>0);
                     if ($this->can($values["id"], 'w', $values)){
                        if ($this->update($values)) {
                           $nbok++;
                        } else {
                           $nbko++;
                        }
                     } else {
                        $nbnoright++;
                     }
                  }
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
            break;
      }
      return $res;
   }

}

?>