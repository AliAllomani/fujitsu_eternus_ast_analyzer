<?php
/**
 *  Fujitsu ETERNUS AST history analyzer
 * 
 * @package Fujitsu ETERNUS AST history analyzer
 * @version 1.0
 * @copyright (c) 2016 Ali Allomani , All rights reserved.
 * @author Ali Allomani <ali.allomani@ts.fujitsu.com>
 * @license GNU General Public License version 3.0 (GPLv3)
 * 
 */

// include required functions 
require("./includes/functions_ast.php");

eternus_ast::config("./AST_Backup/gaca_dx87_vols.csv","./AST_Backup","./cache");


//Get volumes list
$dx_vols = eternus_ast::get_dx_vols_list();

//process request vols
$vol_name = isset($_REQUEST['vol_name']) ? trim($_REQUEST['vol_name']):"";
$vol_name_type = isset($_REQUEST['vol_name_type']) ? trim($_REQUEST['vol_name_type']) : "";

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
$vols = (array) (isset($_REQUEST['vol']) ? $_REQUEST['vol'] : array());
}

if(!count($vols)){ $vols = array('0');}

// get data of vols
$data_models = eternus_ast::read_vols_allocation_from_eval($vols);

// construct chart data
$chart_options = eternus_ast::get_chart_options("AST allocation History",$data_models);


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
<title>Fujitsu ETERNUS AST history analyzer</title>
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
    eternus_ast::print_vols_table($vols);
}else{
    eternus_ast::print_ast_eval_history($vols[0]);
}

?>

</body>
</html>