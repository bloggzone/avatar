<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


if (!function_exists('dbimake')) {
    function dbimake($niche = '')
    {
        if (!file_exists(FCPATH . "gudang/db/{$niche}.sqlite")) {

            if (PHP_SAPI !== 'cli') {
                redirect('/');
            } else {
                echo "\r\n[\033[31mERROR\033[39m]==> NO {$niche} database\r\n";
            }

            die;
        }

        $CI = get_instance();

        $config = array(
            'dsn'      => "sqlite:gudang/db/{$niche}.sqlite",
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => '',
            'dbdriver' => 'pdo',
            'dbprefix' => '',
            'pconnect' => FALSE,
            'db_debug' => (ENVIRONMENT !== 'production'),
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt'  => FALSE,
            'compress' => FALSE,
            'stricton' => FALSE,
            'failover' => array(),
            'save_queries' => TRUE
        );

        return $CI->load->database($config, TRUE);
    }
}

if (!function_exists('CreateNichedb')) {
    function CreateNichedb($niche = "home")
    {
        $path = "gudang/db/{$niche}.sqlite";

        deleteFile($path);

        $db = new SQLite3($path);

        $db->exec("CREATE TABLE IF NOT EXISTS 'tbl_keywords' (
            'id' INTEGER PRIMARY KEY NOT NULL,
            'keyword' VARCHAR NOT NULL,
            'slug' VARCHAR NOT NULL,
            'title' VARCHAR NOT NULL DEFAULT '',
            'json_images' TEXT NOT NULL DEFAULT '',
            'json_sentences' TEXT NOT NULL DEFAULT '',
            'publish' DATETIME NOT NULL DEFAULT '',
            'update' DATETIME NOT NULL DEFAULT '',
            'status' TINYINT NOT NULL DEFAULT '0',
            UNIQUE (slug) ON CONFLICT IGNORE
        )");
    }
}

if (!function_exists('niche_arr')) {
    function niche_arr()
    {
        $path   = "gudang/db/";
        $ext    = ".sqlite";
        $arr    = glob("{$path}*{$ext}");

        $arr  = str_replace([$path, $ext], '', $arr);

        return $arr;
    }
}

if (!function_exists('default_niche')) {
    function default_niche()
    {
        $niche = DEFAULT_NICHE;

        $path   = "gudang/db/";
        $ext    = ".sqlite";

        if (!file_exists(FCPATH . "{$path}{$niche}{$ext}")) {
            $arr    = glob("{$path}*{$ext}");
            $niche  = $arr[0] ?? '';
            $niche  = str_replace([$path, $ext], '', $niche);

            if (!$niche) {
                echo "No database..";
                die;
            }
        }

        return $niche;
    }
}

if (!function_exists('last_post')) {
    function last_post($niche = "home", $limit = 20, $query = "")
    {
        $dbx    = dbimake($niche);

        $date   = date('Y-m-d H:i:s');

        $arr    = $dbx->query("SELECT * FROM tbl_keywords WHERE keyword LIKE \"%$query%\" AND json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND DATE(publish) <= '{$date}' ORDER BY publish DESC LIMIT {$limit} ")->result_array();

        return $arr;
    }
}

if (!function_exists('random_related')) {
    function random_related($niche = "home", $limit = 10, $query = 'keyword')
    {
        $dbx        = dbimake($niche);

        $date = date('Y-m-d H:i:s');

        $arr = $dbx->query("SELECT {$query} FROM tbl_keywords WHERE json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND DATE(publish) <= '{$date}' ORDER BY RANDOM() LIMIT {$limit} ")->result_array();

        return $arr;
    }
}

if (!function_exists('arr_slug')) {
    function arr_slug($niche = "home")
    {
        $dbx    = dbimake($niche);

        $date   = date('Y-m-d H:i:s');

        $arr    = $dbx->query("SELECT keyword, slug, publish FROM tbl_keywords WHERE json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND DATE(publish) <= '{$date}' ORDER BY publish DESC")->result_array();

        return $arr;
    }
}

if (!function_exists('arr_rss')) {
    function arr_rss($niche = "home")
    {
        $dbx        = dbimake($niche);

        $date1   = date('Y-m-d H:i:s',strtotime('-1 hour'));
        $date2   = date('Y-m-d H:i:s',strtotime('-24 hours'));

        $limit  = RSS_MAX;

        $arr    = $dbx->query("SELECT keyword, slug, json_sentences, publish FROM tbl_keywords WHERE json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND publish <= '{$date1}' AND publish >= '{$date2}' ORDER BY publish DESC LIMIT {$limit}")->result_array();

        return $arr;
    }
}

if (!function_exists('post_details')) {
    function post_details($niche = "best", $slug = '')
    {
        $dbx        = dbimake($niche);

        $date   = date('Y-m-d H:i:s');

        $arr    = $dbx->query("SELECT * FROM tbl_keywords WHERE json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND DATE(publish) <= '{$date}' AND slug = '{$slug}'")->row_array();

        return $arr;
    }
}

if (!function_exists('db_info')) {
    function db_info($niche = "home", $limit = 10, $query = 'keyword')
    {
        $dbx        = dbimake($niche);

        $date   = date('Y-m-d H:i:s');

        $arr    = $dbx->query("SELECT {$query} FROM tbl_keywords WHERE json_images != '' AND json_images != '[]' AND json_sentences != '' AND json_sentences != '[]' AND DATE(publish) <= '{$date}' ORDER BY publish DESC LIMIT {$limit}")->result_array();

        return $arr;
    }
}
