<?php

// load config file
require_once '../app/config/config.php';


//Autoload libraries
spl_autoload_register(function ($className) {
    require_once 'libraries/' . $className . '.php';
});
