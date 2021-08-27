<?php

require_once(__DIR__."/PasswordPolicy.php");

use Ancientec\PasswordPolicy\PasswordPolicy;

$customValidate = function($password) {
    //password is not ok:
    if(strpos($password, "abc") !== 0) {
          return "password should prefix abc";
    }

    //password is ok:
    return "";
};

$passwordPolicy = new PasswordPolicy([
    "LengthMin" => 6,
    "CustomValidate" => $customValidate
]);

$passwordPolicy->validate("password");

$passwordPolicy->validate("abcPassword");

var_dump($passwordPolicy->isValid("password"));

var_dump($passwordPolicy->isValid("abcPassword"));