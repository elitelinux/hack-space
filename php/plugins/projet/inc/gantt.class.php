<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/*
gantt php class
version 0.1
Copyright (C) 2005 Alexandre Miguel de Andrade Souza

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either
version 2 of the License.
Please see the accompanying file COPYING for licensing details!

If you need a commercial license of this class to your project, please contact
alexandremasbr@gmail.com
*/
class gantt {

   var $img;
   /**
   * All the information to be sent to class
   * the keys of array will be allocated to class variables
   * See documentation of others variables to know what information 
   * sent to this array
   *
   * @var array
   */
   var $definitions = array();
   var $img_width= 800;
   var $img_height = 500;
   var $img_bg_color = array();
   var $grid_color = array();
   var $workday_color = array();
   var $title_color = array();
   var $title_string = "";
   var $planned = array();
   var $planned_adjusted = array();
   var $real = array();
   var $limit = array();
   var $dependency = array();
   var $milestones = array();
   var $groups = array();
   var $progress = array();
   var $y;
   var $cell;
   var $milestone = array();

   /**
   * The ONLY function to be accessed. All information to the class have to be passed to array
   * $definitions. The class will use the informations to generate the gantt graphic
   *
   * @param array $definitions
   * @return gantt
   */
   function gantt($definitions) {
		
		define('END_TO_START',1);
		define('START_TO_START',2);
		define('END_TO_END',3);
		define('START_TO_END',4);
    
      if (!isset($definitions['grid_color'])) $definitions['grid_color'] = array(218, 218, 218); //default color of weekend days in the grid
      if (!isset($definitions['workday_color'])) $definitions['workday_color'] = array(255, 255, 255    ); //white -> default color of the grid to workdays
      if (!isset($definitions['img_bg_color'])) $definitions['img_bg_color'] = array(255, 255, 255); //color of background

      if (!isset($definitions['title_y'])) $definitions['title_y'] = 10; // absolute vertical position in pixels -> title string
      if (!isset($definitions['title_color'])) $definitions['title_color'] = array(0, 0, 0); //color of title
      if (!isset($definitions['title_bg_color'])) $definitions['title_bg_color'] = array(255, 255, 255); //color of background of title
      //define font
      //        if (!isset($definitions['title']['ttfont']['file'])) $definitions['title']['ttfont']['file'] = './Arial.ttf'; // set path and filename of ttf font -> coment to use gd fonts
      //        if (!isset($definitions['title']['ttfont']['size'])) $definitions['title']['ttfont']['size'] = '12'; // used only with ttf
      if (!isset($definitions['title_font'])) $definitions['title_font'] = 3;  //define the font to title -> 1 to 4 (gd fonts)

      if (!isset($definitions['text']['color'])) $definitions['text']['color'] = array(0, 0, 0); //color of text
      //define font
      //        if (!isset($definitions['text']['ttfont']['file'])) $definitions['text']['ttfont']['file'] = './Arial.ttf'; // set path and filename of ttf font -> coment to use gd fonts
      //        if (!isset($definitions['text']['ttfont']['size'])) $definitions['text']['ttfont']['size'] = '9'; // used only with ttf
      if (!isset($definitions['text_font'])) $definitions['text_font'] = 2; //define the font to text -> 1 to 4 (gd fonts)

      if (!isset($definitions['groups']['color'])) $definitions['groups']['color'] = array(0, 0, 0);// set color of groups
      if (!isset($definitions['groups']['bg_color'])) $definitions['groups']['bg_color'] = array(100,180, 180);// set color of background to groups title
      if (!isset($definitions['groups']['text_color'])) $definitions["group"]['text_color'] = array(0,0,0);
      if (!isset($definitions['groups']['alpha'])) $definitions['groups']['alpha'] = 0; //transparency -> 0-100

      //        if (!isset($definitions['phase']['text_color'])) $definitions['phase']['text_color'] = array(204,250,104);

      if (!isset($definitions['planned']['y'])) $definitions['planned']['y'] = 5; // relative vertical position in pixels -> planned/baseline
      if (!isset($definitions['planned']['height'])) $definitions['planned']['height']= 14; // height in pixels -> planned/baseline
      if (!isset($definitions['planned']['bg_color'])) $definitions['planned']['bg_color'] = array(100,180, 180);// set color of background to groups title
      if (!isset($definitions['planned']['color'])) $definitions['planned']['color']=array(255, 143, 4);// set color of initial planning/baseline
      if (!isset($definitions['planned']['border_color'])) $definitions['planned']['border_color']=array(255, 143, 4);// set border color of initial planning/baseline
      if (!isset($definitions['planned']['alpha'])) $definitions['planned']['alpha'] = 0; //transparency -> 0-100
      //        if (!isset($definitions['planned']['legend'])) $definitions['planned']['legend'] = 'INITIAL PLANNING';

      if (!isset($definitions['planned_adjusted']['y'])) $definitions['planned_adjusted']['y'] = 5;//26; // relative vertical position in pixels -> adjusted planning
      if (!isset($definitions['planned_adjusted']['height'])) $definitions['planned_adjusted']['height']= 14; // height in pixels -> adjusted planning
      if (!isset($definitions['planned_adjusted']['color'])) $definitions['planned_adjusted']['color']=array(0, 0, 204); // set color of adjusted planning
      if (!isset($definitions['planned_adjusted']['border_color'])) $definitions['planned_adjusted']['border_color']=array(0, 0, 204); // set border color of adjusted planning
      if (!isset($definitions['planned_adjusted']['alpha'])) $definitions['planned_adjusted']['alpha'] = 0; //transparency -> 0-100
      //        if (!isset($definitions['planned_adjusted']['legend'])) $definitions['planned_adjusted']['legend'] = 'ADJUSTED PLANNING';

      if (!isset($definitions['real']['y'])) $definitions['real']['y']=8;//29; // relative vertical position in pixels -> real/realized time 
      if (!isset($definitions['real']['height'])) $definitions['real']['height']=8; // height in pixels -> real/realized time 
      if (!isset($definitions['real']['hachured_color'])) $definitions['real']['hachured_color']=array(204,0, 0);// color of hachured of real. to not have hachured, set to same color of real
      if (!isset($definitions['real']['color'])) $definitions['real']['color']=array(255, 255,255);//set color of work done
      if (!isset($definitions['real']['alpha'])) $definitions['real']['alpha'] = 0; //transparency -> 0-100
      //        if (!isset($definitions['real']['legend'])) $definitions['real']['legend'] = 'REALIZED';

      if (!isset($definitions['progress']['y'])) $definitions['progress']['y']=8; // relative vertical position in pixels -> progress
      if (!isset($definitions['progress']['height'])) $definitions['progress']['height']=8; // height in pixels -> progress 
      if (!isset($definitions['progress']['color'])) $definitions['progress']['color']=array(0,191,0); // set color of progress/percentage completed
      if (!isset($definitions['progress']['border_color'])) $definitions['progress']['border_color']=array(0,255,0); // set border color of progress/percentage completed
      if (!isset($definitions['progress']['alpha'])) $definitions['progress']['alpha'] = 0; //transparency -> 0-100
      //        if (!isset($definitions['progress']['legend'])) $definitions['progress']['legend'] = 'PROGRESS';
      if (!isset($definitions['progress']['bar_type'])) $definitions['progress']['bar_type']='planned'; //  if you want set progress bar on planned bar (the x point), if not set, default is on planned_adjusted bar -> you need to adjust $definitions['progress']['y'] to progress y stay over planned bar or whatever you want; 

      if (!isset($definitions['milestone']['title_bg_color'])) $definitions['milestone']['title_bg_color'] = array(204, 204, 230); //color of background of title of milestone
      if (!isset($definitions['milestone']['text_color'])) $definitions['milestone']['text_color'] = array(204,04,104);
      //        if (!isset($definitions['milestone']['legend'])) $definitions['milestone']['legend'] = 'MILESTONE';
      if (!isset($definitions['milestones']['color'])) $definitions['milestones']['color'] = array(254, 54, 50); //set the color to milestone icon
      if (!isset($definitions['milestones']['alpha'])) $definitions['milestones']['alpha']= 0; //transparency -> 0-100

      if (!isset($definitions['today']['color'])) $definitions['today']['color']=array(0, 204, 0); //color of today line
      if (!isset($definitions['today']['pixels'])) $definitions['today']['pixels'] = 5; //set the number of pixels to line interval
      if (!isset($definitions['today']['alpha'])) $definitions['today']['alpha']= 0; //transparency -> 0-100
      //        if (!isset($definitions['today']['legend'])) $definitions['today']['legend'] = 'TODAY';

      if (!isset($definitions['status_report']['color'])) $definitions['status_report']['color']=array(255, 50, 0); //color of last status report line
      if (!isset($definitions['status_report']['pixels'])) $definitions['status_report']['pixels'] = 10; //set the number of pixels to line interval
      if (!isset($definitions['status_report']['alpha'])) $definitions['status_report']['alpha']= 0; //transparency -> 0-100
      //        if (!isset($definitions['status_report']['legend'])) $definitions['status_report']['legend'] = 'LAST STATUS REPORT';

      if (!isset($definitions['legend']['text_color'])) $definitions['legend']['text_color'] = array(104,04,104);
      if (!isset($definitions['legend']['y'])) $definitions['legend']['y'] = 85; // initial position of legent (height of image - y)
      if (!isset($definitions['legend']['x'])) $definitions['legend']['x'] = 50; // distance between two cols of the legend
      if (!isset($definitions['legend']['y_'])) $definitions['legend']['y_'] = 35; //distance between the image bottom and legend botton
      if (!isset($definitions['legend']['ydiff'])) $definitions['legend']['ydiff'] = 20; //diference between lines of legend

      if (!isset($definitions['dependency_color'][1])) $definitions['dependency_color'][1]=array(0, 0, 0);//black
      if (!isset($definitions['dependency_color'][2])) $definitions['dependency_color'][2]=array(0, 0, 0);//black
      if (!isset($definitions['dependency_color'][3])) $definitions['dependency_color'][3]=array(0, 0, 0);//black
      if (!isset($definitions['dependency_color'][4])) $definitions['dependency_color'][4]=array(0, 0, 0);//black
      if (!isset($definitions['dependency_color']['alpha'])) $definitions['dependency']['alpha']= 0; //transparency -> 0-100

      //set the size of each day in the grid for each scale
      if (!isset($definitions['limit']['cell']['y'])) $definitions['limit']['cell']['y'] = '1'; // size of cells (each day)
      if (!isset($definitions['limit']['cell']['m'])) $definitions['limit']['cell']['m'] = '4'; // size of cells (each day)
      if (!isset($definitions['limit']['cell']['w'])) $definitions['limit']['cell']['w'] = '8'; // size of cells (each day)
      if (!isset($definitions['limit']['cell']['d'])) $definitions['limit']['cell']['d'] = '20';// size of cells (each day)

      //set the initial positions of the grid (x,y)
      if (!isset($definitions['grid']['x'])) $definitions['grid']['x'] = 370; // initial position of the grix (x)
      if (!isset($definitions['grid']['y'])) $definitions['grid']['y'] = 1; // initial position of the grix (y)

      //set the height of each row of phases/phases -> groups and milestone rows will have half of this height
      if (!isset($definitions['row']['height'])) $definitions['row']['height'] = 45; // height of each row

      //other settings
      if (!isset($definitions['not_show_groups'])) $definitions['not_show_groups'] = false; // if set to true not show groups, but still need to set phases to a group
        
      $this->definitions = $definitions;
      //allocate the variables of array definitions to class variables
      foreach ($definitions as $key=>$value) {
         $this->$key = $value;
     }

      $this->definesize();

      //create the image
      $this->img = @imagecreatetruecolor($this->img_width,$this->img_height) or imagecreate($this->img_width,$this->img_height);
      //$this->img = imagecreate($this->img_width,$this->img_height);
      //imagealphablending($this->img,true);

      $this->background();
      //$this->title();
      $this->grid();
      $this->groups(); // draws groups and phases
      if (isset($this->dependency)) {
         $this->dependency($this->dependency);
      }
      if (isset($this->definitions['today']['data'])) {
         $this->today();
      }

      if (isset($this->definitions['status_report']['data'])) {
         $this->last_status_report();
      }

      $this->legend();

      $this->draw();
   }
   
