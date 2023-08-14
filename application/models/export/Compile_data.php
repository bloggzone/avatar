<?php defined('BASEPATH') or exit('No direct script access allowed');

class Compile_data extends CI_model
{
	protected $ovo = array();

	public function __construct()
	{
		parent::__construct();
	}

	function start($arr = [])
	{
		$type 	= $arr['type'];
		$niche 	= $arr['niche'];
		//$count 	= $arr['count'];

		foreach ($arr['data'] as $key => $sub_data) {
			$this->do_compile($sub_data, $type, $niche);
		}
	}

	function do_compile($sub_data, $type, $niche)
	{
		foreach ($sub_data as $key => $info) {
			$final 			= array();

			$keyword 		= $info['keyword'];
			$final 			= json_decode($info['json_images'], TRUE);

			if (!is_array($final)) {
				continue;
			}

			$images_count 	= count($final['images']);

			if ($images_count < 1) {
				continue;
			}

			$sentences 		= json_decode($info['json_sentences'], TRUE);

			if (!is_array($sentences)) {
				continue;
			}

			$sentences_count = count($sentences);

			if ($sentences_count < 1) {
				continue;
			}

			$final['sentences'] = $sentences;

			$slug 			= slug_imake($keyword);

			file_put_contents("export/{$type}/{$niche}/{$slug}.srz.php", serialize($final));

			echo "\r\n[\033[32mCOMPILE\033[39m][\033[32m{$niche}\033[39m] ==>  {$slug}\r\n";
		}
	}
}
