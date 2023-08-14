<?php defined('BASEPATH') or exit('No direct script access allowed');

class Frontpage_model extends CI_model
{
	function homepage_compile($data)
	{
		$data['sub_data'] 		= array_chunk($data['data'], 4);
		$data['random_related'] = random_related($data['niche'], 10, 'keyword, slug');

		//json($data);die;

		view('{theme}.home', $data, FALSE);
	}

	function search_compile($data)
	{
		$data['sub_data'] 		= array_chunk($data['data'], 4);
		$data['random_related'] = random_related($data['niche'], 10, 'keyword, slug');

		view('{theme}.search', $data, FALSE);
	}

	function post_compile($data)
	{
		$niche 			= $data['niche'];
		$info 			= $data['info'];

		$keyword 		= $info['keyword'];

		$json_images 	= json_decode($info['json_images'], TRUE);
		$arr_images 	= $json_images['images'];

		$arr_sentences 	= json_decode($info['json_sentences'], TRUE);

		$publish 		= $info['publish'];

		$random_related = random_related($niche, 10, 'keyword, slug');

		$title 			= title_maker(imake_stringcase("ucwords", $keyword));

		$final_data = [
			'niche' 			=> $niche,
			'title' 			=> $title,
			'keyword' 			=> $keyword,
			'images' 			=> $arr_images,
			'sentences' 		=> $arr_sentences,
			'publishDate' 		=> $publish,
			'random_related' 	=> $random_related
		];

		view('{theme}.post', $final_data, FALSE);
	}
}
