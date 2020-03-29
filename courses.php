<?php

/*
  filename 	: courses.php
  author   	: george corser
  course   	: cis355 (winter2016)
  description	: print fomatted output from JSON object 
                  returned by SVSU Courses API
  input    	: api.svsu.edu/courses
 */

// suppress notices
ini_set('error_reporting', 
    E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); 

main();

#-----------------------------------------------------------------------------
# FUNCTIONS
#-----------------------------------------------------------------------------

function main() {

    // echo html head section
    echo '<html>';
    echo '<head>';
    echo '	<link rel="icon" href="img/cardinal_logo.png" type="image/png" />';
    echo '</head>';

    // open html body section
    echo '<body>';

    // in html body section, if gpcorser's schedule, then print gpcorser's heading, else print general CS/CIS/CSIS heading
    if (!strcmp($_GET['instructor'], 'gpcorser')) {
        echo '<h1 align="center">George Corser, PhD</h1>';
        echo '<h2 align="center">CURRENT COURSES</h2>';
    } else {
        echo '<h1 align="center">SVSU/CSIS Department</h1>';
        echo '<h2 align="center">';
        echo $_GET['prefix'] ? ' - Prefix: ' . strtoupper($_GET['prefix']) : "";
        echo $_GET['courseNumber'] ? ' - Course Number: ' . $_GET['courseNumber'] : "";
        echo $_GET['instructor'] ? ' - Instructor: ' . strtoupper($_GET['instructor']) : "";
        echo $_GET['days'] ? ' - Day Of Week: ' . strtoupper($_GET['days']) : "";
        echo '</h2>';
    }

    // if user entered something in a search box, then call printCourses() to filter 
    if ($_GET['prefix'] != "" || $_GET['courseNumber'] != "" || $_GET['instructor'] != "" || $_GET['days'] !="") {
        printCourses($_GET['prefix'], $_GET['courseNumber'], $_GET['instructor'], $_GET['days']);
    }
    // otherwise call printSemester() for all courses for each semester
    else {
        echo "<h3>Spring</h3>";
        printSemester("19/SP");
        echo "<h3>Summer</h3>";
        printSemester("19/SU");
        echo "<h3>Fall</h3>";
        printSemester("19/FA");
        echo "<h3>Winter</h3>";
        printSemester("20/WI");
    }

    // display the entry form for next search
    //printForm(); 
    // display button for course search
    echo '<a href="coursesearch.php" class="btn btn-primary">Search</a>';

    // close html body section
    echo '</body>';
    echo '</html>';
}

#-----------------------------------------------------------------------------
// display the entry form for next search

function printForm() {

    echo '<br />';
    echo '<br />';
    echo '<h2>Course Lookup</h2>';

    // print user entry form
    echo "<form action='courses.php'>";
    echo "Course Prefix (Department)<br/>";
    echo "<input type='text' placeholder='CS' name='prefix'><br/>";
    echo "Course Number<br/>";
    echo "<input type='text' placeholder='116' name='courseNumber'><br/>";
    echo "Instructor<br/>";
    echo "<input type='text' placeholder='gpcorser' name='instructor'><br/>";
    echo "Day Of Week<br/>";
     echo "<input type='text' placeholder='MW' name='days'><br/>";
    //echo "Building/Room<br/>";
    //echo "<input type='text' name='building'>";
    //echo "<input type='text' name='room'><br/>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
}

#-----------------------------------------------------------------------------
// print all courses for a given filter

function printCourses($prefix, $courseNumber, $instructor, $days) {

    // call printListing() for each semester using all parameters
    /*
      $term = "18/WI";
      $string ="https://api.svsu.edu/courses?prefix=$prefix&courseNumber=$courseNumber&term=$term&instructor=$instructor";
      echo "<h3>2018 - Winter</h3>";
      printListing($string);
     */

    $term = "19/SP";
    $string = "https://api.svsu.edu/courses?prefix=$prefix&courseNumber=$courseNumber&term=$term&instructor=$instructor&days=$days";
    echo "<h3>2019 - Spring</h3>";
    printListing($string);

    $term = "19/SU";
    $string = "https://api.svsu.edu/courses?prefix=$prefix&courseNumber=$courseNumber&term=$term&instructor=$instructor&days=$days";
    echo "<h3>2019 - Summer</h3>";
    printListing($string);

    $term = "19/FA";
    $string = "https://api.svsu.edu/courses?prefix=$prefix&courseNumber=$courseNumber&term=$term&instructor=$instructor&days=$days";
    echo "<h3>2019 - Fall</h3>";
    printListing($string);

    $term = "20/WI";
    $string = "https://api.svsu.edu/courses?prefix=$prefix&courseNumber=$courseNumber&term=$term&instructor=$instructor&days=$days";
    echo "<h3>2020 - Winter</h3>";
    printListing($string);
}

#-----------------------------------------------------------------------------
// print all CS/CIS/CSIS courses for a given semester

function printSemester($term) {

    // note: printSemester() is only called when user has not entered anything in entry form
    // print all CIS courses for semester
    $string = "https://api.svsu.edu/courses?prefix=CIS&term=$term";
    printListing($string);

    // print all CS courses for semester
    $string = "https://api.svsu.edu/courses?prefix=CS&term=$term";
    printListing($string);

    // print all CSIS courses for semester
    $string = "https://api.svsu.edu/courses?prefix=CSIS&term=$term";
    printListing($string);

  
}

