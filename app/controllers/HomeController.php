<?php
	namespace app\controllers;

	use app\core\Controller;
	use app\core\QueryBuilder;
	use app\model\Users;
	use app\model\Products;

	/**
	* HomeController
	*/
	class HomeController extends Controller
	{
		
		function __construct()
		{
			parent::__construct();

		}
		function pagePost()
		{
			$this->render('index');
		}
		function getPage()
		{
			$this->render('index');
		}

		public function index()
		{	
			if (isset($_POST)) {
				extract($_POST, EXTR_PREFIX_SAME, "POST");
			}
			//$tb=Users::all();
			
			$tb=Users::where("id","=","203")->get();

			//$tb=Users::all();
			//var_dump("Get find record");

			//$tb2=Users::find('1')->get();
			//var_dump($tb);
			//$tb= new Users();
			//Users::where("ads","=","ad");
			//$tb::testCallStatic("a123","a");
			//$tb->id="2330";
			//$tb->email = "phamtrong20dd20@gmail.com";
			//$tb->save();
			//$tb=users::where("id","=","2330")->delete();
			/*var_dump($_POST);*/
			//var_dump($tb);""
			//var_dump($tb2);

			$this->render('data',['user'=>$tb]);
			
			//echo "Người dùng có id ".$ID." có mail là: ".$Email;

		}
		function abc($a){
			echo $a;
		}
		function cba($a){
			echo "fadf";
		}
	}
?>