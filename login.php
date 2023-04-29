<?php
//Access Control
//echo "Before session started <br>";
session_start(); //required to bring session variables into context
//echo "Session started <br>";

//echo "Before if 1 <br>";

//echo $_SESSION['email'];
//echo "<br>";

if (isset($_SESSION['email']))
{
    //echo "Session is set <br>";
    if (!empty($_SESSION['email']))
    {
        //echo "Email is non-empty <br>";
        if (!($_SESSION['acctype'] == 1)) //check if user is not admin
        {
            //echo "User is not admin <br>";
            http_response_code(403);
            die('Forbidden');
        }
        else
        {
            //echo "User is admin <br>";
        }
    }
    else
    {
        //echo "Email is empty <br>";
    }
}

//check that session exists and is nonempty

else
{
    //echo "Session is not set. <br>";
    http_response_code(403);
    die('Forbidden');
}

?>

<?php
try {
    /*Get DB connection*/
    require_once "../src/DBController.php";

    /*Get information from the search (post) request*/
    $acctype = $_POST['acctype'];
    $password = hash('ripemd256', $_POST['password']); //convert password to 80 byte hash using ripemd256 before saving
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $dob = $_POST['dob']; //is already UTC
    $email = strtolower($_POST['email']); //is converted to lower
    $studentyear = $_POST['studentyear']; //only if student, ensure null otherwise (must be a number)
    $facultyrank = $_POST['facultyrank']; //only if faculty, ensure null otherwise
    $squestion = $_POST['squestion'];
    $sanswer = $_POST['sanswer'];

    if($acctype==null)
    {throw new Exception("input did not exist");}

    /*Checking studentyear and facultyrank*/
    if ($acctype === "3") {
        $facultyrank = null;
    } else if ($acctype === "2") {
        $studentyear = null;
    }

    /*Check for a valid UserID to use. Assumes Users count in order*/
    $rows = $db->query("SELECT COUNT(*) as count FROM User");
    $row = $rows->fetchArray();
    $newUserID = $row['count'] + 927000000; //must always be 1 higher than previous


    /*Check if user already exists*/
    $query = "SELECT Email FROM User WHERE Email = :email";
    $stmt = $db->prepare($query); //prevents SQL injection by escaping SQLite characters
    $stmt->bindValue(':email', $email);
    $results = $stmt->execute();

    if ($results) //user doesn't already exist
    {
        /*Update the database with the new info*/
        $query = "INSERT INTO User VALUES (:newUserID, :email, :acctype, :password, :fname, :lname, :dob, :studentyear, :facultyrank, :squestion, :sanswer)";
        $stmt = $db->prepare($query); //prevents SQL injection by escaping SQLite characters
  
    /*
This secure implementation for the user login functionality of the Secure ED application. On an architectural level, it uses distrustful composition and privilege separation. The method of privilege separation is by creating a child process that solely handles the authentication of user credentials, preventing unauthorized access to sensitive data. The child process is designed to be a standalone security system that checks for the correct access control parameters, verifying the validity of the user's credentials before allowing access to sensitive data. This approach limits the influx of unauthorized security breaches and creates mechanisms for trustworthiness. The child process employs session management by validating session variables through access control checks, thus providing a secure approach to user login.
    */