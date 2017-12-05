<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://use.fontawesome.com/e3f927f214.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js"></script>
        <script type="text/javascript">
            window.MathJax = {
                tex2jax: {
                    inlineMath: [["\\(", "\\)"]],
                    processEscapes: true
                }
            };
        </script>
        <script type="text/javascript" async
            src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML">
        </script>        
        <title>CJT7G6 Project 1</title>
           
        <style>
            table, th, td {
                margin-left: 20px;
                margin-right: 20px;
                margin-bottom: 20px;
                text-align: center;
                border: 1px solid black;
                border-collapse: collapse;
            }
            th {font-style: bold;}
            th, td{padding: 20px;}
            a {
                text-decoration: none;
                padding-bottom: 30px;
                padding-left: 20px;
            }

            a:hover {
                background-color: #ddd;
                color: black;
            }

            .previous {
                background-color: #f1f1f1;
                color: black;
            }

            .next {
                background-color: #4CAF50;
                color: white;
            }

            .round {
                border-radius: 70%;
                font-size:larger;
            }
            
            #boldStuff {
                font-size: x-large;
               
            }
            
            #pageNum{
                font-size: x-large;
                font-weight: bold;
            }
            
            #container{
                display: inline-table;
                float: right;
                margin-right: 40px;
            }
            
            #undoDelete{
                margin-left: 20px;
                margin-bottom: 20px;

            }
            
            #pagination{
                margin-left: 20px;
                margin-bottom: 20px;
            }
            
            #new-question-content {
                text-align: center;
            }
            
            #new-question-label{
                margin-left: 20px;
            }
            
            #new-question-submit-button{
                margin-left: 20px;
                margin-bottom: 20px;
            }
            
            #imaginary_container{
                margin-top:20%; /* Don't copy this */
                margin-bottom: 5%;
            }
            .stylish-input-group .input-group-addon{
                background: white !important; 
            }
            .stylish-input-group .form-control{
                    border-right:0; 
                    box-shadow:0 0 0; 
                    border-color:#ccc;
            }
            .stylish-input-group button{
                border:0;
                background:transparent;
            }
            
            #homeButton{
                margin-top: auto;
                margin-bottom: auto;
            }
            
        </style>
    </head>
    <body>
    <button id="homeButton" onclick="" class="btn btn-link btn-lg"><i class="fa fa-home" aria-hidden="true" style="font-size:70px"></i></button>

        
        <form action="search.php" method="get">
            <div class="container2">
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <div id="imaginary_container">
                            <p>Suggestions: <span id="txtHint"></span></p>
                            <div class="input-group stylish-input-group">
                                <input type="text" class="form-control" name="searchBar" placeholder="Search" onkeyup="">
                                <span class="input-group-addon">
                                    <button type="submit">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>  
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <script type="text/javascript">
            "use strict"; 
            
            function searchHint(str) {                
                if (str.length === 0) { //validate the ength of the string
                    alert("You must enter in a search criteria");
                    $("#txtHint").html("");
                    return;
                } else {
                    $.get("getHint.php", { searchBar: str }, function (data, status) {
                        //document.getElementById("txtHint").innerHTML(data);
                        $("#txtHint").html(data);
                    });
                }             
            }
            
            $('#homeButton').click(function() {
               window.location.replace("index.php"); //redirect back to the home page 
            });
        </script>
        
        <?php 
            //create the global db variables and attempt to initiate connection with the database
            $dbhost = "localhost";
            $dbuser = "root";
            $dbpass = "";
            $dbname = "mathprobdb";
            $tablename = "problem";
            $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('[-]ERROR: Could not connect to the database ' . mysql_error());
            mysqli_select_db($conn, $dbname);
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (isset($_GET['searchBar'])) {
                    $kw = filter_input(INPUT_GET, "searchBar");
                    $keywordArray = explode(", ", $kw);
                    $keywordCount = count($keywordArray);
                    $incrementer = 0;
                    
                    $counter = 0;
                    $keywordSearch = "";
                    
                    //create the query
                    $keywordQuery = "SELECT problem.pid, problem.content, keyword.keyword FROM problem "
                            . "LEFT JOIN probkey_mapping ON probkey_mapping.pid = problem.pid "
                            . "LEFT JOIN keyword ON probkey_mapping.keyid = keyword.keyid WHERE keyword.keyword IN (";
                    
                    //loop to trim each word and construct the query
                    foreach($keywordArray as $key) {
                        $key = trim($key, " \t\n\r\0\x0B");
                        $keywordQuery .= "'" . $key . "'"; //concat the keyword with tick marks
                        
                        if ($incrementer < $keywordCount - 1) { //if it's not the last word
                            $keywordQuery .= ","; //add a comma
                        }
                        $incrementer++; //then increment
                    }
                    
                    $keywordQuery .= ") "; //close the keywords
                    $keywordQuery .= "ORDER BY keyword.keyword"; 
                    
                    $insertResultsQuery = "INSERT INTO search_results " //construct the insert query to insert the results into it's own table
                                        . "$keywordQuery";
                    
                    mysqli_query($conn, $insertResultsQuery); //perform the insert
                                            
                    $sql1 = "SELECT * FROM search_results"; //get all the results from the search results table
                    $retval1 = mysqli_query($conn, $sql1) or die('[-]ERROR: Could not retrieve data ' . mysql_error());
                    
                    print '<form action="servlet.php" method="get">';
                    print "<table>";
                    print "<thead>";
                    print "<tr>";
                    print "<th>Edit</th>";
                    print "<th>Delete</th>";
                    print "<th>Question Number</th>";
                    print "<th>Question Content</th>";
                    print "<th>Keyword</th>";
                    print "</tr>";
                    print "</thead>";
                    print "<tbody>";
                    
                    while($row = mysqli_fetch_array($retval1, MYSQLI_ASSOC)){
                        print "<tr>";
                        print "<td>" . '<button value="' . $row['pid'] . '" onclick="editThisProb("' . $row['pid'] . '") name="edit" id="editMe" type="submit" class="btn btn-link btn-sm"><i class="fa fa-pencil" style="font-size:18px"></i></button>' . "</td>";
                        print "<td>" . '<button value="' . $row['pid'] . '" name="delete" type="submit" class="btn btn-link btn-sm"><i class="fa fa-trash-o" style="font-size:18px"></i></button>' . "</td>";
                        print "<td>" . $row['pid'] . "</td>";
                        print "<td>" . $row['content'] . "</td>";
                        print "<td>" . $row['keyword'] . "</td>";
                        print "</tr>";
                    }
                    
                    print "</tbody>";
                    print "</table>";
                    print "</form>"; 
                    
                    $truncateTable = "TRUNCATE TABLE search_results"; //truncate the search results table to erase everything once we've displayed it
                    mysqli_query($conn, $truncateTable); //perform the truncate
                    mysqli_close($conn); //close the connection
                }
            }
        ?>
        
        <form action="servlet.php" method="get"><button id="undoDelete" name="undoDelete" type="submit" class="btn btn-primary btn-md"><i class="fa fa-undo" style="font-size:20px"></i> Undo Delete</button></form>
        <form id="add-new-question-form" action="servlet.php" method="get">
                <div class="form-group">
                    <label id="new-question-label" for="newQuestionContent">Add a New Question</label>
                    <input id="new-question-content" class="form-control" type="text" name="newQuestionContent" placeholder="New Question Content"/>
                </div>
                <input id="new-question-submit-button" name="newQuestionSubmitButton" class="btn btn-primary" type="submit" value="Submit"/>
        </form>
        
    </body>
</html>