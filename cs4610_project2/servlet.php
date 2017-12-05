<?php
    
    //create the global db variables and attempt to initiate connection with the database
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "mathprobdb";
    $deleted = "deleted_problems";
    $delete = "delete";
    $tablename = "problem";
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('[-]ERROR: Could not connect to the database ' . mysql_error());
    mysqli_select_db($conn, $dbname);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if (isset($_GET['moveUp'])) {
            $pid = $_GET['moveUp']; //get the pid
            $moveUp = $pid - 1; //compute the new pid
            
            //construct the swap query
            $longQuery = "UPDATE $tablename t1 INNER JOIN $tablename t2 ON (t1.pid, t2.pid) IN (($pid, $moveUp), ($moveUp, $pid)) "
                    . "SET t1.content = t2.content";
            
            //execute the swap query
            mysqli_query($conn, $longQuery) or die('[-]ERROR: Could not swap data. ' . mysqli_error($conn));
            header('Location: index.php');
            
        } else if (isset($_GET['moveDown'])) {
            $pid = $_GET['moveDown']; //get the pid
            $moveDown = $pid + 1; //compute the new pid
            
            //construct the swap query
            $longQuery = "UPDATE $tablename t1 INNER JOIN $tablename t2 ON (t1.pid, t2.pid) IN (($pid, $moveDown), ($moveDown, $pid)) "
                    . "SET t1.content = t2.content";
            
            //execute the swap query
            mysqli_query($conn, $longQuery) or die('[-]ERROR: Could not swap data. ' . mysqli_error($conn));
            header('Location: index.php');
            
        } else if (isset($_GET['newQuestionContent'])) { //a new question was submitted
            $newQuestionContent = null;
            $newQuestionContent = $_GET['newQuestionContent']; //get the value from the new question text area
            $query = "INSERT INTO `problem` (`content`) VALUE('$newQuestionContent');"; //construct the INSERT query
            $result = mysqli_query($conn, $query) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); //submit the query
            header('Location: index.php');
            
        } else if (isset($_GET['delete'])) { //if the respective delete button is pressed
            $pid = $_GET['delete']; //get the pid of the problem
            $query = "INSERT INTO $deleted (`content`) SELECT `content` FROM $tablename WHERE pid='$pid'"; //construct the insert query
            $result = mysqli_query($conn, $query) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); //perform the query
            $deleteQuery = "DELETE FROM problem WHERE pid = $pid"; //construct the delete query
            $deleteResult = mysqli_query($conn, $deleteQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); 
            
            //solves an re-indexing issues that may arise
            $dropPIDQuery = "ALTER TABLE $tablename DROP `pid`"; //drop the pid column
            $dropPIDResult = mysqli_query($conn, $dropPIDQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); //submit the query
            $AIQuery = "ALTER TABLE $tablename AUTO_INCREMENT = 1"; //reset AUTO_INCREMENT to 1
            $AIResult = mysqli_query($conn, $AIQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); 
            $renumQuery = "ALTER TABLE $tablename ADD `pid` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST"; //add the pid column back in
            $renumResult = mysqli_query($conn, $renumQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn));
            header('Location: index.php');
            
                       
        } else if (isset($_GET['undoDelete'])) { //functions more like a stack to "pop" the most recently deleted question
            $tableCountQuery = "SELECT COUNT(*) AS totalCount FROM $deleted"; //get the count of the table
            $result = mysqli_query($conn, $tableCountQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); //submit the query
            $resultArray = mysqli_fetch_array($result); //get the array from the resulting object
            $tableCount = $resultArray['totalCount']; //get the number from the array element
            $insertQuery = "INSERT INTO `problem` (`content`) SELECT `content` FROM $deleted WHERE pid = $tableCount"; //get the most recent row that was deleted
            $result = mysqli_query($conn, $insertQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn));
            $deleteQuery = "DELETE FROM $deleted WHERE pid = $tableCount"; //delete the row from
            $result = mysqli_query($conn, $deleteQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn));
            
            //solves any re-indexing issues that may arise 
            $dropPIDQuery = "ALTER TABLE $deleted DROP `pid`"; //drop the pid column
            $dropPIDResult = mysqli_query($conn, $dropPIDQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn)); //submit the query
            $AIQuery = "ALTER TABLE $deleted AUTO_INCREMENT = 1"; //reset AUTO_INCREMENT to 1
            $AIResult = mysqli_query($conn, $AIQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn));
            $renumQuery = "ALTER TABLE $deleted ADD `pid` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST"; //add the pid column back in 
            $renumResult = mysqli_query($conn, $renumQuery) or die('[-]ERROR: Could not retrieve data ' . mysqli_error($conn));
            header('Location: index.php');
            
        } else if (isset($_GET['edit'])){
            class ResponseData {
                public $probcont = "";
                public $probbtn = "";
            }
             
            $pid = $_GET['edit']; //get the pid of the problem
            $query = "SELECT content FROM problem WHERE pid=$pid";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_array($result);
            $tmpcont = $row['content'];
            
            $probcont = "<textarea name=\"editQuestion\" rows=\"3\" class=\"form-control\">" . $tmpcont . "</textarea>";

            $resdata = new ResponseData();
            $resdata->probcont = $probcont;
            echo json_encode($resdata);
        }
    }  
    
    mysqli_close($conn);
    
?>
