<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_instance')) {
    function get_instance()
    {
        $CI = &get_instance();
    }
}

if (!function_exists('get_argv')) {
    function get_argv()
    {
        return $_SERVER['argv'];
    }
}

if (!function_exists('err404')) {
    function err404()
    {
        redirect(base_url());
        exit;
    }
}

if (!function_exists('curl')) {
    function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $header[] = "Accept-Language: en";
        $header[] = "Pragma: no-cache";
        $header[] = "Cache-Control: no-cache";
        $header[] = "Accept-Encoding: gzip,deflate";
        $header[] = "Content-Encoding: gzip";
        $header[] = "Content-Encoding: deflate";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $load = curl_exec($ch);
        curl_close($ch);
        return $load;
    }
}

if (!function_exists('rand_ua')) {
    function rand_ua()
    {
        //$v1   = rand(7,14);
        //$v2   = rand(0,5);
        //$c1   = rand(80,93);
        $ver    = SCRAPER_VERSION;
        $a2     = rand(0, 4692);
        $a3     = rand(0, 90);

        //$a2   = rand(1,20);

        //return  "Mozilla/5.0 (iPhone; CPU iPhone OS {$v1}_{$v2} like Mac OS X) AppleWebKit/{$a1}.{$a2} (KHTML, like Gecko) Version/{$v1}.0 Mobile/15A372 Safari/{$a1}.1";
        //return  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/{$a1}.{$a2} (KHTML, like Gecko) Chrome/{$c1}.0 Safari/{$a1}.{$a2}";
        return  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$ver}.0.{$a2}.{$a3} Safari/537.36";
    }
}

if (!function_exists('json_encode_view')) {
    function json_encode_view($data = NULL)
    {
        $CI = get_instance();
        $CI->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output($data)->_display();
        exit();
    }
}

if (!function_exists('json')) {
    function json($data = NULL)
    {
        $CI = get_instance();
        $CI->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->_display();
        exit();
    }
}

if (!function_exists('cse_site')) {
    function cse_site($site   = CSE_FILTER)
    {
        //return ($site)?"site:{$site} ":"";
        return ($site) ? "{$site} " : "";
    }
}

if (!function_exists('get_proxy')) {
    function get_proxy($proxy = '')
    {
        $mode = PROXY_MODE;

        if (!$mode) {
            return '';
        }

        $file       = "gudang/proxy_list.txt";

        $proxies    = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($proxies) {
            $rk         = array_rand($proxies);
            return $proxies[$rk];
        }
    }
}

if (!function_exists('cek_proxy')) {
    function cek_proxy()
    {
        $file       = "gudang/proxy_list.txt";

        $proxies    = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $cek_proxy  = CEK_PROXY;

        if (!$cek_proxy) {
            return true;
        }

        //if(!$proxies){return false;}

        echo "\r\n[\033[32mSTART\033[39m] ==> CHECK PROXY..\r\n";

        $list = [];

        foreach ($proxies as $key => $proxy) {
            $proxy = (strpos($proxy, '://') !== false) ? $proxy : "http://{$proxy}";

            $do = do_cek_bingproxy($proxy);

            if ($do) {
                $list[] = $proxy;
            }
        }

        $count = count($list);

        if (!$count) {
            echo "\r\n[\033[31mWARNING\033[39m] ==> No available Proxy..\r\n";
            echo "\r\n[\033[32mTIPS\033[39m] ==> Add another proxy or set FALSE \"PROXY_MODE\" at constant.php and then START again..\r\n";
            die;
        }

        $list_txt = implode("\r\n", $list);

        file_put_contents($file, "\r\n{$list_txt}");
    }
}

