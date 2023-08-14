<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller
{

	protected $xepo = array();

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);
		//error_reporting(0);
	}

	function proxy()
	{
		cek_proxy();
	}

	function createdb($niche = "home")
	{
		CreateNichedb($niche);
	}

	function deletedb()
	{
		deleteFile("gudang/db/*.sqlite");
	}

	function start()
	{
		echo "\n\n==> Prepairing..\n\n";

		$path 	= "keywords/";
		$ext 	= ".txt";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			echo "\r\n==> {$file}..\r\n";

			$niche 	= str_replace([$path, $ext], '', $file);
			$this->insert_keywords($niche);
			unlink($file);
		}
	}

	function add_spintax()
	{
		echo "\n\n==> Prepairing..\n\n";

		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		foreach ($arr as $key => $file) {
			echo "\r\n==> {$file}..\r\n";

			$niche 	= str_replace([$path, $ext], '', $file);
			dbAddSpintax($niche);
		}
	}

	function insert_keywords($niche = "home")
	{
		$list 	= @file("keywords/{$niche}.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if (!$list) {
			die;
		}

		if (!file_exists(FCPATH . "gudang/db/{$niche}.sqlite")) {
			CreateNichedb($niche);
		}

		$do 	= dbimake($niche);

		//bardwords filter
		$list 	= badword_filter($list);
		$arr 	= array_chunk($list, 2000);

		$backdate 	= BACK_DATE;
		$schedule 	= SHEDULE_DATE;

		foreach ($arr as $key => $sub_arr) {
			$data = array_map(
				function ($value) use ($backdate, $schedule) {

					$publish = date('Y-m-d H:i:s', rand(strtotime($backdate), strtotime($schedule)));

					return [
						'keyword' 		=> trim($value),
						'slug' 			=> slug_imake($value),
						'publish' 		=> $publish,
					];
				},
				array_values($sub_arr)
			);

			$i = $key + 1;

			echo "==> INSERT Phase {$i}\r\n";

			$do->insert_batch('tbl_keywords', $data);
		}
	}
}
