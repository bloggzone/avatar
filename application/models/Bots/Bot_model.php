<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Bot_model extends CI_model
{
	protected $ovo = array();
	protected $tmp = array();

	public function __construct()
	{
		parent::__construct();
		//error_reporting(0);   	

	}

	function update_batch()
	{
		$do = $this->dbmaster->update_batch('tbl_keywords', $this->tmp['arr'], 'slug');

		if ($do) {
			$this->tmp['arr'] = [];
		}
	}

	function config()
	{
		$this->guzzle = new Client([
			'timeout'  	=> 1800,
			'connect_timeout'  	=> 1800,
			'verify' 	=> false,
			'http_errors' => false,
			'allow_redirects' => ['strict' => true],
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.9'
			]
		]);
	}


	function http_exec($nodeFile, $list)
	{
		$this->config();

		$this->ovo['index'] = [];
		$this->tmp['arr'] 	= [];

		$requests = function () use ($nodeFile, $list) {

			foreach ($list as $key => $info)
			{
				$this->ovo['index'][] = $info;

				$query 	= http_build_query([
					'nodeFile'  => $nodeFile,
					'info' 	 	=> $info
				]);

				$go_url = base_url("exec/exec-bot?{$query}");

				yield new Request("GET", $go_url);
			}
		};

		$oc = OVERCLOCK_LEVEL;

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => $oc,
			'fulfilled' => function ($res, $key) {				

				$status = $res->getStatusCode();

				if($status == 200)
				{
					$info 	= $this->ovo['index'][$key];
					//arr_insert
				}

				$res_html = (string)$res->getBody()->getContents();

				echo "\r\n[\033[32m{$status}\033[39m] ==> {$info}\r\n";

				echo $res_html;
			}
		]);

		$pool->promise()->wait();

		$count = count($this->tmp['arr']);

		if ($count > 0) {
			//$this->update_batch();
			sleep(1);
		}

		echo "\nFinish..\n";
	}

	function single_exec($url)
	{
		$this->config();

		$this->guzzle->request('GET', $url);

		echo "\nFinish..\n";
	}

	function arr_insert($slug, $final = '[]')
	{
		$this->tmp['arr'][] = [
			'slug' 			=> $slug,
			'json_images' 	=> $final,
		];

		$count = count($this->tmp['arr']);

		if ($count >= 200) {
			$this->update_batch();
		}
	}
}
