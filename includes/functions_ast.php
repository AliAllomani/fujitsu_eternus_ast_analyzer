<?php

/**
 *  Fujitsu ETERNUS AST class
 * 
 * @package Fujitsu ETERNUS AST history analyzer
 * @version 1.0
 * @copyright (c) 2016 Ali Allomani , All rights reserved.
 * @author Ali Allomani <ali.allomani@ts.fujitsu.com>
 * @license GNU General Public License version 3.0 (GPLv3)
 * 
 */

class eternus_ast {

private static $dx_vols_list_filename = "./AST_Backup/gaca_dx87_vols.csv";
private static $data_dir = "./AST_Backup";
private static $cache_dir = "./cache";


private static $chart_date_format = "D d M";


/**
construct data array for chart 
**/

public static function get_chart_options($title,$data_models){

	$chart_options  = array(
"title"=>array("text"=>$title,"fontSize"=>18),
"animationEnabled"=>true,
"exportEnabled"=>true,
"axisY"=>array("labelFontSize"=>13),
"axisX"=>array("labelFontSize"=>13),
"legend"=>array("verticalAlign"=>"bottom","horizontalAlign"=>"center"),
"toolTip"=>array(
"shared"=>true
),
			
"data"=>array(
array(
"name" => "Low",
//"type"=> "stackedArea",  
"type"=> "spline",            
"indexLabelFontSize" => 13,
"indexLabelFontColor"=>"red",

"color"=>"rgba(211,19,14,.8)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
"toolTipContent"=>"<b>{label}</b> <br> <span style='\"'color: red;'\"'>{name}</span> {y}% ({s})",
"dataPoints"=>$data_models[0]
),
array(
"name" => "Middle",
"type"=> "spline",              
"indexLabelFontSize" => 13,
"indexLabelFontColor"=>"green",

"color"=>"rgba(100, 170, 40, 0.9)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
"toolTipContent"=>"<span style='\"'color: green;'\"'>{name}</span> {y}% ({s})",
"dataPoints"=>$data_models[1]
),
array(
"name" => "High",
"type"=> "spline",           
"indexLabelFontSize" => 13,
"indexLabelFontColor"=>"blue",

"color"=>"rgba(22,115,211,.9)",
"indexLabel"=>"{y}%",
//"startAngle"=>"-20",      
"showInLegend"=>true,
//"legendMarkerType"=>"square",
"toolTipContent"=>"<span style='\"'color: blue;'\"'>{name}</span> {y}% ({s})",
"dataPoints"=>$data_models[2]
)


)
) ;
	return $chart_options;
}

private static function vol_id_to_eval_filename($vol){
	return "Details_".str_pad( $vol, 5,0, STR_PAD_LEFT)."_00.csv";
}

/**
Get DX volumes list 
@return array
*/

public static function get_dx_vols_list(){
$dx_vols_content = file(self::$dx_vols_list_filename);
foreach($dx_vols_content as $vol_data){
	$vol_data_arr = split(",", $vol_data);
	$dx_vols[$vol_data_arr[0]] = $vol_data_arr;
}
return $dx_vols;
}

/** 
Read AST history from eval csv files and return array
*/

public static function read_vols_allocation_from_eval($vols){

/** 
Get eval files list 
**/

$files_cache_file = self::$cache_dir."/eval_files.txt";
	
if(file_exists($files_cache_file)){
$all_eval_files = (array) json_decode(file_get_contents($files_cache_file),true);
}else{
$all_eval_files = (array) glob(self::$data_dir."/*/perf/*/evaluation/Details_*_*.csv");
// write to cache
file_put_contents($files_cache_file, json_encode($all_eval_files));
}


/**
Read data from eval files
**/

$data = array();
$data_cache_file = self::$cache_dir."/data_".md5(json_encode($vols)).".txt";

if(file_exists($data_cache_file)){
	$data = (array) json_decode(file_get_contents($data_cache_file),true);
}else{

	$vols_eval_files_pattern = implode("|",array_map('self::vol_id_to_eval_filename',$vols));
	$eval_files = array();
	foreach($all_eval_files as $eval_file){
		if(preg_match_all("/(".$vols_eval_files_pattern.")/", $eval_file)){
			$eval_files[] = $eval_file;
		}
	}

$eval_files = (array) $eval_files;

foreach($eval_files as $file){

	$content = file($file);
	$key_date = date("Ymd",strtotime(substr($content[0],0,10)));

	for($i=2;$i<count($content);$i++){
		$content_data = split(",",$content[$i]);	
		$key_tier = strtolower($content_data[3]);
			$data["$key_date"]["$key_tier"] += 1;
			$data["$key_date"]['total'] += 1;
	}
}

$data = (array) $data;

// write to cache
file_put_contents($data_cache_file, json_encode($data));
}




$data_models = array(0=>"",1=>"",2=>"");

/** Get  data models */

foreach($data as $key=>$val){
	
//$total = $val[4]+$val[5]+$val[6];



$data_models[0][] = array(
	"label"=>date(self::$chart_date_format,strtotime($key)),
	"y"=>(float) number_format($val['low']/$val['total']*100,2),"s"=>number_format(($val['low']*1344)/1024/1024,2)." TB");	
$data_models[1][] = array(
	"label"=>date(self::$chart_date_format,strtotime($key)),
	"y"=>(float) number_format($val['mid']/$val['total']*100,2),"s"=>number_format(($val['mid']*1344)/1024/1024,2)." TB");	
$data_models[2][] = array(
	"label"=>date(self::$chart_date_format,strtotime($key)),
	"y"=>(float) number_format($val['high']/$val['total']*100,2),"s"=>number_format(($val['high']*1344)/1024/1024,2)." TB");

}

return (array) $data_models;
}


/** 
Read AST history from csv files and return array
**/

public static function read_ast_history($vols){

	

	$files = glob(self::$data_dir."/*/perf/*/history/*.csv");
$data = array();


foreach($files as $file){
	$content = file($file);
	for($i=1;$i<count($content);$i++){
		$content_data = split(",",$content[$i]);
		if(in_array($content_data[0],$vols)){
		//	$max = (int) count($data);
		//	$data[$max] = $content_data;
		//	$data[$max][] = $file;

			$key_date = date("Ymd",strtotime($content_data[1]));
			$key_vol = $content_data[0];
			$data["$key_date"][$key_vol] = $content_data;
		}
	}
}

$data = (array) $data;

//print_r($data);
//foreach($data as $arr_date=>$arr_vol){
//	print "$arr_date : ". count($arr_vol) . "\n";
//}
//die();

//usort($data,'sort_by_time');
//$data_u = array_unique($data, SORT_REGULAR);
//$data_u = (array) filter_duplicate_dates($data);
//$data_u = $data;

/** get sum per volume **/
foreach($data as $arr_date=>$arr_vol){
	$low_cap=0;$mid_cap=0;$high_cap = 0;
foreach($arr_vol as $vol_data){
$low_cap +=  $vol_data[4];
$mid_cap +=  $vol_data[5];
$high_cap +=   $vol_data[6];
}

$data_u[] = array(
	"low"=>$low_cap,
	"mid"=>$mid_cap,
	"high"=>$high_cap,
	"total"=>($low_cap+$mid_cap+$high_cap),
	"date"=>$arr_date
	);
}

$data_models = array(0=>"",1=>"",2=>"");

//print_r($data_u);
//die();
/** Get  data models */

foreach($data_u as $val){
	
//$total = $val[4]+$val[5]+$val[6];

$data_models[0][] = array("label"=>date(self::$chart_date_format,strtotime($val['date'])),"y"=>floor($val['low']/$val['total']*100),"s"=>$val['low']);	
$data_models[1][] = array("label"=>date(self::$chart_date_format,strtotime($val['date'])),"y"=>floor($val['mid']/$val['total']*100),"s"=>$val['mid']);	
$data_models[2][] = array("label"=>date(self::$chart_date_format,strtotime($val['date'])),"y"=>floor($val['high']/$val['total']*100),"s"=>$val['high']);
	
}

return (array) $data_models;
}


/**
Read AST Evalutaion history from csv file
**/

public static function read_vol_ast_eval_history($vol){
$eval_files = (array) glob(self::$data_dir."/*/perf/*/evaluation/Details_".str_pad( $vol, 5,0, STR_PAD_LEFT)."_*.csv");
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

private static function sort_by_time($a, $b) {
  return strtotime($a[1]) - strtotime($b[1]);
}


/** remove duplicated dates **/
private static function filter_duplicate_dates($array)
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
}