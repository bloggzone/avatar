<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Bing_image extends CI_model
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
		$do = $this->ovo['dbx']->update_batch('tbl_keywords', $this->tmp['arr'], 'slug');

		if ($do) {
			$this->tmp['arr'] = [];
		}
	}

	function add_proxy_callback($proxy_callback)
	{
		return function (callable $handler) use ($proxy_callback) {
			return function (Psr\Http\Message\RequestInterface $request, $options) use ($handler, $proxy_callback) {
				$ip = $proxy_callback();
				$options['proxy'] = [
					'http'  => $ip, // Use this proxy with "http"
					'https' => $ip, // Use this proxy with "https",
				];
				return $handler($request, $options);
			};
		};
	}

	function config()
	{
		$this->guzzle = new Client([
			'timeout'  	=> 30.0,
			'verify' 	=> false,
			'http_errors' => false,
			'allow_redirects' => ['strict' => true],
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'Host' 				=> 'www.bing.com',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
				//'User-Agent' 		=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:200.0) Gecko/20100101 Firefox/200.0',
			]
		]);
	}

	function config_proxy()
	{
		$this->ovo['proxy'] = get_proxy();

		$this->stack = new GuzzleHttp\HandlerStack();
		$this->stack->setHandler(new GuzzleHttp\Handler\CurlHandler());
		$this->stack->push($this->add_proxy_callback(function () {
			return get_proxy(); //function return a proxy
		}));

		$this->guzzle = new Client([
			'handler' 		=> $this->stack,
			'cookies'		=> true,
			'timeout'  		=> 10.0,
			'verify' 		=> false,
			'http_errors' 	=> false,
			'allow_redirects' => ['strict' => true],
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.5',
				'Host' 				=> 'www.bing.com',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
				//'User-Agent' 		=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:200.0) Gecko/20100101 Firefox/200.0',
			]
		]);
	}

	function cookieme()
	{
		$jar = new \GuzzleHttp\Cookie\CookieJar();

		$response = $this->guzzle->request('HEAD', 'https://www.bing.com/', [
			'cookies' => $jar
		]);

		$a = $response->getHeaders();

		return $a['Set-Cookie'][0] ?? '';
	}

	function go_scrap($dbx, $list, $niche)
	{
		if (PROXY_MODE) {
			$this->config_proxy();
		} else {
			$this->config();
		}

		$this->ovo['cookie'] = $this->cookieme();

		$this->ovo['dbx'] 	= $dbx;
		$this->ovo['niche'] = $niche;
		$this->ovo['index'] = [];
		$this->tmp['arr'] 	= [];

		$lang_code = LANG_CODE;
		$max_image = MAX_IMAGE_RESULT;

		$requests = function () use ($list, $lang_code, $max_image) {

			foreach ($list as $key => $info) {
				$kw 	= $info['keyword'];
				$slug 	= $info['slug'];

				$this->ovo['index'][] = $info;

				$query 	= http_build_query([
					'q' 		=> $kw,
					//'cc' 		=> $lang_code,
					'count' 	=> $max_image,
					//'qft' 	=> '+filterui:imagesize-large',
					'qft' 		=> '+filterui:imagesize-custom_500_500',
					'FORM' 		=> 'IRFLTR',
					'first' 	=> 1,
					'tab' 		=> 'REC',
					//'rand_num' 	=> rand_num(), //Biar tidak cache
				]);

				$go_url = "https://www.bing.com/images/async?{$query}";
				
				$header = [
					'headers' => [
						'Cookie' 			=> $this->ovo['cookie'],
						'Referer' 			=> 'https://www.bing.com/'
					]
				];

				yield new Request("GET", $go_url, $header);
			}
		};

		$oc = OVERCLOCK_LEVEL;

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => $oc,
			'fulfilled' => function ($res, $key) {
				$info 	= $this->ovo['index'][$key];

				$status = $res->getStatusCode();

				if ($status === 409) {
					echo "\r\n[\033[32m{$key} WARNING\033[39m] ==> {$status} IP was Banned.\r\n";
				}

				$res_html = (string)$res->getBody()->getContents();

				$this->web_res_filter($info, $res_html);
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

	function web_res_filter($info, $res)
	{
		$niche 	= $this->ovo['niche'];
		$kw 	= $info['keyword'];
		$slug 	= $info['slug'];

		if (strpos($res, 'class="imgpt"') === false) {

			$this->arr_insert($slug);
			echo "\r\n[\033[31mEMPTY IMAGE\033[39m][\033[31m{$niche}\033[39m] ==> {$kw}\r\n";
			return false;
		}

		$images 	= [];

		$html 		= str_get_html($res);

		$max_image = MAX_IMAGE_RESULT;

		foreach ($html->find('div[class=imgpt]') as $key => $s) {
			$data = $s->find('a.iusc', 0);

			if (!$data) {
				continue;
			}

			$data = htmlspecialchars_decode($data->m);

			$json = @json_decode($data);

			$img_url = $json->murl;

			//if(strpos( $img_url,'/wp-content/' ) !== false){continue;}

			$title 	= str_replace(['', '', ' ...'], '', $json->t);
			$title 	= bing_title_cleaner($title);

			$source = $json->purl;
			$domain = parse_url($source, PHP_URL_HOST);

			$size 	= $s->find('div.img_info span.nowrap', 0)->plaintext;

			$s 		= explode('.', $size);

			$sz 	= explode(' x ', $s[0]);

			$width 	= (int)$sz[0] ?? 600;
			$height = (int)$sz[1] ?? 600;

			$itype	= $s[1] ?? 'jpg';


			$images[] 	= [
				'keyword' 	=> $kw,
				'slug' 		=> $slug,
				'title' 	=> $title,
				'alt' 		=> $title,
				'url' 		=> $img_url,
				'thumb' 	=> "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",
				'filetype' 	=> $itype,
				'width' 	=> $width,
				'height' 	=> $height,
				'source' 	=> $source,
				'domain' 	=> $domain
			];

			$count = count($images);

			if ($count >= $max_image) {
				break;
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

			echo "\r\n[\033[32mBING IMAGE\033[39m][\033[32m{$niche}\033[39m] ==> {$kw}\r\n";
		} else {
			echo "\r\n[\033[31mEMPTY\033[39m][\033[31m{$niche}\033[39m] ==> {$kw}\r\n";
		}

		$this->arr_insert($slug, $final);
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
