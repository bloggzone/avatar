<?php defined('BASEPATH') or exit('No direct script access allowed');

class Compile_hugo extends CI_model
{
	protected $ovo = array();

	function start($arr = [])
	{
		$type 	= $arr['type'];
		$niche 	= $arr['niche'];
		//$count 	= $arr['count'];

		$this->ovo['post_ext'] = POST_EXTENSION;

		foreach ($arr['data'] as $i => $sub_data) {
			$dir = "export/{$type}/{$niche}/hugo_{$i}";

			//Create directory
			make_dir($dir);
			sleep(1);

			/*$this->exec_hugo($niche, $i);
			sleep(1);*/

			$this->post_compile($niche, $sub_data, $dir);
		}
	}

	function post_compile($niche, $sub_data, $dir)
	{
		$backdate 	= BACK_DATE;

		foreach ($sub_data as $i => $info) {

			$render 	= exportXML($info);

			if(!$render){continue;}

			$keyword 		= $info['keyword'];
			$title 			= $render['title'];
			$slug 			= $render['slug'];
			$content 		= $render['content'];
			$date 			= $render['date'];
			$arr_tags 		= $render['arr_tags'];

			$tagTxt 		= implode(", ", $arr_tags);

			$publishDate 	= date('Y-m-d', rand(strtotime(str_replace('+', '-', $backdate)), strtotime("-1 day")));

			$image 			= blade_image($keyword,TRUE);

			$data = [
				'niche' 			=> $niche,
				'title' 			=> $title,
				'descTxt' 			=> descbyContent($content),
				'publishDate' 		=> $publishDate,
				'image' 			=> $image
			];

			$res = view('export.hugo', $data, TRUE);
			$res .= $content;

			file_put_contents("{$dir}/{$slug}.html", $res);

			echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==>  {$slug}.html\r\n";
		}
	}


	function exec_hugo($niche, $i = '')
	{

		echo "\r\n[\033[32mHUGO\033[39m][\033[32mCREATE\033[39m] ==> {$niche} {$i}\r\n";

		$path = str_replace('\\', '/', FCPATH);

		if (($fp = popen("cd \"{$path}/export/hugo\" & hugo new site \"{$niche}\" --force", "r"))) {
			while (!feof($fp)) {
				echo fread($fp, 1024);
				flush(); // you have to flush buffer
			}
			fclose($fp);
		}

		sleep(1);
	}
}
