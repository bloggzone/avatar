<?php defined('BASEPATH') or exit('No direct script access allowed');

class Compile_csv extends CI_model
{
	function start($arr = [])
	{
		//$type 	= $arr['type'];
		$niche 	= $arr['niche'];
		//$count 	= $arr['count'];

		foreach ($arr['data'] as $i => $sub_data)
		{
			$content 	= $this->preContent($sub_data,$niche);

			$fn = "csv_{$niche}_{$i}.csv";

			$this->csv_compiler($content, $niche, $fn);

			echo "\r\n[\033[32mEXPORT\033[39m][\033[32m{$niche}\033[39m] ==> {$fn}\r\n";
			
		}
	}

	function csv_compiler($content, $niche, $fn)
	{
		$header = [["niche", "keyword", "slug", "title", "tag", "image", "content", "publish"]];

		if(CSV_LITE)
		{
			$header = [["keyword", "content", "tag"]];
		}	

		$CsvContent = array_merge($header,$content);

		//json($CsvContent);

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

		$sheet = $spreadsheet->getActiveSheet();

		$sheet->fromArray($CsvContent, NULL, 'A1');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->setDelimiter(CSV_DELIMITER);
		$writer->setEnclosure('"');
		$writer->setLineEnding("\r\n");
		$writer->setSheetIndex(0);

		$writer->save("export/csv/{$niche}/{$fn}");

		

	}


	function preContent($sub_data,$niche)
	{
		$final = [];

		$backdate = BACK_DATE;
		$schedule = SHEDULE_DATE;
		$csv_lite = CSV_LITE;

		foreach($sub_data as $key => $info)
		{

			$render 	= exportXML($info);

			if(!$render){continue;}

			$keyword 		= $info['keyword'];
			$title 			= $render['title'];
			$slug 			= $render['slug'];
			$content 		= $render['content'];
			$date 			= $render['date'];
			$arr_tags 		= $render['arr_tags'];

			$tagTxt 		= implode(", ", $arr_tags);

			$publish 		= date('Y-m-d\TH:i:s\Z', rand(strtotime($backdate), strtotime($schedule)));

			if($csv_lite)
			{
				$final[] = [
					$keyword,
					$content,			
					$tagTxt
				];
			}
			else
			{
				$final[] = [
					$niche,
					$keyword,
					$slug,				
					$title,				
					$tagTxt,
					blade_image($keyword,TRUE),
					$content,
					$publish
				];
			}			

		}

		return $final;

	}



}
