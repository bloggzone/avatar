<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'third_party/simple_html_dom.php';

class Googlebase extends CI_Controller
{

	protected $xepo = array();

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);
		//error_reporting(0);
		$this->load->model([
			'scraper/googlebase_image',
			'scraper/bing_image',
			'scraper/bing_web'
		]);
	}

	function scrape($type = 'google')
	{
		echo "\r\n[\033[32mDB\033[39m] ==> Analize..\r\n";

		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			if (PROXY_MODE) {
				cek_proxy();
			}

			$niche 	= str_replace([$path, $ext], '', $file);

			echo "\r\n[\033[32mSTART\033[39m][\033[32mDB\033[39m] ==> {$niche}";

			$this->do_fixEmpty($niche);
			$this->pre_scrape($niche);

			echo "\r\n[\033[32mFINISH\033[39m][\033[32mDB\033[39m] ==> {$niche}";

			//break;
		}

		$this->clear_empty();
		$this->status();
	}

	function pre_scrape($niche)
	{
		$dbx 	= dbimake($niche);

		//$c_phase= db_web_phase();

		$do		= $dbx->query("SELECT keyword FROM tbl_keywords WHERE json_images = '' OR json_sentences = ''");
		$count	= $do->num_rows();

		$max	= ceil($count / SCRAPER_PHASE);

		for ($i = 1; $i <= $max; $i++) {
			//change ip;
			$this->exec_scrap($niche, 'json_images', $i);
			$this->exec_scrap($niche, 'json_sentences', $i);
			sleep(2);
		}
	}

	function exec_scrap($niche, $phase, $i = '')
	{
		$ptx = strtoupper(str_replace("_", " ", $phase));

		echo "\r\n[\033[32m{$ptx}\033[39m] ==> {$niche} {$i} keywords\r\n";

		$path = str_replace('\\', '/', FCPATH);

		if (($fp = popen("cd \"{$path}\" & php index.php googlebase do_exec_scrap \"{$niche}\" \"{$phase}\"", "r"))) {
			while (!feof($fp)) {
				echo fread($fp, 1024);
				flush(); // you have to flush buffer
			}
			fclose($fp);
		}

		sleep(1);
	}

	function do_exec_scrap($niche, $phase)
	{
		$dbx 	= dbimake($niche);

		$limit 	= SCRAPER_PHASE;

		$arr	= $dbx->query("SELECT keyword,slug FROM tbl_keywords WHERE {$phase} = '' LIMIT {$limit}")->result_array();

		//$sub 	= array_column($arr,"keyword");

		if ($phase === "json_images") {

			$i_source = IMAGE_SOURCE;

			if ($i_source === "google") {
				$this->googlebase_image->go_scrap($dbx, $arr, $niche);
			} else {
				//bing image
				$this->bing_image->go_scrap($dbx, $arr, $niche);
			}
		} else if ($phase === "json_sentences") {

			$c_source = CONTENT_SOURCE;			

			$this->bing_web->go_scrap($dbx, $arr, $niche);
		}
	}

	function do_fixEmpty($niche)
	{
		$dbx 		= dbimake($niche);
		$dbx->query('UPDATE tbl_keywords SET "json_images" = "" WHERE "json_images" = "[]" ');
		$dbx->query("UPDATE tbl_keywords SET 'json_sentences' = '' WHERE 'json_sentences' = '[]' ");
	}

	function clear_empty()
	{
		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			$niche 	= str_replace([$path, $ext], '', $file);

			$this->do_clear_empty($niche);

			echo "\r\n[\033[32mDB\033[39m] ==> Fix {$niche} Result";

			//break;
		}
	}

	function do_clear_empty($niche)
	{
		$dbx 		= dbimake($niche);
		$dbx->query('UPDATE tbl_keywords SET "json_images" = "[]" WHERE "json_images" = "" ');
		$dbx->query("UPDATE tbl_keywords SET 'json_sentences' = '[]' WHERE 'json_sentences' = '' ");
	}

	function status()
	{
		$this->load->model('status_model');
		$this->status_model->status();
	}

	function delete_data()
	{
		echo "\n\n==> Clear data..\n\n";
		delete_data();
	}
}
