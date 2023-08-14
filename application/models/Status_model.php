<?php defined('BASEPATH') or exit('No direct script access allowed');


class Status_model extends CI_model
{
	function status()
	{
		echo "\r\n\r\n[\033[32mSTATUS\033[39m] ==> Scraping status";

		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		$arr_report = [];

		foreach ($arr as $key => $file) {
			$niche 	= str_replace([$path, $ext], '', $file);

			$arr_report[] = $this->db_status($niche);

			//break;
		}

		$this->db_report($arr_report);
	}

	function db_status($niche)
	{
		$dbx 	= dbimake($niche);

		$total			= $dbx->query("SELECT * FROM tbl_keywords")->num_rows();
		$full			= $dbx->query("SELECT * FROM tbl_keywords WHERE json_images != '[]' AND json_sentences != '[]' AND json_sentences != ''")->num_rows();
		$empty_image 	= $dbx->query("SELECT * FROM tbl_keywords WHERE json_images = '[]' OR json_images = ''")->num_rows();
		$empty_article 	= $dbx->query("SELECT * FROM tbl_keywords WHERE json_sentences = '[]' OR json_sentences = ''")->num_rows();
		$all_empty 		= $dbx->query("SELECT * FROM tbl_keywords WHERE json_images = '[]' AND json_sentences = '[]'")->num_rows();

		$arr = [
			"niche" 		=> $niche,
			"total" 		=> $total,
			"full" 			=> $full,
			"empty_image" 	=> $empty_image,
			"empty_article" => $empty_article,
			"all_empty" 	=> $all_empty
		];

		return $arr;
	}

	function db_report($arr = [])
	{
		$txt = "\r\n\r\n";

		foreach ($arr as $key => $val) {
			$niche 			= $val['niche'];
			$total 			= $val['total'];
			$full 			= $val['full'];
			$empty_image 	= $val['empty_image'];
			$empty_article 	= $val['empty_article'];
			$all_empty 		= $val['all_empty'];

			$txt .= "_________\r\n\r\n";

			$txt .= "Report for \"{$niche}\" Niche with {$total} total Keywords :\r\n" . PHP_EOL;
			$txt .= "=> {$full} Keywords with Full data" . PHP_EOL;
			$txt .= "=> {$empty_image} keywords Empty Image" . PHP_EOL;
			$txt .= "=> {$empty_article} keywords Empty Article" . PHP_EOL;
			$txt .= "=> {$all_empty} keywords with All Empty data" . PHP_EOL;

			$txt .= "\r\n\r\n";
		}

		echo $txt;

		$file = "gudang/logs/scraping_log.txt";

		echo "File report : {$file} \r\n\r\n";

		file_put_contents($file, $txt);
	}
}
