<?php defined('BASEPATH') or exit('No direct script access allowed');

class Compile_html extends CI_model
{
	protected $ovo = array();

	function start($arr = [])
	{
		$type 	= $arr['type'];
		$niche 	= $arr['niche'];
		//$count 	= $arr['count'];

		$this->ovo['post_ext'] = POST_EXTENSION;

		foreach ($arr['data'] as $i => $sub_data) {
			$dir = "export/{$type}/{$niche}/html_{$i}";

			//Create directory
			make_dir($dir);
			make_dir("{$dir}/p");
			sleep(1);

			$random_related = $this->imake_related($sub_data);

			$this->homepage_compile($niche, $sub_data, $random_related, $dir);

			$this->post_compile($niche, $sub_data, $random_related, $dir);

			$this->page_compile($niche, $random_related, $dir);

			$this->sitemap_compile($niche, $random_related, $dir);
		}
	}

	function imake_related($sub_data)
	{
		$ra = array_column($sub_data, 'keyword', 'slug');

		$random_related = array_map(
			function ($slug, $kw) {

				return [
					'keyword' 		=> $kw,
					'slug' 			=> $slug
				];
			},
			array_keys($ra),
			array_values($ra)
		);

		return $random_related;
	}

	function homepage_compile($niche, $sub_data, $random_related, $dir)
	{
		$ext = $this->ovo['post_ext'];

		echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==>  index{$ext}\r\n";

		shuffle($sub_data);
		array_splice($sub_data, 16); //limit 16

		$this->img_downloader($dir, $sub_data);

		$sub_data 	= array_chunk($sub_data, 4);

		$data = [
			'niche' 			=> $niche,
			'sub_data' 			=> $sub_data,
			'random_related' 	=> $random_related
		];

		//json($data);

		file_put_contents("{$dir}/index{$ext}", view('{theme}.home', $data, TRUE));
	}

	function img_downloader($dir, $sub_data)
	{
		if (IMAGE_DOWNLOAD) {
			echo "\r\n[\033[32mPLEASE WAIT\033[39m] ==> Initialize to download images..\r\n";

			$this->load->model('scraper/bing_download');
			make_dir("{$dir}/assets/img");
			sleep(1);
			$arr 	= array_column($sub_data, 'keyword');
			$this->bing_download->go_scrap($dir, $arr);
		}
	}

	function sitemap_compile($niche, $random_related, $dir)
	{
		//$date 		= date('c',time());

		$ext = $this->ovo['post_ext'];

		$dt = '';
		$dt .= '<?xml version="1.0" encoding="utf-8"?>';
		$dt .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

		foreach ($random_related as $i => $info) {
			$slug   	= $info['slug'];
			$publish 	= $this->ovo['publishDate'][$slug];
			$date 		= str_replace('+00:00', 'Z', gmdate('c', strtotime($publish)));
			//$slug 	= slug_imake($keyword,TRUE);
			$dt    .= "<url><loc>http://domain.com/{$slug}{$ext}</loc><lastmod>{$date}</lastmod></url>" . PHP_EOL;
		}

		$dt .= '</urlset>';

		echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==>  sitemap.xml\r\n";

		file_put_contents("{$dir}/sitemap.xml", $dt);
	}

	function post_compile($niche, $sub_data, $random_related, $dir)
	{
		$ext = $this->ovo['post_ext'];

		$backdate 	= BACK_DATE;

		foreach ($sub_data as $i => $info) {

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

			$publishDate 	= date('Y-m-d\TH:i:s\Z', rand(strtotime(str_replace('+', '-', $backdate)), strtotime("-1 day")));

			$this->ovo['publishDate'][$render['slug']] = $publishDate;

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

			$res = view('{theme}.post_openai', $data, TRUE);

			if (MINIFY_HTML) {
				$res = Minify_Html($res);
			}

			file_put_contents("{$dir}/{$slug}{$ext}", $res);

			echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==>  {$slug}{$ext}\r\n";
		}
	}

	function page_compile($niche, $random_related, $dir)
	{
		foreach (pages() as $page_name) {
			$data = [
				'niche' 			=> $niche,
				'page' 				=> $page_name,
				'random_related' 	=> $random_related
			];

			$res = view('pages.page', $data, TRUE);

			if (MINIFY_HTML) {
				$res = Minify_Html($res);
			}

			file_put_contents("{$dir}/p/{$page_name}.html", $res);

			echo "\r\n[\033[32msuccess\033[39m] ==> {$page_name}.html\r\n";
		}
	}
}
