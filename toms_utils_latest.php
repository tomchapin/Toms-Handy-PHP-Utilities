<?php

// --------------------------------------------------------------------
// Tom's Handy Utilities
// Version: 1.07.2
// Release Date: 05/18/2010
// Author: Tom Chapin (tchapin@gmail.com)
// URL: http://github.com/tomchapin/Toms-Handy-PHP-Utilities
// --------------------------------------------------------------------
// Description:
// A library of various handy php functions which have
// either been written (or compiled from various online sources) 
// --------------------------------------------------------------------

// Function used to build the options for an HTML select box
// Parameters:
// $options (array) (
//		'item_names' => (required array of strings)
//		'item_values' => (optional array of strings)
//		'selected_values' => (optional array of strings or a single string)
//		'selected_names' => (optional array of strings or a single string to use if selected_values is not defined)
//		'show_blank_entry' => (optional boolean)
//		'blank_entry_text' => (optional string)
// )
// Returns: (string) - HTML representing all the options
function build_select_options($options=array()){
	$item_names = get_array_value($options, 'item_names');
	$item_values = get_array_value($options, 'item_values');
	$selected_values = get_array_value($options, 'selected_values');
	$selected_names = get_array_value($options, 'selected_names');
	$show_blank_entry = get_array_value($options, 'show_blank_entry');
	$blank_entry_text = get_array_value($options, 'blank_entry_text');
	$actual_names = array();
	$actual_values = array();
	$actual_selected = array();
	$return_string = '';
	// Verify and assign pieces
	if(is_array($item_names)){
		$actual_names = $item_names;
		if(is_array($item_values)){
			if(count($item_names) == count($item_values)){
				// Use values as values since everything matches
				$actual_values = $item_values;
			}else{
				return '<option>Err: Number of Items != Number of Values</option>'."\n";
			}
		}else{
			// Values not specified, so we just use item names as values
			$actual_values = $item_names;
		}
	}elseif(is_array($item_values)){
		$actual_names = $item_values;
		$actual_values = $item_values;
	}else{
		return '<option>Err: Missing or Invalid Parameters</option>'."\n";
	}
	// Check selected values
	if($selected_values!=NULL){
		if(!is_array($selected_values)){
			// Convert to an array with one value (so script won't error out later
			$actual_selected = array($selected_values);
		}
	}
	// Check selected names (if selected values aren't specified)
	if($selected_names!=NULL && $selected_values==NULL){
		if(!is_array($selected_names)){
			// Convert to an array with one value (so script won't error out later
			$actual_selected = array($selected_names);
		}
	}
	// First entry (if $show_blank_entry)
	if($show_blank_entry == "true" || $show_blank_entry == true){
		if($blank_entry_text != NULL){
			$return_string .='<option value="">'.$blank_entry_text.'</option>'."\n";
		}else{
			$return_string .='<option value=""> -- Select -- </option>'."\n";
		}
	}
	// Loop through everything to build the strings
	for($i=0; $i<count($actual_names); $i++){
		$value = get_array_value($actual_values, $i);
		$selected = '';
		if(!empty($actual_selected) && $value != '' && $value != NULL){
			if(in_array($value, $actual_selected)){
				$selected = ' selected="selected"';
			}
		}
		$return_string .='<option value="'.$actual_values[$i].'"'.$selected.'>'.$actual_names[$i].'</option>'."\n";
	}
	return $return_string;
}


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

// Function used to break an array of data into multiple pieces or "columns"
// $tmp_array (array) - an indexed array (not associative) containing multiple items
// $column_max_number (int) - maximum number of pieces or "columns" of data to return
// Example:
// $example_array = array("item1", "item2", "item3", "item4", "item5", "item6");
// $columns_array = break_array_into_pieces($example_array, 3);
// ------------------------------------------------
// $columns_array:
// array(
// 		[0] -> array('item1','item2')
// 		[1] -> array('item3','item4')
// 		[2] -> array('item5','item6')
// )
// ------------------------------------------------
// Returns: multi-dimensional array
function break_array_into_pieces($tmp_array=NULL, $column_max_number=3){
	$tmp_columns = array();
	if(count($tmp_array)>=$column_max_number){
		// Determine the number of rows per column,
		$row_count = ceil(count($tmp_array)/$column_max_number);
		$current_item = 0;
		for($current_col=1; $current_col<=$column_max_number; $current_col++){
			$tmp_column = array();
			// Add all of the cities in this column to the array
			for($current_row=1; $current_row<=$row_count; $current_row++){
				if(isset($tmp_array[$current_item])){
					array_push($tmp_column, $tmp_array[$current_item]);
					$current_item++;
				}else{
					// We've run out of items!
					break;
				}
			}
			if(!empty($tmp_column)){
				array_push($tmp_columns,$tmp_column);
			}
		}
	}else{
		// Not enough items to fill out all the columns, so we just create a column for each item across
		for($i=0; $i<count($tmp_array); $i++){
			$tmp_columns[$i] = array($tmp_array[$i]);
		}
	}
	return $tmp_columns;
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

// Function used to download a remote file using the fread function and save it to a location on the server
// Parameters:
// $remote_url (string) - an external URL
// $local_filename (string) - an absolute local file path to save the result to
// Returns: boolean (true if successful, false if not)
function download_file_with_fread($remote_url='', $local_filename='') {
	$rh = fopen($remote_url, 'rb');
	$wh = fopen($local_filename, 'w');
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

// Function used to download a remote file using CURL and save it to a location on the server
// Parameters:
// $remote_url (required string) - an external URL
// $local_filename (required string) - an absolute local file path to save the result to
// $use_cookies (optional boolean) - whether or not to use session cookies
// $cookie_jar_filename (required string if $use_cookies is true) - an absolute local file path to save session cookies to
// $follow_location (optional boolean) - whether or not to follow location redirects with CURL
// Returns: boolean (true if successful, false if not)
function download_file_with_curl($remote_url='', $local_filename='', $use_cookies=false, $cookie_jar_filename='', $follow_location=false){
	$return_val = false;
	$fp = fopen ($local_filename, 'w'); // This is the file where we save the information
	$ch = curl_init($remote_url); // Here is the file we are downloading
	$userAgent = 'Firefox (WindowsXP) – Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6';
	if($use_cookies && strlen($cookie_jar_filename)>0){
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar_filename);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar_filename);
	}
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);
	curl_setopt($ch, CURLOPT_URL,$remote_url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	fclose($fp);
		
	if ($response) {
		if(file_exists($local_filename)){
			$return_val = true;
		}
	}
	return $return_val;

}

