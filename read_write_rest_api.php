<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');


require_once('core/func.php');
require_once('core/core.inc.php');  //connection file





$data = json_decode(file_get_contents("php://input"));

//get data from json file
$key = $_GET['username'];
$type = $_GET['type'];




if ($type == 'reg_student') {
	$length = count($data->student);
	if ($length < 1) {
		echo 'Error: student count is zero';
		return;
	}

	$values = ''; //prepare sql value
	$status = false;
	for ($i=0;  $i<$length;  $i++) {
		$status = true;
		if ( count($data->student[$i]) != 8) { //check if data column is complete
			echo 'Error: student ' . ++$i . ' has errors!';
			return;
		}

		$regno = $data->student[$i][0];
		$regno = str_replace(' ', '', $regno);
		$regno = strtoupper($regno);
		if (!verify_regno($regno)) {
			$report = 'Error: Student ' . ++$i . ' regno is invalid!';
			$status = false; break;
		}
		elseif (regno_exist($regno)) {
			$report = 'Error: student ' . ++$i . ' regno ( ' . $regno . ') already exist!';
			$status = false; break;
		}

		$id = $data->student[$i][1];
		if ( !ctype_digit($id) ) {
			$report = 'Error: Student ' . ++$i . ' id is invalid!';
			$status = false; break;
		}

		$sname = $data->student[$i][2];
		$sname = $conn->real_escape_string($sname);
	    $sname = noSpace($sname);
	    $sname = ucwords( strtolower($sname) );
	    if ( $sname == '' || !ctype_alpha($sname) ) {
	    	$report = 'Error: Student ' . ++$i . ' surname is invalid!';
			$status = false; break;
		}

		$fname = $data->student[$i][3];
		$fname = $conn->real_escape_string($fname);
	    $fname = noSpace($fname);
	    $fname = ucwords( strtolower($fname) );
	    if ( $fname == '' || !ctype_alpha($fname) ){
	    	$report = 'Error: Student ' . ++$i . ' firstname is invalid!';
			$status = false; break;
		}

		$mname = $data->student[$i][4];
		$mname = $conn->real_escape_string($mname);
	    $mname = noSpace($mname);
	    $mname = ucwords( strtolower($mname) );
	    if ( $mname != '' && !ctype_alpha($mname) ){
	    	$report = 'Error: Student ' . ++$i . ' middle name is invalid!';
			$status = false; break;
		}

		$gender = $data->student[$i][5];
	    $gender = noSpace($gender);
	    $gender = strtoupper($gender);
	    if ( $gender!='M' && $gender!='F' ){
	    	$report = 'Error: Student ' . ++$i . ' gender is invalid!';
			$status = false; break;
		}

		$level = $data->student[$i][6];
		$level = (int) $level;
		if ($level<100 || $level>500){
			$report = 'Error: Student ' . ++$i . ' level is invalid!';
			$status = false; break;
		}

		$remark = $data->student[$i][7];

		//prepare sql values
		$values .= "('$regno', '$id', '$sname', '$fname', '$mname', '$gender', '$level', '$remark'),";
	}

	

	if (!$status) {
		echo $report; return; }



	else {
		$values = substr($values, 0, strlen($values)-1);

		$sql = "INSERT INTO $dbname.student
				VALUES $values";
		if (query($sql)) {
			echo count($data->student) . ' record(s) saved';  return; }
		else {
			echo 'Error: an error occured while saving into database!';  return; }
	}
} //register students block




