<?php defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

//require APPPATH.'third_party/simple_html_dom.php';

class Openai_web extends CI_model
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

	function config()
	{
		$this->guzzle = new Client([
			'cookies'		=> true,
			'timeout'  		=> 180.0,
			'verify' 		=> false,
			'http_errors' 	=> false,
			'allow_redirects' => ['strict' => true],
			'headers' 	=> [
				'Accept-Encoding' 	=> 'gzip, deflate',
				'Accept-Language' 	=> 'en-US,en;q=0.9',
				'Cache-Control' 	=> 'max-age=0',
				'User-Agent' 		=> rand_ua(),
				//'User-Agent' 		=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.02) Gecko/20100101 Firefox/96.02',
			]
		]);
	}

	function go_scrap($dbx, $list, $niche, $keyIndex)
	{
		if (!$list) {
			die;
		}

		$this->config();

		$this->ovo['dbx'] 	= $dbx;
		$this->ovo['niche'] = $niche;
		$this->ovo['index'] = [];
		$this->tmp['arr'] 	= [];

		$api_speed 		= API_SPEED;
		$api_model 		= API_MODEL;
		$api_key 		= api_config($keyIndex);
		$this->ovo['api_key'] = $api_key;
		$this->ovo['api_model'] = $api_model;
		$oprompt_file 	= oprompt_file();

		$requests 	= function () use ($list, $oprompt_file,  $api_key, $api_model) {

			foreach ($list as $key => $info) {
				$kw 	= $info['keyword'];
				$prompt	= openai_prompt($kw, $oprompt_file);

				$this->ovo['index'][] = $info;

				$max_tokens = ($api_model === 'gpt-3.5-turbo')?2048:MAX_TOKENS;

				$body = [
				    "model" 			=> $api_model,
				    "temperature" 		=> 0.7,
				    "max_tokens" 		=> $max_tokens,
				    "top_p" 			=> 1,
				    "frequency_penalty" => 0,
				    "presence_penalty" 	=> 0,
				    //"stream" 			=> true
				];

				$go_url = "";

				if($api_model === 'gpt-3.5-turbo'){

					$body["messages"] = [
				    	[
				    		"role" => "system",
				    		"content" => $prompt,
				    	]
				    ];

				    $go_url 	= "https://api.openai.com/v1/chat/completions";	
				}
			    else
			    {
			    	$body["prompt"] = $prompt;

			    	$go_url = "https://api.openai.com/v1/completions";
			    }

				$body  	= json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

				$header = [
					'Content-Type' 	=> 'application/json',
					'Authorization' => "Bearer {$api_key}"
				];

				yield new Request("POST", $go_url, $header, $body);
			}
		};

		$pool = new Pool($this->guzzle, $requests(), [
			'concurrency' => $api_speed,
			'fulfilled' => function ($res, $key) {
				$code 		= $res->getStatusCode();
				$response 	= (string)$res->getBody()->getContents();
				$json 		= json_decode($response,true);

				if ($code != 200) {
					openai_error_handle($code, $json, $this->ovo['api_key']);
				}				
				
				$content 	= '';

				if($this->ovo['api_model'] === 'gpt-3.5-turbo')
				{
					$content 	= $json['choices'][0]['message']['content']??'';
				}
			    else
			    {
			    	$content 	= $json['choices'][0]['text']??'';
			    }

				if ($content) {

					$info 	= $this->ovo['index'][$key];
					$this->web_res_filter($info, $content);
				}
			}
		]);

		$pool->promise()->wait();

		$count = count($this->tmp['arr']);

		if ($count > 0) {
			$this->update_batch();
			sleep(1);
		}
	}

	function web_res_filter($info, $content)
	{
		$niche 		= $this->ovo['niche'];
		$kw 	= $info['keyword'];
		$slug 	= $info['slug'];
		$title 	= '';

		if ($content) {

			//$content = htmlspecialchars_decode($content);

			$content 	= str_replace(["&lt;", "&gt;"], ["<", ">"], $content);	

			$title 		= titlebyContent($content);
			
			if($title){
				$content = removeH1tag($content);
			}

			$content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);

			$content = fix_openaiContent($content);

			echo "\r\n[\033[32mOPENAI ARTICLE\033[39m][\033[32m{$niche}\033[39m] ==> {$kw}\r\n";
		} else {
			echo "\r\n[\033[31mOPENAI EMPTY ARTICLE\033[39m][\033[31m{$niche}\033[39m] ==> {$kw}\r\n";
			$content 	= '[]';
		}

		$this->tmp['arr'][] = [
			'slug' 	=> $slug,
			'title' => $title,
			'json_sentences' => $content,
		];

		$count = count($this->tmp['arr']);

		if ($count >= 2) {
			$this->update_batch();
		}
	}
}