#-----------------------------------------------------------------------------
// print an html table for one single query of the api

function printListing($apiCall) {

    $json = curl_get_contents($apiCall);
     //$json = curl_get_contents("https://api.svsu.edu/courses?prefix=CIS&term=16/SP");
    // the line of code below suddenly stopped working!
    //$json = file_get_contents($apiCall); 
    $obj = json_decode($json);

    if (!($obj->courses == null)) {
        
       

        echo "<table border='3' width='100%'>";

        foreach ($obj->courses as $course) {
        if ($course->meetingTimes[0]->days=='M'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='M')) {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
        }        
        foreach ($obj->courses as $course) {
            echo "<script>console.log('Debug Objects: " . $course->meetingTimes[1]->days . "' );</script>";
            echo "<script>console.log('Debug Objects!!!: " . $course->meetingTimes[0]->days . "' );</script>";
        if ($course->meetingTimes[0]->days=='T'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='T')) {
            
            
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}
foreach ($obj->courses as $course) {
if ($course->meetingTimes[0]->days=='MW'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='MW')) {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}


foreach ($obj->courses as $course) {
if ($course->meetingTimes[0]->days=='W'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='W')) {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}


foreach ($obj->courses as $course) {
        if ($course->meetingTimes[0]->days=='TR'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='TR')) {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}       


foreach ($obj->courses as $course) {
if ($course->meetingTimes[0]->days=='R'|| (!($course->meetingTimes[0]->days) && $course->meetingTimes[1]->days=='R')) {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}


foreach ($obj->courses as $course) {
        if ($course->meetingTimes[0]->days=='' && $course->meetingTimes[1]->days=='') {
            $building = strtoupper(trim($_GET['building']));
            $buildingMatch = false;
            $thisBuilding0 = trim($course->meetingTimes[0]->building);
            $thisBuilding1 = trim($course->meetingTimes[1]->building);
            if ($building && ($thisBuilding0 == $building || $thisBuilding1 == $building))
                $buildingMatch = true;
            if (!($building))
                $buildingMatch = true;
            if (!$buildingMatch)
                continue;
            $room = strtoupper(trim($_GET['room']));
            $roomMatch = false;
            $thisroom0 = trim($course->meetingTimes[0]->room);
            $thisroom1 = trim($course->meetingTimes[1]->room);
            if ($room && ($thisroom0 == $room || $thisroom1 == $room))
                $roomMatch = true;
            if (!($room))
                $roomMatch = true;
            if (!$roomMatch)
                continue;
            // different <tr bgcolor=...> for each professor
            switch ($course->meetingTimes[0]->instructor) {
                case "james":            // 1
                    $printline = "<tr bgcolor='#B19CD9'>";  // pastel purple
                    break;
                case "icho":             // 2
                    $printline = "<tr bgcolor='lightblue'>";  // light blue
                    break;
                case "krahman":           // 3 
                    $printline = "<tr bgcolor='pink'>";  // pink
                    break;
                case "gpcorser":           // 4
                    $printline = "<tr bgcolor='yellow'>";   // yellow
                    break;
                case "pdharam":           // 5
                    $printline = "<tr bgcolor='#77DD77'>";  // pastel green (light green)
                    break;
                case "amulahuw":           // 6
                    $printline = "<tr bgcolor='#FFB347'>";  // pastel orange
                    break;
                default:
                    $printline = "<tr>"; // no background color
            }
            $printline .= "<td width='13%'>" . $course->prefix . " " . $course->courseNumber . "*" . $course->section . "</td>";
            $printline .= "<td width='40%'>" . $course->title . " (" . $course->lineNumber . ")" . "</td>";
            $printline .= "<td width='12%'>Av:" . $course->seatsAvailable . " (" . $course->capacity . ")" . "</td>";

            // print day and time column
            
             if ($course->meetingTimes[0]->days) {   
                $printline .= "<td width='15%'>" . $course->meetingTimes[0]->days . " " . $course->meetingTimes[0]->startTime;
                // $printline .= . "<br /> " . $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime ;
                $printline .= "</td>";
            } else {
                $printline .= "<td width='15%'>";
                $printline .= $course->meetingTimes[1]->days . " " . $course->meetingTimes[1]->startTime . "</td> ";
            }
            // print building and room column
            $printline .= "<td width='10%'>";
            if (substr($course->section, -2, 1) == "9")
                $printline .= "(Online)";
            else
            if (substr($course->section, -2, 1) == "7")
                $printline .= $course->meetingTimes[1]->building . " " . $course->meetingTimes[1]->room;
            else
                $printline .= $course->meetingTimes[0]->building . " " . $course->meetingTimes[0]->room;
            $printline .= "</td>";

            // print instructor column
            $printline .= "<td width='10%'>" . $course->meetingTimes[0]->instructor . "</td>";
            $printline .= "</tr>";
            echo $printline;
        } 
}       


        echo "</table>";
        echo "<br/>";
 // end foreach
        
        
        
        
    } // end if (!($obj->courses == null))
    else {
        echo "No courses fit search criteria";
        echo "<br />";
    }
}

#-----------------------------------------------------------------------------
// read file into a string

function curl_get_contents($url) {

    // alternative to file_get_contents

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}



