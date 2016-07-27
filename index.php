<?php
session_start();
$error = "";


if (array_key_exists("logout", $_GET)) {
    session_unset();
    setcookie("id", "", time() - 60*60);
    $_COOKIE['id'] = "";
}
else if ((array_key_exists("id", $_SESSION) AND $_SESSION['id']) OR (array_key_exists("id", $_COOKIE) AND $_COOKIE['id']))
    header("Location: loggedinpage.php");


if (array_key_exists("submit", $_POST)) {
    
    include ("connection.php");
    
    if (!$_POST['email'])
        $error .= "<li>An email address is required.</li>";
    if (!$_POST['password'])
        $error .= "<li>A password is required.</li>";
    if ($error) {
        $error = "There were error(s) in your form:<ul>".$error."</ul>";
    }
    else if ($_POST['signUp'] == '1') {
        $query = "SELECT `id` FROM `users` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";
        $result = mysqli_query($link, $query);
        if (mysqli_num_rows($result))
            $error = "That email is already taken.";
        else {
            //$userID = mysqli_fetch_array($result);
            //$userID = $userID['id'];
            $query = "INSERT INTO `users` (`email`, `password`) VALUES('".mysqli_real_escape_string($link, $_POST['email'])."', '".mysqli_real_escape_string($link, $_POST['password'])."')";
            
            if (!mysqli_query($link, $query))
                $error = "<p>Could not sign you up - try later.</p>";
            else {
                $last_id = mysqli_insert_id($link);
                $query = "UPDATE `users` SET password='". md5(md5(mysqli_insert_id($link)).$_POST['password'])."' WHERE id=".mysqli_insert_id($link)." LIMIT 1";
                mysqli_query($link, $query);
                
                $_SESSION['id'] = $last_id;
                if ($_POST['stayLoggedIn'] == 1) {
                    setcookie("id", $last_id, time() + 60*60*24);
                }
                header("Location: loggedinpage.php");
                //echo "Sign up successful";
            }
            
            
        }
    }
    else {
        $query = "SELECT * FROM `users` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";
        $row = mysqli_fetch_array(mysqli_query($link, $query));
        if (isset($row)) {
            $hashedPassword = md5(md5($row['id']).$_POST['password']);
            //echo "hashedpass: ".$hashedPassword."<br>";
            //echo "userID = ".$row['id']." pass = ".$_POST['password']."<br>";
            if ($hashedPassword == $row['password']) {
                //echo "Password correct. Logging in...";
                
                $_SESSION['id'] = $row['id'];
                if ($_POST['stayLoggedIn'] == '1')
                    setcookie("id", $row['id'], time() + 60*60*24);
                header("Location: loggedinpage.php");
                
            }
            else
                $error = "Password is not correct";
        }
        else {
            $error = "This email/password doesn't exist.";
        }
    }
    
}


?>

<?php include("header.php"); ?>
        <div class="container" id="homePageContainer">
            <h1>Secret Diary</h1>
            <p><strong>Store your thoughts permanently and securely.</strong></p>
            <div id="error"><?php if ($error != "") {
        echo '<div class="alert alert-danger" role="alert">'.$error.'</div>';
            }  
            ?></div>
            <form method="post" id="signUpForm">
                <p>Interested? Sign up now.</p>
                <fieldset class="form-group">
                    <!---<label for="email">Email address</label> -->
                    <input class="form-control" type="email" name="email" id="email" placeholder="Your email">
                </fieldset>
                <fieldset class="form-group">
                    <!--<label for="password">Password</label> -->
                    <input class="form-control" type="password" name="password" id="password" placeholder="Your password">
                </fieldset>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="stayLoggedIn" value="1"> Stay Logged In
                    </label>
                </div>
                <fieldset class="form-group">
                    <input type="hidden" name="signUp" value="1">                
                    <input type="submit" class="btn btn-success" name="submit" value="Sign up!">
                </fieldset>
                
                <p><a class="toggleForm">Log In</a></p>
            </form>
            
            
            
            <form method="post" id="logInForm">
                <p>Login using your email address and password.</p>
                <fieldset class="form-group">
                    <!---<label for="email">Email address</label> -->
                    <input class="form-control" type="email" name="email" id="email" placeholder="Your email">
                </fieldset>
                <fieldset class="form-group">
                    <!--<label for="password">Password</label> -->
                    <input class="form-control" type="password" name="password" id="password" placeholder="Your password">
                </fieldset>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="stayLoggedIn" value="1"> Stay Logged In
                    </label>
                </div>
                <fieldset class="form-group">
                    <input type="hidden" name="signUp" value="0">                
                    <input type="submit" class="btn btn-success" name="submit" value="Log In!">
                </fieldset>
                
                <p><a class="toggleForm">Sign up</a></p>
            </form>
                       
        </div>
<?php include("footer.php"); ?>