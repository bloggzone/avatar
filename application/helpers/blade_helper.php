<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('view')) {
    function view($view, $data = [], $is_var = TRUE)
    {

        $apppath         = APPPATH;
        $dir             = FCPATH;
        $path            = "{$dir}blade";
        $cache_dir       = "{$apppath}cache/blade";

        $theme           = THEME_NAME;
        $data['layout'] = "theme.{$theme}.layout";

        $blade           = new \duncan3dc\Laravel\BladeInstance($path, $cache_dir);

        $blade->addPath("{$dir}/blade/ads");

        if ($view !== 'pages.page') {
            $view = str_replace("{theme}", "theme.{$theme}", $view);
        }

        $res             = $blade->render($view, $data);

        if (MINIFY_HTML) {
            $res            = Minify_Html($res);
        }

        if ($is_var) {
            return $res;
        } else {
            echo $res;
        }
    }
}

if (!function_exists('pages')) {
    function pages()
    {
        return [
            'dmca',
            'contact',
            'privacy-policy',
            'copyright',
        ];
    }
}


if (!function_exists('blade_sitename')) {
    function blade_sitename($niche = "")
    {
        $sn = SITE_NAME;

        $title = str_replace(['{niche}'], $niche, $sn);
        $title = ucwords($title);
        $title = trim($title);

        return $title;
    }
}

if (!function_exists('blade_sitedesc')) {
    function blade_sitedesc($niche = "")
    {
        $sd = SITE_DESCRIPTION;

        $desc = str_replace(['{niche}'], $niche, $sd);
        $desc = trim($desc);

        return $desc;
    }
}

if (!function_exists('blade_image')) {
    function blade_image($kw = "", $force = FALSE, $options = '')
    {
        $id         = IMAGE_DOWNLOAD;
        $img_url    = "";

        if (!$id or $force) {
            $kw_encode  = rawurlencode($kw);
            $img_url    = "https://tse1.mm.bing.net/th?q={$kw_encode}{$options}";
        } else {
            $kw = md5($kw);
            $img_url = "assets/img/{$kw}.jpg";
        }

        return $img_url;
    }
}

if (!function_exists('blade_slug')) {
    function imake_url($niche, $slug)
    {
        $niche  = str_replace(' ', '-', $niche);

        $ext    = POST_EXTENSION;

        $path   = "{$slug}{$ext}";

        $host = "";

        if (PHP_SAPI !== 'cli') {
            $host   = base_url("{$niche}/");
            $pro    = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        }

        $url = "{$host}{$path}";

        return $url;
    }
}

if (!function_exists('backlink_arr')) {
    function backlink_arr()
    {
        $list   = @file("gudang/backlink.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $list;
    }
}

if (!function_exists('conver_to_array')) {
    function conver_to_array($str, $delim, $n)
    {
      return array_map(function($p) use ($delim) {
          return implode($delim, $p);
      }, array_chunk(explode($delim, $str), $n));
    }
}

if (!function_exists('single_backlink_render')) {
    function single_backlink_render($content, $keyword)
    {
        //if(!BACKLINK_RENDER){return $content;}

        $arr_tags   = conver_to_array($keyword, " ",2);
        if(!$arr_tags){return $content;}
        $str        = $arr_tags[array_rand($arr_tags)];
        $link_arr   = backlink_arr();
        if(!$link_arr){return $content;}
        $link       = $link_arr[array_rand($link_arr)];
        $atag       = "<a href='{$link}' target='_blank'>{$str}</a>";

        $content  = preg_replace("/{$str}/", $atag, $content, 1);

        return $content;

    }
}
