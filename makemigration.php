<?php
require __DIR__ . '/vendor/autoload.php';
use Parser\Db;
Db::init()->migrate(true);