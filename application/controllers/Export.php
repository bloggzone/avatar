<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Export extends CI_Controller
{

	protected $xepo = array();

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);

		$this->load->model([
			'export/compile_data',
			'export/compile_xml',
			'export/compile_html',
			'export/compile_hugo',
			'export/compile_csv'
		]);

		$this->load->helper('blade');
	}

	function start($type = "data")
	{
		echo "\n\n==> Compile {$type}..\n\n";

		$this->clear_empty();

		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			$niche 	= str_replace([$path, $ext], '', $file);

			make_dir("export/{$type}/{$niche}");
			sleep(1);

			$data = $this->db_info($type, $niche);

			$this->do_compile($data);

			echo "\r\n\r\n[\033[32mFINISH\033[39m] ==> Export {$niche}\r\n\r\n";

			//break;
		}
	}

	function db_info($type, $niche)
	{
		$dbx 		= dbimake($niche);

		$per_day	 	= ARTICLE_PER_DAY;

		if($per_day)
		{
			$this->update_date($dbx, $per_day, $niche);
		}

		//$c_phase 	= db_web_phase();

		$arr		= $dbx->query("SELECT * FROM tbl_keywords WHERE json_images != '[]' AND json_sentences != '[]' AND json_images != '' AND json_sentences != ''");

		$count 		= $arr->num_rows();
		$data 		= $arr->result_array();

		shuffle($data);

		$max_xml 		= XML_PER_NICHE;
		$max_article 	= ARTICLE_PER_XML;
		
		if($max_xml > 0)
		{
			$max_article 	= ceil($count / $max_xml);			
		}

		$sub_data 	= array_chunk($data,$max_article);

		return [
			'type' 	=> $type,
			'niche' => $niche,
			'count' => $count,
			'data' 	=> $sub_data,
		];
	}

	function do_compile($data = [])
	{
		$type = $data['type'];

		if ($type === "blogspot" or $type === "wordpress") {
			$this->compile_xml->start($data);
		} else if ($type === "static_html") {
			sitemap_note();
			$this->compile_html->start($data);
		} else if ($type === "hugo") {
			//hugo_note();
			$this->compile_hugo->start($data);
		} else if ($type === "csv") {
			//hugo_note();
			$this->compile_csv->start($data);
		} else {
			$this->compile_data->start($data);
		}
	}


	function update_date($dbx, $perDay, $niche)
	{
		$do			= $dbx->query("SELECT slug FROM tbl_keywords");
		$list		= $do->result_array();

		shuffle($list);

		$data = [];

		echo "[\033[32mSTART\033[39m]==> Change Publish date Max {$perDay} / day\r\n";
	

		$arr 	= array_chunk($list,$perDay);

		$count 	= 1;

		foreach ($arr as $key => $sub_data)
		{
			$backdate = $count;
			$schedule = $count+1;

			$backTxt 	= ($backdate > 1)?"days":"day";
			$scheTxt 	= ($schedule > 1)?"days":"day";

			$backTime 	= strtotime("+{$backdate} {$backTxt}");

			$beda 		= ceil(86400/$perDay);

			foreach ($sub_data as $i => $info)
			{
				$slug 		= $info['slug'];
				
				$publish 	= date('Y-m-d\TH:i:s\Z', rand($backTime, $backTime + $beda));

				$backTime	= $backTime + $beda;

				echo "[\033[32mDATE\033[39m][\033[32m{$publish}\033[39m]==> {$slug}\r\n";

				$data[] = [
					'slug' 	 	=> $slug,
					'publish' 	=> $publish,
				];
			}

			$count++;
		}

		echo "[\033[32mDB\033[39m][\033[32m{$niche}\033[39m]==> Mix Publish date\r\n";

		$dbx->update_batch('tbl_keywords', $data, 'slug');

	}
	
	function clear_empty()
	{
		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			$niche 	= str_replace([$path, $ext], '', $file);

			$this->do_clear_empty($niche);

			echo "\r\n[\033[32mDB\033[39m] ==> Checking {$niche} Database";

			//break;
		}

		echo "\r\n\r\n";
	}

	function do_clear_empty($niche)
	{
		$dbx 	= dbimake($niche);
		$c_phase 	= db_web_phase();
		$dbx->query('UPDATE tbl_keywords SET "json_images" = "[]" WHERE "json_images" = "" ');
		//$c_phase 	= db_web_phase();
		$dbx->query("UPDATE tbl_keywords SET 'json_sentences' = '[]' WHERE 'json_sentences' = '' ");
	}

	function delete_export()
	{
		delete_export();
	}
}
