<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

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
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Frédéric VAN BEVEREN
// Purpose of file:
// ----------------------------------------------------------------------

class PluginUtilitairesObject
    {
    var $title;		// Nom de l'objet (en-tête du tableau)
    var $table;		// Table
    var $alias;		// Alias de la table
    var $uniqueId;		// Identifiant unique de la table
    var $joinId = array();	// Champ(s) joint(s) de cette table au champ joint de la table parent
    var $condition;		// Critère(s) de sélection
    var $parent = null;	// Table à laquelle est liée cette table
    var $joinIdParent;		// Champ joint de la table parent
    var $exclude;
    var $isSubQuery = false;	// Table utilisée en tant que sous requête (pas de traitement)

    var $children = array();

   // Constructeur
   function PluginUtilitairesObject($table, $alias, $uniqueId, $joinId, $condition = '', $parent = null, $joinIdParent = null, $exclude = false, $isSubQuery = false) {
		$this->table = $table;
		if ($alias)
			$this->alias = $alias;
		else
			$this->alias = $table;
		$this->uniqueId = $uniqueId;
		if ($joinId)
			{
			if (is_array($joinId))
				$this->joinId = $joinId;
			else
				$this->joinId[] = $joinId;
			}
		else
			$this->joinId[] = $uniqueId;
	
		$this->condition = $condition;
		$this->parent = $parent;
		if ($parent)
			{
			$this->parent->addChild($this);
			if ($joinIdParent)
				$this->joinIdParent = $joinIdParent;
			else
				$this->joinIdParent = $parent->uniqueId;
			}
		$this->exclude = $exclude;
		$this->isSubQuery = $isSubQuery;
   }

   function addChild($child) {
		$this->children [] = $child;
   }

   function select($action) {
		if ($action == PluginUtilitairesUtilitaire::TEST)
			$select = "SELECT COUNT(DISTINCT `".$this->alias."`.`".$this->uniqueId."`) AS NB ";
		else if ($action == PluginUtilitairesUtilitaire::TRUNCATE
            || $action == PluginUtilitairesUtilitaire::LINK)
			$select = "DELETE `".$this->table."` ";
		return $select;
   }

   function from() {
		$from = '';
		$sTable = $this->table == $this->alias ? "`".$this->table."`" : "`".$this->table."` AS `".$this->alias."`";
	
		if ($this->parent)
			{
			if (!$this->isSubQuery)
			$from = $this->parent->from();
	
			if ($this->exclude)
				$from .= " LEFT OUTER JOIN ".$sTable." ON ";
			else
				$from .= " INNER JOIN ".$sTable." ON ";
	
			$count = 0;
			$from1 = '';
			foreach($this->joinId as $j)
				{
				if ($count)
					$from1 .= " OR `".$this->alias."`.`$j` = `".$this->parent->alias."`.`".$this->joinIdParent."`";
				else
					$from1 .= "`".$this->alias."`.`$j` = `".$this->parent->alias."`.`".$this->joinIdParent."`";
				$count ++;
				}
			$from .= $count < 2 ? $from1 : "($from1)";
	
			if ($this->condition != '')
				$from .= " AND ".$this->condition;
			}
		else
			$from = " FROM ".$sTable;
	
		// Jointures sous requetes
		foreach($this->children as $sq)
			{
			if ($sq->isSubQuery)
				$from .= $sq->from();
			}
		return $from;
   }

   function where($whereParentEmpty = true) {
		$where = '';
	
		if ($this->parent && !$this->isSubQuery)
			$where = $this->parent->where();
	
		if (!$this->parent && $this->condition != '')
			$where .= ($where == '' && $whereParentEmpty ? " WHERE 1=1 " : " ").$this->condition;
	
		if ($this->exclude)
			$where .= ($where == '' && $whereParentEmpty ? " WHERE " : " AND ")."`".$this->alias."`.`".$this->uniqueId."` IS NULL";
	
		// Where sous requetes
		foreach($this->children as $sq)
			{
			if ($sq->isSubQuery)
			$where .= $sq->where($where == '');
			}
	
		return $where;
   }

   function process($action, $objectId, $actionId, $entities, $date, $test = false) {
		global $DB;
		
		$nb = 0;
		$res = array('ok' => 0,
                    'ko' => 0);
		// Pas de traitement si sous requète
		if ($action == PluginUtilitairesUtilitaire::TEST
         || $action == PluginUtilitairesUtilitaire::LINK) {
         if (!$this->isSubQuery) {
            if (!$this->parent) {
               echo "<div align='center'>";
               echo "<table class='tab_cadre_fixe' cellpadding='5'>";
               echo "<colgroup>";
               echo "<col width='200px' align='left' style='font:bold'>";
               echo "<col width='60px' align='right'>";
               echo "<tr><th colspan=2 align=center>".__('Records to process', 'utilitaires')."</th></tr>";
               echo "<tr><th colspan=2 align=center>".$this->title."</th></tr>";
            }

            foreach($this->children as $child) {
               $child->process($action, $objectId, $actionId, $entities, $date, $test);
            }
            $query = $this->select($action).$this->from().$this->where();
            //echo "<pre>".print_r($query)."</pre>";
            
            switch ($action) {
            
				case PluginUtilitairesUtilitaire::TEST:
					 $result = $DB->query($query) or die($DB->error());
               if($DB->numrows($result) != 0) {
                  $line = $DB->fetch_array($result);
                  $nb += $line["NB"];
                  if ($nb > 0) {
                     echo "<tr class='tab_bg_1'>";
                     echo "<td title='".$query."'>".$this->table."</td><td class='center'>".$line['NB']."</td>";
                  }
               }
					break;
				case PluginUtilitairesUtilitaire::LINK:
					if($result = $DB->query($query)) {
                  return 1;
               } else {
                  return 0;
               }
					break;
				}

            if (!$this->parent) {
               echo "<tr class='tab_bg_2'>";
               if ($nb > 0) {
                  echo "<td>".__('Total')."</td><td class='b center'>".$nb."</td>";
               } else {
                  echo "<td colspan='2' class='center'>";
                  echo "<div class='red'>".__('Nothing to treat', 'utilitaires')."</div>";
                  echo "</td>";
               }
               echo "</tr>";
               echo "</table></div><br>";
            }
         }
      } else if ($action == PluginUtilitairesUtilitaire::TRUNCATE) {
         if (!$this->isSubQuery) {
            if (!$this->parent) {
               echo "<div align='center'>";
               echo "<table class='tab_cadre_fixe' cellpadding='5'>";
               echo "<colgroup>";
               echo "<col width='200px' align='left' style='font:bold'>";
               echo "<col width='60px' align='right'>";
               echo "<tr><th colspan=2 align=center>".__('Records to process', 'utilitaires')."</th></tr>";
               echo "<tr><th colspan=2 align=center>".$this->title."</th></tr>";
            }
            foreach($this->children as $child) {
               $child->process($action, $objectId, $actionId, $entities, $date, $test);
            }
            if ($test) {
               $action = PluginUtilitairesUtilitaire::TEST;
            }
            $query = $this->select($action).$this->from();//.$this->where()
            //echo "<pre>".print_r($query)."</pre>";
         
            if($result = $DB->query($query)) {
               if ($test) {
                  if($DB->numrows($result) != 0) {
                     $line = $DB->fetch_array($result);
                     $nb += $line["NB"];
                     if ($nb > 0) {
                        echo "<tr class='tab_bg_1'>";
                        echo "<td title='".$query."'>".$this->table."</td><td class='center'>".$line['NB']."</td>";
                     }
                  }
                  if (!$this->parent) {
                     echo "<tr class='tab_bg_2'>";
                     if ($nb > 0) {
                        echo "<td>".__('Total')."</td><td class='b center'>".$nb."</td>";
                     } else {
                        echo "<td colspan='2' class='center'>";
                        echo "<div class='red'>".__('Nothing to treat', 'utilitaires')."</div>";
                        echo "</td>";
                     }
                     echo "</tr>";
                     echo "</table></div><br>";
                  }
               } else {
                  return 1;
               }
            }
         }
      } else if ($action == PluginUtilitairesUtilitaire::TODO) {
         $object = new $objectId();
         $objecttable = getTableForItemType($objectId);
         
         $restrict = "1 = 1";
         if ($object->maybeTemplate()) {
            $restrict .= " AND `is_template` = '0'";
         }
         if ($actionId == PluginUtilitairesUtilitaire::PURGE_ACTION) {
            $restrict .= " AND `is_deleted` = '1'";
         }
         
         if ($actionId == PluginUtilitairesUtilitaire::DELETE_DELAY) {
            $restrict .= " AND `status` = '".CommonITILObject::CLOSED."'
                           AND (`closedate` < '".$date."'
                              OR `closedate` IS NULL)";
         }
         if ($object->isEntityAssign()
               && $entities != -1) {
            $restrict .= getEntitiesRestrictRequest(" AND ",$objecttable,'',$entities,$object->maybeRecursive());
         }

         $count = countElementsInTable($objecttable,$restrict);
         
         $items = getAllDatasFromTable($objecttable,$restrict);
      
         if (!empty($items)) {
            
            echo "<div class='center'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><td>";
            Html::createProgressBar(__('Work in progress...'));
            echo "</td></tr></table></div></br>\n";
            $i = 0;
            foreach ($items as $item) {
               
               if($object->delete(array('id' => $item['id']), 1, 0)) {
                  $res['ok']++;
               } else {
                  $res['ko']++;
               }
               $i++;

               Html::changeProgressBarPosition($i, $count);
            }
            
            Html::changeProgressBarPosition($i, $count, __('Task completed.'));
         }
      }
      if ($action == PluginUtilitairesUtilitaire::TEST
            || $action == PluginUtilitairesUtilitaire::TRUNCATE
               || $action == PluginUtilitairesUtilitaire::LINK)
         return $nb;
      else
         return $res;
		
   }
}

?>