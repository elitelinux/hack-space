<?php
define('GLPI_ROOT', '../..');
include (GLPI_ROOT."/inc/includes.php");
commonHeader($LANG['plugin_hreporting']["name"], '' ,"plugins", "hreporting");
?>

<link href="lib/protovis/examples/ex.css" rel="stylesheet" type="text/css">
<script src="lib/protovis/protovis.min.js" type="text/javascript"></script>
<script src="lib/protovis/examples/crimea/crimea.js" type="text/javascript"></script>
<style type="text/css">
#fig {
width: 600px;
height: 300px;
}
</style>
<div class="center"><div id="fig">
    <script type="text/javascript+protovis">

var w = 545,
    h = 280,
    x = pv.Scale.ordinal(crimea, function(d) d.date).splitBanded(0, w, 4 / 5),
    y = pv.Scale.linear(0, 1500).range(0, h),
    k = x.range().band / causes.length,
    format = pv.Format.date("%b");

var vis = new pv.Panel()
    .width(w)
    .height(h)
    .margin(19.5)
    .right(40);

var panel = vis.add(pv.Panel)
    .data(crimea)
    .left(function(d) x(d.date))
    .width(x.range().band);

panel.add(pv.Bar)
    .data(causes)
    .bottom(0)
    .width(k)
    .left(function() this.index * k)
    .height(function(t, d) y(d[t]))
    .fillStyle(pv.colors("lightpink", "darkgray", "lightblue"))
    .strokeStyle(function() this.fillStyle().darker())
    .lineWidth(1);

panel.anchor("bottom").add(pv.Label)
    .visible(function() !(this.parent.index % 3))
    .textBaseline("top")
    .textMargin(5)
    .text(function(d) format(d.date));

vis.add(pv.Rule)
    .data(y.ticks())
    .bottom(y)
    .strokeStyle(function(i) i ? "rgba(255, 255, 255, .5)" : "black")
  .anchor("right").add(pv.Label)
    .visible(function() !(this.index & 1))
    .textMargin(6);

vis.render();

    </script>
  </div></div>

<?php
commonFooter();
