<?php
$host = getenv('IP');
$username = getenv('C9_USER');
$password = '';
$dbname = 'cheapomail';
// For sessions
session_start();
try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
}
catch(PDOException $e){
    echo $e;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     
     // user to be added
    $fname = $_POST["firstname"];
    $lname = $_POST["lastname"];
    $uname = $_POST["username"];
    $pword = sha1($_POST["password"]);
    
    // login
    $logname = $_POST["logname"];
    $logpass = sha1($_POST["logpass"]);
    // mail to be sent
    $subj = $_POST["subject"];
    $recps = $_POST["recipients"];
    $body = $_POST["body"];
    
    //id of message read
    $read_id = $_POST["read_id"];
    
    // indicate logout
    $lout = $_POST["logout"];
    
    //login
    if(isset($logname) && isset($logpass)){
        $sql = "SELECT * FROM users WHERE username = '$logname' AND password = '$logpass';";
        $stmt = $conn->query($sql);
        $res = $stmt->fetch();
        
        if($res != null){
            $_SESSION["username"] = $res["username"];
            $_SESSION["user_id"] = $res["id"];
            echo "User Found";
        }
        else{
            echo "No User Found";
        }
    }
    
    //add a user
    if (isset($uname) && isset($pword) && isset($fname) && isset($lname)){
        $fname = strip_tags($fname);
        $lname = strip_tags($lname);
        $uname = strip_tags($uname);
        $pword = strip_tags($pword);
        
        $sql = "INSERT INTO users(firstname, lastname, username, password) VALUES('$fname', '$lname', '$uname', '$pword');";
        $conn->exec($sql);
        
        echo 'Successfully Added User!';
    }
    
    //logout
    if($lout == "true"){
        session_unset();
        session_destroy();
    }
    
    //read mail
    if(isset($read_id)){
        $rdate = date("Y/m/d");
        $nid = $_SESSION["user_id"];
        
        $stat = $conn->query("SELECT message_id FROM messages_read;");
        $arr = $stat->fetchAll(PDO::FETCH_COLUMN, 0);
        
        //so that message is only added to messages_read once
        if (in_array($read_id, $arr) == false){
            $sql = "INSERT INTO messages_read(message_id, reader_id, date_read) VALUES('$read_id', '$nid', '$rdate');";
            $conn->exec($sql);
        
            echo "Read";
        }
    }
    
    //send a message
    if (isset($recps) && isset($subj) && isset($body)){
        
        //get id of sender
        $sid = $_SESSION["user_id"];
    
        $cdate = date("Y/m/d");
        
        $recps = explode(",", $recps); //split strings by comma
    
        //insert message for each recipient
        foreach($recps as $recp){
            
            //get id of receiver
            $stmt2 = $conn->query("SELECT id FROM users WHERE username = '$recp'");
            $s = $stmt2->fetch();
            $rid = $s["id"];
    
            // query to be sent
            $q = "INSERT INTO messages(recipient_id, user_id, subject, body, date_sent) VALUES('$rid', '$sid', '$subj', '$body', '$cdate');";
            $conn->exec($q);
        }
        
        echo "Message sent!";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // user id to recieve mail
    $rcvr = $_SESSION["user_id"];
    $getmail = strip_tags($_GET["getmail"]);
    
    if ($getmail == 'true'){
        
        $stmt = $conn->query("SELECT * FROM messages WHERE recipient_id = '$rcvr' ORDER BY date_sent LIMIT 10;");
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt2 = $conn->query("SELECT message_id FROM messages_read;");
        $res2 = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);
        
        if(count($res) == 0){
            echo "<h2>You Have No Mail Today!</h2>";
        }
        
        else{
            foreach($res as $mail){
                
                $new = $conn->query("SELECT username FROM users WHERE id = '" . $mail["user_id"] . "';");
                $sendr = $new->fetch();
                
                if (in_array($mail["id"], $res2)){
                    echo '<div class="mail read">';
                    echo '<p1>(Read)</p1><br>';
                }
                else{
                    echo '<div class="mail unread">';
                    
                }
                    
                  
                echo '<p1>From: ' . $sendr["username"] . '</p1><br>';
                echo '<p1>Subject: ' . $mail["subject"] . '</p1><br>';
                echo '<p1>Date: ' . $mail["date"] . '</p1><br>';
                echo '<p1 class="recv">Message: ' . $mail["body"] . '</p1>';
                echo '<input type="submit" class="showbutton" value="Read"/><br>';
                echo '<p1 class="hide">' . $mail["id"] . '</p1><br>';
                echo '</div><br>';
              
            }
        }
    }
}