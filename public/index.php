<?php
	require_once(dirname(__DIR__).'/app/core/App.php');
	$config = require_once(dirname(__DIR__).'/config/main.php');
	$view = require_once(dirname(__DIR__).'/config/view.php');
	$app = new App($config,$view);
	$app->run();
?>