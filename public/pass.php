<?php
$pass = 'Admin@123';
$pass_hash = '$2y$12$WgOiHuJWSvGeOAUFLei3BeXqqQCAA/iIjvNkLyOYk3vozfEMPANri';

if (password_verify($pass, $pass_hash)) {
    echo $pass;
} else {
    echo "Wrong password";
}
echo "<pre>";
var_dump(password_hash($pass,PASSWORD_BCRYPT, ['cost' => 12]));

