<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] 	= 'frontpage';
$route['404_override'] 			= 'frontpage/err404';
$route['translate_uri_dashes'] 	= FALSE;

$ext = str_replace('.', '\.', POST_EXTENSION);

$route['search'] 				= 'frontpage/post_search';
$route['sitemap\.xml'] 			= 'sitemap/main_sitemap';
$route['submap/(:any)\.xml'] 	= 'sitemap/sub_sitemap/$1';
$route['rss/(:any)\.rss'] 		= 'sitemap/rss_map/$1';
$route['p/(:any)'.$ext] 		= 'frontpage/page_imake/$1';
$route['(:any)/(:any)'.$ext] 	= 'frontpage/post_imake/$1/$2';

if(PHP_SAPI !== 'cli')
{
	//Handle Akses langsung ke Controller
	$route['(.*)'] 				= 'frontpage/err404';
}