// Function used to cache a remote file and return the contents as a string
// Parameters:
// $options (array) (
//		'remote_url' => (required string - Remote URL to download via server-side pull)
//		'cache_folder' => (required string - Absolute file path of the folder to store cache files at)
//		'cache_file_name' => (optional string - Desired cache file name. Will use MD5 hash of remote url if not specified.)
//		'cache_duration' => (required integer - Number of seconds to wait before re-caching file)
//		'download_method' => (optional string - Download method to use. Can be either "fread" or "curl". Defaults to fread, which is simpler.)
//		'curl_options' => (required array if downlod_method is curl) (
//			'use_cookies' => (optional boolean) - whether or not to use session cookies
//			'cookie_jar_filename' => (required string if $use_cookies is true) - an absolute local file path to save session cookies to
//			'follow_location' => (optional boolean) - whether or not to use follow location redirects
//		)
// )
// Returns: (string) - Contents of the cached file
function cache_remote_file($options=array()){
	$return_string = '';
	// Get values from options array
	$remote_url = get_array_value($options, 'remote_url', '');
	$cache_folder = get_array_value($options, 'cache_folder', '');
	$cache_file_name = get_array_value($options, 'cache_file_name', '');
	$cache_duration = get_array_value($options, 'cache_duration', '');
	$download_method = get_array_value($options, 'download_method', '');
	// CURL options to use (if download method isn't "fread")
	$curl_options = get_array_value($options, 'curl_options');
	$curl_use_cookies = get_array_value($curl_options, 'use_cookies');
	$curl_cookie_jar_filename = get_array_value($curl_options, 'cookie_jar_filename');
	$curl_follow_location = get_array_value($curl_options, 'follow_location');
	// Error-check parameters
	if(strlen($remote_url)==0){
		$return_string .= "Error: Remote URL parameter is missing!"."\n";
	}
	if(strlen($cache_folder)==0){
		$return_string .= "Error: Cache folder parameter is missing!"."\n";
	}else{
		if(!is_dir($cache_folder)){
			$return_string .= "Error: Cache folder doesn't exist!"."\n";
		}
	}
	if(strlen($cache_duration)==0 || !is_numeric($cache_duration)){
		$return_string .= "Error: Cache duration parameter is missing or not an integer!"."\n";
	}
	// Generate a cache file name if it's not set
	if(strlen($cache_file_name)==0){
		$cache_file_name = md5($remote_url)."_cache.tmp";
	}
	// Check to see if we have write access to file path
	if(!is_writable($cache_folder)){
		$return_string .= "Error: Cache file path is not writable!"."\n";
	}
	// Proceed to actual functionality if no errors were found
	if(strlen($return_string)==0){
		$cache_file_path = $cache_folder.'/'.$cache_file_name;
		$start_download = false;
		$use_cached_file = false;
		// Check for existing file
		if(file_exists($cache_file_path)){
			// Check cache lifetime
			if(time()-filemtime($cache_file_path)>$cache_duration){
				$start_download = true;
			}else{
				$use_cached_file = true;
			}
		}else{
			$start_download = true;
		}
		if($start_download){
			// Download the file
			if($download_method == "curl"){	
				// Use CURL
				$download_result = download_file_with_curl($remote_url, $cache_file_path, $curl_use_cookies, $curl_cookie_jar_filename, $curl_follow_location);
			}else{
				// default to fread method (simpler)
				$download_result = download_file_with_fread($remote_url, $cache_file_path);
			}
			$use_cached_file=false;
			// Check download
			if($download_result && file_exists($cache_file_path)){
				// File is cached now so we can use it!
				$use_cached_file = true;
			}else{
				// Attempt to use cached file if we are unable to access remote file right now
				if(file_exists($cache_file_path)){
					// Throw an error if the cache file is older than 24 hours
					if(time()-filemtime($cache_file_path)>(60*60*24)){
						$return_string .= "Error: Unable to download remote file, and local cache is older than 24 hours!"."\n";
					}else{
						$use_cached_file = true;
					}
				}else{
					$return_string .= "Error: Unable to download remote file!"."\n";
				}
			}
		}
		if($use_cached_file){
			// Use cached file
			$cached_file_contents = file_get_contents($cache_file_path);
			if($cached_file_contents){
				$return_string = $cached_file_contents;
			}else{
				$return_string .= "Error: Unable to access cached file!"."\n";
			}
		}
	}
	return $return_string;
}
// -------------------------------------------------------------------------
// Old shortcut functions which have been renamed
// -------------------------------------------------------------------------
function download_file($file_source='', $file_target='') {
	return download_file_with_fread($file_source, $file_target);
}
?>