   function today() {
      $y= $this->definitions['grid']['y']+40;
      $rows = $this->rows();
      $y2 = ($rows*$this->definitions['row']['height'])+$y;
      $x = daysNumb($this->definitions['today']['data'],$this->limit['start'])*$this->cell +$this->definitions['grid']['x'];
      //imageline($this->img,$x,$y,$x,$y2,IMG_COLOR_STYLED);
      $this->line_styled($x,$y,$x,$y2,$this->definitions['today']['color'],$this->definitions['today']['alpha'],$this->definitions['today']['pixels']);
   }
   
   function last_status_report() {
   
      $y= $this->definitions['grid']['y']+40;
      $rows = $this->rows();


      $y2 = ($rows*$this->definitions['row']['height'])+$y;
      $x = daysNumb($this->definitions['status_report']['data'],$this->limit['start'])*$this->cell +$this->definitions['grid']['x'];

      $this->line_styled($x,$y,$x,$y2,$this->definitions['status_report']['color'],$this->definitions['status_report']['alpha'],$this->definitions['status_report']['pixels']);
   }
   
   function line_styled($x,$y,$x2,$y2,$color,$alpha,$pixels) {
   
      $w  = imagecolorallocatealpha($this->img, 255, 255, 255,100);
      //$red = imagecolorallocate($im, 255, 0, 0);
      $color = $this->color_alocate($color,$alpha);
      for ($i=0;$i<$pixels;$i++) {
         $style[] = $color;
      }
      for ($i=0;$i<$pixels;$i++) {
         $style[] = $w;
      }

      imagesetstyle($this->img,$style);
      imageline($this->img,$x,$y,$x,$y2,IMG_COLOR_STYLED);
   }
   
