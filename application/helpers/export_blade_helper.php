<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('exportXML')) {
    function exportXML($info = [])
    {
    	$backdate 	= BACK_DATE;
		$schedule 	= SHEDULE_DATE;

		$data 		= [];

		$arrImage 	= imagesChecker($info['json_images']);

		if($arrImage)
		{
			$data 	= $arrImage;
		}

		$scheck 	= sentencesChecker($info['json_sentences']);

		if(!$scheck){return false;}

		$keyword 	= $info['keyword'];

		$sentences 	= $scheck['sentences'];		

		$t1 		= $info['title']??'';
		$title 		= ($t1)?$t1:title_maker($keyword);
		$title 		= imake_stringcase("ucwords", $title,($keyword));

		$slug 		= slug_imake($keyword);

		$data['title'] 		= $title;
		$data['keyword'] 	= $keyword;
		$data['sentences'] 	= $sentences;	

		$viewBlade 	= $scheck['blade'];

		$content 	= view("export._{$viewBlade}",$data,TRUE);

		$content 	= Minify_Html($content);

        $per_day        = ARTICLE_PER_DAY;

        $date       = "";

        if($per_day)
        {
            $date = $info['publish'];
            /*$date = date_create($date);
            $date = date_format($date,"Y-m-d\TH:i:s\Z");*/
        } 
        else
        {
          $date       = date('Y-m-d\TH:i:s\Z', rand(strtotime($backdate), strtotime($schedule)));  
        }  

		$arr_tags 	= explode('-',$slug);

		$arr_tags 	= array_unique($arr_tags);

		return [
			"title" 	=> $title,
			"slug" 		=> $slug,
			"content" 	=> $content,
			"date" 		=> $date,
			"arr_tags" 	=> $arr_tags
		];

	}
}

if (!function_exists('imagesChecker')) {
    function imagesChecker($json_images = [])
    {
    	$data = json_decode($json_images ,TRUE);

		if(!is_array($data)){return false;}

		$i_count 	= count($data['images']);

		if($i_count < 1){return false;}

		return $data;
    }
}

if (!function_exists('sentencesChecker')) {
    function sentencesChecker($sentences = '')
    {
    	$blade = "post";

    	if(!$sentences OR $sentences == '[]')
    	{
    		return false;
    	}

    	$json = json_decode($sentences,TRUE);

		if(!is_array($json)){

			$blade 		= "openai";

		} else {

			$s_count 	= count($json);
			if($s_count < 1){return false;}
			$sentences 	= $json;
		}

		return [
			'sentences' => $sentences,
			'blade'  	=> $blade
		];
    }
}

if (!function_exists('sentencesToContent')) {
    function sentencesToContent($sentences = '', $keyword = '')
    {
    	$data = [
    		'keyword' => $keyword,
    		'sentences' => $sentences,
    	];

    	return view('export._sentences', $data, TRUE);
    }
}

if (!function_exists('removeH1tag')) {
    function removeH1tag($sentences = '')
    {
    	$sentences = preg_replace('/<h1>(.*?)<\/h1>/','', $sentences);

		return $sentences;
    }
}


if (!function_exists('titlebyContent')) {
    function titlebyContent($content = '')
    {
    	//if(!TITLE_BY_CONTENT){return false;}

    	preg_match('/<h1>(.*?)<\/h1>/s', $content, $title);
    	$title = trim($title[1]??'');

		return $title;
    }
}

if (!function_exists('descbyContent')) {
    function descbyContent($content = '')
    {
    	preg_match('/<p>(.*?)<\/p>/s', $content, $desc);
    	$desc = trim($desc[1]??'');

		return $desc;
    }
}

if (!function_exists('fix_openaiContent')) {
    function fix_openaiContent($content = '')
    {
        if (strpos($content, '===') !== false) {
            $content        = preg_replace('/.*(===|---).*/m', '', $json_sentences);
            $content        = Minify_Html($content);            
        }

        return $content;
    }
}