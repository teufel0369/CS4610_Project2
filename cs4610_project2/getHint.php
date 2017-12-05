<?php 
    //create the global db variables and attempt to initiate connection with the database
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "mathprobdb";
    $tablename = "keyword";
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('[-]ERROR: Could not connect to the database ' . mysql_error());
    mysqli_select_db($conn, $dbname); 
    mysqli_set_charset($conn, 'utf8');
    
    $keyArr = array();
    $hint = "";
    $result = mysqli_query($conn, "SELECT keyword.keyword FROM mathprobdb.keyword"); //perform the query
    
    //grab the array of keywords
    while ($row = mysqli_fetch_assoc($result)) {
        $keyArr[] = $row['keyword'];
    }
    
    //get the query from the search bar
    $searchBar = filter_input(INPUT_GET, "searchBar"); //filter the input
    $searchBar = trim($searchBar, " \t\n\r\0\x0B"); //trim the input
    $searchBar = strtolower($searchBar); //convert any text to lowercase
    $len = strlen($searchBar); //get the length of the search word
        
    //loop through the array and do a predictive comparison
    foreach ($keyArr as $key1) {
        if (stristr($searchBar, substr($key1, 0, $len))) {
            if ($hint === "") {
                $hint = $key1;
            } else {
                $hint .= ", $key1";
            }
        }
    }
    
    //send the response back to the frontend
    echo $hint === "" ? "no suggestion" : $hint;;
    
    mysqli_close($conn);
?>

