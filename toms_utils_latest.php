<?php

// --------------------------------------------------------------------
// Tom's Handy Utilities
// Version: 1.0
// Author: Tom Chapin (tchapin@gmail.com)
// URL: http://github.com/tomchapin/Toms-Handy-PHP-Utilities
// --------------------------------------------------------------------
// Description:
// A library of various handy php functions which have
// either been written (or compiled from various online sources) 
// --------------------------------------------------------------------

// Shortcut function used to display a string (or an array) along with a line break and then flush the output buffers.
function buffer_display($text_val=NULL){
	if(is_array($text_val)){
		print_r($text_val);
		echo "\n<BR/>";
		flush_buffers();
	}elseif($text_val!=NULL){
		echo $text_val;
		echo "\n<BR/>";
		flush_buffers();
	}
}

// Function used to flush buffers and display output to screen before script is finished
function flush_buffers(){
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
} 

// Function used to convert an object into an array
function objectsIntoArray($arrObjData, $arrSkipIndices = array()){
    $arrData = array();
   
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
   
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

// Function used to get field names from a specific database table
function get_field_names($table_name=NULL){
	global $config, $conn;
	$field_names = array();
	if(isset($table_name)){
		$sql = 'SHOW COLUMNS FROM `'.mysql_real_escape_string($table_name).'`';
		$result = $conn->Execute($sql);
		$field_names = $result->getRows();
	}
	return $field_names;
}

// Function used to get a variable from an array without erroring out if the variable doesn't exist
// $tmp_array (array) - can be any one of the php server-side objects ($_REQUEST, $_SESSION, etc...) or any array.
// $array_index (string) or (int), depending on whether or not it's an associative array
// $invalid_return - whatever value you want the function to return if the array index isn't found (defaults to NULL)
function get_array_value($tmp_array=NULL, $array_index=NULL, $invalid_return=NULL){
	if(isset($tmp_array) && isset($array_index)){
		if(isset($tmp_array[$array_index])){
			return $tmp_array[$array_index];
		}else{
			return $invalid_return;
		}
	}else{
		return $invalid_return;
	}
}

// Function used to redirect to a url... Try PHP header redirect, then Javascript redirect, then try http redirect.:
function redirect($url){
	if (!headers_sent()){    //If headers not sent yet... then do php redirect
		header('Location: '.$url); exit;
	}else{                    //If headers are sent... do java redirect... if java disabled, do html redirect.
		echo '<script type="text/javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
		echo '</noscript>'; exit;
	}
}

// Function used to redirect to a page inside the current directory
function redirect_to_page($pagename="/"){
	if($_SERVER['HTTPS'] == 'on'){
		$prefix = "https://";
	}else{
		$prefix = "http://";
	}
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$url = $prefix.$host.$uri."/".$pagename;
	redirect($url);
}

// This function will take a key=>value array and generate two different
// SQL strings with the SQL columns based off of the array keys and the SQL values
// are based off of the associated array values
// Returns: Array with two keys, dbColumns and dbValues.
// Example useage:
//		$event = array('column1name' => 'text value here', 'column2name' => 'yay more text!');
// 		$SQLstrings = build_SQL_INSERT_strings_from_array($event);
// 		$sql = "INSERT INTO `event` (".$SQLstrings['dbColumns'].") VALUES (".$SQLstrings['dbValues'].")";
// Warning: This function does not currently take string length into account and break query into segments!
function build_SQL_INSERT_strings_from_array($tmpArray = array()){
	$returnArray = array();
	// Database columns
	$dbColumns = '';
	$loopItem = 1;
	$tmpArrayKeys = array_keys($tmpArray);
	foreach($tmpArrayKeys as $arrayKey){
		$separator = ", ";
		if($loopItem==count($tmpArrayKeys)){
			// Last item
			$separator = "";
		}
		$dbColumns .= "`".$arrayKey."`".$separator;
		$loopItem++;
	}
	// Database values
	$dbValues = '';
	$loopItem = 1;
	foreach($tmpArray as $array_item){
		$separator = ", ";
		if($loopItem==count($tmpArray)){
			// Last item
			$separator = "";
		}
		if($array_item == NULL || $array_item == '' || is_array($array_item)){
			$dbValues .= "''".$separator;
		}elseif(is_int($array_item)){
			$dbValues .= "'".$array_item."'".$separator;
		}else{
			$dbValues .= "'".mysql_real_escape_string($array_item)."'".$separator;
		}
		$loopItem++;
	}
	$returnArray['dbColumns'] = $dbColumns;
	$returnArray['dbValues'] = $dbValues;
	return $returnArray;	
}

// This function will take a key=>value array and generate a SQL UPDATE string
// based off of the array keys (db columns) and the array values (db values)
// Returns: string
// Example useage:
//		$event = array('column1name' => 'text value here', 'column2name' => 'yay more text!');
// 		$SQLstring = build_SQL_UPDATE_string_from_array($event);
//		$sql = 'UPDATE `event` SET '.$SQLstring.' WHERE `ID` = 1 LIMIT 1;';
// Warning: This function does not currently take string length into account and break query into segments!
function build_SQL_UPDATE_string_from_array($tmpArray = array()){
	$returnString = "";
	// Database columns
	$loopItem = 1;
	$tmpArrayKeys = array_keys($tmpArray);
	foreach($tmpArrayKeys as $arrayKey){
		$separator = ", ";
		if($loopItem==count($tmpArrayKeys)){
			// Last item
			$separator = "";
		}
		$returnString .= "`".$arrayKey."` = '".mysql_real_escape_string($tmpArray[$arrayKey])."'".$separator;
		$loopItem++;
	}
	return $returnString;
}

// Function used to convert a date string to MySQL format
// and to make sure that it is actually a valid date
// $dateString (string) - can be any date string that is recognizeable by the php strtotime function
// Returns: MySQL compatible date (if valid), or boolean FALSE if invalid.
function generate_mysql_date($dateString=""){
  $timestamp = strtotime($dateString);
 
  if (!is_numeric($timestamp)){
     return false;
  }
  
  $month = date( 'm', $timestamp );
  $day   = date( 'd', $timestamp );
  $year  = date( 'Y', $timestamp );
 
  if (checkdate($month, $day, $year)){
     return date('Y-m-d', $timestamp);
  }
 
  return false; 
}

// Function used to download a file and save it to a location on the server
// $file_source (string) - either an absolute local file path or an external URL
// $file_target (string) - an absolute local file path to save the result to
function download_file($file_source, $file_target) {
	$rh = fopen($file_source, 'rb');
	$wh = fopen($file_target, 'wb');
	if ($rh===false || $wh===false) {
		// error reading or opening file
	   return false;
	}
	while (!feof($rh)) {
		if (fwrite($wh, fread($rh, 1024)) === FALSE) {
		   // 'Download error: Cannot write to file ('.$file_target.')';
		   return false;
	   }
	}
	fclose($rh);
	fclose($wh);
	// No error
	return true;
}
?>