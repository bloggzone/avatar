<?xml version='1.0' encoding='UTF-8'?>
<ns0:feed xmlns:ns0="http://www.w3.org/2005/Atom">
<ns0:title type="html">Shuriken MOD</ns0:title>
<ns0:generator>Blogger</ns0:generator>
<ns0:link href="http://a/b" rel="self" type="application/atom+xml" />
<ns0:link href="http://a/b" rel="alternate" type="text/html" />
<ns0:updated><?= date("Y-m-d\TH:i:s\Z") ?></ns0:updated>
<?php
foreach ($sublist as $key => $kw)
{
	$slug 		= url_title($kw,'-');
	
	$data 		= get_data($slug,$niche);

	$images 	= $data['images']??null;

	if(count($images) < 2){ continue; }

	$keyword 		 	= $data['images'][0]['keyword'];	
	$title 		= title_maker($keyword);

	$data['title'] 		= $title;
	$data['keyword'] 	= $keyword;

	$content 	= $this->load->view('export/post', $data, TRUE);
	$content 	= Minify_Html($content);
	$content 	= htmlspecialchars($content);

	$date 		= date('Y-m-d\TH:i:s\Z', rand(strtotime('-6 month'), strtotime('+2 month')));

	$arr_tags 	= explode('-',url_title($keyword, '-', TRUE));
	$arr_tags[] = $niche;
	$arr_tags 	= array_unique($arr_tags);

?>
	<ns0:entry>
		<?php foreach ($arr_tags as $tag)
		{
			if(strlen($tag) <= 3){continue;}
		?>
		<ns0:category scheme="http://www.blogger.com/atom/ns#" term="<?= $tag ?>" />
		<?php } ?>
		<ns0:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/blogger/2008/kind#post" />
		<ns0:id>post-<?= $key ?></ns0:id>
		<ns0:author>
		<ns0:name>admin</ns0:name>
		</ns0:author>
		<ns0:content type="html"><?= $content ?></ns0:content>
		<ns0:published><?= $date ?></ns0:published>
		<ns0:title type="html"><?= $title ?></ns0:title>
		<ns0:link href="http://localhost/wpan/<?= $key ?>/" rel="self" type="application/atom+xml" />
		<ns0:link href="http://localhost/wpan/<?= $key ?>/" rel="alternate" type="text/html" />
	</ns0:entry>
<?php } ?>
</ns0:feed>