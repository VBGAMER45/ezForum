<?php

/**
 * ezForum http://www.ezforum.com
 * Copyright 2011-2016 ezForum
 * License: BSD
 *
 * Based on:
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file has functions in it to do with character set and string
	manipulation.  It provides these functions:

	string utf8_strtolower(string $string)
		- converts a UTF-8 string into a lowercase UTF-8 string.
		- equivalent to mb_strtolower($string, 'UTF-8')

	string utf8_strtoupper(string $string)
		- converts a UTF-8 string into a uppercase UTF-8 string.
		- equivalent to mb_strtoupper($string, 'UTF-8')

	void fix_serialized_columns()
		- fixes corrupted serialized strings after a character set conversion.

*/

// Converts the given UTF-8 string into lowercase.
function utf8_strtolower($string)
{
	static $case_folding = array(
		'A' => 'a',		'B' => 'b',		'C' => 'c',		'D' => 'd',
		'E' => 'e',		'F' => 'f',		'G' => 'g',		'H' => 'h',
		'I' => 'i',		'J' => 'j',		'K' => 'k',		'L' => 'l',
		'M' => 'm',		'N' => 'n',		'O' => 'o',		'P' => 'p',
		'Q' => 'q',		'R' => 'r',		'S' => 's',		'T' => 't',
		'U' => 'u',		'V' => 'v',		'W' => 'w',		'X' => 'x',
		'Y' => 'y',		'Z' => 'z',		'µ' => '?',		'À' => 'à',
		'Á' => 'á',		'Â' => 'â',		'Ã' => 'ã',		'Ä' => 'ä',
		'Å' => 'å',		'Æ' => 'æ',		'Ç' => 'ç',		'È' => 'è',
		'É' => 'é',		'Ê' => 'ê',		'Ë' => 'ë',		'Ì' => 'ì',
		'Í' => 'í',		'Î' => 'î',		'Ï' => 'ï',		'Ð' => 'ð',
		'Ñ' => 'ñ',		'Ò' => 'ò',		'Ó' => 'ó',		'Ô' => 'ô',
		'Õ' => 'õ',		'Ö' => 'ö',		'Ø' => 'ø',		'Ù' => 'ù',
		'Ú' => 'ú',		'Û' => 'û',		'Ü' => 'ü',		'Ý' => 'ý',
		'Þ' => 'þ',		'ß' => 'ss',	'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => 'i?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?n',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'Œ' => 'œ',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'Š' => 'š',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'Ÿ' => 'ÿ',		'?' => '?',
		'?' => '?',		'Ž' => 'ž',		'?' => 's',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => 'ƒ',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '??',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '??',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => 'h?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => 'a?',	'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '???',	'?' => '???',	'?' => '???',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',		'?' => '???',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '??',	'?' => '??',
		'?' => '??',	'?' => '??',		'?' => '???',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '??',	'?' => '??',	'?' => '??',		'?' => '??',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '??',	'?' => '??',	'?' => '??',		'?' => '??',
		'?' => '??',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '??',	'?' => '??',
		'?' => '??',	'?' => '??',		'?' => '???',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => 'k',		'?' => 'å',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => 'ff',	'?' => 'fi',	'?' => 'fl',	'?' => 'ffi',
		'?' => 'ffl',	'?' => 'st',	'?' => 'st',	'?' => '??',
		'?' => '??',	'?' => '??',	'?' => '??',	'?' => '??',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',	'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',
	);

	return strtr($string, $case_folding);
}

// Convert the given UTF-8 string to uppercase.
function utf8_strtoupper($string)
{
	static $case_folding = array(
		'a' => 'A',		'b' => 'B',		'c' => 'C',		'd' => 'D',
		'e' => 'E',		'f' => 'F',		'g' => 'G',		'h' => 'H',
		'i' => 'I',		'j' => 'J',		'k' => 'K',		'l' => 'L',
		'm' => 'M',		'n' => 'N',		'o' => 'O',		'p' => 'P',
		'q' => 'Q',		'r' => 'R',		's' => 'S',		't' => 'T',
		'u' => 'U',		'v' => 'V',		'w' => 'W',		'x' => 'X',
		'y' => 'Y',		'z' => 'Z',		'?' => 'µ',		'à' => 'À',
		'á' => 'Á',		'â' => 'Â',		'ã' => 'Ã',		'ä' => 'Ä',
		'å' => 'Å',		'æ' => 'Æ',		'ç' => 'Ç',		'è' => 'È',
		'é' => 'É',		'ê' => 'Ê',		'ë' => 'Ë',		'ì' => 'Ì',
		'í' => 'Í',		'î' => 'Î',		'ï' => 'Ï',		'ð' => 'Ð',
		'ñ' => 'Ñ',		'ò' => 'Ò',		'ó' => 'Ó',		'ô' => 'Ô',
		'õ' => 'Õ',		'ö' => 'Ö',		'ø' => 'Ø',		'ù' => 'Ù',
		'ú' => 'Ú',		'û' => 'Û',		'ü' => 'Ü',		'ý' => 'Ý',
		'þ' => 'Þ',		'ss' => 'ß',	'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'i?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?n' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'œ' => 'Œ',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'š' => 'Š',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'ÿ' => 'Ÿ',		'?' => '?',
		'?' => '?',		'ž' => 'Ž',		's' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'ƒ' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'h?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'a?' => '?',	'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'???' => '?',	'???' => '?',	'???' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',		'???' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',		'???' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',		'??' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',		'??' => '?',
		'??' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',		'???' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'k' => '?',		'å' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'ff' => '?',	'fi' => '?',	'fl' => '?',	'ffi' => '?',
		'ffl' => '?',	'st' => '?',	'st' => '?',	'??' => '?',
		'??' => '?',	'??' => '?',	'??' => '?',	'??' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',	'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',		'?' => '?',
		'?' => '?',		'?' => '?',		'?' => '?',	'?' => '?',
		'?' => '?',		'?' => '?',		'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',	'??' => '??',	'??' => '??',
		'??' => '??',	'??' => '??',
	);

	return strtr($string, $case_folding);
}

// Fixes corrupted serialized strings after a character set conversion.
function fix_serialized_columns()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_action, extra
		FROM {db_prefix}log_actions
		WHERE action IN ({string:remove}, {string:delete})',
		array(
			'remove' => 'remove',
			'delete' => 'delete',
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (safe_unserialize($row['extra']) === false && preg_match('~^(a:3:{s:5:"topic";i:\d+;s:7:"subject";s:)(\d+):"(.+)"(;s:6:"member";s:5:"\d+";})$~', $row['extra'], $matches) === 1)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}log_actions
				SET extra = {string:extra}
				WHERE id_action = {int:current_action}',
				array(
					'current_action' => $row['id_action'],
					'extra' => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4],
				)
			);
	}
	$smcFunc['db_free_result']($request);

	// Refresh some cached data.
	updateSettings(array(
		'memberlist_updated' => time(),
	));

}

?>