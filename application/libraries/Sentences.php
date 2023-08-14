<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sentences
{
	protected $_stop = '/(?<=[.?!;:])\s+/';

	function parseResult($result, $word)
	{
		$extracted = preg_split($this->_stop, $result, -1, PREG_SPLIT_NO_EMPTY);

		$new_sentences = [];
		foreach ($extracted as $sentence) {
			$sentence = preg_replace('/\.+/', '.', trim($sentence));
			$sentence = str_replace(' .', '.', $sentence);
			$pos = $this->xstr_contains($sentence, ['-', '–', 'http']);
			$word_count = count(explode(' ', $sentence));

			if ($pos === false && $word_count > 4) {
				$sentence = str_replace(['"'], '', $sentence);
				$sentence = $this->xmb_ucfirst(mb_strtolower($sentence));
				$new_sentences[] = $sentence;
			}
		}
		return $new_sentences;
	}

	function xstr_contains($haystack, $needles)
	{
		foreach ($needles as $needle) {
			if (stripos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	function xmb_ucfirst(string $str, string $encoding = null): string
	{
		if (is_null($encoding)) {
			$encoding = mb_internal_encoding();
		}

		return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) .
			mb_substr($str, 1, null, $encoding);
	}
}