if (!function_exists('do_cek_bingproxy')) {
    function do_cek_bingproxy($proxy = '')
    {
        if (!$proxy or substr_count($proxy, ".") < 3) {
            return false;
        }

        echo "\r\n[\033[32mCHECKING\033[39m] ==> {$proxy}\r\n";

        $client = new \GuzzleHttp\Client([
            'cookies'       => true,
            'timeout'       => 10.0,
            'verify'        => false,
            'http_errors'   => false,
            'allow_redirects' => ['strict' => true],
            'headers'   => [
                'Accept-Encoding'   => 'gzip, deflate',
                'Accept-Language'   => 'en-US,en;q=0.9',
                'Host'              => 'www.bing.com',
                'Cache-Control'     => 'max-age=0',
                'User-Agent'        => rand_ua()
            ],
            'proxy' => [
                'http'  => $proxy, // Use this proxy with "http"
                'https' => $proxy, // Use this proxy with "https",
            ]
        ]);

        try {

            $query = http_build_query([
                'q'         => "home",
                'cc'        => "en",
                'qft'       => '+filterui:imagesize-custom_500_500',
                'FORM'      => 'IRFLTR',
                'first'     => 1,
                'tab'       => 'REC',
                'rand_num'  => rand_num(), //Biar tidak cache
            ]);

            $response   = $client->head('http://bing.com/images/async?{$query}');

            $code       = $response->getStatusCode();

            if ($code === 200 or $code === 301) {
                echo "\r\n==> Status : \033[32mAVAILABLE\033[39m\r\n";
                return $code;
            } else {
                echo "\r\n==> Status : \033[31mUNAVAILABLE\033[39m\r\n";
                return FALSE;
            }
        } catch (Exception $e) {

            echo "\r\n==> Status : \033[31mUNAVAILABLE\033[39m\r\n";
        }
    }
}