   function groups() {
   
      $start_grid = $this->definitions['grid']['x'];
      $this->y = $this->definitions['grid']['y'] + 40;

      foreach ($this->groups['group'] as $cod=>$phases) {

         if ($this->definitions["not_show_groups"] != true) {

            $y = &$this->y;
            $x = daysNumb($this->groups['group'][$cod]['start'],$this->limit['start'])*$this->cell +$start_grid;
//modif tsmr projet 1 jour
            $x2 = daysNumb($this->groups['group'][$cod]['end'],$this->groups['group'][$cod]['start'])*$this->cell +$x;
            //echo "$x : $x2";
            $this->rectangule($x,$y,$x2,$y+6,$this->groups['color'],$this->groups['alpha']);
            $y2 = $y+7;
            $this->polygon(array($x,$y2,$x+5, $y2,$x,$y+10),3,$this->groups['color'],$this->groups['alpha']);
            $this->polygon(array($x2-5,$y2,$x2, $y2,$x2,$y+10),3,$this->groups['color'],$this->groups['alpha']);

            //progress
            if (isset($this->groups['group'][$cod]['progress'])) {
               if ($this->groups['group'][$cod]['progress']!=0||$this->groups['group'][$cod]['progress']!=null) {
                  $this->rectangule($x+1,$y+2,(($x2-$x)*($this->groups['group'][$cod]['progress']/100))+$x-1,$y+4,$this->progress['color'],$this->progress['alpha']);
                  $xp=2;
                  if ($this->groups['group'][$cod]['progress']<10) $xp=12-$this->groups['group'][$cod]['progress'];
                  if ($this->groups['group'][$cod]['progress']>10) $xp=(88-$this->groups['group'][$cod]['progress'])*2;
                  if (isset($this->definitions['text']['ttfont']['file'])){
                     $this->text($this->groups['group'][$cod]['progress'].'%',(($x2-$x)*($this->groups['group'][$cod]['progress']/100))+$x+$xp,$y+10,$this->progress['color']);
                  } else {
                     $this->text($this->groups['group'][$cod]['progress'].'%',(($x2-$x)*($this->groups['group'][$cod]['progress']/100))+$x+$xp,$y+10-($this->definitions['text_font']),$this->progress['color']);
                  }
               }
            }
                
            $y2 = $y +$this->definitions['row']['height']/2;

            // title of group
            $this->rectangule(0,$y,$start_grid-1,$y+$this->definitions['row']['height']/2,$this->groups['group'][$cod]['bg_color']);
            $this->text($this->groups['group'][$cod]['name'],5,$y+$this->definitions['row']['height']/4-6,$this->definitions["group"]['text_color']);

            //border
            $this->border(0,$y,$start_grid,$y2,$this->title_color);
            $this->border($start_grid,$y,$this->img_width-1,$y2,$this->title_color);

            // increase y
            $y += $this->definitions['row']['height']/2;
                
         }

         //loop group phases
         if (isset($this->groups['group'][$cod]['phase'])) $this->phases($cod);
         //$this->milestones($cod);

      }
   }
   
   function phases($group) {
   
      $start_grid = $this->definitions['grid']['x'];
      $y = &$this->y;

      //print_r($this->progress);
      foreach ($this->groups['group'][$group]['phase'] as $phase=>$cod) {
         if (isset($this->planned['phase'][$cod]['start'])){

            // planned
            $x = daysNumb($this->planned['phase'][$cod]['start'],$this->limit['start'])*$this->cell +$start_grid;
            $x2 = daysNumb($this->planned['phase'][$cod]['end'],$this->planned['phase'][$cod]['start'])*$this->cell +$x + 7;
            $w1 = $y + $this->definitions['planned']['y'];
            $w2 = $w1 + $this->definitions['planned']['height'];
            $this->definitions['planned']['points'][$cod]['x1'] = $x;
            $this->definitions['planned']['points'][$cod]['x2'] = $x2;
            $this->definitions['planned']['points'][$cod]['y1'] = $w1;
            $this->definitions['planned']['points'][$cod]['y2'] = $w2;
            
            //$this->rectangule($x,$w1,$x2,$w2,$this->planned['color'],$this->planned['alpha']);
            $plannedColor = $this->planned['color'];
            if (isset($this->planned['phase'][$cod]['bg_color'])) $plannedColor=$this->planned['phase'][$cod]['bg_color'];
            $this->rectangule($x,$w1,$x2,$w2,$plannedColor,$this->planned['alpha']);
            
            $this->border($x,$w1,$x2,$w2,$this->title_color,$this->planned['alpha']);

            // adjusted
            if (isset($this->planned_adjusted['phase'][$cod])){
               $t = daysNumb($this->planned_adjusted['phase'][$cod]['start'],$this->limit['start'])*$this->cell +$start_grid;
               $t2 = daysNumb($this->planned_adjusted['phase'][$cod]['end'],$this->planned_adjusted['phase'][$cod]['start'])*$this->cell +$t;
               $w1 = $y + $this->definitions['planned_adjusted']['y'];
               $w2 = $w1 + $this->definitions['planned_adjusted']['height'];
               $this->definitions['planned_adjusted']['points'][$cod]['x1'] = $t;
               $this->definitions['planned_adjusted']['points'][$cod]['x2'] = $t2;
               $this->definitions['planned_adjusted']['points'][$cod]['y1'] = $w1;
               $this->definitions['planned_adjusted']['points'][$cod]['y2'] = $w2;
               $this->rectangule($t,$w1,$t2,$w2,$this->planned_adjusted['color'],$this->planned_adjusted['alpha']);
               $this->border($t,$w1,$t2,$w2,$this->title_color,$this->planned_adjusted['alpha']);
    
               //real
               if (isset($this->real['phase'][$cod]['start'])&&isset($this->planned_adjusted['phase'][$cod]['start'])) {
    
                  $z = daysNumb($this->real['phase'][$cod]['start'],$this->limit['start'])*$this->cell +$start_grid;
                  $z2 = daysNumb($this->real['phase'][$cod]['start'],$this->real['phase'][$cod]['start'])*$this->cell +$z;
                  $w1 = $y + $this->definitions['real']['y'];
                  $w2 = $w1 + $this->definitions['real']['height'];
                  $this->rectangule($z,$w1,$z2,$w2,$this->real['color'],$this->real['alpha']);
                  $this->border($z,$w1,$z2,$w2,$this->definitions['real']['hachured_color']);
                  //hachured
                  for ($i=$z;$i<($z2-5);$i+=3){
                     $this->line($i,$w2,$i+5,$w1,$this->definitions['real']['hachured_color']);
                  }
               }
            }
            //progress
            if ((isset($this->progress['phase'][$cod]['progress'])&&isset($this->planned['phase'][$cod]['start'])&&($this->progress['bar_type']=='planned'))||(isset($this->progress['phase'][$cod]['progress'])&&isset($this->planned_adjusted['phase'][$cod]['start'])&&($this->progress['bar_type']!='planned'))) {
               if ($this->progress['phase'][$cod]['progress']!=0||$this->progress['phase'][$cod]['progress']!=null) {
                  if ($this->progress['bar_type']=='planned') {
                     $this->rectangule($x,$y+$this->progress['y'],(($x2-$x)*($this->progress['phase'][$cod]['progress']/100))+$x,$y+$this->progress['y']+$this->progress['height'],$this->progress['color'],$this->progress['alpha']);
                     $this->border($x,$y+$this->progress['y'],(($x2-$x)*($this->progress['phase'][$cod]['progress']/100))+$x,$y+$this->progress['y']+$this->progress['height'],$this->title_color,$this->progress['alpha']);
                     if (isset($this->definitions['text']['ttfont']['file'])){
                        $this->text($this->progress['phase'][$cod]['progress'].'%',(($x2-$x)*($this->progress['phase'][$cod]['progress']/100))+$x+2,$y+$this->progress['y'],$this->text['color']);
                     } else {
                        $this->text($this->progress['phase'][$cod]['progress'].'%',(($x2-$x)*($this->progress['phase'][$cod]['progress']/100))+$x+2,$y+$this->progress['y']-($this->definitions['text_font']),$this->text['color']);
                     }

                  } else {
                     $this->rectangule($t,$y+$this->progress['y'],(($t2-$t)*($this->progress['phase'][$cod]['progress']/100))+$t,$y+$this->progress['y']+$this->progress['height'],$this->progress['color'],$this->progress['alpha']);
                     $this->border($t,$y+$this->progress['y'],(($t2-$t)*($this->progress['phase'][$cod]['progress']/100))+$t,$y+$this->progress['y']+$this->progress['height'],$this->title_color,$this->progress['alpha']);
                     $this->text($this->progress['phase'][$cod]['progress'].'%',(($t2-$t)*($this->progress['phase'][$cod]['progress']/100))+$t+2,$y+$this->progress['y']-($this->definitions['text_font']),$this->text['color']);
                  }
               }
            }
            //box
            $x2 = daysNumb($this->planned['phase'][$cod]['end'],$this->planned['phase'][$cod]['start'])*$this->cell +$start_grid;
            $y2 = $y;
            if (isset($this->planned['phase'][$cod]['start'])) $y2 += $this->definitions['row']['height']/2;
            if (isset($this->planned_adjusted['phase'][$cod]['start'])) $y2 += $this->definitions['row']['height']/2;
            $this->border($start_grid,$y,$this->img_width-1,$y2,$this->title_color);
            $this->border(0,$y,$start_grid,$y2,$this->title_color);

            // name of phase
            if (isset($this->definitions['phase']['text_color'])) $plannedColor=$this->definitions['phase']['text_color'];
            $this->text($this->planned['phase'][$cod]['name'],15,$y+($y2-$y)*1/4,$plannedColor);

            $y = $y2;
         } else {
            $x = daysNumb($this->milestone['phase'][$cod]['data'],$this->limit['start'])*$this->cell +$this->definitions['grid']['x'];
            $this->definitions['milestone']['points'][$cod]['x1'] = $x;
            $this->definitions['milestone']['points'][$cod]['x2'] = $x+12;
            $this->definitions['milestone']['points'][$cod]['y1'] = $y;
            $this->definitions['milestone']['points'][$cod]['y2'] = $y+15;
            // title of group
            $this->rectangule(0,$y,$this->definitions['grid']['x']-1,($y+$this->definitions['row']['height']/2),$this->definitions['milestone']['title_bg_color']);
            $this->border(0,$y,$this->definitions['grid']['x'],$y+$this->definitions['row']['height']/2,$this->title_color);
            $this->text($this->milestone['phase'][$cod]['title'],15,$y+$this->definitions['row']['height']/4-6,$this->definitions['milestone']['text_color']);

            //grid box
            $this->border($this->definitions['grid']['x'],$y,$this->img_width-1,$y+$this->definitions['row']['height']/2,$this->title_color);

            //milestone
            $this->polygon(array($x,$y+15,$x+12,$y+15,$x+6,$y),3,$this->milestones['color'],$this->milestones['alpha']);
            $y += $this->definitions['row']['height']/2;
   
         }
      }
   }
    
