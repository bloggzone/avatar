<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('db_web_phase')) {
    function db_web_phase()
    {
        $c_source = CONTENT_SOURCE;

        return ($c_source === 'openai')?'txt_content':'json_sentences';
        
    }
}

if (!function_exists('oprompt_file')) {
    function oprompt_file($file='')
    {
        $o_file = O_PROMPT_FILE;
        $file   = (!$file)?$o_file:$file;
        $type   = O_TYPE;
        $years  = date('Y');
        $lang   = O_LANGUAGE;
        $max_p  = O_MIN_PARAGRAPHS;

        $prompt_ = @file_get_contents("gudang/prompt/{$file}.txt");

        return str_replace(['{type}', '{years}', '{lang}', '{max_p}'], [$type, $years, $lang, $max_p], $prompt_);
        
    }
}

if (!function_exists('openai_prompt')) {
    function openai_prompt($keyword = '', $oprompt_txt='')
    {

        $prompt_ = ($oprompt_txt)?$oprompt_txt:oprompt_file();

        return str_replace('{keyword}', $keyword, $prompt_);
        
    }
}

if (!function_exists('json_check')) {
    function json_check($d = '')
    {
        return (is_string($d) AND is_array(json_decode($d,TRUE)) )?true:false;
        
    }
}

if (!function_exists('api_config')) {
    function api_config($index="")
    {
        $list   = @file("gudang/tmp/openai-key.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $count = count($list);

        if (!$count) {
            echo "\r\n[\033[31mERROR\033[39m] ==> Silahkan Insert API Key OpenAi ke dalam file \"openai-key.txt\". 1 line per api key..\r\n\r\n";
            die;
        }

        //$i = array_rand($list);

        $i = $index % $count;

        $key = $list[$i];

        echo "\r\n[\033[32mAPI KEY\033[39m][\033[32m{$i}\033[39m] ==> {$key}\r\n";

        return $key;
    }
}

if (!function_exists('openai_error_handle')) {
    function openai_error_handle($code, $response, $apiKey)
    {
        $msg = $response['error']['message']??'';
        $type = $response['error']['type']??'';

        echo "\r\n[\033[31mERROR {$code}\033[39m][\033[31m{$type}\033[39m] ==> {$msg}\r\n";

        if($type === 'insufficient_quota' OR $type === 'billing_not_active' OR strpos($msg, 'terminated due to violation') !== false OR strpos($msg, 'Incorrect API key provided') !== false)
        {
            $fn     = "openai-key.txt";
            $txt    = @file_get_contents("openai-key.txt");
            $txt    = str_replace($apiKey, "", $txt);
            file_put_contents($fn, $txt);

            echo "\r\n[\033[31mEXIT\033[39m] ==> Closing Tread..\r\n";
            sleep(5);
            die;
        }
    }
}

function openapi_config()
{
    $list   = @file("openai-key.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!count($list)) {
        echo "\r\n[\033[31mERROR\033[39m] ==> Silahkan Insert API Key OpenAi terlebih dahulu..\r\n";
        die;
    }
    else
    {
        $listTxt = implode("\r\n", $list);
        file_put_contents("gudang/tmp/openai-key.txt", "\r\n{$listTxt}");
        sleep(1);
    }
}

if (!function_exists('scraper_tread')) {
    function scraper_tread()
    {
        $list   = @file("gudang/tmp/openai-key.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $count = count($list);

        if(!$count){die;}

        return $count;
    }
}