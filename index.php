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
	$vol_data_arr = split(",", $vol_data);
	$dx_vols[$vol_data_arr[0]] = $vol_data_arr;
}

$vol_name = trim($_REQUEST['vol_name']);
$vol_name_type = $_REQUEST['vol_name_type'];

if($vol_name){
foreach($dx_vols as $val){
	if($vol_name_type == "regex"){
if(preg_match_all("/".$vol_name."/i", $val[1])){
		$vols[] = $val[0];
	}
	}else{
	if(stristr($val[1],$vol_name)){
		$vols[] = $val[0];
	}
}
}
}else{
$vols = (array) $_REQUEST['vol'];
}

if(!count($vols)){ $vols = array('0');}

//$vols = array($user_vol);

$data_models = read_vols_allocation_from_eval($vols,$data_dir);

$chart_options = get_chart_options("AST allocation History",$data_models);




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


<div style="width:100%;">
<legend style="width:50%;float:left;">
	<label>Select by volume</label>
<form action="index.php" method=get>
<select name='vol[]' class='select-search' style='width:100%;'>

<?php
foreach($dx_vols as $vol_data){
	print "<option value='$vol_data[0]'".(in_array($vol_data[0],$vols) ? "selected":"").">$vol_data[0] - $vol_data[1] ($vol_data[7]) (".number_format($vol_data[8]/1024,2)." GB)</option>";
}
print "</select>"
?>
</form>
</legend>

<legend style="width:40%;float:left;">
<label>Search by volume name pattern</label>
<form action="index.php" method=get>
<input type=text size=30 name="vol_name" value="<?=$vol_name;?>">
<select name="vol_name_type">
<option value="regex">RegEx</option>
<option value="any">Any</option>
</select>
<input type=submit value="Search">
</form>
</legend>
</div>
<div style="clear:both;"></div>
<?php
if(count($vols) > 1 ){
print "<legend><b>No. of Volumes : </b> ".count($vols). "</legend>";
}
?>

<div id="chartContainer" style="height: 450px; width: 99%;"></div>



<?php

if(count($vols) > 1) {
print "<h3>Volumes list</h3>
<table class=table>
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Size (GB)</th>
<th>pool</th>
</tr>
</thead>
<tbody>
";
foreach($vols as $val){
print "<tr>
<td>$val</td>
<td>".$dx_vols[$val][1]."</td>
<td>".number_format($dx_vols[$val][8]/1024,2)."</td>
<td>".$dx_vols[$val][7]."</td>
</tr>
";
}
print "</tbody>
</table>";
}else{
 $eval_content_arr = read_vol_ast_eval_history($vols[0],$data_dir);

		print "
		<h3>Evaluation History</h3>
		<table class='table fixed_headers'>
		<thead>
		<tr>
		<th class='date'></th>
		<th colspan=3>Low</th>
		<th colspan=3>Middle</th>
		<th colspan=3>High</th>
		</tr><tr>";
		print "<th class='date'>date</th>";

	 	print "<th>Match</th>";
	 	print "<th>ToMid</th>";
	 	print "<th>ToHigh</th>";
	 
	 	

	 	print "<th>ToLow</th>";
	 	print "<th>Match</th>";
	 	print "<th>ToHigh</th>";


		print "<th>ToLow</th>";
	 	print "<th>ToMid</th>";
	 	print "<th>Match</th>";

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

	 	if(count($val[LOW_NotEnoughCapacity])){
			print "<td colspan=3>".number_format(((count($val[LOW_NotEnoughCapacity])/$total)*100),2)."% No Capacity !</td>";
	 	}else{
	 	print "<td>".number_format(((count($val[LOW_Match])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[LOW_UpgradeToMiddle])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[LOW_UpgradeToHigh])/$total)*100),2)."%</td>";
	 	}
	 	

	 	if(count($val[MID_NotEnoughCapacity])){
			print "<td colspan=3>".number_format(((count($val[MID_NotEnoughCapacity])/$total)*100),2)."% No Capacity !</td>";
	 	}else{
	 	print "<td>".number_format(((count($val[MID_DowngradeToLow])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[MID_Match])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[MID_UpgradeToHigh])/$total)*100),2)."%</td>";
	 	}


	 	if(count($val[HIGH_NotEnoughCapacity])){
			print "<td colspan=3>".number_format(((count($val[HIGH_NotEnoughCapacity])/$total)*100),2)."% No Capacity !</td>";
	 	}else{
		print "<td>".number_format(((count($val[HIGH_DowngradeToLow])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[HIGH_DowngradeToMiddle])/$total)*100),2)."%</td>";
	 	print "<td>".number_format(((count($val[HIGH_Match])/$total)*100),2)."%</td>";
	 	}

	 	print "<tr>";
	 }
	 print "
	 </tbody>
	 </table>";
}

?>

</body>