<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . 'third_party/simple_html_dom.php';

class Openai extends CI_Controller
{

	protected $xepo = array();

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);
		//error_reporting(0);
		$this->load->model([
			'scraper/bing_image',
			'scraper/openai_web'
		]);
	}

	function scrap()
	{
		openapi_config();
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

			$this->dbx 	= dbimake($niche);

			$this->do_fixEmpty($niche);
			$this->scrap_image($niche);
			$this->scrap_openai($niche);

			echo "\r\n[\033[32mFINISH\033[39m][\033[32mDB\033[39m] ==> {$niche}";

			//break;
		}

		$this->clear_empty();
		$this->status();
	}

	/*IMAGE SCRAPER*/

	function scrap_image($niche)
	{
		$do		= $this->dbx->query("SELECT keyword FROM tbl_keywords WHERE json_images = ''");
		$count	= $do->num_rows();

		if(!$count){return false;}

		$max	= ceil($count / SCRAPER_PHASE);

		for ($i = 0; $i < $max; $i++) {
			//change ip;
			$this->do_scrap_image($niche);
			sleep(2);
		}
	}

	function do_scrap_image($niche)
	{
		$limit 	= SCRAPER_PHASE;

		$index 	= $limit * $index;

		$arr	= $this->dbx->query("SELECT keyword,slug FROM tbl_keywords WHERE json_images = '' LIMIT {$limit} OFFSET {$index} ")->result_array();

		$this->bing_image->go_scrap($this->dbx, $arr, $niche);
	}

	/*END IMAGE SCRAPER*/

	/*OPENAI SCRAPER*/

	function scrap_openai($niche)
	{
		$count 	= 0;
		$i 		= 1;
		$max 	= MAX_ROTATING;

		do {

			echo "\r\n[\033[32mOPENAI\033[39m] ==> Phase {$i}";

			$count = $this->db_counter();

			$this->go_scrap_openai($niche, $count);

			$i++;

		} while ($count >= 5 && $i <= $max);
	}

	function go_scrap_openai($niche,$count)
	{
		if(!$count){return false;}		

		//$max		= ceil($count / SCRAPER_PHASE);	

		//$sc 		= SCRAPER_TREAD;
		$sc 		= scraper_tread();
		$max 		= ($count < $sc)?$count:$sc;

		$s_limit 	= ceil($count / $max);	

		$doArr 		= [];

		for ($i = 0; $i < $max; $i++) {
			//change ip;
			$doArr[$i] = $this->exec_scrap_openai($niche, $s_limit, $i);
			//sleep(1);
		}

		$count = count($doArr);

		for ($i = 0; $i < $count; $i++) {
			while (!feof($doArr[$i])) {
					echo fread($doArr[$i], 1024);
					flush(); // you have to flush buffer
			}
				
			fclose($doArr[$i]);
		}		
	}

	function db_counter()
	{
		$do			= $this->dbx->query("SELECT keyword FROM tbl_keywords WHERE json_sentences = ''");
		$count		= $do->num_rows();
		return $count;
	}

	function exec_scrap_openai($niche, $s_limit, $index = 0)
	{

		$niche 		= rawurlencode($niche);
		$path 		= str_replace('\\', '/', FCPATH);

		$cli_hide 	= SCRAPER_BACKGROUND;
		$hide 		= ($cli_hide)?'/B':'';

	    if (substr(php_uname(), 0, 7) == "Windows")
	    {
			return popen("start {$hide} cmd /k  \"php \"{$path}index.php\" openai do_scrap_openai {$niche} {$s_limit} {$index} && exit;\"", "r");			
	    }
	    else {
	        exec("cd \"{$path}\" && php index.php openai do_scrap_openai {$niche} {$s_limit} {$index} > /dev/null  && exit;");  
	    }
	}

	function do_scrap_openai($niche, $s_limit, $i = 0)
	{
		$index 	= ceil($s_limit * $i);

		$niche = rawurldecode($niche);

		$this->dbx 	= dbimake($niche);

		$arr	= $this->dbx->query("SELECT keyword,slug FROM tbl_keywords WHERE json_sentences = '' LIMIT {$s_limit} OFFSET {$index} ")->result_array();

		$this->openai_web->go_scrap($this->dbx, $arr, $niche, $i);
	}

	/*END OPENAI SCRAPER*/

	/*ADDITIONAL FUNC*/

	function do_fixEmpty($niche)
	{
		$this->dbx->query('UPDATE tbl_keywords SET "json_images" = "" WHERE "json_images" = "[]" ');
		$this->dbx->query("UPDATE tbl_keywords SET 'json_sentences' = '' WHERE 'json_sentences' = '[]' ");
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
