<?php

/**
construct data array for chart 
**/

function get_chart_options($title,$data_models){

	$chart_options  = array(
"title"=>array("text"=>$title),
"animationEnabled"=>true,
"legend"=>array("verticalAlign"=>"bottom","horizontalAlign"=>"center"),
"toolTip"=>array(
"shared"=>true
),
			
"data"=>array(
array(
"name" => "Low",
"type"=> "spline",       
"indexLabelFontFamily" => "Garamond",       
"indexLabelFontSize" => 13,
"color"=>"rgba(211,19,14,.8)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
//"toolTipContent"=>"{label} ({s}) {y}%",
"dataPoints"=>$data_models[0]
),
array(
"name" => "Middle",
"type"=> "spline",       
"indexLabelFontFamily" => "Garamond",       
"indexLabelFontSize" => 13,
"color"=>"rgba(95,53,87,.8)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
//"toolTipContent"=>"{name} {label} ({s}) {y}%",
"dataPoints"=>$data_models[1]
),
array(
"name" => "High",
"type"=> "spline",       
"indexLabelFontFamily" => "Garamond",       
"indexLabelFontSize" => 13,
"color"=>"rgba(22,115,211,.9)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
//"toolTipContent"=>"{label} ({y}) {y}%",
"dataPoints"=>$data_models[2]
)


)
) ;
	return $chart_options;
}


/** 
Read AST history from csv files and return array
**/

function read_ast_history($vols,$data_dir){

	$chart_date_format = "D d M";

	$files = glob($data_dir."/*/perf/*/history/*.csv");
$data = array();


foreach($files as $file){
	$content = file($file);
	for($i=1;$i<count($content);$i++){
		$content_data = split(",",$content[$i]);
		if(in_array($content_data[0],$vols)){
			$max = (int) count($data);
			$data[$max] = $content_data;
			$data[$max][] = $file;
		}
	}
}

usort($data,'sort_by_time');
//$data_u = array_unique($data, SORT_REGULAR);
$data_u = (array) filter_duplicate_dates($data);
$data_models = array(0=>"",1=>"",2=>"");


/** Get  data models */
$i=1;
foreach($data_u as $val){
	
$total = $val[4]+$val[5]+$val[6];

$data_models[0][] = array("label"=>date($chart_date_format,strtotime($val[1])),"y"=>floor($val[4]/$total*100),"s"=>$val[4]);	
$data_models[1][] = array("label"=>date($chart_date_format,strtotime($val[1])),"y"=>floor($val[5]/$total*100),"s"=>$val[5]);	
$data_models[2][] = array("label"=>date($chart_date_format,strtotime($val[1])),"y"=>floor($val[6]/$total*100),"s"=>$val[6]);
$i++;	
}

return (array) $data_models;
}


/**
Read AST Evalutaion history from csv file
**/

function read_vol_ast_eval_history($vol,$data_dir){
$eval_files = (array) glob($data_dir."/*/perf/*/evaluation/Details_".str_pad( $vol, 5,0, STR_PAD_LEFT)."_*.csv");
$c=0;
foreach($eval_files as $eval_file){
	//print file_get_contents($eval_file);
	//print "\n\r";
	$eval_content = file($eval_file);
	$eval_content_arr[$c]['date']=$eval_content[0];
	for($i=2;$i<count($eval_content);$i++){
		$eval_content_sp = split(",",$eval_content[$i]);
		$eval_content_arr[$c]["{$eval_content_sp[3]}_{$eval_content_sp[2]}"][] = $eval_content_sp[0];
	}
	 $c++;
}
return (array) $eval_content_arr;
}


/** 
sort array by sub array value 
**/

function sort_by_time($a, $b) {
  return strtotime($a[1]) - strtotime($b[1]);
}


/** remove duplicated dates **/
function filter_duplicate_dates($array)
{
  $new_array = array();
  $vals = array();
  
  foreach ($array as $val)
  {
    if(!in_array($val[1],$vals)){
		$new_array[] = $val;
		$vals[] = $val[1];
	}
  }

  return $new_array;
}