if ($type == 'student_attendance') {
	$course_code = $data->course;
	if ( !verify_course_code($course_code) ) {
    	$report = 'Error: Invalid course code!';  return; }


    if (!course_exist($course_code)) {
    	echo 'Error: ' . $course_code. ' isn\'t registered!'; return;}


    $lecture_date = $data->date;
    $lecture_date = str_replace('#', ':', $lecture_date);
    $temp = date('Y-m-d', strtotime($lecture_date));
    $lecture_date = date('Y-m-d H:i:s', strtotime($lecture_date));

    $sql = "SELECT * FROM $dbname.attendance
    		WHERE course_code='$course_code' AND DATE(lecture_date)>='$temp'";
    $fetch = countRows($sql);
    if ($fetch>0) 
    	{echo 'Error: Attendance was already taken for today!'; return;}


    $list = $data->list;
    $list = strtoupper($list);
    $list = trim($list);
    if (strlen($list)<12) { //not even a student was submitted
    	echo 'Error: you have not selected any student!';  return; }

    
    $list = str_replace('    ', ' ', $list);
    $list = str_replace('   ', ' ', $list);
    $list = str_replace('  ', ' ', $list);
    
    $list = explode(' ', $list);

    $new_val = [];  $i=0;  $status=true;
    foreach ($list as $regno) {
    	if (!verify_regno($regno)) {
			$report = 'Error: Regno ' . ++$i . ' is invalid.';
			$status = false; break;
		}
		elseif (!regno_exist($regno)) {
			$report = 'Error: ' . $regno . ' at position ' . ++$i . ' is not a student!';
			$status = false; break;
		}
		else array_push($new_val, $regno);
    }

    if (count($new_val) !== count(array_unique($new_val)) ) {
    	echo "Error: There are duplicate regnos in the list submitted!";
    	return;
    }

    if (!$status) {
		echo $report; return; }


	else { //data is good
		unset($list);

		$new_val = implode(' ', $new_val);

		$sql = "INSERT INTO $dbname.attendance
				VALUES (NULL, '$course_code', '$new_val', '$lecture_date')";

		if (query($sql)) {
			echo "Record saved for $course_code; $lecture_date";  return; }
		else {
			echo 'Error: an error occured while saving into database.';  return; }
	}

} //student attendance




if ($type == 'reg_course') {
	$course_code = $data->course_code;
	$course_code = noSpace($course_code);
	$course_code = $conn->real_escape_string($course_code);
	if ( !verify_course_code($course_code) ) {
    	echo 'Error: Invalid course code';  return; }


    if (course_exist($course_code)) {
    	echo 'Error: ' . $course_code. ' already exist'; return;}


	$course_title = $data->course_title;
	$course_title = space($course_title);
	$course_title = $conn->real_escape_string($course_title);
	if ( strlen($course_title)<3) {
    	echo 'Error: Invalid course title';  return; }


	$course_unit = $data->course_unit;
	$course_unit = (int) space($course_unit);	
	if ( $course_unit<1 || $course_unit>15) {
    	echo 'Error: range of course unit is 1-15';  return; }

    

	$sql = "INSERT INTO $dbname.course
			VALUES ('$course_code', '$course_title', '$course_unit')";

	if (query($sql)) {
		echo "Record saved";  return; }
	else {
		echo 'Error: an error occured while saving into database.';  return; }
} //register course





//////////Retrieve data section


if ($type == 'get_attendance') {
	$course_code = $data->course_code;
	if ( !verify_course_code($course_code) ) {
    	echo 'Error: Invalid course code!';  return; }


    if (!course_exist($course_code)) {
    	echo 'Error: ' . $course_code. ' isn\'t registered'; return;}


    $lecture_date = $data->lecture_date;
    $lecture_date = str_replace('#', ':', $lecture_date);
    $lecture_date = date('Y-m-d', strtotime($lecture_date));


    $sql = "SELECT attendance.student, course.title 
    		FROM $dbname.attendance
    		INNER JOIN $dbname.course ON attendance.course_code=course.code
    		WHERE course_code='$course_code' AND DATE(lecture_date)='$lecture_date'";
    $fetch = countRows($sql);
    if ($fetch<1) 
    	{echo 'Error: No record found!'; return;}

    $fetch = getRow($sql);
    $student = $fetch['student'];
    $temp = '\'' . str_replace(' ', '\', \'', $student) . '\'';


    $sql = "SELECT regno, CONCAT(sname, ' ', fname, ' ', mname) AS fullname 
    		FROM $dbname.student 
    		WHERE regno IN ($temp)";
    $fetch = getRows($sql);
    $res = [];
    while ($result = $fetch->fetch_assoc()) {
    	$res[$result['regno']] = $result['fullname'];
    }

    echo json_encode($res);


} //get student attendance



if ($type == 'get_courses') {
	$sql = "SELECT * FROM $dbname.course";
    $fetch = countRows($sql);
    if ($fetch<1) 
    	{echo 'Error: No record found'; return;}


    $fetch = getRows($sql);
    $res = [];
    while ($result = $fetch->fetch_assoc()) {
    	$res[$result['code']] = array(
    		'title' => $result['title'],
    		'unit' => $result['unit']
    	);
    }

    echo json_encode($res);


} //get course list


?>