   function dependency($dependency) {
   
      imagesetthickness($this->img,2);
      foreach ($dependency as $cod=>$details) {
         $from = $details['phase_from'];
         $to = $details['phase_to'];
         $x[0]=0;$x[1]=0;$y[0]=0;$y[1]=0;$x[2]=0;$x[3]=0;$y[2]=0;$y[3]=0;
         if (isset($this->planned_adjusted['phase'][$from]['start'])) {
            $x[0] =$this->definitions['planned_adjusted']['points'][$from]['x1'];
            $x[1] =$this->definitions['planned_adjusted']['points'][$from]['x2'] ;
            $y[0]=$this->definitions['planned_adjusted']['points'][$from]['y1']+1;
            $y[1]=$this->definitions['planned_adjusted']['points'][$from]['y2'] ;
         } else if (isset($this->planned['phase'][$from]['start'])) {
            $x[0] =$this->definitions['planned']['points'][$from]['x1'];
            $x[1] =$this->definitions['planned']['points'][$from]['x2'] ;
            $y[0]=$this->definitions['planned']['points'][$from]['y1']+1;
            $y[1]=$this->definitions['planned']['points'][$from]['y2'] ;
         } else if (isset($this->milestone['phase'][$from]['data'])) {
            $x[0] =$this->definitions['milestone']['points'][$from]['x1']+6;
            $x[1] =$this->definitions['milestone']['points'][$from]['x2']-6;
            $y[0]=$this->definitions['milestone']['points'][$from]['y1']+1;
            $y[1]=$this->definitions['milestone']['points'][$from]['y2'] ;
         }
         if (isset($this->planned_adjusted['phase'][$to]['start'])) {
            $x[2] =$this->definitions['planned_adjusted']['points'][$to]['x1'];
            $x[3] =$this->definitions['planned_adjusted']['points'][$to]['x2'] ;
            $y[2]=$this->definitions['planned_adjusted']['points'][$to]['y1']+1;
            $y[3]=$this->definitions['planned_adjusted']['points'][$to]['y2'] ;
         } else if (isset($this->planned['phase'][$to]['start'])) {
            $x[2] =$this->definitions['planned']['points'][$to]['x1'];
            $x[3] =$this->definitions['planned']['points'][$to]['x2'] ;
            $y[2]=$this->definitions['planned']['points'][$to]['y1']+1;
            $y[3]=$this->definitions['planned']['points'][$to]['y2'] ;
         } else if (isset($this->milestone['phase'][$to]['data'])) {
            $x[2] =$this->definitions['milestone']['points'][$to]['x1']+6;
            $x[3] =$this->definitions['milestone']['points'][$to]['x2']-6;
            $y[2]=$this->definitions['milestone']['points'][$to]['y1']+1;
            $y[3]=$this->definitions['milestone']['points'][$to]['y2'] ;
         }
         if (($x[0]==0)&&($x[1]==0)&&($y[0]==0)&&($y[1]==0)) {$x[0]=$x[2];$x[1]=$x[3];$y[0]=$y[2];$y[1]=$y[3];}
         if (($x[2]==0)&&($x[3]==0)&&($y[2]==0)&&($y[3]==0)) {$x[2]=$x[0];$x[3]=$x[1];$y[2]=$y[0];$y[3]=$y[1];}

         switch ($details['type']) {
            case END_TO_START:

               $ydif = 6;

               $this->line($x[1],$y[1],$x[1],$y[1]+$ydif,$this->definitions['dependency_color'][END_TO_START],$this->definitions['dependency']['alpha']);
               $this->line($x[1],$y[1]+$ydif,$x[2],$y[1]+$ydif,$this->definitions['dependency_color'][END_TO_START],$this->definitions['dependency']['alpha']);
               $this->line($x[2],$y[1]+$ydif,$x[2],$y[2],$this->definitions['dependency_color'][END_TO_START],$this->definitions['dependency']['alpha']);

               $this->polygon(array($x[2]-4,$y[2]-4,$x[2]+4,$y[2]-4,$x[2],$y[2]),3,$this->definitions['dependency_color'][END_TO_START],$this->definitions['dependency']['alpha']);
               break;
            case END_TO_END:

               $xdif = 10;
               $ydif = 0;
               if ($x[3]>=$x[1]) {

                  $this->line($x[1],$y[1],$x[3],$y[1],$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
                  $this->line($x[3],$y[1],$x[3],$y[2],$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
                  $this->polygon(array($x[3]+4,$y[2]-4,$x[3]-4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
               } else {
                  $this->line($x[1],$y[1],$x[1],$y[2],$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
                  $this->line($x[1],$y[2],$x[3],$y[2],$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
                  $this->polygon(array($x[3]+4,$y[2]+4,$x[3]+4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color'][END_TO_END],$this->definitions['dependency']['alpha']);
               }
               break;
            case START_TO_START:

               $ydif = 8;

               $this->line($x[0]+1,$y[1],$x[0]+1,$y[1]+$ydif,$this->definitions['dependency_color'][START_TO_START]);
               $this->line($x[0]+1,$y[1]+$ydif,$x[2],$y[1]+$ydif,$this->definitions['dependency_color'][START_TO_START]);
               $this->line($x[2],$y[1]+$ydif,$x[2],$y[2],$this->definitions['dependency_color'][START_TO_START]);


               $this->polygon(array($x[2]-4,$y[2]-4,$x[2]+4,$y[2]-4,$x[2],$y[2]),3,$this->definitions['dependency_color'][START_TO_START]);
               break;
            case START_TO_END:

               $xdif = 5;
               $ydif = 3;

               $this->line($x[0]+1,$y[1],$x[0]+1,$y[1]+$ydif,$this->definitions['dependency_color'][START_TO_END]);
               $this->line($x[0]+1,$y[1]+$ydif,$x[3],$y[1]+$ydif,$this->definitions['dependency_color'][START_TO_END]);

               $this->line($x[3],$y[1]+$ydif,$x[3],$y[2],$this->definitions['dependency_color'][START_TO_END]);


               $this->polygon(array($x[3]+4,$y[2]-4,$x[3]-4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color'][START_TO_END]);
               break;

            default:
               break;
         }
      }
   }
    
   function line($x1,$y1,$x2,$y2,$color,$alpha = 0) {
      $color = $this->color_alocate($color,$alpha);
      imageline($this->img,$x1,$y1,$x2,$y2,$color);
   }
   
   function legend() {
   
      //legend
      $x = 20;
      $x2 = 30;
      $xdiff = 10;
      $ydiff = $this->definitions['legend']['ydiff'];

      $y = $this->img_height - $this->definitions['legend']['y'];
      $y_ = $this->definitions['legend']['y_'];
        
      if (isset($this->definitions['planned']['legend'])) {
         //echo "$planned";
         $planned = count($this->planned['phase']);
         //foreach ($this->planned['phase'] as $cod=>$detail) {
         //    if ($this->planned['phase'][$cod]['start']) {
         //        $planned++;
         //    }
         //}
         //$planned = 0;
         if ($planned > 0) {
             //planned
            $this->rectangule($x,$y+5,$x2,$y+10,$this->planned['color'],$this->planned['alpha']);
            $this->text($this->definitions['planned']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
            $y +=$ydiff;
            if ($this->img_height-$y < $y_) {
               $y = $y = $this->img_height - $this->definitions['legend']['y'];
               $x += $this->definitions['legend']['x'];
               $x2 += $this->definitions['legend']['x'];
            }
         }
      }
        
      // planned_adjusted
      if (isset($this->definitions['planned_adjusted']['legend'])) {
         //$planned_adjusted = 0;
         $planned_adjusted = count($this->planned_adjusted['phase']);
         if ($planned_adjusted > 0) {
            $this->rectangule($x,$y+5,$x2,$y+10,$this->planned_adjusted['color'],$this->planned_adjusted['alpha']);
            $this->text($this->definitions['planned_adjusted']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
            $y +=$ydiff;
            if ($this->img_height-$y < $y_) {
               $y = $y = $this->img_height - $this->definitions['legend']['y'];
               $x += $this->definitions['legend']['x'];
               $x2 += $this->definitions['legend']['x'];
            }
         }
      }
      //real
      if (isset($this->definitions['real']['legend'])) {
         $real = count($this->real['phase']);
         //$real = 0;
         if ($real >0){
            $this->rectangule($x,$y+5,$x2,$y+10,$this->real['color'],$this->real['alpha']);
            $this->text($this->definitions['real']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
            for ($i=$x;$i<($x2);$i+=3){
               $this->line($i,$y+10,$i+5,$y+5,$this->definitions['real']['hachured_color']);
            }
            $y +=$ydiff;
            if ($this->img_height-$y < $y_) {
               $y = $y = $this->img_height - $this->definitions['legend']['y'];
               $x += $this->definitions['legend']['x'];
               $x2 += $this->definitions['legend']['x'];
            }
         }
      }
      // progress
      if (isset($this->definitions['progress']['legend'])) {
         $progress = count($this->progress['phase']);
         //$progress = 0;
         if ($progress>0){
            $this->rectangule($x,$y+5,$x2,$y+10,$this->progress['color'],$this->progress['alpha']);
            $this->text($this->definitions['progress']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
            $y +=$ydiff;
            if ($this->img_height-$y < $y_) {
               $y = $y = $this->img_height - $this->definitions['legend']['y'];
               $x += $this->definitions['legend']['x'];
               $x2 += $this->definitions['legend']['x'];
            }
         }
      }
      //milestone
      if (isset($this->definitions['milestone']['legend'])) {
         $milestone = count($this->milestones['milestone']);
         //$milestone = 0;
         if ($milestone > 0) {
               $this->polygon(array($x,$y+15,$x+12,$y+15,$x+6,$y),3,$this->milestones['color'],$this->milestones['alpha']);
               $this->text($this->definitions['milestone']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
               $y +=$ydiff;
            if ($this->img_height-$y < $y_) {
               $y = $y = $this->img_height - $this->definitions['legend']['y'];
               $x += $this->definitions['legend']['x'];
               $x2 += $this->definitions['legend']['x'];
            }
         }
      }
      //today
      if ((isset($this->definitions['today']['data'])) && (isset($this->definitions['today']['legend']))) {
         $this->line_styled($x+5,$y+3,$x+5,$y+15,$this->definitions['today']['color'],$this->definitions['today']['alpha'],$this->definitions['today']['pixels']);
         //$this->text($this->definitions['milestone']['legend'],$x2+$xdiff,$y);
         $this->text($this->definitions['today']['legend'],$x2+$xdiff,$y,$this->definitions['legend']['text_color']);
         $y +=$ydiff;
         if ($this->img_height-$y < $y_) {
            $y = $y = $this->img_height - $this->definitions['legend']['y'];
            $x += $this->definitions['legend']['x'];
            $x2 += $this->definitions['legend']['x'];
         }
      }
      //last status report
      if ((isset($this->definitions['status_report']['data'])) && (isset($this->definitions['status_report']['legend']))) {
         $this->line_styled($x+5,$y+3,$x+5,$y+15,$this->definitions['status_report']['color'],$this->definitions['status_report']['alpha'],$this->definitions['status_report']['pixels']);
         $this->text($this->definitions['status_report']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
      }
   }
    
   function rows() {
      $rows = 0;
      if (isset($this->planned['phase'])) {
         foreach ($this->planned['phase'] as $cod=>$detail) {
            if (isset($this->planned['phase'][$cod]['start'])) {
               $rows += 1/2;
            }
            if (isset($this->planned_adjusted['phase'][$cod]['start'])) {
               $rows += 1/2;
            }
         }
      }
      if ($this->definitions["not_show_groups"] != true){
         $rows += count($this->groups['group'])/2;
      }
      if (isset($this->milestone['phase'])) $rows += count($this->milestone['phase'])/2;
      return $rows;
   }
    
   function grid() {
   
      $months = $this->months($this->limit['start'],$this->limit['end']);
      $n_days = daysNumb($this->limit['end'],$this->limit['start'])+1;
      $x = $this->definitions['grid']['x'];
      $x1 = $this->definitions['grid']['x'];
      $y= $this->definitions['grid']['y'];
      $rows = $this->rows();
      $y2 = ($rows*$this->definitions['row']['height'])+$y + 40;
      $n_d = -date("d",$this->limit['start']);
      foreach ($months as $month => $startdate) {
         $n_m = next($months);

         $this->border(0,$y,$x,$y+40,$this->title_color);
         if (date("Y",$n_m)> '1969'){ //to bypass a bug in php for windows
            if ($n_m > mktime(0,0,0,2,19,date("Y",$n_m))) {
               $n_m = mktime(0,0,0,date("m",$n_m),date("d",$n_m),date("Y",$n_m));
            }
         }
         if ($n_m < $startdate) {
            $n_m = mktime(0,0,0,date("m",$this->limit['end']),date("d",$this->limit['end'])+1,date("Y",$this->limit['end']));
         }

         $n_d += date('t',$startdate);
         if ($n_m >= $this->limit['end']) {
            $x2 = $this->img_width-1;
         } else {
            $x2 = $n_d*$this->cell+$x1;
         }

         $this->rectangule($x,$y,$x2,$y+20,$this->workday_color);
         if ($this->limit['detail']=='m') {
            $ydiff = 15;
         } else {
            $ydiff = 5;
         }

         if ($this->limit['detail']=='m' || $this->limit['detail']=='y') {
            $this->rectangule($x,$y+20,$x2,$y2,$this->workday_color);
         } else {
            $this->border($x,$y,$x2,$y+20,$this->title_color);
         }
            
         if ($x2 - $x > 45) {
            //test tsmr
            $this->text($month,$x+($x2-$x)/2-20,$y+$ydiff);
         }
         $x = $x2;
        }
        $this->border(0,$y,$x,$y+40,$this->title_color);
        $x = $this->definitions['grid']['x'];
        $xs = $x;
        $xe = $x2;

        $start = $this->limit['start'];
        $end = $this->limit['end'];
        //year
        if ($this->limit['detail']=='y') {
            $dm=0;
            $dy=0;
            while( $start <= $end )    {
               $month = date("m",$start);
               $day = date("d",$start);
               $year= date("Y",$start);
               $x2=$x+$this->cell;
               if ( date('w', $start ) != 6 && date( 'w', $start) != 0 ){
                  $this->rectangule($x,$y+20,$x2,$y+40,$this->workday_color);
                  $this->rectangule($x,$y+41,$x2,$y2,$this->workday_color);
               } else {
                  $this->rectangule($x,$y+20,$x2,$y+40,$this->grid_color);
                  $this->rectangule($x,$y+41,$x2,$y2,$this->grid_color);
               }


               if( $month != $dm ) {
                  $this->border($x,$y+20,$x,$y+40,$this->title_color); // entete
                  $this->border($x,$y+41,$x,$y2,$this->title_color);
                  $lm=strtoupper(strftime("%b",mktime(0,0,0,$month,$day-1,$year)));
                  if ($dm!=0) $this->text($lm{0},$x-20,$y+$ydiff+20);
               }
               $dm=$month;
               if( $year != $dy ) {
                  if ($dy!=0) {
                     $this->border($x,$y,$xy,$yy+20,$this->title_color); // entete
                     $this->text(date('Y',mktime(0,0,0,$month,$day-1,$year)),$x+($xy-$x)/2-15,$y+$ydiff);
                  }
                  $xy=$x;
                  $yy=$y;
                  $this->border($x,$y,$x,$y+20,$this->title_color); // entete
               }
               $dy=$year;
               $x=$x2;
               $start = mktime(0,0,0,$month,$day+1,$year);
            }
            $this->border($x,$y,$xy,$yy+20,$this->title_color); // entete
            $this->text(date('Y',mktime(0,0,0,$month,$day-1,$year)),$x+($xy-$x)/2-15,$y+$ydiff);
            $this->border($x-2,$y+10,$x,$y+40,$this->title_color); // entete
            $this->border($x-2,$y+41,$x,$y2,$this->title_color);
            $this->border($x-2,$y,$x,$y+20,$this->title_color); // entete
            $lm=strtoupper(strftime("%b",mktime(0,0,0,$month,$day-1,$year)));
            if ($dm!=0) $this->text($lm{0},$x-20,$y+$ydiff+20);
        }
      //month
      if ($this->limit['detail']=='m') {
         $dm=0;
         while( $start <= $end ) {
            $month = date("m",$start);
            $day = date("d",$start);
            $year= date("Y",$start);
            $x2=$x+$this->cell;
            if ( date('w', $start ) != 6 && date( 'w', $start) != 0 ){
               $this->rectangule($x,$y+41,$x2,$y2,$this->workday_color);
            } else {
               $this->rectangule($x,$y+41,$x2,$y2,$this->grid_color);
            }
            if ( $month != $dm ){
               $this->border($x,$y,$x,$y+40,$this->title_color); // entete
               $this->border($x,$y+41,$x,$y2,$this->title_color);
            }
            $dm=$month;
            $x=$x2;
            $start = mktime(0,0,0,$month,$day+1,$year);

         }
         $this->border($x,$y,$x,$y+40,$this->title_color); // entete
         $this->border($x,$y+41,$x,$y2,$this->title_color);
      }
      //day
      if ($this->limit['detail']=='d') {
         while( $start <= $end ) {
            $month = date("m",$start);
            $day = date("d",$start);
            $year= date("Y",$start);
            $x2=$x+$this->cell;
            if ( date('w', $start ) != 6 && date( 'w', $start) != 0 ){
               $this->rectangule($x,$y+20,$x2,$y+40,$this->workday_color);
               $this->rectangule($x,$y+41,$x2,$y2,$this->workday_color);
            } else {
               $this->rectangule($x,$y+20,$x2,$y+40,$this->grid_color);
               $this->rectangule($x,$y+41,$x2,$y2,$this->grid_color);
            }
            if (date( 'w', $start) != 1 ) {
               $this->border($x,$y+20,$x2,$y+40,$this->title_color);
               $this->border($x,$y+40,$x2,$y2,$this->title_color,90);
            } else {
               $this->border($x,$y+20,$x2,$y2,$this->title_color);
            }
            $this->text($day,$x+4,$y+23);

            $x=$x2;
            $start = mktime(0,0,0,$month,$day+1,$year);
         }
         if(date( 'w', $start) != 1 ){
            $this->border($x,$y+20,$x2,$y+40,$this->title_color);
            $this->border($x,$y+40,$x2,$y2,$this->title_color,90);
         } else {
            $this->border($x,$y+20,$x2,$y2,$this->title_color);
         }
      }
      // week
      if ($this->limit['detail']=='w') {
         $this->limit['start'] = mktime(0,0,0,date("m",$this->limit['start']),date("d",$this->limit['start'])+1,date("Y",$this->limit['start']));
         $this->limit['end'] = mktime(0,0,0,date("m",$this->limit['end']),date("d",$this->limit['end'])+1,date("Y",$this->limit['end']));
         while( $start < $end )    {
            $month = date("m",$start);
            $day = date("d",$start);
            $year= date("Y",$start);
            $n_w = mktime(0,0,0,$month,$day+(7-date( 'w', $start)),$year);
            if ($n_w > $end || $n_w > $end) {
               $n_w = mktime(0,0,0,date("m",$end),date("d",$end)+1,date("Y",$end));
            }
            $days = date( 'w', $n_w)-date( 'w', $start);
            if ($days <= 0) {
               $days += 7;
            }
            $x2=$x+$this->cell*$days;

            $this->rectangule($x,$y+20,$x2-($this->cell*2),$y2,$this->workday_color);
            $this->rectangule($x2-($this->cell*2),$y+20,$x2,$y2,$this->grid_color);
            $this->border($x,$y+20,$x2,$y2,$this->title_color);
            $this->border($x,$y+40,$x+($this->cell),$y2,$this->title_color,100);
            $this->border($x+($this->cell),$y+40,$x+($this->cell*2),$y2,$this->title_color,100);
            $this->border($x+($this->cell*2),$y+40,$x+($this->cell*3),$y2,$this->title_color,100);
            $this->border($x+($this->cell*3),$y+40,$x+($this->cell*4),$y2,$this->title_color,100);
            $this->border($x+($this->cell*4),$y+40,$x+($this->cell*5),$y2,$this->title_color,100);
            $this->border($x+($this->cell*5),$y+40,$x+($this->cell*6),$y2,$this->title_color,100);
            $this->text(date( 'd', mktime(0,0,0,date( 'm', $start),date( 'd', $start)+1,date( 'Y', $start)))."-".date( 'd', $n_w),$x+($x2-$x)/2-15,$y+23);

            $x=$x2;
            $start = $n_w;
         }
         $this->border($xs,$y+20,$xe,$y+40,$this->title_color);
         $this->border($x,$y+20,$x2,$y2,$this->title_color);
         $this->border($x,$y+40,$x+($this->cell),$y2,$this->title_color);
      }
   }
   
   function definesize($det=NULL) {
      
      if (isset($det)) {
         unset($this->limit['detail']);
         $this->limit['detail']=$det;
      }

      if (((!isset($this->limit['start']))||(!isset($this->limit['end'])))&&isset($this->groups['group'])) {
         foreach ($this->groups['group'] as $code=>$phases) {
            if ($this->definitions["not_show_groups"] != true) {
               if ((!isset($this->limit['start']))||($this->limit['start'] > $this->groups['group'][$code]['start'])) {
                  $this->limit['start'] = $this->groups['group'][$code]['start'];
               }
               if ((!isset($this->limit['end']))||($this->limit['end'] < $this->groups['group'][$code]['end'])) {
                  $this->limit['end'] = $this->groups['group'][$code]['end'];
               }
            }
            if (isset($this->groups['group'][$code]['milestone'])) {
               foreach ($this->groups['group'][$code]['milestone'] as $milestone=>$cod) {
                  if ((!isset($this->limit['start']))||($this->limit['start'] > $this->milestones['milestone'][$cod]['data'])) {
                     $this->limit['start'] = $this->milestones['milestone'][$cod]['data'];
                  }
                  if ((!isset($this->limit['end']))||($this->limit['end'] < $this->milestones['milestone'][$cod]['data'])) {
                     $this->limit['end'] = $this->milestones['milestone'][$cod]['data'];
                  }
               }
            }
            if (isset($this->groups['group'][$code]['phase'])) {
               foreach ($this->groups['group'][$code]['phase'] as $phase=>$cod) {
                  if (isset($this->planned['phase'][$cod]['start'])&&((!isset($this->limit['start']))||($this->limit['start'] > $this->planned['phase'][$cod]['start']))) {
                     $this->limit['start'] = $this->planned['phase'][$cod]['start'];
                  }
                  if (isset($this->planned['phase'][$cod]['start'])&&((!isset($this->limit['end']))||($this->limit['end'] < $this->planned['phase'][$cod]['end']))) {
                     $this->limit['end'] = $this->planned['phase'][$cod]['end'];
                  }
                  if (isset($this->planned_adjusted['phase'][$cod]['start'])&&((!isset($this->limit['start']))||($this->limit['start'] > $this->planned_adjusted['phase'][$cod]['start']))) {
                     $this->limit['start'] = $this->planned_adjusted['phase'][$cod]['start'];
                  }
                  if (isset($this->planned_adjusted['phase'][$cod]['start'])&&((!isset($this->limit['end']))||($this->limit['end'] < $this->planned_adjusted['phase'][$cod]['end']))) {
                     $this->limit['end'] = $this->planned_adjusted['phase'][$cod]['end'];
                  }
               }
            }
         }
         $this->limit['start'] = mktime(0,0,0,date("m",$this->limit['start']),date("d",$this->limit['start'])-1,date("Y",$this->limit['start']));
         $this->limit['end'] = mktime(0,0,0,date("m",$this->limit['end']),date("d",$this->limit['end'])+2,date("Y",$this->limit['end']));
      }
      if (isset($this->limit['detail']))  {
         $detail=$this->limit['detail'];
      } else {
         $this->limit['detail']='D';
         $detail=$this->limit['detail'];
      }
      if (strtolower($detail)=='y') {
         $this->cell = $this->limit['cell']['y'];
         $this->limit['start']= mktime(0,0,0,1,1,date('Y',$this->limit['start']));
         $this->limit['end']= mktime(0,0,0,12,31,date('Y',$this->limit['end']));
      } elseif (strtolower($detail)=='m') {
         $this->cell = $this->limit['cell']['m'];
         $this->limit['start']= mktime(0,0,0,date('m',$this->limit['start']),1,date('Y',$this->limit['start']));
         $this->limit['end']= mktime(0,0,0,date('m',$this->limit['end'])+1,1,date('Y',$this->limit['end']));
      } elseif (strtolower($detail)=='w') {
         $this->cell = $this->limit['cell']['w'];
         $this->limit['start']= mktime(0,0,0,date('m',$this->limit['start']),date('d',$this->limit['start'])-(date('w',$this->limit['start'])),date('Y',$this->limit['start']));
         $this->limit['end']= mktime(0,0,0,date('m',$this->limit['end']),date('d',$this->limit['end'])+(7-date('w',$this->limit['end'])),date('Y',$this->limit['end']));
      } elseif (strtolower($detail)=='d') {
         $this->cell = $this->limit['cell']['d'];
      }

      $n_days = daysNumb($this->limit['end'],$this->limit['start']);
      $this->img_width = $this->definitions['grid']['x']+ceil($n_days*$this->cell);
      $rows = $this->rows();
      $this->img_height = $this->definitions['grid']['y'] -40+ $this->definitions['legend']['y']  + $rows*$this->definitions['row']['height'];
      if (($this->limit['detail']=='D')||($this->limit['detail']=='W')||($this->limit['detail']=='M')||($this->limit['detail']=='Y'))  {
      //        $this->title_string=$this->title_string." ".$this->limit['detail'].$this->img_width;
         if ($detail=='D') {
            if ($this->img_width>1000) {
               $detail=$this->definesize('W');
               return 'W';
            }
         } else {
            if ($detail=='W') {
               if ($this->img_width>1000) {
                  $detail=$this->definesize('M');
                  return 'M';
               }
            } else {
               if ($detail=='M') {
                  if ($this->img_width>1000) {
                     $detail=$this->definesize('Y');
                     return 'Y';
                  }
               }
            }
         }
         $detail=strtolower($detail);
      }
      unset($this->limit['detail']);
      $this->limit['detail']=$detail;
      //        $this->title_string=$this->title_string." ".$this->limit['detail'].$this->img_width;
      return $detail;

   }
   
   function months($start,$end){
      setlocale(LC_TIME,$this->definitions['locale']);
      while( $start <= $end )    {
         $month = strftime("%m/%y",$start);
         $months[$month] = $start;
         $m = date("m",$start);
         $y = date("Y",$start);
         $n_m = $m +1;
         $start = mktime(0,0,0,$n_m,1,$y);
      }
      return $months;
   }
    
   function border($x1,$y1,$x2,$y2,$color,$alpha = 0){
      $color = $this->color_alocate($color,$alpha);
      imagerectangle($this->img,$x1,$y1,$x2,$y2,$color);
   }

   function rectangule($x1,$y1,$x2,$y2,$color,$alpha = 0){
      $color = $this->color_alocate($color,$alpha);
      imagefilledrectangle($this->img,$x1,$y1,$x2,$y2,$color);
   }
    
   function title(){
      setlocale(LC_TIME,$this->definitions['locale']);
      $color = $this->color_alocate($this->definitions['title_color']);
      $this->rectangule(0,0,$this->img_width,$this->definitions['grid']['y'],$this->definitions['title_bg_color']);
      $xdiff = strlen($this->definitions['title_string'])*3;
      $this->title_string=str_replace("<DateBeg>",strftime("%d %b %Y",$this->limit['start']),$this->title_string);
      $this->title_string=str_replace("<DateEnd>",strftime("%d %b %Y",$this->limit['end']),$this->title_string);
      if (isset($this->definitions['title']['ttfont']['file'])) {
         $font_size = $this->definitions['title']['ttfont']['size'];
      imagettftext($this->img, $font_size,0, $this->img_width/2-$xdiff,$this->definitions['title_y']+$font_size, $color,$this->definitions['title']['ttfont']['file'],$this->title_string);
      } else{
         imagestring($this->img,$this->definitions['title_font'],$this->img_width/2-$xdiff,$this->definitions['title_y'],$this->title_string,$color);
      }

   }

   function text($string,$x,$y,$color = 0){
      if ($color==0) {
         $color = $this->definitions['text']['color'];
      }

      $color = $this->color_alocate($color,0);
      if (isset($this->definitions['text']['ttfont']['size'])) $font_size = $this->definitions['text']['ttfont']['size'];
      if (isset($this->definitions['text']['ttfont']['file'])){
         imagettftext($this->img, $font_size,0, $x,$y+$font_size, $color,$this->definitions['text']['ttfont']['file'],$string);
      } else {
         imagestring($this->img, $this->definitions['text_font'], $x,$y, $string,$color);
      }
   }

   // alocatte the color for background
   function background(){
      $bg = imagecolorallocate($this->img,$this->img_bg_color[0],$this->img_bg_color[1],$this->img_bg_color[2]);
      imagefill($this->img,0,0,$bg);
   }
    
   function color_alocate($color,$alpha = 40){
      return imagecolorallocatealpha($this->img,$color[0],$color[1],$color[2],$alpha);
   }
    
   function polygon($points, $n_points, $color,$alpha=0){
      $color = $this->color_alocate($color,$alpha);
      imagefilledpolygon($this->img,$points,$n_points,$color);
   }

   //generate the image
   function draw($image_type= 'png')    {

      //echo  "ok, chegou atÃ© aqui";
      if ($this->definitions['image']['type']) {
         $image_type = $this->definitions['image']['type'];
      }
      if (isset($this->definitions['image']['filename'])) {
         $filename = $this->definitions['image']['filename'];
      }
      if (isset($this->definitions['image']['jpg_quality'])) {
         $jpg_quality = $this->definitions['image']['jpg_quality'];
      } else {
         $jpg_quality = 100;
      }
      if (isset($this->definitions['image']['wbmp_foreground'])) {
         $foreground = $this->color_alocate($this->definitions['image']['wbmp_foreground']);
      } else {
         $foreground = null;
      }

      switch ($image_type) {
         case 'png':
            if (function_exists("imagepng")) {
               if (isset($filename)) {
                  imagepng($this->img, $filename);
               } else {
                  header("Content-type: image/png");
                  imagepng($this->img);
               }
            }
            break;
         case 'gif':
            if (function_exists("imagegif")) {
               header("Content-type: image/gif");
               if (isset($filename)) {
                  imagegif($this->img,'gantt.gif');
               } else {
                  imagegif($this->img);
               }
            }
            break;
         case 'jpg':
            if (function_exists("imagejpeg")) {
               header("Content-type: image/jpeg");
               imagejpeg($this->img,'gantt.jpg', $jpg_quality);
            }
            break;
         case 'wbmp':
            if (function_exists("imagewbmp")) {
               header("Content-type: image/vnd.wap.wbmp");
               if (isset($filename)) {
                  imagewbmp($this->img,$filename,$foreground);
               } else {
                  imagewbmp($this->img,'',$foreground);
               }
            }
            break;
         default:
            die("No image support for $image_type in this PHP server");
            break;
      }

      //imagepng($this->img);
      imagedestroy($this->img);
   }
}

function daysNumb($dat1,$dat2) {

   return intervaleDates(date('Y',$dat1).date('m',$dat1).date('d',$dat1) , date('Y',$dat2).date('m',$dat2).date('d',$dat2));
}
 
function intervaleDates($date1,$date2) { 
   
   // $date2 sera plus récente que $date1 
   if (intval($date1) > intval($date2)) { 
      $tmp = $date1; 
      $date1 = $date2; 
      $date2 = $tmp; 
   } 
   // les dates sont-elles au bon format ? 
   if (preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})/", $date1, $regs1) 
         && preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})/", $date2, $regs2)) {
      $d1 = intval($regs1[3]);    $m1 = intval($regs1[2]);    $y1 = intval($regs1[1]); 
      $d2 = intval($regs2[3]);    $m2 = intval($regs2[2]);    $y2 = intval($regs2[1]); 
      $by1 = ($y1 - 2000) % 4; 
      $by2 = ($y2 - 2000) % 4; 
      $dy1 = array(0,31,($by1 == 0 ? 29 : 28),31,30,31,30,31,31,30,31,30,31); 
      $dy2 = array(0,31,($by2 == 0 ? 29 : 28),31,30,31,30,31,31,30,31,30,31); 
      // si les années sont différentes 
      // on ajoute les jours restant à $y1 et les jours de plus à $y2 + 1 pour le passage d'année 
      // puis on ajoute les jours des années entre $y1 et $y2 
      if ($y1 != $y2) { 
         $interval = intervaleDates($date1,$y1."1231") + intervaleDates($y2."0101",$date2) + 1; 
         for ($i = $y1 + 1; $i < $y2; $i++) { 
            $b = ($i - 2000) % 4; 
            $interval += ($b == 0 ? 366 : 365); 
         } 
         return $interval; 
      } 
      // Si $y1 == $y2 
      // si les mois sont égaux, on renvoie la différence entre les jours 
      if ($m1 == $m2) 
         return $d2 - $d1; 
      // sinon on fait un savant calcul ;) 
      if ($m2 > $m1) { 
         $r1 = 0; 
         for ($i = $m1; $i < $m2; $i++) 
            $r1 += $dy1[$i]; 
         return $r1 - $d1 + $d2; 
      } 
   } 
   echo "<b>Parse error:</b> Argument(s) incorrect(s) pour intervaleInDays(). Attendu : 'AAAAMMJJ'<br />\n"; 
   return FALSE; 
}

?>