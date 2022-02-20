<?php
    //assets import
    use Utils\RandomStringGenerator;
    include "./rand.php";

    //globals
    $msgErr = "";
    $msgSucc = "";
    //database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    
    try {
      $conn = new PDO("mysql:host=$servername;dbname=dansample", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }

    //button click workflow
    if(isset($_POST['submit'])){
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $cpassword = trim($_POST['cpassword']);

        //sanity check
        if(empty($email) && empty($password)){
            $msgErr = "Fill out the form";
        } else if(empty($email)){
            $msgErr = "Email cannot be empty";
        } else if(empty($password)){
            $msgErr = "Password cannot be empty";
        } else if($password != $cpassword){
            $msgErr = "Password must match";
        } else {
            //user email check if already exists
            $stmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
            $stmt->execute([$email]);
            if( $stmt->rowCount() > 0 ) {
                $msgErr = "The email entered is already in use!";
            } else {
                //hash password
                $password = password_hash($password, PASSWORD_DEFAULT);
                //generate user ID & session ID
                $generator = new RandomStringGenerator;
                $userId = $generator->generate(10);
                $sessionId = $generator->generate(32);

                //add user to database
                $stmt = $conn->prepare("INSERT INTO users(Email, Password, Userid, Sessionid) VALUES(?,?,?,?)");
                $stmt->execute([$email, $password, $userId, $sessionId]);
                $stmt = NULL;

                echo "success";
                //redirect user to [page was in]
                if(isset($backpage)){
                    echo '<script>window.location.href="../'.$backpage.'";</script>';
                } else {
                    echo '<script>window.location.href="./home.php";</script>';
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body{
            min-height: 100vh;
            position: relative;
        }
        .form{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 35%;
        }
    </style>
</head>
<body>
    <form class="form" action="index.php" method="POST">
    <h3>Create an account with us</h3>
    <div class="notif">
        <p>
        <?php
            if ($msgErr != "" && $msgSucc != ""){
            echo '<p class="messbox">'.$msgErr.'</p>';
            } else if($msgErr != ""){
            echo '<p class="messbox">'.$msgErr.'</p>';
            } else if($msgSucc != ""){
            echo '<p class="messbox">'.$msgSucc.'</p>';
            }
        ?>
        </p>
    </div>
    <div class="mb-3">
        <label for="exampleInputEmail1" class="form-label">Email address</label>
        <input name="email" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" required>
        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
    </div>
    <div class="mb-3">
        <label for="exampleInputPassword1" class="form-label">Password</label>
        <input name="password" type="password" class="form-control" id="exampleInputPassword1" required>
    </div>
    <div class="mb-3">
        <label for="exampleInputPassword1" class="form-label">Confirm Password</label>
        <input name="cpassword" type="password" class="form-control" id="exampleInputPassword1" required>
    </div>
    <div class="mb-3 form-check">
        <input name="checkbox" type="checkbox" class="form-check-input" id="exampleCheck1" required>
        <label class="form-check-label" for="exampleCheck1">Check me out</label>
    </div>
    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
    </form>
</body>
</html>