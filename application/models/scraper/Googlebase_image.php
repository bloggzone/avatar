<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Googlebase_image extends CI_model
{
	protected $ovo = array();
	protected $tmp = array();

	public function __construct()
	{
		parent::__construct();
		//error_reporting(0);

		$this->guzzle = new Client([
			'timeout'  		=> 30.0,
			'verify' 		=> false,
			'http_errors' 	=> false,
			'headers' 		=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'User-Agent' 		=> rand_ua(),
			],
			'proxy' => get_proxy()
		]);
	}

	function update_batch()
	{
		$do = $this->ovo['dbx']->update_batch('tbl_keywords', $this->tmp['arr'], 'slug');

		if ($do) {
			$this->tmp['arr'] = [];
		}
	}

	function go_scrap($dbx, $list, $niche)
	{
		$this->ovo['dbx'] 	= $dbx;
		$this->ovo['niche'] = $niche;
		$this->ovo['index'] = [];
		$this->tmp['arr'] 	= [];

		$filter = cse_site();

		$requests = function () use ($list, $filter) {

			foreach ($list as $key => $info) {
				$kw 	= $info['keyword'];
				$slug 	= $info['slug'];

				$this->ovo['index'][] = $info;

				$q = "{$filter}{$kw}";

				$query = http_build_query([
					'q' 		=> $q, //no filter ON
					'source' 	=> 'lnms',
					'tbm' 		=> 'isch',
					//'tbs' 		=> 'isz:l',
					'client' 	=> 'firefox-b-d',
					'sa' 	=> 'X',
					'biw' 	=> 500,
					'bih' 	=> 500,
					'dpr' 	=> 1
				]);

				yield new Request("GET", "https://www.google.com/search?{$query}");
			}
		};

		$oc = OVERCLOCK_LEVEL;

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => $oc,
			'fulfilled' => function ($res, $key) {
				$info 	= $this->ovo['index'][$key];

				if ($res->getStatusCode() != 200) {
					echo "\r\n[\033[32m{$key} WARNING\033[39m] ==> IP was Banned.\r\n";
				} else {
					$res = (string)$res->getBody()->getContents();

					if ($res) {
						$this->web_res_filter($info, $res);
					}
				}
			}
		]);

		$pool->promise()->wait();

		$count = count($this->tmp['arr']);

		if ($count > 0) {
			$this->update_batch();
			sleep(1);
		}

		echo "\nFinish..\n";
	}

	function getValues($array)
	{
		$return = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $vk => $vv) {
					$return[] = $vv;
				}
			} else {
				$return[] = $value;
			}
		}

		return $return;
	}

	function array_flatten($array)
	{

		$return = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return = array_merge($return, $this->array_flatten($value));
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	function filterResult($array, &$result)
	{
		$array = array_filter($array);

		foreach ($array as $key => $value) {
			$data = [];

			if (filter_var($value, FILTER_VALIDATE_URL)) {
				$result[] = array_filter($this->array_flatten($array));
			}


			if (is_string($value)) {
				$result[] = $value;
			}


			if (is_array($value)) {
				$this->filterResult($value, $result);
			}
		}
	}

	function web_res_filter($info, $res)
	{
		$niche 	= $this->ovo['niche'];
		$kw 	= $info['keyword'];
		$slug 	= $info['slug'];

		if (!$res) {

			return false;

			echo "\r\n[\033[31mEMPTY\033[39m] ==> {$kw}\r\n";
		}

		$i = 0;

		for ($i = 0; $i <= 10; $i++) {

			if (strpos($res, "AF_initDataCallback({key: 'ds:1', hash: '{$i}', data:") !== false) {
				break;
			}
		}

		$exploded 	= explode("AF_initDataCallback({key: 'ds:1', hash: '{$i}', data:", $res);

		$data 		= $exploded[1] ?? '';

		$data 		= explode(', sideChannel: {}});</script>', $data);
		$data 		= $data[0];

		$data 		= json_decode($data, true);

		$rawResults = [];
		$images 	= [];

		if (isset($data[31][0][12][2])) {
			$rawResults = $data[31][0][12][2];
		}

		$max_image = MAX_IMAGE_RESULT;

		foreach ($rawResults as $rawResult) {
			$count = count($images);

			if ($count >= $max_image) {
				break;
			}

			$result = [];

			$this->filterResult($rawResult, $result);
			$data = $this->getValues($result);

			$result = [];

			if (count($data) >= 11) {
				$result['keyword'] 	= $kw;
				$result['slug'] 	= $slug;

				$result['title'] 	= imake_stringcase("ucwords", $data[13]);
				$result['alt'] 		= imake_stringcase("ucwords", $data[19] ?? $kw);
				$result['url'] 		= $data[8];
				$result['thumb'] 	= str_replace('&usqp=CAU', '', $data[4]);
				$result['filetype'] = $this->getFileType($data[8]);
				$result['width'] 	= $data[6];
				$result['height'] 	= $data[7];
				$result['source'] 	= $data[12];
				$result['domain'] 	= parse_url($data[12], PHP_URL_HOST);

				$images[] = $result;
			}
		}

		$img_count 	= count($images);

		$final 		= '[]';

		if ($img_count >= 1) {
			$fin = [
				'images'	=> $images,
				'related'	=> [],
				'sentences'	=> [],
			];

			$encode 	= json_encode($fin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

			if ($encode) {
				$final = $encode;
			}

			echo "\r\n[\033[32mGOOGLE IMAGE\033[39m][\033[32m{$niche}\033[39m] ==> {$kw}\r\n";
		} else {
			echo "\r\n[\033[31mEMPTY\033[39m][\033[31m{$niche}\033[39m] ==> {$kw}\r\n";
		}

		$this->tmp['arr'][] = [
			'slug' 			=> $slug,
			'json_images' 	=> $final,
		];

		$count = count($this->tmp['arr']);

		if ($count >= 200) {
			$this->update_batch();
		}
	}

	function getFileType($url)
	{
		$url 		= strtolower($url);
		$tmp 		= @parse_url($url)['path'];
		$ext 		= pathinfo($tmp, PATHINFO_EXTENSION);
		$arr_ext 	= ['jpg', 'png', 'webp', 'gif', 'bmp', 'gif'];

		if (!in_array($ext, $arr_ext)) {
			$ext 	= 'jpg';
		}

		return $ext;
	}
}
