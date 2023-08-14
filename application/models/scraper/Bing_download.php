<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Bing_download extends CI_model
{
	protected $ovo = array();
	protected $tmp = array();

	public function __construct()
	{
		parent::__construct();
		//error_reporting(0);
	}

	function config()
	{
		$this->guzzle = new Client([
			'stream'	=> true,
			'timeout'  	=> 30.0,
			'verify' 	=> false,
			'http_errors' => false,
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate, br',
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'Host' 				=> 'www.bing.com',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
			]
		]);
	}


	function go_scrap($dir, $list)
	{
		$this->config();

		$this->ovo['dir'] = $dir;
		$this->ovo['index'] = [];
		$this->tmp['arr'] 	= [];

		$requests = function () use ($list) {

			foreach ($list as $key => $kw) {
				$this->ovo['index'][] = $kw;

				$query = http_build_query([
					'q' 		=> $kw,
					'rand_num' 	=> rand_num(), //Biar tidak cache
				]);

				$header = [
					'headers' => [
						'Referer' => 'https://www.bing.com/',
					]
				];

				yield new Request("GET", "http://tse1.mm.bing.net/th?{$query}", $header);
			}
		};

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => 20,
			'fulfilled' => function ($res, $key) {
				$kw 	= $this->ovo['index'][$key];

				if ($res->getStatusCode() != 200) {
					echo "\r\n[\033[32m{$key} WARNING\033[39m] ==> IP was Banned.\r\n";
				} else {
					$res 	= (string)$res->getBody()->getContents();

					$dir 	= $this->ovo['dir'];
					$dir 	= ($dir !== '') ? "{$dir}/" : "";


					$fn 	= md5($kw);

					write_file("{$dir}assets/img/{$fn}.jpg", $res);

					echo "\r\n[\033[32mIMAGE DOWNLOAD\033[39m] ==> {$kw}\r\n";
				}
			}
		]);

		$pool->promise()->wait();


		echo "\nFinish..\n";
	}
}
