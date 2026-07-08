<?php
session_start();
require_once '../config/func.php';

session_destroy();
redirect('login.php');
