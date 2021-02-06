<?php  

function is_post_request(){
	return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function allcap($data){
	$data = trim($data);
	$data = $data = str_replace('    ', ' ', $data);
	$data = $data = str_replace('   ', ' ', $data);
	$data = $data = str_replace('  ', ' ', $data);
	$data = strtoupper($data);
	
	return $data;
}

function space($data){
	$data = trim($data);
	$data = str_replace('    ', ' ', $data);
	$data = str_replace('   ', ' ', $data);
	$data = str_replace('  ', ' ', $data);
	return $data;
}

function noSpace($data){
	$data = trim($data);
	$data = str_replace(' ', '', $data);
	return $data;
}

function firstWordCap($data){
	$data = trim($data);
	$data = str_replace('    ', ' ', $data);
	$data = str_replace('   ', ' ', $data);
	$data = str_replace('  ', ' ', $data);
	$data = strtoupper($data);
	return $data;
}


function isDate($date_time) {
	$date_time = noSpace($date_time);
    $date_time = explode('-', $date_time);

    if (count($date_time) != 3) 	
    	return false;
    elseif ( checkdate($date_time[1], $date_time[2], $date_time[0]) )
    	return true;
    else
    	return false;
}

function activeStatus($data) {
	if ($data == '1')
		return 'Active';
	elseif ($data == '0')
		return 'In-active';
	else
		return '';
}

function gender($data) {
	$data = strtolower($data);
	if ($data == 'm')
		return 'Male';
	elseif ($data == 'f')
		return 'Female';
	else
		return '';
}

function formStatus($data) {
	$data = strtolower($data);
	if ($data == 'a')
		return 'Completed';
	elseif ($data == 'p')
		return 'Pending';
	elseif ($data == 'r')
		return 'Rejected';
	else
		return '';
}


function verify_regno($data) {
	if (strlen($data)!=12) return false;
	elseif (!ctype_alpha(substr($data, 0, 3))
			|| substr($data, 3, 1)!='/'
			|| !ctype_digit(substr($data, 4, 4))
			|| substr($data, 8, 1)!='/'
			|| !ctype_digit(substr($data, 9, 3)) ) return false;
	else return true;
}

function verify_course_code($data) {
	if (strlen($data)!=6) return false;
	elseif (!ctype_alpha(substr($data, 0, 3))
			|| !ctype_digit(substr($data, 4, 3))) return false;
	else return true;
}
function course_exist($course_code) {
	global $dbname;
	$sql = "SELECT COUNT(code) AS found 
			FROM $dbname.course
			WHERE code='$course_code'";
	$fetch = getRow($sql);
	$fetch = $fetch['found'];
	if ($fetch>0) return true;
	else return false;
}
function regno_exist($regno) {
	global $dbname;
	$sql = "SELECT COUNT(regno) AS found 
			FROM $dbname.student
			WHERE regno='$regno'";
	$fetch = getRow($sql);
	$fetch = $fetch['found'];
	if ($fetch>0) return true;
	else return false;
}


?>