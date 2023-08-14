<?php 

$config = [
    "model" 			=> "text-davinci-003",
    //"prompt" 			=> "Write a unique and professional blog post about \"{$keyword}\" also give the example or solution.",
    "temperature" 		=> 0.7,
    "max_tokens" 		=> 500,
    "top_p" 			=> 1,
    "frequency_penalty" => 0,
    "presence_penalty" 	=> 0
];

return $config;
