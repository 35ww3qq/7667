<?php
require_once '../includes/config.php';
require_once '../includes/backlink_checker.php';

$checker = new BacklinkChecker();
$checker->checkAllBacklinks();