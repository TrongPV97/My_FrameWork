<?php
	use app\core\Controller;

	Router::get('/pagePost','HomeController@pagePost');
	Router::get('/test','HomeController@index');
	Router::get('/getPage','HomeController@getPage');
	Router::get('/r1/d1/{1}',function(){
		echo "r1";
	});
	Router::get('/r2/d1/{1}',function(){
		echo "r2";
	});
	Router::get('/r3/{1}',function(){
		echo "r3";
	});
	Router::any('*',function(){
		echo '404';
	});
	
?>