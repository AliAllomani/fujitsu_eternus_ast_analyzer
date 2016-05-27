<?php

/**
Author : Ali Allomani <ali.allomani@ts.fujitsu.com>
**/

require("./includes/functions_ast.php");


$data_dir = "./AST_Backup";

//$user_vol = intval($_GET['vol']);
//if(!$user_vol){die("define vol");}

$dx_vols_content = file($data_dir."/gaca_dx87_vols.csv");
foreach($dx_vols_content as $vol_data){
	$dx_vols[] = split(",", $vol_data);
}

$vols = (array) $_REQUEST['vol'];
if(!count($vols)){ $vols = array('0');}

//$vols = array($user_vol);

$data_models = read_ast_history($vols,$data_dir);

$chart_options = get_chart_options("AST History for Vol ".$vols[0],$data_models);




?>

<!DOCTYPE HTML>
<html>

<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/select2.min.css">
<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script> 
<script type="text/javascript" src="js/select2.min.js"></script> 
<script type="text/javascript" src="js/canvasjs.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

$(".select-search").select2();
$(".select-search").change(function(){
$(this).closest('form').trigger('submit');
});
var chart = new CanvasJS.Chart("chartContainer",<?=json_encode($chart_options)?>);
	chart.render();

});

</script>
</head>
<body>
<div class='header'>
<div class='logo'><img src="images/fujitsu_logo.gif" /></div>
<div class='copyright'>ETERNUS AST history analyzer © 2016 by <a href="mailto:ali.allomani@ts.fujitsu.com">Ali Allomani</a></div>
</div>

<form action="index.php" method=get>
<select name='vol[]' class='select-search' style='width:100%;'>

<?php
foreach($dx_vols as $vol_data){
	print "<option value='$vol_data[0]'".(in_array($vol_data[0],$vols) ? "selected":"").">$vol_data[0] - $vol_data[1] ($vol_data[7]) (".($vol_data[8]/1024)." GB)</option>";
}
print "</select>"
?>
</form>
<div id="chartContainer" style="height: 400px; width: 99%;"></div>

<h3>Evaluation History</h3>

<?php


 $eval_content_arr = read_vol_ast_eval_history($vols[0],$data_dir);

// print_r($eval_content_arr);
		print "<table class='table fixed_headers'>
		<thead>
		<tr>
		<th class='date'></th>
		<th colspan=4>Low</th>
		<th colspan=4>Middle</th>
		<th colspan=4>High</th>
		</tr><tr>";
		print "<th class='date'>date</th>";

	 	print "<th>Match</th>";
	 	print "<th>ToMid</th>";
	 	print "<th>ToHigh</th>";
	 	print "<th>NoCap</th>";
	 	

	 	print "<th>ToLow</th>";
	 	print "<th>Match</th>";
	 	print "<th>ToHigh</th>";
	 	print "<th>NoCap</th>";

		print "<th>ToLow</th>";
	 	print "<th>ToMid</th>";
	 	print "<th>Match</th>";
	 	print "<th>NoCap</th>";
	 	print "</tr>
	 	</thead>
	 	<tbody>";
	 foreach($eval_content_arr as $val){

	 	$total = 0;
	 	foreach($val as $kv=>$vv){
	 		if($kv !== "date"){$total += count($vv);}	
	 	}

	 	print "<tr>";
	 	print "<td class='date'>".substr($val['date'],5,5)."</td>";
	 	print "<td>".number_format(((count($val[LOW_Match])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[LOW_UpgradeToMiddle])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[LOW_UpgradeToHigh])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[LOW_NotEnoughCapacity])/$total)*100),2)."%</td>";
	 	

	 	print "<td>".number_format(((count($val[MID_DowngradeToLow])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[MID_Match])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[MID_UpgradeToHigh])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[MID_NotEnoughCapacity])/$total)*100),2)."%</td>";

		print "<td>".number_format(((count($val[HIGH_DowngradeToLow])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[HIGH_DowngradeToMiddle])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[HIGH_Match])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[HIGH_NotEnoughCapacity])/$total)*100),2)."%</td>";
	 	print "<tr>";
	 }
	 print "
	 </tbody>
	 </table>";

?>

</body>