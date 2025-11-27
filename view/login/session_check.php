<?php
session_start();
echo "session_id: ".session_id()."<br>";
$_SESSION['hello111'] = "world";
echo "session saved.";


phpinfo();