<?php defined('BASEPATH') or exit('No direct script access allowed');

class Fix_publish extends CI_model
{
	function fix($force = FALSE)
	{
		echo "\r\n\r\n[\033[32mDB\033[39m] ==> Fix Publish date\r\n\r\n";

		$this->force = $force;

		$path 	= "gudang/db/";
		$ext 	= ".sqlite";
		$arr 	= glob("{$path}*{$ext}");

		$arr_report = [];

		foreach ($arr as $key => $file) {
			$niche 	= str_replace([$path, $ext], '', $file);

			$this->do_fix($niche);
			//break;
		}
	}

	function do_fix($niche)
	{
		$dbx 		= dbimake($niche);

		$backdate 	= strtotime(BACK_DATE);
		$shedule 	= strtotime(SHEDULE_DATE);

		$where 		= ($this->force) ? "" : "WHERE publish = ''";

		$do			= $dbx->query("SELECT slug FROM tbl_keywords {$where}");

		$list		= $do->result_array();

		$arr 		= array_chunk($list, 100);

		$backdate 	= BACK_DATE;
		$schedule 	= SHEDULE_DATE;

		foreach ($arr as $key => $sub_arr) {
			$data = array_map(
				function ($info) use ($backdate, $schedule) {

					$slug 		= $info['slug'];
					$publish 	= date('Y-m-d H:i:s', rand(strtotime($backdate), strtotime($schedule)));

					return [
						'slug' 			=> $slug,
						'publish' 		=> $publish,
					];
				},
				array_values($sub_arr)
			);

			$i = $key + 1;

			echo "[\033[32mDB\033[39m][\033[32m{$niche}\033[39m]==> Update Phase {$i}\r\n";

			$dbx->update_batch('tbl_keywords', $data, 'slug');
		}
	}

	function download_css()
	{
		make_dir("assets/css");
		sleep(1);

		$css = @file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/css/bootstrap-grid.min.css");

		file_put_contents("assets/css/bootstrap-grid.min.css", $css);
	}
}
