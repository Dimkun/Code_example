<?php

/** 
 * Main files connect
 */

require_once 'core/controller.php';
require_once 'core/model.php';
require_once 'core/view.php';


error_reporting(E_ALL);
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once 'core/route.php';
Route::start(); // запускаем маршрутизатор
