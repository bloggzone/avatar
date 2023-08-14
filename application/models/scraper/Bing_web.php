<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Bing_web extends CI_model
{
	protected $ovo = array();
	protected $tmp = array();

	public function __construct()
	{
		parent::__construct();
		error_reporting(0);

		$this->load->library('sentences');
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
				$options['headers'] = [
					'Cookie' 			=> "",
					'Referer' 			=> 'https://www.bing.com/',
				];
				$options['proxy'] = $ip;
				return $handler($request, $options);
			};
		};
	}

	function config()
	{
		$this->guzzle = new Client([
			'cookies'		=> true,
			'timeout'  		=> 10.0,
			'verify' 		=> false,
			'http_errors' 	=> false,
			'allow_redirects' => ['strict' => true],
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'Host' 				=> 'www.bing.com',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
				//'User-Agent' 		=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.02) Gecko/20100101 Firefox/96.02',
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
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'Host' 				=> 'www.bing.com',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
				//'User-Agent' 		=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.02) Gecko/20100101 Firefox/96.02',
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
		if (!$list) {
			die;
		}

		if (PROXY_MODE) {
			$this->config_proxy();
		} else {
			$this->config();
		}

		$this->ovo['cookie'] = $this->cookieme();

		$this->ovo['dbx'] 	= $dbx;
		$this->ovo['niche'] = $niche;
		$this->ovo['index'] = [];
		$this->ovo['proxy'] = get_proxy();
		$this->tmp['arr'] 	= [];

		$lang_code 		= LANG_CODE;
		$max_article 	= MAX_ARTICLE_LEVEL;

		$requests = function () use ($list, $lang_code, $max_article) {

			foreach ($list as $key => $info) {
				$kw 	= $info['keyword'];
				$slug 	= $info['slug'];

				$this->ovo['index'][] = $info;

				$query = http_build_query([
					'q' 		=> $kw,
					'pq' 		=> $kw,
					'qs' 		=> 'n',
					'form' 		=> 'QBRE',
					'src' 		=> 'IE-SearchBox',
					//'cc' 		=> $lang_code,
					'count' 	=> $max_article,
					//'rand_num' 	=> rand_num(), //Biar tidak cache
				]);

				$go_url = "http://www.bing.com/search?{$query}";

				$header = [
					'headers' 	=> [
						'Cookie' 			=> $this->ovo['cookie'],
						'Referer' 			=> 'https://www.bing.com/',
					]
				];

				yield new Request("GET", $go_url, $header);
				//yield new Request("GET","https://api.ipify.org?format=json");
			}
		};

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => 20,
			'fulfilled' => function ($res, $key) {
				if ($res->getStatusCode() != 200) {
					echo "\r\n[\033[32mWARNING\033[39m] ==> IP was Banned.\r\n";
					//exec("\"C:/Program Files/nodejs/node.exe\" \"C:/laragon/www/bot/modem.js\"");
				}

				$res_html = (string)$res->getBody()->getContents();

				if ($res_html) {
					$info 		= $this->ovo['index'][$key];

					$do 		= $this->bing_sentences($res_html);

					$this->web_res_filter($info, $do);
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

	function bing_sentences($scrape_res)
	{
		$res = [];

		$scrape_res = html_entity_decode($scrape_res);

		if (strpos($scrape_res, 'class="b_caption"') !== false) {
			$html = str_get_html($scrape_res);

			if ($html) {
				foreach ($html->find('span[class=news_dt]') as $date) {
					$date->innertext = '';
				}

				foreach ($html->find('div[class=b_caption]') as $s) {
					if ($s) {
						$txt = $s->find('p', 0);

						if ($txt) {
							$clear_txt = str_replace([' · ', ' …', '[...]'], ['', '.', ''], $txt->plaintext);
							$res[] = $clear_txt;
						}
					}
				}
			}
		}

		return $res;
	}

	function web_res_filter($info, $result)
	{
		$niche 		= $this->ovo['niche'];
		$kw 	= $info['keyword'];
		$slug 	= $info['slug'];

		$sentences 	= [];

		foreach ($result as $key => $desc) {
			$new_sentences = $this->sentences->parseResult($desc, $kw);

			if ($new_sentences) {
				$sentences = array_merge($sentences, $new_sentences);
			}
		}

		$final 		= '[]';

		if ($sentences) {
			shuffle($sentences);

			$ecd  	= json_encode($sentences, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

			if ($ecd) {
				$final = $ecd;
			}

			echo "\r\n[\033[32mBING ARTICLE\033[39m][\033[32m{$niche}\033[39m] ==> {$kw}\r\n";
		} else {
			echo "\r\n[\033[31mEMPTY\033[39m][\033[31m{$niche}\033[39m] ==> {$kw}\r\n";
		}

		$this->tmp['arr'][] = [
			'slug' => $slug,
			'json_sentences' => $final,
		];

		$count = count($this->tmp['arr']);

		if ($count >= 200) {
			$this->update_batch();
		}
	}
}
