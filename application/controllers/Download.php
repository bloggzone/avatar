<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Download extends CI_Controller
{

	protected $xepo = array();

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);
		//error_reporting(0);
		$this->load->model([
			'scraper/bing_download',
		]);
	}

	function start()
	{
		echo "\n\n==> Prepairing..\n\n";

		$list 	= @file("keywords/paint.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$this->bing_download->go_scrap($list);
	}
}
