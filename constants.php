<?php 

/* ==> IMAKE CONFIGURATION CENTER <== */


/*	SCRAPER CONFIG
| -------------------------------------------------------------------
| OVERCLOCK_LEVEL 	= Integer ( 1 - 20 )
| BADWORD_FILTER 	= Boolean TRUE / FALSE
| IMAGE_SOURCE		= String ( bing / google )
| IMAGE_SOURCE		= String ( bing / openapi )
| LANG_CODE			= String ( gudang/lang_CODE.txt )
| IS_UTF8			= String TRUE / FALSE
| CSE_FILTER		= String ( site:pinterest.com/pin/ )
| SCRAPING_MODE		= String ( RANDOM, IMAGE_ONLY, or IMAGE_ARTICLE )
| MAX_IMAGE_RESULT	= Integer ( 1 - 35 )
| MAX_ARTICLE_LEVEL	= Integer ( 20 - 30 )
| GOOGLE_SUGGEST 	= Boolean TRUE / FALSE
| PROXY_MODE 		= Boolean TRUE / FALSE
| CEK_PROXY 		= Boolean TRUE / FALSE
| SCRAPER_PHASE 	= Integer ( 1500 - 10000 ) keywords
| SCRAPER_VERSION 	= Integer
| -------------------------------------------------------------------
*/

define("OVERCLOCK_LEVEL",	20); //✔

define("BADWORD_FILTER",	FALSE); //✔

define("IMAGE_SOURCE",		"bing"); //✔

define('LANG_CODE',			'en-us');//✔

define('IS_UTF8',			FALSE);//✔

define("CSE_FILTER",		""); //✔

define("SCRAPING_MODE",		"IMAGES_ONLY"); //✔

define("MAX_IMAGE_RESULT",	3); //✔

define("MAX_ARTICLE_LEVEL",	30); //✔

//define("GOOGLE_SUGGEST",	FALSE);

define("PROXY_MODE",		FALSE); //✔

define("CEK_PROXY",			TRUE); //✔

define("SCRAPER_PHASE",		5000); //✔

define("SCRAPER_VERSION",	97); //✔


/*	EXPORT CONFIG
| -------------------------------------------------------------------
| THEME_NAME 		= String ( hybrid, native1)
| SITE_NAME 		= String
| SITE_DESCRIPTION 	= String
| SITE_AUTHOR 		= String
|
| TITLE_PREFIX 		= String (Prefix pada title)
| TITLE_PREFIX 		= String (Suffix pada title)
| XML_PER_NICHE 	= Integer (skip article per xml if value greater than 0)
| XML_PER_NICHE 	= Integer (skip article per xml if value greater than 0)
| ARTICLE_PER_XML 	= Integer (0 = OFF)
|
|
| ADS_LINK 			= String
| IS_UADS			= Boolean TRUE / FALSE (U-Ads Client)
| RSS_MAX			= Integer ( 25 - 100 )
| -------------------------------------------------------------------
*/

define("THEME_NAME",		"native1");

define("SITE_NAME",			"{niche} Tips and References");

define("SITE_DESCRIPTION",	"Best {niche} Tips and References website . Search anything about {niche} Ideas in this website.");

define("SITE_AUTHOR",		"Azhie");

$date = date('Y');
$rand = rand(10,30);

//Contoh Penggunaan date or rand
// {$date}
// +{$rand}

define("TITLE_PREFIX",		"");
define("TITLE_SUFFIX",		"");


define("ARTICLE_PER_DAY",	0);// 0 = OFF
define("XML_PER_NICHE",		0);// 0 = OFF
define("ARTICLE_PER_XML",	100);
define("BACK_DATE",			"-100 day");
define("SHEDULE_DATE",		"+10 month");
define("WP_CATEGORY",		"wallpaper");
define("WP_DRAFT",			FALSE);//TRUE or FALSE

define("CSV_LITE",			FALSE);
define("CSV_DELIMITER",		',');

define("MINIFY_HTML",		FALSE);

define("IMAGE_DOWNLOAD",	FALSE);

define("ADS_LINK",			"#EDIT-WITH-YOUR-ADS");

define("IS_UADS",			FALSE);

define("CDN_IMAGE",			TRUE);

define("RSS_MAX",			100);//1 - 100

/*	NATIVE CONFIG
| -------------------------------------------------------------------
| DEFAULT_NICHE		= String
| POST_EXTENSION	= String ( .html / .xhtml / etc)
| -------------------------------------------------------------------
*/

define("DEFAULT_NICHE",	"sport");

define("POST_EXTENSION", ".html");

/*	OPENAI CONFIG
| -------------------------------------------------------------------
| O_APIKEY		= String
| O_PROMPT	= String
| -------------------------------------------------------------------
*/

define("CONTENT_SOURCE",		"bing"); //bing / openai

define("O_LANGUAGE", 			"english");
define("O_TYPE", 				"news, tips, review, or tutorial");
define("O_PROMPT_FILE", 		"1k");// \gudang\prompt\default.txt
define("O_MIN_PARAGRAPHS", 		"10");
//define("TITLE_BY_CONTENT",		TRUE);

define("SCRAPER_BACKGROUND",	FALSE);//TRUE or FALSE
//define("SCRAPER_TREAD", 		2);
define("API_SPEED", 			2);
define("MAX_ROTATING", 			3);

//OPENAI API REQUEST

define("API_MODEL",				"gpt-3.5-turbo");
define("MAX_TOKENS",			3000);



//EXPORT CONFIG