if (!function_exists('deleteDirectory')) {
    function deleteDirectory($dir)
    {

        echo "\r\n[\033[31mDELETE\033[39m] ==> {$dir}\r\n";

        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}

if (!function_exists('make_dir')) {
    function make_dir($target_dir)
    {
        if (!is_dir($target_dir)) {
            mkdir("./{$target_dir}", 0777, TRUE);
        }
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($dir)
    {
        $files = glob($dir);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        sleep(1);
    }
}

if (!function_exists('delete_data')) {
    function delete_data()
    {
        deleteFile("gudang/db/*.sqlite");
        deleteFile(APPPATH . "/cache/blade/*.php");

        deleteDirectory('export');
        sleep(1);
        make_dir('export');
        sleep(1);

        /*$arr_dir = ['compiled'];

        foreach ($arr_dir as $key => $dir)
        {
            //$fpath  = "gudang/{$dir}";
            $dirs   = glob("{$fpath}/*", GLOB_ONLYDIR);

            foreach ($dirs as $key => $sdir)
            {
                echo "\r\n[\033[32mDELETE\033[39m] ==> {$sdir}\r\n";  

                deleteDirectory($sdir);
            }

            make_dir($fpath);
        }*/
    }
}

if (!function_exists('delete_export')) {
    function delete_export()
    {
        deleteDirectory('export');
        sleep(1);
        make_dir('export');
        sleep(1);
    }
}

if (!function_exists('random_sentences')) {
    function random_sentences($arr, $start = 0, $limit = 1, $rand = FALSE)
    {
        if ($rand) {
            shuffle($arr);
        }

        $arr = array_slice($arr, $start, $limit);

        $str = implode(' ', $arr);

        return $str;
    }
}

if (!function_exists('get_data')) {
    function get_data($slug, $niche)
    {
        $filename = "gudang/compiled/{$niche}/{$slug}.srz.php";

        return @unserialize(@file_get_contents($filename));
    }
}

if (!function_exists('do_spintax')) {
    function do_spintax($str = '', $isfile = FALSE, $replace = [])
    {
        //{{do_spintax('{bebek|ayam|kuda}')}}
        //$replace = [ 'search 1|replacement 1', 'search 2|replacement 2'];

        if ($isfile) {
            $str = @file_get_contents(FCPATH . "/{$str}");
        }

        $sp     = \bjoernffm\Spintax\Parser::parse($str);
        $res    = $sp->generate();

        if ($replace) {
            foreach ($replace as $info) {
                $pre = explode('|', $info);

                $src = $pre[0];
                $rep = $pre[1];

                if ($rep) {
                    $res = str_replace($src, $rep, $res);
                }
            }
        }

        return $res;
    }
}

if (!function_exists('badword_filter')) {
    function badword_filter($arr = [])
    {
        if (BADWORD_FILTER === TRUE) {
            echo "\r\n[\033[32mCHECK\033[39m] ==> Badwords check..\r\n";

            $arr_bad    = @file('gudang/badwords_quotes.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!$arr_bad) {
                $arr_bad    = badword_compile();
            }

            $list = array_chunk($arr_bad, 200);

            foreach ($list as $key => $sublist) {
                $blacklist = '/.*' . implode('.*|.*', $sublist) . '.*/i';

                $arr = array_filter($arr, function ($row) use ($blacklist) {
                    return !preg_match($blacklist, $row);
                });
            }
        }

        return $arr;
    }
}

if (!function_exists('badword_compile')) {
    function badword_compile()
    {
        $bad        = file('gudang/badword.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $arr_bad = [];

        foreach ($bad as $key => $val) {
            $arr_bad[] = preg_quote($val);
        }

        file_put_contents('gudang/badwords_quotes.txt', implode("\r\n", $arr_bad));

        return $arr_bad;
    }
}

if (!function_exists('slug_imake')) {
    function slug_imake($slug = '', $raw = FALSE)
    {
        if (IS_UTF8) {
            $slug = url_title(convert_accented_characters($slug), '-', TRUE);
        } else {
            $slug = str_to_url($slug, $raw);
        }

        return $slug;
    }
}

if (!function_exists('cdn_image')) {
    function cdn_image($url)
    {
        if (CDN_IMAGE) {
            if (strpos($url, ".wp.com/") === FALSE and strpos($url, "i.pinimg.com") === FALSE) {
                $cdn_url = "https://i2.wp.com/";
                //$cdn_url = "https://cdn.statically.io/img/";
                $url = str_replace(['http://', 'https://'], $cdn_url, $url);
            }
        }

        $url = str_replace('https://i2.wp.com/i2.wp.com/', 'https://i2.wp.com/', $url);

        return $url;
    }
}

if (!function_exists('imake_stringcase')) {
    function imake_stringcase($case = "", $str = '')
    {
        switch ($case) {
            case 'ucfirst':
                $case = MB_CASE_TITLE;
                break;
            case 'strtoupper':
                $case = MB_CASE_UPPER;
                break;
            case 'strtolower':
                $case = MB_CASE_LOWER;
                break;
            default:
                //ucwords
                $case = MB_CASE_TITLE;
                break;
        }

        return mb_convert_case($str, $case, "UTF-8");
    }
}

if (!function_exists('bing_title_cleaner')) {
    function bing_title_cleaner($title = '')
    {
        //$title      = strtolower($title);
        $title      = str_replace(['|', '-', '–', '~', ':', ' ...', '#', '$', '@', '%'], '', $title);
        $title      = preg_replace('/\s+/', ' ', $title);
        $title_arr  = explode(' ', $title);

        $tld   = ['.com', '.org', '.net', '.biz', '.club', '.online', '.shop'];

        $blacklist  = '/.*' . implode('.*|.*', $tld) . '.*/i';

        $rows = array_filter($title_arr, function ($row) use ($blacklist) {
            return !preg_match($blacklist, $row);
        });

        $title = implode(' ', $rows);

        return $title;
    }
}

if (!function_exists('bing_thumb_id')) {
    function bing_thumb_id($url, $key)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $result);

        return isset($result[$key]) ? $result[$key] : null;
    }
}

if (!function_exists('url_to_str')) {
    function url_to_str($kw)
    {
        $kw = urldecode($kw);
        $kw = str_replace("-", " ", $kw);
        $kw = trim($kw);
        return $kw;
    }
}

if (!function_exists('str_to_url')) {
    function str_to_url($kw, $raw = FALSE)
    {
        $kw = str_replace("'", " ", $kw);
        $kw = preg_replace('/[^\p{L}\p{N}]/u', ' ', $kw);
        $kw = preg_replace('/\s+/', ' ', $kw);
        $kw = trim($kw);
        $kw = str_replace(' ', '-', $kw);
        if ($raw === TRUE) {
            $kw = rawurlencode($kw);
        }
        return $kw;
    }
}

if (!function_exists('str_cleaner')) {
    function str_cleaner($kw)
    {
        $kw = str_replace("'", " ", $kw);
        $kw = preg_replace('/[^\p{L}\p{N}]/u', ' ', $kw);
        $kw = preg_replace('/\s+/', ' ', $kw);
        $kw = trim($kw);
        return $kw;
    }
}

if (!function_exists('array_to_str')) {
    function array_to_str($array)
    {
        $hasil = '';
        foreach ($array as $value) {
            $hasil .= $value . PHP_EOL;
        }
        return $hasil;
    }
}

if (!function_exists('str_contains')) {
    function str_contains($str, $filter_path)
    {
        $sketch = '/.*' . implode('.*|.*', $filter_path) . '.*/i';

        return preg_match($sketch, $str);
    }
}

if (!function_exists('sitemap_note')) {
    function sitemap_note()
    {
        $txt = @file_get_contents("gudang/note/Note Sitemap Static HTML.txt");

        file_put_contents("export/Note Sitemap Static HTML.txt", $txt);
    }
}
