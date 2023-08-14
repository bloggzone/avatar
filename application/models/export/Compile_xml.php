<?php defined('BASEPATH') or exit('No direct script access allowed');

class Compile_xml extends CI_model
{
	function start($arr = [])
	{
		$type 	= $arr['type'];
		$niche 	= $arr['niche'];
		//$count 	= $arr['count'];

		foreach ($arr['data'] as $i => $sub_data) {
			$arr = [
				'niche' 	=> $niche,
				'sub_data' 	=> $sub_data,
			];

			$blog 	= view("export.{$type}", $arr, TRUE);

			$fn 	= "{$type}_{$niche}_{$i}.xml";

			file_put_contents("export/{$type}/{$niche}/{$fn}", $blog);

			echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==>  {$fn}\r\n";
		}
	}
}
