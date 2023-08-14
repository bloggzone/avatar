<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Frontpage extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit", -1);

		$this->load->helper('blade');
		$this->load->model('frontpage/frontpage_model');
	}

	function index()
	{
		$niche 	= default_niche();

		$list 	= last_post($niche, 20);

		$data 	= [
			'niche' => $niche,
			'data' 	=> $list,
		];

		$this->frontpage_model->homepage_compile($data);
	}

	function post_imake($niche = '', $slug = '')
	{
		$niche 	= rawurldecode($niche);
		$slug 	= rawurldecode($slug);

		$niche 	= str_replace('-', ' ', $niche);

		$info 	= post_details($niche, $slug);

		$render = exportXML($info);

		if(!$render){die;}

		$keyword 		= $info['keyword'];
		$title 			= $render['title'];
		$slug 			= $render['slug'];
		$content 		= $render['content'];
		$date 			= $render['date'];
		$arr_tags 		= $render['arr_tags'];
		$image 			= blade_image($keyword,TRUE);

		$tagTxt 		= implode(", ", $arr_tags);

		$random_related = random_related($niche,10,'keyword, slug');

		$data = [
			'niche' 			=> $niche,
			'title' 			=> $title,
			'keyword' 			=> $keyword,
			'slug' 				=> $slug,
			'content' 			=> $content,
			'image' 			=> $image,
			'tagTxt' 			=> $tagTxt,
			'publishDate' 		=> $date,
			'random_related' 	=> $random_related
		];

		view('{theme}.post_openai', $data, FALSE);
	}

	function post_search()
	{
		$niche 	= $this->input->get('n', true);
		$query 	= $this->input->get('q', true);

		$niche 	= ($niche) ? $niche : default_niche();

		$list 	= last_post($niche, 20, $query);

		$data 	= [
			'query' => $query,
			'niche' => $niche,
			'data' 	=> $list,
		];

		//json($data);

		$this->frontpage_model->homepage_compile($data);
	}

	function page_imake($page_name)
	{
		if (!in_array($page_name, pages())) {
			err404();
		}

		$niche 	= default_niche();

		$random_related = random_related($niche, 10, 'keyword, slug');

		$data = [
			'niche' 			=> $niche,
			'page' 				=> $page_name,
			'random_related' 	=> $random_related
		];

		view('pages.page', $data, FALSE);
	}

	function activate($force = FALSE)
	{
		$this->load->model(['scraper/bing_download', 'frontpage/fix_publish']);

		$this->fix_publish->fix($force);
		//$this->fix_publish->download_css();

		if (IMAGE_DOWNLOAD) {
			echo "\r\n[\033[32mPLEASE WAIT\033[39m] ==> Initialize to download images..\r\n";

			$niche 	= default_niche();

			$data 	= db_info($niche, 20, 'keyword');
			$arr 	= array_column($data, 'keyword');
			make_dir("assets/img");
			sleep(1);
			$this->bing_download->go_scrap("", $arr);
		}
	}

	function err404()
	{
		err404();
	}
}
