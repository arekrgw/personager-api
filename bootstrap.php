<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = DotEnv::createImmutable(__DIR__);
$dotenv->load();
