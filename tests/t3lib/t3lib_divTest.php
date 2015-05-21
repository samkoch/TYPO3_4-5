<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 Ingo Renner <ingo@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Testcase for class t3lib_div
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 *
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_divTest extends tx_phpunit_testcase {

	/**
	 * Enable backup of global and system variables
	 *
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * Exclude TYPO3_DB from backup/ restore of $GLOBALS
	 * because resource types cannot be handled during serializing
	 *
	 * @var array
	 */
	protected $backupGlobalsBlacklist = array('TYPO3_DB');

	public function tearDown() {
		t3lib_div::purgeInstances();
	}


	///////////////////////////////
	// Tests concerning gif_compress
	///////////////////////////////

	/**
	 * @test
	 */
	public function gifCompressFixesPermissionOfConvertedFileIfUsingImagemagick() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('gifCompressFixesPermissionOfConvertedFileIfUsingImagemagick() test not available on Windows.');
		}

		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] || !$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']) {
			$this->markTestSkipped('gifCompressFixesPermissionOfConvertedFileIfUsingImagemagick() test not available without imagemagick setup.');
		}

		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		$fixtureGifFile = $testFinder->getAbsoluteCoreTestsPath() . 't3lib/fixtures/clear.gif';

		$GLOBALS['TYPO3_CONF_VARS']['GFX']['gif_compress'] = TRUE;

			// Copy file to unique filename in typo3temp, set target permissions and run method
		$testFilename = PATH_site . 'typo3temp/' . uniqid('test_') . '.gif';
		@copy($fixtureGifFile, $testFilename);
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';
		t3lib_div::gif_compress($testFilename, 'IM');

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($testFilename)), 2);
		t3lib_div::unlink_tempfile($testFilename);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	/**
	 * @test
	 */
	public function gifCompressFixesPermissionOfConvertedFileIfUsingGd() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('gifCompressFixesPermissionOfConvertedFileIfUsingImagemagick() test not available on Windows.');
		}

		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		$fixtureGifFile = $testFinder->getAbsoluteCoreTestsPath() . 't3lib/fixtures/clear.gif';

		$GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib'] = TRUE;
		$GLOBALS['TYPO3_CONF_VARS']['GFX']['gdlib_png'] = FALSE;

		$GLOBALS['TYPO3_CONF_VARS']['GFX']['gif_compress'] = TRUE;

			// Copy file to unique filename in typo3temp, set target permissions and run method
		$testFilename = PATH_site . 'typo3temp/' . uniqid('test_') . '.gif';
		@copy($fixtureGifFile, $testFilename);
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';
		t3lib_div::gif_compress($testFilename, 'GD');

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($testFilename)), 2);
		t3lib_div::unlink_tempfile($testFilename);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	///////////////////////////////
	// Tests concerning png_to_gif_by_imagemagick
	///////////////////////////////

	/**
	 * @test
	 */
	public function pngToGifByImagemagickFixesPermissionsOfConvertedFile() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('pngToGifByImagemagickFixesPermissionsOfConvertedFile() test not available on Windows.');
		}

		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] || !$GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']) {
			$this->markTestSkipped('pngToGifByImagemagickFixesPermissionsOfConvertedFile() test not available without imagemagick setup.');
		}

		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		$fixturePngFile = $testFinder->getAbsoluteCoreTestsPath() . 't3lib/fixtures/clear.png';

		$GLOBALS['TYPO3_CONF_VARS']['FE']['png_to_gif'] = TRUE;

			// Copy file to unique filename in typo3temp, set target permissions and run method
		$testFilename = PATH_site . 'typo3temp/' . uniqid('test_') . '.png';
		@copy($fixturePngFile, $testFilename);
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';
		$newGifFile = t3lib_div::png_to_gif_by_imagemagick($testFilename);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($newGifFile)), 2);
		t3lib_div::unlink_tempfile($newGifFile);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	///////////////////////////////
	// Tests concerning read_png_gif
	///////////////////////////////

	/**
	 * @test
	 */
	public function readPngGifFixesPermissionsOfConvertedFile() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('readPngGifFixesPermissionsOfConvertedFile() test not available on Windows.');
		}

		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['im']) {
			$this->markTestSkipped('readPngGifFixesPermissionsOfConvertedFile() test not available without imagemagick setup.');
		}

		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		$testGifFile = $testFinder->getAbsoluteCoreTestsPath() . 't3lib/fixtures/clear.gif';

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';
		$newPngFile = t3lib_div::read_png_gif($testGifFile, TRUE);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($newPngFile)), 2);
		t3lib_div::unlink_tempfile($newPngFile);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	///////////////////////////
	// Tests concerning cmpIPv4
	///////////////////////////

	/**
	 * Data provider for cmpIPv4ReturnsTrueForMatchingAddress
	 *
	 * @return array Data sets
	 */
	public static function cmpIPv4DataProviderMatching() {
		return array(
			'host with full IP address' => array('127.0.0.1', '127.0.0.1'),
			'host with two wildcards at the end' => array('127.0.0.1', '127.0.*.*'),
			'host with wildcard at third octet' => array('127.0.0.1', '127.0.*.1'),
			'host with wildcard at second octet' => array('127.0.0.1', '127.*.0.1'),
			'/8 subnet' => array('127.0.0.1', '127.1.1.1/8'),
			'/32 subnet (match only name)' => array('127.0.0.1', '127.0.0.1/32'),
			'/30 subnet' => array('10.10.3.1', '10.10.3.3/30'),
			'host with wildcard in list with IPv4/IPv6 addresses' => array('192.168.1.1', '127.0.0.1, 1234:5678::/126, 192.168.*'),
			'host in list with IPv4/IPv6 addresses' => array('192.168.1.1', '::1, 1234:5678::/126, 192.168.1.1'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpIPv4DataProviderMatching
	 */
	public function cmpIPv4ReturnsTrueForMatchingAddress($ip, $list) {
		$this->assertTrue(t3lib_div::cmpIPv4($ip, $list));
	}

	/**
	 * Data provider for cmpIPv4ReturnsFalseForNotMatchingAddress
	 *
	 * @return array Data sets
	 */
	public static function cmpIPv4DataProviderNotMatching() {
		return array(
			'single host' => array('127.0.0.1', '127.0.0.2'),
			'single host with wildcard' => array('127.0.0.1', '127.*.1.1'),
			'single host with /32 subnet mask' => array('127.0.0.1', '127.0.0.2/32'),
			'/31 subnet' => array('127.0.0.1', '127.0.0.2/31'),
			'list with IPv4/IPv6 addresses' => array('127.0.0.1', '10.0.2.3, 192.168.1.1, ::1'),
			'list with only IPv6 addresses' => array('10.20.30.40', '::1, 1234:5678::/127'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpIPv4DataProviderNotMatching
	 */
	public function cmpIPv4ReturnsFalseForNotMatchingAddress($ip, $list) {
		$this->assertFalse(t3lib_div::cmpIPv4($ip, $list));
	}
	///////////////////////////
	// Tests concerning cmpIPv6
	///////////////////////////

	/**
	 * Data provider for cmpIPv6ReturnsTrueForMatchingAddress
	 *
	 * @return array Data sets
	 */
	public static function cmpIPv6DataProviderMatching() {
		return array(
			'empty address' => array('::', '::'),
			'empty with netmask in list' => array('::', '::/0'),
			'empty with netmask 0 and host-bits set in list' => array('::', '::123/0'),
			'localhost' => array('::1', '::1'),
			'localhost with leading zero blocks' => array('::1', '0:0::1'),
			'host with submask /128' => array('::1', '0:0::1/128'),
			'/16 subnet' => array('1234::1', '1234:5678::/16'),
			'/126 subnet' => array('1234:5678::3', '1234:5678::/126'),
			'/126 subnet with host-bits in list set' => array('1234:5678::3', '1234:5678::2/126'),
			'list with IPv4/IPv6 addresses' => array('1234:5678::3', '::1, 127.0.0.1, 1234:5678::/126, 192.168.1.1'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpIPv6DataProviderMatching
	 */
	public function cmpIPv6ReturnsTrueForMatchingAddress($ip, $list) {
		$this->assertTrue(t3lib_div::cmpIPv6($ip, $list));
	}

	/**
	 * Data provider for cmpIPv6ReturnsFalseForNotMatchingAddress
	 *
	 * @return array Data sets
	 */
	public static function cmpIPv6DataProviderNotMatching() {
		return array(
			'empty against localhost' => array('::', '::1'),
			'empty against localhost with /128 netmask' => array('::', '::1/128'),
			'localhost against different host' => array('::1', '::2'),
			'localhost against host with prior bits set' => array('::1', '::1:1'),
			'host against different /17 subnet' => array('1234::1', '1234:f678::/17'),
			'host against different /127 subnet' => array('1234:5678::3', '1234:5678::/127'),
			'host against IPv4 address list' => array('1234:5678::3', '127.0.0.1, 192.168.1.1'),
			'host against mixed list with IPv6 host in different subnet' => array('1234:5678::3', '::1, 1234:5678::/127'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpIPv6DataProviderNotMatching
	 */
	public function cmpIPv6ReturnsFalseForNotMatchingAddress($ip, $list) {
		$this->assertFalse(t3lib_div::cmpIPv6($ip, $list));
	}

	///////////////////////////////
	// Tests concerning IPv6Hex2Bin
	///////////////////////////////

	/**
	 * Data provider for IPv6Hex2BinReturnsCorrectBinaryHosts
	 *
	 * @return array Data sets
	 */
	public static function IPv6Hex2BinDataProviderCorrectlyConverted() {
		return array(
			'empty 1' => array('::', str_pad('', 16, "\x00")),
			'empty 2, already normalized' => array('0000:0000:0000:0000:0000:0000:0000:0000', str_pad('', 16, "\x00")),
			'empty 3, already normalized' => array('0102:0304:0000:0000:0000:0000:0506:0078', "\x01\x02\x03\x04" . str_pad('', 8, "\x00") . "\x05\x06\x00\x78"),
			'expansion in middle 1' => array('1::2', "\x00\x01" . str_pad('', 12, "\x00") . "\x00\x02"),
			'expansion in middle 2' => array('beef::fefa', "\xbe\xef" . str_pad('', 12, "\x00") . "\xfe\xfa"),
		);
	}

	/**
	 * @test
	 * @dataProvider IPv6Hex2BinDataProviderCorrectlyConverted
	 */
	public function IPv6Hex2BinReturnsCorrectBinaryHosts($inputIP, $binary) {
		$this->assertTrue(t3lib_div::IPv6Hex2Bin($inputIP) === $binary);
	}

	/////////////////////////////////
	// Tests concerning normalizeIPv6
	/////////////////////////////////

	/**
	 * Data provider for normalizeIPv6ReturnsCorrectlyNormalizedFormat
	 *
	 * @return array Data sets
	 */
	public static function normalizeIPv6DataProviderCorrectlyNormalized() {
		return array(
			'empty' => array('::', '0000:0000:0000:0000:0000:0000:0000:0000'),
			'localhost' => array('::1', '0000:0000:0000:0000:0000:0000:0000:0001'),
			'some address on right side' => array('::F0F', '0000:0000:0000:0000:0000:0000:0000:0F0F'),
			'expansion in middle 1' => array('1::2', '0001:0000:0000:0000:0000:0000:0000:0002'),
			'expansion in middle 2' => array('1:2::3', '0001:0002:0000:0000:0000:0000:0000:0003'),
			'expansion in middle 3' => array('1::2:3', '0001:0000:0000:0000:0000:0000:0002:0003'),
			'expansion in middle 4' => array('1:2::3:4:5', '0001:0002:0000:0000:0000:0003:0004:0005'),
		);
	}

	/**
	 * @test
	 * @dataProvider normalizeIPv6DataProviderCorrectlyNormalized
	 */
	public function normalizeIPv6ReturnsCorrectlyNormalizedFormat($inputIP, $normalized) {
		$this->assertTrue(t3lib_div::normalizeIPv6($inputIP) === $normalized);
	}

	///////////////////////////////
	// Tests concerning validIP
	///////////////////////////////

	/**
	 * Data provider for checkValidIpReturnsTrueForValidIp
	 *
	 * @return array Data sets
	 */
	public static function validIpDataProvider() {
		return array(
			'0.0.0.0' => array('0.0.0.0'),
			'private IPv4 class C' => array('192.168.0.1'),
			'private IPv4 class A' => array('10.0.13.1'),
			'private IPv6' => array('fe80::daa2:5eff:fe8b:7dfb'),
		);
	}

	/**
	 * @test
	 * @dataProvider validIpDataProvider
	 */
	public function validIpReturnsTrueForValidIp($ip) {
		$this->assertTrue(t3lib_div::validIP($ip));
	}

	/**
	 * Data provider for checkValidIpReturnsFalseForInvalidIp
	 *
	 * @return array Data sets
	 */
	public static function invalidIpDataProvider() {
		return array(
			'null' => array(null),
			'zero' => array(0),
			'string' => array('test'),
			'string empty' => array(''),
			'string null' => array('null'),
			'out of bounds IPv4' => array('300.300.300.300'),
			'dotted decimal notation with only two dots' => array('127.0.1'),
		);
	}

	/**
	 * @test
	 * @dataProvider invalidIpDataProvider
	 */
	public function validIpReturnsFalseForInvalidIp($ip) {
		$this->assertFalse(t3lib_div::validIP($ip));
	}

	///////////////////////////////
	// Tests concerning cmpFQDN
	///////////////////////////////

	/**
	 * Data provider for cmpFqdnReturnsTrue
	 *
	 * @return array Data sets
	 */
	public static function cmpFqdnValidDataProvider() {
		return array(
			'localhost should usually resolve, IPv4' => array('127.0.0.1', '*'),
			'localhost should usually resolve, IPv6' => array('::1', '*'),
				// other testcases with resolving not possible since it would
				// require a working IPv4/IPv6-connectivity
			'aaa.bbb.ccc.ddd.eee, full' => array('aaa.bbb.ccc.ddd.eee', 'aaa.bbb.ccc.ddd.eee'),
			'aaa.bbb.ccc.ddd.eee, wildcard first' => array('aaa.bbb.ccc.ddd.eee', '*.ccc.ddd.eee'),
			'aaa.bbb.ccc.ddd.eee, wildcard last' => array('aaa.bbb.ccc.ddd.eee', 'aaa.bbb.ccc.*'),
			'aaa.bbb.ccc.ddd.eee, wildcard middle' => array('aaa.bbb.ccc.ddd.eee', 'aaa.*.eee'),
			'list-matches, 1' => array('aaa.bbb.ccc.ddd.eee', 'xxx, yyy, zzz, aaa.*.eee'),
			'list-matches, 2' => array('aaa.bbb.ccc.ddd.eee', '127:0:0:1,,aaa.*.eee,::1'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpFqdnValidDataProvider
	 */
	public function cmpFqdnReturnsTrue($baseHost, $list) {
		$this->assertTrue(t3lib_div::cmpFQDN($baseHost, $list));
	}

	/**
	 * Data provider for cmpFqdnReturnsFalse
	 *
	 * @return array Data sets
	 */
	public static function cmpFqdnInvalidDataProvider() {
		return array(
			'num-parts of hostname to check can only be less or equal than hostname, 1' => array('aaa.bbb.ccc.ddd.eee', 'aaa.bbb.ccc.ddd.eee.fff'),
			'num-parts of hostname to check can only be less or equal than hostname, 2' => array('aaa.bbb.ccc.ddd.eee', 'aaa.*.bbb.ccc.ddd.eee'),
		);
	}

	/**
	 * @test
	 * @dataProvider cmpFqdnInvalidDataProvider
	 */
	public function cmpFqdnReturnsFalse($baseHost, $list) {
		$this->assertFalse(t3lib_div::cmpFQDN($baseHost, $list));
	}


	///////////////////////////////
	// Tests concerning testInt
	///////////////////////////////

	/**
	 * Data provider for testIntReturnsTrue
	 *
	 * @return array Data sets
	 */
	public function functionTestIntValidDataProvider() {
		return array(
			'int' => array(32425),
			'negative int' => array(-32425),
			'largest int' => array(PHP_INT_MAX),
			'int as string' => array('32425'),
			'negative int as string' => array('-32425'),
			'zero' => array(0),
			'zero as string' => array('0'),
		);
	}

	/**
	 * @test
	 * @dataProvider functionTestIntValidDataProvider
	 */
	public function testIntReturnsTrue($int) {
		$this->assertTrue(t3lib_div::testInt($int));
	}

	/**
	 * Data provider for testIntReturnsFalse
	 *
	 * @return array Data sets
	 */
	public function functionTestIntInvalidDataProvider() {
		return array(
			'int as string with leading zero' => array('01234'),
			'positive int as string with plus modifier' => array('+1234'),
			'negative int as string with leading zero' => array('-01234'),
			'largest int plus one' => array(PHP_INT_MAX + 1),
			'string' => array('testInt'),
			'empty string' => array(''),
			'int in string' => array('5 times of testInt'),
			'int as string with space after' => array('5 '),
			'int as string with space before' => array(' 5'),
			'int as string with many spaces before' => array('     5'),
			'float' => array(3.14159),
			'float as string' => array('3.14159'),
			'float as string only a dot' => array('10.'),
			'float as string trailing zero would evaluate to int 10' => array('10.0'),
			'float as string trailing zeros	 would evaluate to int 10' => array('10.00'),
			'null' => array(NULL),
			'empty array' => array(array()),
			'int in array' => array(array(32425)),
			'int as string in array' => array(array('32425')),
		);
	}

	/**
	 * @test
	 * @dataProvider functionTestIntInvalidDataProvider
	 */
	public function testIntReturnsFalse($int) {
		$this->assertFalse(t3lib_div::testInt($int));
	}


	///////////////////////////////
	// Tests concerning isFirstPartOfStr
	///////////////////////////////

	/**
	 * Data provider for isFirstPartOfStrReturnsTrueForMatchingFirstParts
	 *
	 * @return array
	 */
	public function isFirstPartOfStrReturnsTrueForMatchingFirstPartDataProvider() {
		return array(
			'match first part of string' => array('hello world', 'hello'),
			'match whole string' => array('hello', 'hello'),
			'integer is part of string with same number' => array('24', 24),
			'string is part of integer with same number' => array(24, '24'),
			'integer is part of string starting with same number' => array('24 beer please', 24),
		);
	}

	/**
	 * @test
	 * @dataProvider isFirstPartOfStrReturnsTrueForMatchingFirstPartDataProvider
	 */
	public function isFirstPartOfStrReturnsTrueForMatchingFirstPart($string, $part) {
		$this->assertTrue(t3lib_div::isFirstPartOfStr($string, $part));
	}

	/**
	 * Data provider for checkIsFirstPartOfStrReturnsFalseForNotMatchingFirstParts
	 *
	 * @return array
	 */
	public function isFirstPartOfStrReturnsFalseForNotMatchingFirstPartDataProvider() {
		return array(
			'no string match' => array('hello', 'bye'),
			'no case sensitive string match' => array('hello world', 'Hello'),
			'array is not part of string' => array('string', array()),
			'string is not part of array' => array(array(), 'string'),
			'null is not part of string' => array('string', NULL),
			'string is not part of array' => array(NULL, 'string'),
			'null is not part of array' => array(array(), NULL),
			'array is not part of string' => array(NULL, array()),
			'empty string is not part of empty string' => array('', ''),
			'null is not part of empty string' => array('', NULL),
			'false is not part of empty string' => array('', FALSE),
			'empty string is not part of null' => array(NULL, ''),
			'empty string is not part of false' => array(FALSE, ''),
			'empty string is not part of zero integer' => array(0, ''),
			'zero integer is not part of null' => array(NULL, 0),
			'zero integer is not part of empty string' => array('', 0),
		);
	}

	/**
	 * @test
	 * @dataProvider isFirstPartOfStrReturnsFalseForNotMatchingFirstPartDataProvider
	 */
	public function isFirstPartOfStrReturnsFalseForNotMatchingFirstPart($string, $part) {
		$this->assertFalse(t3lib_div::isFirstPartOfStr($string, $part));
	}


	///////////////////////////////
	// Tests concerning splitCalc
	///////////////////////////////

	/**
	 * Data provider for splitCalc
	 *
	 * @return array expected values, arithmetic expression
	 */
	public function splitCalcDataProvider() {
		return array(
			'empty string returns empty array' => array(
				array(),
				'',
			),
			'number without operator returns array with plus and number' => array(
				array(array('+', 42)),
				'42',
			),
			'two numbers with asterisk return first number with plus and second number with asterisk' => array(
				array(array('+', 42), array('*', 31)),
				'42 * 31',
			),
		);
	}

	/**
	 * @test
	 * @dataProvider splitCalcDataProvider
	 */
	public function splitCalcCorrectlySplitsExpression($expected, $expression) {
		$this->assertEquals($expected, t3lib_div::splitCalc($expression, '+-*/'));
	}


	//////////////////////////////////
	// Tests concerning calcPriority
	//////////////////////////////////

	/**
	 * Data provider for calcPriority
	 *
	 * @return array expected values, arithmetic expression
	 */
	public function calcPriorityDataProvider() {
		return array(
			'add' => array(9, '6 + 3'),
			'substract with positive result' => array(3, '6 - 3'),
			'substract with negative result' => array(-3, '3 - 6'),
			'multiply' => array(6, '2 * 3'),
			'divide' => array(2.5, '5 / 2'),
			'modulus' => array(1, '5 % 2'),
			'power' => array(8, '2 ^ 3'),
			'three operands with non integer result' => array(6.5, '5 + 3 / 2'),
			'three operands with power' => array(14, '5 + 3 ^ 2'),
			'three operads with modulus' => array(4, '5 % 2 + 3'),
			'four operands' => array(3, '2 + 6 / 2 - 2'),
		);
	}

	/**
	 * @test
	 * @dataProvider calcPriorityDataProvider
	 */
	public function calcPriorityCorrectlyCalculatesExpression($expected, $expression) {
		$this->assertEquals($expected, t3lib_div::calcPriority($expression));
	}


	//////////////////////////////////
	// Tests concerning calcPriority
	//////////////////////////////////

	/**
	 * Data provider for valid validEmail's
	 *
	 * @return array Valid email addresses
	 */
	public function validEmailValidDataProvider() {
		return array(
			'short mail address' => array('a@b.c'),
			'simple mail address' => array('test@example.com'),
			'uppercase characters' => array('QWERTYUIOPASDFGHJKLZXCVBNM@QWERTYUIOPASDFGHJKLZXCVBNM.NET'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6 and 5.3.2 but fails with 5.3.0 on windows
			// 'equal sign in local part' => array('test=mail@example.com'),
			'dash in local part' => array('test-mail@example.com'),
			'plus in local part' => array('test+mail@example.com'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6 and 5.3.2 but fails with 5.3.0 on windows
			// 'question mark in local part' => array('test?mail@example.com'),
			'slash in local part' => array('foo/bar@example.com'),
			'hash in local part' => array('foo#bar@example.com'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6 and 5.3.2 but fails with 5.3.0 on windows
			// 'dot in local part' => array('firstname.lastname@employee.2something.com'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6, but not ok with 5.3.2
			// 'dash as local part' => array('-@foo.com'),
		);
	}

	/**
	 * @test
	 * @dataProvider validEmailValidDataProvider
	 */
	public function validEmailReturnsTrueForValidMailAddress($address) {
		$this->assertTrue(t3lib_div::validEmail($address));
	}

	/**
	 * Data provider for invalid validEmail's
	 *
	 * @return array Invalid email addresses
	 */
	public function validEmailInvalidDataProvider() {
		return array(
			'@ sign only' => array('@'),
			'duplicate @' => array('test@@example.com'),
			'duplicate @ combined with further special characters in local part' => array('test!.!@#$%^&*@example.com'),
			'opening parenthesis in local part' => array('foo(bar@example.com'),
			'closing parenthesis in local part' => array('foo)bar@example.com'),
			'opening square bracket in local part' => array('foo[bar@example.com'),
			'closing square bracket as local part' => array(']@example.com'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6, but not ok with 5.3.2
			// 'top level domain only' => array('test@com'),
			'dash as second level domain' => array('foo@-.com'),
			'domain part starting with dash' => array('foo@-foo.com'),
			'domain part ending with dash' => array('foo@foo-.com'),
			'number as top level domain' => array('foo@bar.123'),
				// Fix / change if TYPO3 php requirement changed: Address not ok with 5.2.6, but ok with 5.3.2 (?)
			// 'dash as top level domain' => array('foo@bar.-'),
			'dot at beginning of domain part' => array('test@.com'),
				// Fix / change if TYPO3 php requirement changed: Address ok with 5.2.6, but not ok with 5.3.2
			// 'local part ends with dot' => array('e.x.a.m.p.l.e.@example.com'),
			'trailing whitespace' => array('test@example.com '),
			'trailing carriage return' => array('test@example.com' . CR),
			'trailing linefeed' => array('test@example.com' . LF),
			'trailing carriage return linefeed' => array('test@example.com' . CRLF),
			'trailing tab' => array('test@example.com' . TAB),
		);
	}

	/**
	 * @test
	 * @dataProvider validEmailInvalidDataProvider
	 */
	public function validEmailReturnsFalseForInvalidMailAddress($address) {
		$this->assertFalse(t3lib_div::validEmail($address));
	}


	//////////////////////////////////
	// Tests concerning intExplode
	//////////////////////////////////

	/**
	 * @test
	 */
	public function intExplodeConvertsStringsToInteger() {
		$testString = '1,foo,2';
		$expectedArray = array(1, 0, 2);
		$actualArray = t3lib_div::intExplode(',', $testString);

		$this->assertEquals($expectedArray, $actualArray);
	}


	//////////////////////////////////
	// Tests concerning revExplode
	//////////////////////////////////

	/**
	 * @test
	 */
	public function revExplodeExplodesString() {
		$testString = 'my:words:here';
		$expectedArray = array('my:words', 'here');
		$actualArray = t3lib_div::revExplode(':', $testString, 2);

		$this->assertEquals($expectedArray, $actualArray);
	}


	//////////////////////////////////
	// Tests concerning trimExplode
	//////////////////////////////////

	/**
	 * @test
	 */
	public function checkTrimExplodeTrimsSpacesAtElementStartAndEnd() {
		$testString = ' a , b , c ,d ,,  e,f,';
		$expectedArray = array('a', 'b', 'c', 'd', '', 'e', 'f', '');
		$actualArray = t3lib_div::trimExplode(',', $testString);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeRemovesNewLines() {
		$testString = ' a , b , ' . LF . ' ,d ,,  e,f,';
		$expectedArray = array('a', 'b', 'd', 'e', 'f');
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeRemovesEmptyElements() {
		$testString = 'a , b , c , ,d ,, ,e,f,';
		$expectedArray = array('a', 'b', 'c', 'd', 'e', 'f');
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeKeepsRemainingResultsWithEmptyItemsAfterReachingLimitWithPositiveParameter() {
		$testString = ' a , b , c , , d,, ,e ';
		$expectedArray = array('a', 'b', 'c,,d,,,e');
			// Limiting returns the rest of the string as the last element
		$actualArray = t3lib_div::trimExplode(',', $testString, FALSE, 3);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeKeepsRemainingResultsWithoutEmptyItemsAfterReachingLimitWithPositiveParameter() {
		$testString = ' a , b , c , , d,, ,e ';
		$expectedArray = array('a', 'b', 'c,d,e');
			// Limiting returns the rest of the string as the last element
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE, 3);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeKeepsRamainingResultsWithEmptyItemsAfterReachingLimitWithNegativeParameter() {
		$testString = ' a , b , c , d, ,e, f , , ';
		$expectedArray = array('a', 'b', 'c', 'd', '', 'e');
			// limiting returns the rest of the string as the last element
		$actualArray = t3lib_div::trimExplode(',', $testString, FALSE, -3);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeKeepsRamainingResultsWithoutEmptyItemsAfterReachingLimitWithNegativeParameter() {
		$testString = ' a , b , c , d, ,e, f , , ';
		$expectedArray = array('a', 'b', 'c');
			// Limiting returns the rest of the string as the last element
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE, -3);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeReturnsExactResultsWithoutReachingLimitWithPositiveParameter() {
		$testString = ' a , b , , c , , , ';
		$expectedArray = array('a', 'b', 'c');
			// Limiting returns the rest of the string as the last element
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE, 4);

		$this->assertEquals($expectedArray, $actualArray);
	}

	/**
	 * @test
	 */
	public function checkTrimExplodeKeepsZeroAsString() {
		$testString = 'a , b , c , ,d ,, ,e,f, 0 ,';
		$expectedArray = array('a', 'b', 'c', 'd', 'e', 'f', '0');
		$actualArray = t3lib_div::trimExplode(',', $testString, TRUE);

		$this->assertEquals($expectedArray, $actualArray);
	}


	//////////////////////////////////
	// Tests concerning removeArrayEntryByValue
	//////////////////////////////////

	/**
	 * @test
	 */
	public function checkRemoveArrayEntryByValueRemovesEntriesFromOneDimensionalArray() {
		$inputArray = array(
			'0' => 'test1',
			'1' => 'test2',
			'2' => 'test3',
			'3' => 'test2',
		);
		$compareValue = 'test2';
		$expectedResult = array(
			'0' => 'test1',
			'2' => 'test3',
		);
		$actualResult = t3lib_div::removeArrayEntryByValue($inputArray, $compareValue);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function checkRemoveArrayEntryByValueRemovesEntriesFromMultiDimensionalArray() {
		$inputArray = array(
			'0' => 'foo',
			'1' => array(
				'10' => 'bar',
			),
			'2' => 'bar',
		);
		$compareValue = 'bar';
		$expectedResult = array(
			'0' => 'foo',
			'1' => array(),
		);
		$actualResult = t3lib_div::removeArrayEntryByValue($inputArray, $compareValue);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function checkRemoveArrayEntryByValueRemovesEntryWithEmptyString() {
		$inputArray = array(
			'0' => 'foo',
			'1' => '',
			'2' => 'bar',
		);
		$compareValue = '';
		$expectedResult = array(
			'0' => 'foo',
			'2' => 'bar',
		);
		$actualResult = t3lib_div::removeArrayEntryByValue($inputArray, $compareValue);
		$this->assertEquals($expectedResult, $actualResult);
	}

	//////////////////////////////////
	// Tests concerning getBytesFromSizeMeasurement
	//////////////////////////////////

	/**
	 * Data provider for getBytesFromSizeMeasurement
	 *
	 * @return array expected value, input string
	 */
	public function getBytesFromSizeMeasurementDataProvider() {
		return array(
			'100 kilo Bytes' => array('102400', '100k'),
			'100 mega Bytes' => array('104857600', '100m'),
			'100 giga Bytes' => array('107374182400', '100g'),
		);
	}

	/**
	 * @test
	 * @dataProvider getBytesFromSizeMeasurementDataProvider
	 */
	public function getBytesFromSizeMeasurementCalculatesCorrectByteValue($expected, $byteString) {
		$this->assertEquals($expected, t3lib_div::getBytesFromSizeMeasurement($byteString));
	}


	//////////////////////////////////
	// Tests concerning getIndpEnv
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getIndpEnvTypo3SitePathReturnNonEmptyString() {
		$this->assertTrue(strlen(t3lib_div::getIndpEnv('TYPO3_SITE_PATH')) >= 1);
	}

	/**
	 * @test
	 */
	public function getIndpEnvTypo3SitePathReturnsStringStartingWithSlash() {
		$result = t3lib_div::getIndpEnv('TYPO3_SITE_PATH');
		$this->assertEquals('/', $result[0]);
	}

	/**
	 * @test
	 */
	public function getIndpEnvTypo3SitePathReturnsStringEndingWithSlash() {
		$result = t3lib_div::getIndpEnv('TYPO3_SITE_PATH');
		$this->assertEquals('/', $result[strlen($result) - 1]);
	}

	/**
	 * @return array
	 */
	public static function hostnameAndPortDataProvider() {
		return array(
			'localhost ipv4 without port' => array('127.0.0.1', '127.0.0.1', ''),
			'localhost ipv4 with port' => array('127.0.0.1:81', '127.0.0.1', '81'),
			'localhost ipv6 without port' => array('[::1]', '[::1]', ''),
			'localhost ipv6 with port' => array('[::1]:81', '[::1]', '81'),
			'ipv6 without port' => array('[2001:DB8::1]', '[2001:DB8::1]', ''),
			'ipv6 with port' => array('[2001:DB8::1]:81', '[2001:DB8::1]', '81'),
			'hostname without port' => array('lolli.did.this', 'lolli.did.this', ''),
			'hostname with port' => array('lolli.did.this:42', 'lolli.did.this', '42'),
		);
	}

	/**
	 * @test
	 * @dataProvider hostnameAndPortDataProvider
	 */
	public function getIndpEnvTypo3HostOnlyParsesHostnamesAndIpAdresses($httpHost, $expectedIp) {
		$_SERVER['HTTP_HOST'] = $httpHost;
		$this->assertEquals($expectedIp, t3lib_div::getIndpEnv('TYPO3_HOST_ONLY'));
	}

	/**
	 * @test
	 * @dataProvider hostnameAndPortDataProvider
	 */
	public function getIndpEnvTypo3PortParsesHostnamesAndIpAdresses($httpHost, $dummy, $expectedPort) {
		$_SERVER['HTTP_HOST'] = $httpHost;
		$this->assertEquals($expectedPort, t3lib_div::getIndpEnv('TYPO3_PORT'));
	}


	//////////////////////////////////
	// Tests concerning underscoredToUpperCamelCase
	//////////////////////////////////

	/**
	 * Data provider for underscoredToUpperCamelCase
	 *
	 * @return array expected, input string
	 */
	public function underscoredToUpperCamelCaseDataProvider() {
		return array(
			'single word' => array('Blogexample', 'blogexample'),
			'multiple words' => array('BlogExample', 'blog_example'),
		);
	}

	/**
	 * @test
	 * @dataProvider underscoredToUpperCamelCaseDataProvider
	 */
	public function underscoredToUpperCamelCase($expected, $inputString) {
		$this->assertEquals($expected, t3lib_div::underscoredToUpperCamelCase($inputString));
	}


	//////////////////////////////////
	// Tests concerning underscoredToLowerCamelCase
	//////////////////////////////////

	/**
	 * Data provider for underscoredToLowerCamelCase
	 *
	 * @return array expected, input string
	 */
	public function underscoredToLowerCamelCaseDataProvider() {
		return array(
			'single word' => array('minimalvalue', 'minimalvalue'),
			'multiple words' => array('minimalValue', 'minimal_value'),
		);
	}

	/**
	 * @test
	 * @dataProvider underscoredToLowerCamelCaseDataProvider
	 */
	public function underscoredToLowerCamelCase($expected, $inputString) {
		$this->assertEquals($expected, t3lib_div::underscoredToLowerCamelCase($inputString));
	}

	//////////////////////////////////
	// Tests concerning camelCaseToLowerCaseUnderscored
	//////////////////////////////////

	/**
	 * Data provider for camelCaseToLowerCaseUnderscored
	 *
	 * @return array expected, input string
	 */
	public function camelCaseToLowerCaseUnderscoredDataProvider() {
		return array(
			'single word' => array('blogexample', 'blogexample'),
			'single word starting upper case' => array('blogexample', 'Blogexample'),
			'two words starting lower case' => array('minimal_value', 'minimalValue'),
			'two words starting upper case' => array('blog_example', 'BlogExample'),
		);
	}

	/**
	 * @test
	 * @dataProvider camelCaseToLowerCaseUnderscoredDataProvider
	 */
	public function camelCaseToLowerCaseUnderscored($expected, $inputString) {
		$this->assertEquals($expected, t3lib_div::camelCaseToLowerCaseUnderscored($inputString));
	}


	//////////////////////////////////
	// Tests concerning lcFirst
	//////////////////////////////////

	/**
	 * Data provider for lcFirst
	 *
	 * @return array expected, input string
	 */
	public function lcfirstDataProvider() {
		return array(
			'single word' => array('blogexample', 'blogexample'),
			'single Word starting upper case' => array('blogexample', 'Blogexample'),
			'two words' => array('blogExample', 'BlogExample'),
		);
	}

	/**
	 * @test
	 * @dataProvider lcfirstDataProvider
	 */
	public function lcFirst($expected, $inputString) {
		$this->assertEquals($expected, t3lib_div::lcfirst($inputString));
	}


	//////////////////////////////////
	// Tests concerning encodeHeader
	//////////////////////////////////

	/**
	 * @test
	 */
	public function encodeHeaderEncodesWhitespacesInQuotedPrintableMailHeader() {
		$this->assertEquals(
			'=?utf-8?Q?We_test_whether_the_copyright_character_=C2=A9_is_encoded_correctly?=',
			t3lib_div::encodeHeader(
				"We test whether the copyright character \xc2\xa9 is encoded correctly",
				'quoted-printable',
				'utf-8'
			)
		);
	}

	/**
	 * @test
	 */
	public function encodeHeaderEncodesQuestionmarksInQuotedPrintableMailHeader() {
		$this->assertEquals(
			'=?utf-8?Q?Is_the_copyright_character_=C2=A9_really_encoded_correctly=3F_Really=3F?=',
			t3lib_div::encodeHeader(
				"Is the copyright character \xc2\xa9 really encoded correctly? Really?",
				'quoted-printable',
				'utf-8'
			)
		);
	}


	//////////////////////////////////
	// Tests concerning isValidUrl
	//////////////////////////////////

	/**
	 * Data provider for valid isValidUrl's
	 *
	 * @return array Valid ressource
	 */
	public function validUrlValidRessourceDataProvider() {
		return array(
			'http' => array('http://www.example.org/'),
			'http without trailing slash' => array('http://qwe'),
			'http directory with trailing slash' => array('http://www.example/img/dir/'),
			'http directory without trailing slash' => array('http://www.example/img/dir'),
			'http index.html' => array('http://example.com/index.html'),
			'http index.php' => array('http://www.example.com/index.php'),
			'http test.png' => array('http://www.example/img/test.png'),
			'http username password querystring and ancher' => array('https://user:pw@www.example.org:80/path?arg=value#fragment'),
			'file' => array('file:///tmp/test.c'),
			'file directory' => array('file://foo/bar'),
			'ftp directory' => array('ftp://ftp.example.com/tmp/'),
			'mailto' => array('mailto:foo@bar.com'),
			'news' => array('news:news.php.net'),
			'telnet'=> array('telnet://192.0.2.16:80/'),
			'ldap' => array('ldap://[2001:db8::7]/c=GB?objectClass?one'),
		);
	}

	/**
	 * @test
	 * @dataProvider validUrlValidRessourceDataProvider
	 */
	public function validURLReturnsTrueForValidRessource($url) {
		$this->assertTrue(t3lib_div::isValidUrl($url));
	}

	/**
	 * Data provider for invalid isValidUrl's
	 *
	 * @return array Invalid ressource
	 */
	public function isValidUrlInvalidRessourceDataProvider() {
		return array(
			'http missing colon' => array('http//www.example/wrong/url/'),
			'http missing slash' => array('http:/www.example'),
			'hostname only' => array('www.example.org/'),
			'file missing protocol specification' => array('/tmp/test.c'),
			'slash only' => array('/'),
			'string http://' => array('http://'),
			'string http:/' => array('http:/'),
			'string http:' => array('http:'),
			'string http' => array('http'),
			'empty string' => array(''),
			'string -1' => array('-1'),
			'string array()' => array('array()'),
			'random string' => array('qwe'),
		);
	}

	/**
	 * @test
	 * @dataProvider isValidUrlInvalidRessourceDataProvider
	 */
	public function validURLReturnsFalseForInvalidRessoure($url) {
		$this->assertFalse(t3lib_div::isValidUrl($url));
	}


	//////////////////////////////////
	// Tests concerning isOnCurrentHost
	//////////////////////////////////

	/**
	 * @test
	 */
	public function isOnCurrentHostReturnsTrueWithCurrentHost() {
		$testUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		$this->assertTrue(t3lib_div::isOnCurrentHost($testUrl));
	}

	/**
	 * Data provider for invalid isOnCurrentHost's
	 *
	 * @return array Invalid Hosts
	 */
	public function checkisOnCurrentHostInvalidHosts() {
		return array(
			'empty string' => array(''),
			'arbitrary string' => array('arbitrary string'),
			'localhost IP' => array('127.0.0.1'),
			'relative path' => array('./relpath/file.txt'),
			'absolute path' => array('/abspath/file.txt?arg=value'),
			'differnt host' => array(t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '.example.org'),
		);
	}


	////////////////////////////////////////
	// Tests concerning sanitizeLocalUrl
	////////////////////////////////////////

	/**
	 * Data provider for valid sanitizeLocalUrl's
	 *
	 * @return array Valid url
	 */
	public function sanitizeLocalUrlValidUrlDataProvider() {
		$subDirectory = t3lib_div::getIndpEnv('TYPO3_SITE_PATH');
		$typo3SiteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		$typo3RequestHost = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST');

		return array(
			'alt_intro.php' => array('alt_intro.php'),
			'alt_intro.php?foo=1&bar=2' => array('alt_intro.php?foo=1&bar=2'),
			$subDirectory . 'typo3/alt_intro.php' => array($subDirectory . 'typo3/alt_intro.php'),
			$subDirectory . 'index.php' => array($subDirectory . 'index.php'),
			'../index.php' => array('../index.php'),
			'../typo3/alt_intro.php' => array('../typo3/alt_intro.php'),
			'../~userDirectory/index.php' => array('../~userDirectory/index.php'),
			'../typo3/mod.php?var1=test-case&var2=~user' => array('../typo3/mod.php?var1=test-case&var2=~user'),
			PATH_site . 'typo3/alt_intro.php' => array(PATH_site . 'typo3/alt_intro.php'),
			$typo3SiteUrl . 'typo3/alt_intro.php' => array($typo3SiteUrl . 'typo3/alt_intro.php'),
			$typo3RequestHost . $subDirectory . '/index.php' => array($typo3RequestHost . $subDirectory . '/index.php'),
		);
	}

	/**
	 * @test
	 * @dataProvider sanitizeLocalUrlValidUrlDataProvider
	 */
	public function sanitizeLocalUrlAcceptsNotEncodedValidUrls($url) {
		$this->assertEquals($url, t3lib_div::sanitizeLocalUrl($url));
	}

	/**
	 * @test
	 * @dataProvider sanitizeLocalUrlValidUrlDataProvider
	 */
	public function sanitizeLocalUrlAcceptsEncodedValidUrls($url) {
		$this->assertEquals(rawurlencode($url), t3lib_div::sanitizeLocalUrl(rawurlencode($url)));
	}

	/**
	 * Data provider for invalid sanitizeLocalUrl's
	 *
	 * @return array Valid url
	 */
	public function sanitizeLocalUrlInvalidDataProvider() {
		return array(
			'empty string' => array(''),
			'http domain' => array('http://www.google.de/'),
			'https domain' => array('https://www.google.de/'),
			'relative path with XSS' => array('../typo3/whatever.php?argument=javascript:alert(0)'),
		);
	}

	/**
	 * @test
	 * @dataProvider sanitizeLocalUrlInvalidDataProvider
	 */
	public function sanitizeLocalUrlDeniesPlainInvalidUrls($url) {
		$this->assertEquals('', t3lib_div::sanitizeLocalUrl($url));
	}

	/**
	 * @test
	 * @dataProvider sanitizeLocalUrlInvalidDataProvider
	 */
	public function sanitizeLocalUrlDeniesEncodedInvalidUrls($url) {
		$this->assertEquals('', t3lib_div::sanitizeLocalUrl(rawurlencode($url)));
	}


	//////////////////////////////////////
	// Tests concerning arrayDiffAssocRecursive
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function arrayDiffAssocRecursiveHandlesOneDimensionalArrays() {
		$array1 = array(
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		);
		$array2 = array(
			'key1' => 'value1',
			'key3' => 'value3',
		);
		$expectedResult = array(
			'key2' => 'value2',
		);
		$actualResult = t3lib_div::arrayDiffAssocRecursive($array1, $array2);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function arrayDiffAssocRecursiveHandlesMultiDimensionalArrays() {
		$array1 = array(
			'key1' => 'value1',
			'key2' => array(
				'key21' => 'value21',
				'key22' => 'value22',
				'key23' => array(
					'key231' => 'value231',
					'key232' => 'value232',
				),
			),
		);
		$array2 = array(
			'key1' => 'value1',
			'key2' => array(
				'key21' => 'value21',
				'key23' => array(
					'key231' => 'value231',
				),
			),
		);
		$expectedResult = array(
			'key2' => array(
				'key22' => 'value22',
				'key23' => array(
					'key232' => 'value232',
				),
			),
		);
		$actualResult = t3lib_div::arrayDiffAssocRecursive($array1, $array2);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function arrayDiffAssocRecursiveHandlesMixedArrays() {
		$array1 = array(
			'key1' => array(
				'key11' => 'value11',
				'key12' => 'value12',
			),
			'key2' => 'value2',
			'key3' => 'value3',
		);
		$array2 = array(
			'key1' => 'value1',
			'key2' => array(
				'key21' => 'value21',
			),
		);
		$expectedResult = array(
			'key3' => 'value3',
		);
		$actualResult = t3lib_div::arrayDiffAssocRecursive($array1, $array2);
		$this->assertEquals($expectedResult, $actualResult);
	}


	//////////////////////////////////////
	// Tests concerning removeDotsFromTS
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function removeDotsFromTypoScriptSucceedsWithDottedArray() {
		$typoScript = array(
			'propertyA.' => array(
				'keyA.' => array(
					'valueA' => 1,
				),
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$expectedResult = array(
			'propertyA' => array(
				'keyA' => array(
					'valueA' => 1,
				),
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$this->assertEquals($expectedResult, t3lib_div::removeDotsFromTS($typoScript));
	}

	/**
	 * @test
	 */
	public function removeDotsFromTypoScriptOverridesSubArray() {
		$typoScript = array(
			'propertyA.' => array(
				'keyA' => 'getsOverridden',
				'keyA.' => array(
					'valueA' => 1,
				),
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$expectedResult = array(
			'propertyA' => array(
				'keyA' => array(
					'valueA' => 1,
				),
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$this->assertEquals($expectedResult, t3lib_div::removeDotsFromTS($typoScript));
	}

	/**
	 * @test
	 */
	public function removeDotsFromTypoScriptOverridesWithScalar() {
		$typoScript = array(
			'propertyA.' => array(
				'keyA.' => array(
					'valueA' => 1,
				),
				'keyA' => 'willOverride',
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$expectedResult = array(
			'propertyA' => array(
				'keyA' => 'willOverride',
				'keyB' => 2,
			),
			'propertyB' => 3,
		);

		$this->assertEquals($expectedResult, t3lib_div::removeDotsFromTS($typoScript));
	}

	//////////////////////////////////////
	// Tests concerning naturalKeySortRecursive
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function naturalKeySortRecursiveReturnsFalseIfInputIsNotAnArray() {
		$testValues = array(
			1,
			'string',
			FALSE
		);
		foreach($testValues as $testValue) {
			$this->assertFalse(t3lib_div::naturalKeySortRecursive($testValue));
		}
	}

	/**
	 * @test
	 */
	public function naturalKeySortRecursiveSortsOneDimensionalArrayByNaturalOrder() {
		$testArray = array(
			'bb' => 'bb',
			'ab' => 'ab',
			'123' => '123',
			'aaa' => 'aaa',
			'abc' => 'abc',
			'23' => '23',
			'ba' => 'ba',
			'bad' => 'bad',
			'2' => '2',
			'zap' => 'zap',
			'210' => '210'
		);
		$expectedResult = array(
			'2',
			'23',
			'123',
			'210',
			'aaa',
			'ab',
			'abc',
			'ba',
			'bad',
			'bb',
			'zap'
		);
		t3lib_div::naturalKeySortRecursive($testArray);
		$this->assertEquals($expectedResult, array_values($testArray));
	}

	/**
	 * @test
	 */
	public function naturalKeySortRecursiveSortsMultiDimensionalArrayByNaturalOrder() {
		$testArray = array(
			'2' => '2',
			'bb' => 'bb',
			'ab' => 'ab',
			'23' => '23',
			'aaa' => array(
				'bb' => 'bb',
				'ab' => 'ab',
				'123' => '123',
				'aaa' => 'aaa',
				'2' => '2',
				'abc' => 'abc',
				'ba' => 'ba',
				'23' => '23',
				'bad' => array(
					'bb' => 'bb',
					'ab' => 'ab',
					'123' => '123',
					'aaa' => 'aaa',
					'abc' => 'abc',
					'23' => '23',
					'ba' => 'ba',
					'bad' => 'bad',
					'2' => '2',
					'zap' => 'zap',
					'210' => '210'
				),
				'210' => '210',
				'zap' => 'zap'
			),
			'abc' => 'abc',
			'ba' => 'ba',
			'210' => '210',
			'bad' => 'bad',
			'123' => '123',
			'zap' => 'zap'
		);

		$expectedResult = array(
			'2',
			'23',
			'123',
			'210',
			'aaa',
			'ab',
			'abc',
			'ba',
			'bad',
			'bb',
			'zap'
		);
		t3lib_div::naturalKeySortRecursive($testArray);

		$this->assertEquals($expectedResult, array_values(array_keys($testArray['aaa']['bad'])));
		$this->assertEquals($expectedResult, array_values(array_keys($testArray['aaa'])));
		$this->assertEquals($expectedResult, array_values(array_keys($testArray)));
	}

	//////////////////////////////////////
	// Tests concerning get_dirs
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getDirsReturnsArrayOfDirectoriesFromGivenDirectory() {
		$path = PATH_t3lib;
		$directories = t3lib_div::get_dirs($path);

		$this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $directories);
	}

	/**
	 * @test
	 */
	public function getDirsReturnsStringErrorOnPathFailure() {
		$path = 'foo';
		$result = t3lib_div::get_dirs($path);
		$expectedResult = 'error';

		$this->assertEquals($expectedResult, $result);
	}


	//////////////////////////////////
	// Tests concerning hmac
	//////////////////////////////////

	/**
	 * @test
	 */
	public function hmacReturnsHashOfProperLength() {
		$hmac = t3lib_div::hmac('message');
		$this->assertTrue(!empty($hmac) && is_string($hmac));
		$this->assertTrue(strlen($hmac) == 40);
	}

	/**
	 * @test
	 */
	public function hmacReturnsEqualHashesForEqualInput() {
		$msg0 = 'message';
		$msg1 = 'message';
		$this->assertEquals(t3lib_div::hmac($msg0), t3lib_div::hmac($msg1));
	}

	/**
	 * @test
	 */
	public function hmacReturnsNoEqualHashesForNonEqualInput() {
		$msg0 = 'message0';
		$msg1 = 'message1';
		$this->assertNotEquals(t3lib_div::hmac($msg0), t3lib_div::hmac($msg1));
	}


	//////////////////////////////////
	// Tests concerning quoteJSvalue
	//////////////////////////////////

	/**
	 * Data provider for quoteJSvalueTest.
	 *
	 * @return array
	 */
	public function quoteJsValueDataProvider() {
		return array(
			'Immune characters are returned as is' => array(
				'._,',
				'._,',
			),
			'Alphanumerical characters are returned as is' => array(
				'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
				'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
			),
			'Angel brackets and ampersand are encoded' => array(
				'<>&',
				'\x3C\x3E\x26',
			),
			'Quotes and slashes are encoded' => array(
				'"\'\\/',
				'\x22\x27\x5C\x2F',
			),
			'Empty string stays empty' => array(
				'',
				'',
			),
			'Exclamation mark and space are properly encoded' => array(
				'Hello World!',
				'Hello\x20World\x21',
			),
			'Whitespaces are properly encoded' => array(
				TAB . LF . CR . ' ',
				'\x09\x0A\x0D\x20',
			),
			'Null byte is properly encoded' => array(
				chr(0),
				'\x00',
			),
			'Umlauts are properly encoded' => array(
				'ÜüÖöÄä',
				'\xDC\xFC\xD6\xF6\xC4\xE4',
			),
		);
	}

	/**
	 * @test
	 *
	 * @param string $input
	 * @param string $expected
	 *
	 * @dataProvider quoteJsValueDataProvider
	 */
	public function quoteJsValueTest($input, $expected) {
		$this->assertSame(
			'\'' . $expected . '\'',
			t3lib_div::quoteJSvalue($input)
		);
	}


	//////////////////////////////////
	// Tests concerning readLLfile
	//////////////////////////////////

	/**
	 * @test
	 */
	public function readLLfileHandlesLocallangXMLOverride() {
		$unique = uniqid('locallangXMLOverrideTest');

		$xml = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
			<T3locallang>
				<data type="array">
					<languageKey index="default" type="array">
						<label index="buttons.logout">EXIT</label>
					</languageKey>
				</data>
			</T3locallang>';

		$file = PATH_site . 'typo3temp/' . $unique . '.xml';
		t3lib_div::writeFileToTypo3tempDir($file, $xml);

			// Get default value
		$defaultLL = t3lib_div::readLLfile('EXT:lang/locallang_core.xml', 'default');

			// Set override file
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:lang/locallang_core.xml'][$unique] = $file;

			// Get override value
		$overrideLL = t3lib_div::readLLfile('EXT:lang/locallang_core.xml', 'default');

			// Clean up again
		unlink($file);

		$this->assertNotEquals($overrideLL['default']['buttons.logout'], '');
		$this->assertNotEquals($defaultLL['default']['buttons.logout'], $overrideLL['default']['buttons.logout']);
		$this->assertEquals($overrideLL['default']['buttons.logout'], 'EXIT');
	}


	///////////////////////////////
	// Tests concerning _GETset()
	///////////////////////////////

	/**
	 * @test
	 */
	public function getSetWritesArrayToGetSystemVariable() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		$getParameters = array('foo' => 'bar');
		t3lib_div::_GETset($getParameters);
		$this->assertSame($getParameters, $_GET);
	}

	/**
	 * @test
	 */
	public function getSetWritesArrayToGlobalsHttpGetVars() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		$getParameters = array('foo' => 'bar');
		t3lib_div::_GETset($getParameters);
		$this->assertSame($getParameters, $GLOBALS['HTTP_GET_VARS']);
	}

	/**
	 * @test
	 */
	public function getSetForArrayDropsExistingValues() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset(array('foo' => 'bar'));

		t3lib_div::_GETset(array('oneKey' => 'oneValue'));

		$this->assertEquals(
			array('oneKey' => 'oneValue'),
			$GLOBALS['HTTP_GET_VARS']
		);
	}

	/**
	 * @test
	 */
	public function getSetAssignsOneValueToOneKey() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset('oneValue', 'oneKey');

		$this->assertEquals(
			'oneValue',
			$GLOBALS['HTTP_GET_VARS']['oneKey']
		);
	}

	/**
	 * @test
	 */
	public function getSetForOneValueDoesNotDropUnrelatedValues() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset(array('foo' => 'bar'));
		t3lib_div::_GETset('oneValue', 'oneKey');

		$this->assertEquals(
			array('foo' => 'bar', 'oneKey' => 'oneValue'),
			$GLOBALS['HTTP_GET_VARS']
		);
	}

	/**
	 * @test
	 */
	public function getSetCanAssignsAnArrayToASpecificArrayElement() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset(array('childKey' => 'oneValue'), 'parentKey');

		$this->assertEquals(
			array('parentKey' => array('childKey' => 'oneValue')),
			$GLOBALS['HTTP_GET_VARS']
		);
	}

	/**
	 * @test
	 */
	public function getSetCanAssignAStringValueToASpecificArrayChildElement() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset('oneValue', 'parentKey|childKey');

		$this->assertEquals(
			array('parentKey' => array('childKey' => 'oneValue')),
			$GLOBALS['HTTP_GET_VARS']
		);
	}

	/**
	 * @test
	 */
	public function getSetCanAssignAnArrayToASpecificArrayChildElement() {
		$_GET = array();
		$GLOBALS['HTTP_GET_VARS'] = array();

		t3lib_div::_GETset(
			array('key1' => 'value1', 'key2' => 'value2'),
			'parentKey|childKey'
		);

		$this->assertEquals(
			array(
				'parentKey' => array(
					'childKey' => array('key1' => 'value1', 'key2' => 'value2')
				)
			),
			$GLOBALS['HTTP_GET_VARS']
		);
	}


	///////////////////////////
	// Tests concerning getUrl
	///////////////////////////

	/**
	 * @test
	 */
	public function getUrlWithAdditionalRequestHeadersProvidesHttpHeaderOnError() {
		$url = 'http://typo3.org/i-do-not-exist-' . time();

		$report = array();
		t3lib_div::getUrl(
			$url,
			0,
			array(),
			$report
		);
		$this->assertContains(
			'404',
			$report['message']
		);
	}

	/**
	 * @test
	 */
	public function getUrlProvidesWithoutAdditionalRequestHeadersHttpHeaderOnError() {
		$url = 'http://typo3.org/i-do-not-exist-' . time();

		$report = array();
		t3lib_div::getUrl(
			$url,
			0,
			FALSE,
			$report
		);
		$this->assertContains(
			'404',
			$report['message'],
			'Did not provide the HTTP response header when requesting a failing URL.'
		);
	}


	///////////////////////////////
	// Tests concerning fixPermissions
	///////////////////////////////

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsGroup() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissionsSetsGroup() tests not available on Windows');
		}
		if (!function_exists('posix_getegid')) {
			$this->markTestSkipped('Function posix_getegid() not available, fixPermissionsSetsGroup() tests skipped');
		}
		if (posix_getegid() === -1) {
			$this->markTestSkipped(
				'The fixPermissionsSetsGroup() is not available on Mac OS because posix_getegid() always returns -1 on Mac OS.'
			);
		}

			// Create and prepare test file
		$filename = PATH_site . 'typo3temp/' . uniqid('test_');
		t3lib_div::writeFileToTypo3tempDir($filename, '42');

		$currentGroupId = posix_getegid();

			// Set target group and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'] = $currentGroupId;
		$fixPermissionsResult = t3lib_div::fixPermissions($filename);

		clearstatcache();
		$resultFileGroup = filegroup($filename);
		unlink($filename);

		$this->assertEquals($resultFileGroup, $currentGroupId);
	}

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsPermissionsToFile() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test file
		$filename = PATH_site . 'typo3temp/' . uniqid('test_');
		t3lib_div::writeFileToTypo3tempDir($filename, '42');
		chmod($filename, 0742);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0660';
		$fixPermissionsResult = t3lib_div::fixPermissions($filename);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($filename)), 2);
		unlink($filename);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultFilePermissions, '0660');
	}

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsPermissionsToHiddenFile() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test file
		$filename = PATH_site . 'typo3temp/' . uniqid('.test_');
		t3lib_div::writeFileToTypo3tempDir($filename, '42');
		chmod($filename, 0742);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0660';
		$fixPermissionsResult = t3lib_div::fixPermissions($filename);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($filename)), 2);
		unlink($filename);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultFilePermissions, '0660');
	}

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsPermissionsToDirectory() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test directory
		$directory = PATH_site . 'typo3temp/' . uniqid('test_');
		t3lib_div::mkdir($directory);
		chmod($directory, 1551);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0770';
		$fixPermissionsResult = t3lib_div::fixPermissions($directory . '/');

			// Get actual permissions and clean up
		clearstatcache();
		$resultDirectoryPermissions = substr(decoct(fileperms($directory)), 1);
		t3lib_div::rmdir($directory);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultDirectoryPermissions, '0770');
	}

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsPermissionsToHiddenDirectory() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test directory
		$directory = PATH_site . 'typo3temp/' . uniqid('.test_');
		t3lib_div::mkdir($directory);
		chmod($directory, 1551);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0770';
		$fixPermissionsResult = t3lib_div::fixPermissions($directory);

			// Get actual permissions and clean up
		clearstatcache();
		$resultDirectoryPermissions = substr(decoct(fileperms($directory)), 1);
		t3lib_div::rmdir($directory);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultDirectoryPermissions, '0770');
	}

	/**
	 * @test
	 */
	public function fixPermissionsCorrectlySetsPermissionsRecursive() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test directory and file structure
		$baseDirectory = PATH_site . 'typo3temp/' . uniqid('test_');
		t3lib_div::mkdir($baseDirectory);
		chmod($baseDirectory, 1751);
		t3lib_div::writeFileToTypo3tempDir($baseDirectory . '/file', '42');
		chmod($baseDirectory . '/file', 0742);
		t3lib_div::mkdir($baseDirectory . '/foo');
		chmod($baseDirectory . '/foo', 1751);
		t3lib_div::writeFileToTypo3tempDir($baseDirectory . '/foo/file', '42');
		chmod($baseDirectory . '/foo/file', 0742);
		t3lib_div::mkdir($baseDirectory . '/.bar');
		chmod($baseDirectory . '/.bar', 1751);
			// Use this if writeFileToTypo3tempDir is fixed to create hidden files in subdirectories
		// t3lib_div::writeFileToTypo3tempDir($baseDirectory . '/.bar/.file', '42');
		// t3lib_div::writeFileToTypo3tempDir($baseDirectory . '/.bar/..file2', '42');
		touch($baseDirectory . '/.bar/.file', '42');
		chmod($baseDirectory . '/.bar/.file', 0742);
		touch($baseDirectory . '/.bar/..file2', '42');
		chmod($baseDirectory . '/.bar/..file2', 0742);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0660';
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0770';
		$fixPermissionsResult = t3lib_div::fixPermissions($baseDirectory, TRUE);

			// Get actual permissions
		clearstatcache();
		$resultBaseDirectoryPermissions = substr(decoct(fileperms($baseDirectory)), 1);
		$resultBaseFilePermissions = substr(decoct(fileperms($baseDirectory . '/file')), 2);
		$resultFooDirectoryPermissions = substr(decoct(fileperms($baseDirectory . '/foo')), 1);
		$resultFooFilePermissions = substr(decoct(fileperms($baseDirectory . '/foo/file')), 2);
		$resultBarDirectoryPermissions = substr(decoct(fileperms($baseDirectory . '/.bar')), 1);
		$resultBarFilePermissions = substr(decoct(fileperms($baseDirectory . '/.bar/.file')), 2);
		$resultBarFile2Permissions = substr(decoct(fileperms($baseDirectory . '/.bar/..file2')), 2);

			// Clean up
		unlink($baseDirectory . '/file');
		unlink($baseDirectory . '/foo/file');
		unlink($baseDirectory . '/.bar/.file');
		unlink($baseDirectory . '/.bar/..file2');
		t3lib_div::rmdir($baseDirectory . '/foo');
		t3lib_div::rmdir($baseDirectory . '/.bar');
		t3lib_div::rmdir($baseDirectory);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultBaseDirectoryPermissions, '0770');
		$this->assertEquals($resultBaseFilePermissions, '0660');
		$this->assertEquals($resultFooDirectoryPermissions, '0770');
		$this->assertEquals($resultFooFilePermissions, '0660');
		$this->assertEquals($resultBarDirectoryPermissions, '0770');
		$this->assertEquals($resultBarFilePermissions, '0660');
		$this->assertEquals($resultBarFile2Permissions, '0660');
	}

	/**
	 * @test
	 */
	public function fixPermissionsDoesNotSetPermissionsToNotAllowedPath() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

			// Create and prepare test file
		$filename = PATH_site . 'typo3temp/../typo3temp/' . uniqid('test_');
		touch($filename);
		chmod($filename, 0742);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0660';
		$fixPermissionsResult = t3lib_div::fixPermissions($filename);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($filename)), 2);
		unlink($filename);

			// Test if everything was ok
		$this->assertFalse($fixPermissionsResult);
	}

	/**
	 * @test
	 */
	public function fixPermissionsSetsPermissionsWithRelativeFileReference() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('fixPermissions() tests not available on Windows');
		}

		$filename = 'typo3temp/' . uniqid('test_');
		t3lib_div::writeFileToTypo3tempDir(PATH_site . $filename, '42');
		chmod(PATH_site . $filename, 0742);

			// Set target permissions and run method
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0660';
		$fixPermissionsResult = t3lib_div::fixPermissions($filename);

			// Get actual permissions and clean up
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms(PATH_site . $filename)), 2);
		unlink(PATH_site . $filename);

			// Test if everything was ok
		$this->assertTrue($fixPermissionsResult);
		$this->assertEquals($resultFilePermissions, '0660');
	}


	///////////////////////////////
	// Tests concerning mkdir
	///////////////////////////////

	/**
	 * @test
	 */
	public function mkdirCorrectlyCreatesDirectory() {
		$directory = PATH_site . 'typo3temp/' . uniqid('test_');
		$mkdirResult = t3lib_div::mkdir($directory);
		$directoryCreated = is_dir($directory);
		t3lib_div::rmdir($directory);
		$this->assertTrue($mkdirResult);
		$this->assertTrue($directoryCreated);
	}

	/**
	 * @test
	 */
	public function mkdirCorrectlyCreatesHiddenDirectory() {
		$directory = PATH_site . 'typo3temp/' . uniqid('.test_');
		$mkdirResult = t3lib_div::mkdir($directory);
		$directoryCreated = is_dir($directory);
		t3lib_div::rmdir($directory);
		$this->assertTrue($mkdirResult);
		$this->assertTrue($directoryCreated);
	}

	/**
	 * @test
	 */
	public function mkdirCorrectlyCreatesDirectoryWithTrailingSlash() {
		$directory = PATH_site . 'typo3temp/' . uniqid('test_');
		$mkdirResult = t3lib_div::mkdir($directory);
		$directoryCreated = is_dir($directory);
		t3lib_div::rmdir($directory);
		$this->assertTrue($mkdirResult);
		$this->assertTrue($directoryCreated);
	}

	/**
	 * @test
	 */
	public function mkdirSetsPermissionsOfCreatedDirectory() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('mkdirSetsPermissionsOfCreatedDirectory() test not available on Windows');
		}

		$directory = PATH_site . 'typo3temp/' . uniqid('test_');
		$oldUmask = umask(023);
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0772';
		t3lib_div::mkdir($directory);
		clearstatcache();
		$resultDirectoryPermissions = substr(decoct(fileperms($directory)), 1);
		umask($oldUmask);
		rmdir($directory);
		$this->assertEquals($resultDirectoryPermissions, '0772');
	}

	/**
	 * @test
	 */
	public function mkdirSetsGroupOwnershipOfCreatedDirectory() {
		$swapGroup = $this->checkGroups(__FUNCTION__);
		if ($swapGroup !== FALSE) {
			$GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'] = $swapGroup;
			$directory = uniqid('mkdirtest_');
			t3lib_div::mkdir(PATH_site . 'typo3temp/' . $directory);
			clearstatcache();
			$resultDirectoryGroupInfo = posix_getgrgid((filegroup(PATH_site . 'typo3temp/' . $directory)));
			$resultDirectoryGroup = $resultDirectoryGroupInfo['name'];
			@rmdir(PATH_site . 'typo3temp/' . $directory);
			$this->assertEquals($resultDirectoryGroup, $swapGroup);
		}
	}

	///////////////////////////////
	// Helper function for filesystem ownership tests
	///////////////////////////////

	/**
	 * Check if test on filesystem group ownership can be done in this environment
	 * If so, return second group of webserver user
	 * @param string calling method name
	 * @return mixed FALSE if test cannot be run, string name of the second group of webserver user
	 */
	private function checkGroups($methodName) {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped($methodName . '() test not available on Windows.');
			return FALSE;
		}

		$groups = posix_getgroups();
		if (count($groups) <= 1) {
			$this->markTestSkipped($methodName . '() test cannot be done when the web server user is only member of 1 group.');
			return FALSE;
		}
		$groupInfo = posix_getgrgid($groups[1]);
		return $groupInfo['name'];
	}

	///////////////////////////////
	// Tests concerning mkdir_deep
	///////////////////////////////

	/**
	 * @test
	 */
	public function mkdirDeepCreatesDirectory() {
		$directory = 'typo3temp/' . uniqid('test_');
		t3lib_div::mkdir_deep(PATH_site, $directory);
		$isDirectoryCreated = is_dir(PATH_site . $directory);
		rmdir(PATH_site . $directory);
		$this->assertTrue($isDirectoryCreated);
	}

	/**
	 * @test
	 */
	public function mkdirDeepCreatesSubdirectoriesRecursive() {
		$directory = 'typo3temp/' . uniqid('test_');
		$subDirectory = $directory . '/foo';
		t3lib_div::mkdir_deep(PATH_site, $subDirectory);
		$isDirectoryCreated = is_dir(PATH_site . $subDirectory);
		rmdir(PATH_site . $subDirectory);
		rmdir(PATH_site . $directory);
		$this->assertTrue($isDirectoryCreated);
	}

	/**
	 * @test
	 */
	public function mkdirDeepFixesPermissionsOfCreatedDirectory() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('mkdirDeepFixesPermissionsOfCreatedDirectory() test not available on Windows.');
		}

		$directory = uniqid('mkdirdeeptest_');
		$oldUmask = umask(023);
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0777';
		t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', $directory);
		clearstatcache();
		$resultDirectoryPermissions = substr(decoct(fileperms(PATH_site . 'typo3temp/' . $directory)), -3, 3);
		@rmdir(PATH_site . 'typo3temp/' . $directory);
		umask($oldUmask);
		$this->assertEquals($resultDirectoryPermissions, '777');
	}

	/**
	 * @test
	 */
	public function mkdirDeepFixesPermissionsOnNewParentDirectory() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('mkdirDeepFixesPermissionsOnNewParentDirectory() test not available on Windows.');
		}

		$directory = uniqid('mkdirdeeptest_');
		$subDirectory = $directory . '/bar';
		$GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] = '0777';
		$oldUmask = umask(023);
		t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', $subDirectory);
		clearstatcache();
		$resultDirectoryPermissions = substr(decoct(fileperms(PATH_site . 'typo3temp/' . $directory)), -3, 3);
		@rmdir(PATH_site . 'typo3temp/' . $subDirectory);
		@rmdir(PATH_site . 'typo3temp/' . $directory);
		umask($oldUmask);
		$this->assertEquals($resultDirectoryPermissions, '777');
	}

	/**
	 * @test
	 */
	public function mkdirDeepDoesNotChangePermissionsOfExistingSubDirectories() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('mkdirDeepDoesNotChangePermissionsOfExistingSubDirectories() test not available on Windows.');
		}

		$baseDirectory = PATH_site . 'typo3temp/';
		$existingDirectory = uniqid('test_existing_') . '/';
		$newSubDirectory = uniqid('test_new_');
		@mkdir($baseDirectory . $existingDirectory);
		chmod($baseDirectory . $existingDirectory, 0742);
		t3lib_div::mkdir_deep($baseDirectory, $existingDirectory . $newSubDirectory);
		$resultExistingDirectoryPermissions = substr(decoct(fileperms($baseDirectory . $existingDirectory)), 2);
		@rmdir($baseDirectory, $existingDirectory . $newSubDirectory);
		@rmdir($baseDirectory, $existingDirectory);
		$this->assertEquals($resultExistingDirectoryPermissions, '0742');
	}

	/**
	 * @test
	 */
	public function mkdirDeepSetsGroupOwnershipOfCreatedDirectory() {
		$swapGroup = $this->checkGroups(__FUNCTION__);
		if ($swapGroup!==FALSE) {
			$GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'] = $swapGroup;
			$directory = uniqid('mkdirdeeptest_');
			t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', $directory);
			clearstatcache();
			$resultDirectoryGroupInfo = posix_getgrgid((filegroup(PATH_site . 'typo3temp/' . $directory)));
			$resultDirectoryGroup = $resultDirectoryGroupInfo['name'];
			@rmdir(PATH_site . 'typo3temp/' . $directory);
			$this->assertEquals($resultDirectoryGroup, $swapGroup);
		}
	}

	/**
	 * @test
	 */
	public function mkdirDeepSetsGroupOwnershipOfCreatedParentDirectory() {
		$swapGroup = $this->checkGroups(__FUNCTION__);
		if ($swapGroup!==FALSE) {
			$GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'] = $swapGroup;
			$directory = uniqid('mkdirdeeptest_');
			$subDirectory = $directory . '/bar';
			t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', $subDirectory);
			clearstatcache();
			$resultDirectoryGroupInfo = posix_getgrgid((filegroup(PATH_site . 'typo3temp/' . $directory)));
			$resultDirectoryGroup = $resultDirectoryGroupInfo['name'];
			@rmdir(PATH_site . 'typo3temp/' . $subDirectory);
			@rmdir(PATH_site . 'typo3temp/' . $directory);
			$this->assertEquals($resultDirectoryGroup, $swapGroup);
		}
	}

	/**
	 * @test
	 */
	public function mkdirDeepSetsGroupOwnershipOnNewSubDirectory() {
		$swapGroup = $this->checkGroups(__FUNCTION__);
		if ($swapGroup!==FALSE) {
			$GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'] = $swapGroup;
			$directory = uniqid('mkdirdeeptest_');
			$subDirectory = $directory . '/bar';
			t3lib_div::mkdir_deep(PATH_site . 'typo3temp/', $subDirectory);
			clearstatcache();
			$resultDirectoryGroupInfo = posix_getgrgid((filegroup(PATH_site . 'typo3temp/' . $subDirectory)));
			$resultDirectoryGroup = $resultDirectoryGroupInfo['name'];
			@rmdir(PATH_site . 'typo3temp/' . $subDirectory);
			@rmdir(PATH_site . 'typo3temp/' . $directory);
			$this->assertEquals($resultDirectoryGroup, $swapGroup);
		}
	}

	/**
	 * @test
	 */
	public function mkdirDeepCreatesDirectoryInVfsStream() {
		if (!class_exists('\vfsStreamWrapper')) {
			$this->markTestSkipped('mkdirDeepCreatesDirectoryInVfsStream() test not available with this phpunit version.');
		}

		\vfsStreamWrapper::register();
		$baseDirectory = uniqid('test_');
		\vfsStreamWrapper::setRoot(new \vfsStreamDirectory($baseDirectory));
		t3lib_div::mkdir_deep('vfs://' . $baseDirectory . '/', 'sub');
		$this->assertTrue(is_dir('vfs://' . $baseDirectory . '/sub'));
	}

	///////////////////////////////
	// Tests concerning unQuoteFilenames
	///////////////////////////////

	/**
	 * Data provider for ImageMagick shell commands
	 * @see	explodeAndUnquoteImageMagickCommands
	 */
	public function imageMagickCommandsDataProvider() {
		return array(
			// Some theoretical tests first
			array(
				'aa bb "cc" "dd"',
				array('aa', 'bb', '"cc"', '"dd"'),
				array('aa', 'bb', 'cc', 'dd'),
			),
			array(
				'aa bb "cc dd"',
				array('aa', 'bb', '"cc dd"'),
				array('aa', 'bb', 'cc dd'),
			),
			array(
				'\'aa bb\' "cc dd"',
				array('\'aa bb\'', '"cc dd"'),
				array('aa bb', 'cc dd'),
			),
			array(
				'\'aa bb\' cc "dd"',
				array('\'aa bb\'', 'cc', '"dd"'),
				array('aa bb', 'cc', 'dd'),
			),
			// Now test against some real world examples
			array(
				'/opt/local/bin/gm.exe convert +profile \'*\' -geometry 170x136!  -negate "C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]" "C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"',
				array(
					'/opt/local/bin/gm.exe',
					'convert',
					'+profile',
					'\'*\'',
					'-geometry',
					'170x136!',
					'-negate',
					'"C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]"',
					'"C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"'
				),
				array(
					'/opt/local/bin/gm.exe',
					'convert',
					'+profile',
					'*',
					'-geometry',
					'170x136!',
					'-negate',
					'C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]',
					'C:/Users/Someuser.Domain/Documents/Htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif'
				),
			),
			array(
				'C:/opt/local/bin/gm.exe convert +profile \'*\' -geometry 170x136!  -negate "C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]" "C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"',
				array(
					'C:/opt/local/bin/gm.exe',
					'convert',
					'+profile',
					'\'*\'',
					'-geometry',
					'170x136!',
					'-negate',
					'"C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]"',
					'"C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"'
				),
				array(
					'C:/opt/local/bin/gm.exe',
					'convert',
					'+profile',
					'*',
					'-geometry',
					'170x136!',
					'-negate',
					'C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]',
					'C:/Program Files/Apache2/htdocs/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif'
				),
			),
			array(
				'/usr/bin/gm convert +profile \'*\' -geometry 170x136!  -negate "/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]" "/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"',
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'\'*\'',
					'-geometry',
					'170x136!',
					'-negate',
					'"/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]"',
					'"/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"'
				),
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'*',
					'-geometry',
					'170x136!',
					'-negate',
					'/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]',
					'/Shared Items/Data/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif'
				),
			),
			array(
				'/usr/bin/gm convert +profile \'*\' -geometry 170x136!  -negate "/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]" "/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"',
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'\'*\'',
					'-geometry',
					'170x136!',
					'-negate',
					'"/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]"',
					'"/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif"'
				),
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'*',
					'-geometry',
					'170x136!',
					'-negate',
					'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]',
					'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif'
				),
			),
			array(
				'/usr/bin/gm convert +profile \'*\' -geometry 170x136!  -negate \'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]\' \'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif\'',
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'\'*\'',
					'-geometry',
					'170x136!',
					'-negate',
					'\'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]\'',
					'\'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif\''
				),
				array(
					'/usr/bin/gm',
					'convert',
					'+profile',
					'*',
					'-geometry',
					'170x136!',
					'-negate',
					'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif[0]',
					'/Network/Servers/server01.internal/Projects/typo3temp/temp/61401f5c16c63d58e1d92e8a2449f2fe_maskNT.gif'
				),
			),
		);
	}

	/**
	 * Tests if the commands are exploded and unquoted correctly
	 *
	 * @dataProvider	imageMagickCommandsDataProvider
	 * @test
	 */
	public function explodeAndUnquoteImageMagickCommands($source, $expectedQuoted, $expectedUnquoted) {
		$actualQuoted 	= t3lib_div::unQuoteFilenames($source);
		$acutalUnquoted = t3lib_div::unQuoteFilenames($source, TRUE);

		$this->assertEquals($expectedQuoted, $actualQuoted, 'The exploded command does not match the expected');
		$this->assertEquals($expectedUnquoted, $acutalUnquoted, 'The exploded and unquoted command does not match the expected');
	}


	///////////////////////////////
	// Tests concerning split_fileref
	///////////////////////////////

	/**
	 * @test
	 */
	public function splitFileRefReturnsFileTypeNotForFolders(){
		$directoryName = uniqid('test_') . '.com';
		$directoryPath = PATH_site . 'typo3temp/';
		$directory = $directoryPath . $directoryName;
		mkdir($directory, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']));

		$fileInfo = t3lib_div::split_fileref($directory);

		$directoryCreated = is_dir($directory);
		rmdir($directory);

		$this->assertTrue($directoryCreated);
		$this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $fileInfo);
		$this->assertEquals($directoryPath, $fileInfo['path']);
		$this->assertEquals($directoryName, $fileInfo['file']);
		$this->assertEquals($directoryName, $fileInfo['filebody']);
		$this->assertEquals('', $fileInfo['fileext']);
		$this->assertArrayNotHasKey('realFileext', $fileInfo);
	}

	/**
	 * @test
	 */
	public function splitFileRefReturnsFileTypeForFilesWithoutPathSite() {
		$testFile = 'fileadmin/media/someFile.png';

		$fileInfo = t3lib_div::split_fileref($testFile);
		$this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $fileInfo);
		$this->assertEquals('fileadmin/media/', $fileInfo['path']);
		$this->assertEquals('someFile.png', $fileInfo['file']);
		$this->assertEquals('someFile', $fileInfo['filebody']);
		$this->assertEquals('png', $fileInfo['fileext']);
	}


	/////////////////////////////
	// Tests concerning dirname
	/////////////////////////////

	/**
	 * @see dirnameWithDataProvider
	 *
	 * @return array<array>
	 */
	public function dirnameDataProvider() {
		return array(
			'absolute path with multiple part and file' => array('/dir1/dir2/script.php', '/dir1/dir2'),
			'absolute path with one part' => array('/dir1/', '/dir1'),
			'absolute path to file without extension' => array('/dir1/something', '/dir1'),
			'relative path with one part and file' => array('dir1/script.php', 'dir1'),
			'relative one-character path with one part and file' => array('d/script.php', 'd'),
			'absolute zero-part path with file' => array('/script.php', ''),
			'empty string' => array('', ''),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider dirnameDataProvider
	 *
	 * @param string $input the input for dirname
	 * @param string $expectedValue the expected return value expected from dirname
	 */
	public function dirnameWithDataProvider($input, $expectedValue) {
		$this->assertEquals(
			$expectedValue,
			t3lib_div::dirname($input)
		);
	}


	/////////////////////////////////////
	// Tests concerning resolveBackPath
	/////////////////////////////////////

	/**
	 * @see resolveBackPathWithDataProvider
	 *
	 * @return array<array>
	 */
	public function resolveBackPathDataProvider() {
		return array(
			'empty path' => array('', ''),
			'this directory' => array('./', './'),
			'relative directory without ..' => array('dir1/dir2/dir3/', 'dir1/dir2/dir3/'),
			'relative path without ..' => array('dir1/dir2/script.php', 'dir1/dir2/script.php'),
			'absolute directory without ..' => array('/dir1/dir2/dir3/', '/dir1/dir2/dir3/'),
			'absolute path without ..' => array('/dir1/dir2/script.php', '/dir1/dir2/script.php'),
			'only one directory upwards without trailing slash' => array('..', '..'),
			'only one directory upwards with trailing slash' => array('../', '../'),
			'one level with trailing ..' => array('dir1/..', ''),
			'one level with trailing ../' => array('dir1/../', ''),
			'two levels with trailing ..' => array('dir1/dir2/..', 'dir1'),
			'two levels with trailing ../' => array('dir1/dir2/../', 'dir1/'),
			'leading ../ without trailing /' => array('../dir1', '../dir1'),
			'leading ../ with trailing /' => array('../dir1/', '../dir1/'),
			'leading ../ and inside path' => array('../dir1/dir2/../dir3/', '../dir1/dir3/'),
			'one times ../ in relative directory' => array('dir1/../dir2/', 'dir2/'),
			'one times ../ in absolute directory' => array('/dir1/../dir2/', '/dir2/'),
			'one times ../ in relative path' => array('dir1/../dir2/script.php', 'dir2/script.php'),
			'one times ../ in absolute path' => array('/dir1/../dir2/script.php', '/dir2/script.php'),
			'consecutive ../' => array('dir1/dir2/dir3/../../../dir4', 'dir4'),
			'distrubuted ../ with trailing /' => array('dir1/../dir2/dir3/../', 'dir2/'),
			'distributed ../ without trailing /' => array('dir1/../dir2/dir3/..', 'dir2'),
			'multiple distributed and consecutive ../ together' => array('dir1/dir2/dir3/dir4/../../dir5/dir6/dir7/../dir8/', 'dir1/dir2/dir5/dir6/dir8/'),
			'multiple distributed and consecutive ../ together' => array('dir1/dir2/dir3/dir4/../../dir5/dir6/dir7/../dir8/', 'dir1/dir2/dir5/dir6/dir8/'),
			'dirname with leading ..' => array('dir1/..dir2/dir3/', 'dir1/..dir2/dir3/'),
			'dirname with trailing ..' => array('dir1/dir2../dir3/', 'dir1/dir2../dir3/'),
			'more times upwards than downwards in directory' => array('dir1/../../', '../'),
			'more times upwards than downwards in path' => array('dir1/../../script.php', '../script.php'),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider resolveBackPathDataProvider
	 *
	 * @param string $input the input for resolveBackPath
	 * @param $expectedValue the expected return value from resolveBackPath
	 */
	public function resolveBackPathWithDataProvider($input, $expectedValue) {
		$this->assertEquals(
			$expectedValue,
			t3lib_div::resolveBackPath($input)
		);
	}


	/////////////////////////////////////////////////////////////////////////////////////
	// Tests concerning makeInstance, setSingletonInstance, addInstance, purgeInstances
	/////////////////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function makeInstanceWithEmptyClassNameThrowsException() {
		t3lib_div::makeInstance('');
	}

	/**
	 * @test
	 */
	public function makeInstanceReturnsClassInstance() {
		$className = get_class($this->getMock('foo'));

		$this->assertTrue(
			t3lib_div::makeInstance($className) instanceof $className
		);
	}

	/**
	 * @test
	 */
	public function makeInstancePassesParametersToConstructor() {
		$className = 'testingClass' . uniqid();
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' {' .
				'  public $constructorParameter1;' .
				'  public $constructorParameter2;' .
				'  public function __construct($parameter1, $parameter2) {' .
				'    $this->constructorParameter1 = $parameter1;' .
				'    $this->constructorParameter2 = $parameter2;' .
				'  }' .
				'}'
			);
		}

		$instance = t3lib_div::makeInstance($className, 'one parameter', 'another parameter');

		$this->assertEquals(
			'one parameter',
			$instance->constructorParameter1,
			'The first constructor parameter has not been set.'
		);
		$this->assertEquals(
			'another parameter',
			$instance->constructorParameter2,
			'The second constructor parameter has not been set.'
		);
	}

	/**
	 * @test
	 */
	public function makeInstanceCalledTwoTimesForNonSingletonClassReturnsDifferentInstances() {
		$className = get_class($this->getMock('foo'));

		$this->assertNotSame(
			t3lib_div::makeInstance($className),
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * @test
	 */
	public function makeInstanceCalledTwoTimesForSingletonClassReturnsSameInstance() {
		$className = get_class($this->getMock('t3lib_Singleton'));

		$this->assertSame(
			t3lib_div::makeInstance($className),
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * @test
	 */
	public function makeInstanceCalledTwoTimesForSingletonClassWithPurgeInstancesInbetweenReturnsDifferentInstances() {
		$className = get_class($this->getMock('t3lib_Singleton'));

		$instance = t3lib_div::makeInstance($className);
		t3lib_div::purgeInstances();

		$this->assertNotSame(
			$instance,
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setSingletonInstanceForEmptyClassNameThrowsException() {
		$instance = $this->getMock('t3lib_Singleton');

		t3lib_div::setSingletonInstance('', $instance);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setSingletonInstanceForClassThatIsNoSubclassOfProvidedClassThrowsException() {
		$instance = $this->getMock('t3lib_Singleton', array('foo'));
		$singletonClassName = get_class($this->getMock('t3lib_Singleton'));

		t3lib_div::setSingletonInstance($singletonClassName, $instance);
	}

	/**
	 * @test
	 */
	public function setSingletonInstanceMakesMakeInstanceReturnThatInstance() {
		$instance = $this->getMock('t3lib_Singleton');
		$singletonClassName = get_class($instance);

		t3lib_div::setSingletonInstance($singletonClassName, $instance);

		$this->assertSame(
			$instance,
			t3lib_div::makeInstance($singletonClassName)
		);
	}

	/**
	 * @test
	 */
	public function setSingletonInstanceCalledTwoTimesMakesMakeInstanceReturnLastSetInstance() {
		$instance1 = $this->getMock('t3lib_Singleton');
		$singletonClassName = get_class($instance1);
		$instance2 = new $singletonClassName();

		t3lib_div::setSingletonInstance($singletonClassName, $instance1);
		t3lib_div::setSingletonInstance($singletonClassName, $instance2);

		$this->assertSame(
			$instance2,
			t3lib_div::makeInstance($singletonClassName)
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function addInstanceForEmptyClassNameThrowsException() {
		$instance = $this->getMock('foo');

		t3lib_div::addInstance('', $instance);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function addInstanceForClassThatIsNoSubclassOfProvidedClassThrowsException() {
		$instance = $this->getMock('foo', array('bar'));
		$singletonClassName = get_class($this->getMock('foo'));

		t3lib_div::addInstance($singletonClassName, $instance);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function addInstanceWithSingletonInstanceThrowsException() {
		$instance = $this->getMock('t3lib_Singleton');

		t3lib_div::addInstance(get_class($instance), $instance);
	}

	/**
	 * @test
	 */
	public function addInstanceMakesMakeInstanceReturnThatInstance() {
		$instance = $this->getMock('foo');
		$className = get_class($instance);

		t3lib_div::addInstance($className, $instance);

		$this->assertSame(
			$instance,
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * @test
	 */
	public function makeInstanceCalledTwoTimesAfterAddInstanceReturnTwoDifferentInstances() {
		$instance = $this->getMock('foo');
		$className = get_class($instance);

		t3lib_div::addInstance($className, $instance);

		$this->assertNotSame(
			t3lib_div::makeInstance($className),
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * @test
	 */
	public function addInstanceCalledTwoTimesMakesMakeInstanceReturnBothInstancesInAddingOrder() {
		$instance1 = $this->getMock('foo');
		$className = get_class($instance1);
		t3lib_div::addInstance($className, $instance1);

		$instance2 = new $className();
		t3lib_div::addInstance($className, $instance2);

		$this->assertSame(
			$instance1,
			t3lib_div::makeInstance($className),
			'The first returned instance does not match the first added instance.'
		);
		$this->assertSame(
			$instance2,
			t3lib_div::makeInstance($className),
			'The second returned instance does not match the second added instance.'
		);
	}

	/**
	 * @test
	 */
	public function purgeInstancesDropsAddedInstance() {
		$instance = $this->getMock('foo');
		$className = get_class($instance);

		t3lib_div::addInstance($className, $instance);
		t3lib_div::purgeInstances();

		$this->assertNotSame(
			$instance,
			t3lib_div::makeInstance($className)
		);
	}

	/**
	 * Data provider for validPathStrDetectsInvalidCharacters.
	 *
	 * @return array
	 */
	public function validPathStrInvalidCharactersDataProvider() {
		return array(
			'double slash in path' => array('path//path'),
			'backslash in path' => array('path\\path'),
			'directory up in path' => array('path/../path'),
			'directory up at the beginning' => array('../path'),
			'NUL character in path' => array("path\x00path"),
			'BS character in path' => array("path\x08path"),
		);
	}

	/**
	 * Tests whether invalid characters are detected.
	 *
	 * @param string $path
	 * @dataProvider validPathStrInvalidCharactersDataProvider
	 * @test
	 */
	public function validPathStrDetectsInvalidCharacters($path) {
		$this->assertFalse(t3lib_div::validPathStr($path));
	}

	/**
	 * Tests whether Unicode characters are recognized as valid file name characters.
	 *
	 * @test
	 */
	public function validPathStrWorksWithUnicodeFileNames() {
		$this->assertTrue(t3lib_div::validPathStr('fileadmin/templates/Ссылка (fce).xml'));
	}

	/**
	 * Tests whether verifyFilenameAgainstDenyPattern detects the null character.
	 *
	 * @test
	 */
	public function verifyFilenameAgainstDenyPatternDetectsNullCharacter() {
		$this->assertFalse(t3lib_div::verifyFilenameAgainstDenyPattern("image\x00.gif"));
	}


	/////////////////////////////////////////////////////////////////////////////////////
	// Tests concerning sysLog
	/////////////////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function syslogFixesPermissionsOnFileIfUsingFileLogging() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('syslogFixesPermissionsOnFileIfUsingFileLogging() test not available on Windows.');
		}

			// Fake all required settings
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] = 0;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit'] = TRUE;
		unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']);
		$testLogFilename = PATH_site . 'typo3temp/' . uniqid('test_') . '.txt';
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'file,' . $testLogFilename . ',0';
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';

			// Call method, get actual permissions and clean up
		t3lib_div::syslog('testLog', 'test', 1);
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($testLogFilename)), 2);
		t3lib_div::unlink_tempfile($testLogFilename);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	/**
	 * @test
	 */
	public function deprecationLogFixesPermissionsOnLogFile() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('deprecationLogFixesPermissionsOnLogFile() test not available on Windows.');
		}

			// Fake all required settings and get an unique logfilename
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = uniqid('test_');
		$deprecationLogFilename = t3lib_div::getDeprecationLogFileName();
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = TRUE;
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';

			// Call method, get actual permissions and clean up
		t3lib_div::deprecationLog('foo');
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($deprecationLogFilename)), 2);
		@unlink($deprecationLogFilename);

		$this->assertEquals($resultFilePermissions, '0777');
	}

	///////////////////////////////////////////////////
	// Tests concerning hasValidClassPrefix
	///////////////////////////////////////////////////

	/**
	 * @return array
	 */
	public function validClassPrefixDataProvider() {
		return array(
			array('tx_foo'),
			array('tx_foo_bar'),
			array('Tx_foo'),
			array($GLOBALS['TYPO3_CONF_VARS']['FE']['userFuncClassPrefix'] . 'foo'),
		);
	}

	/**
	 * @test
	 * @dataProvider validClassPrefixDataProvider
	 * @param string $className Class name to test
	 */
	public function hasValidClassPrefixAcceptsValidPrefixes($className) {
		$this->assertTrue(
			t3lib_div::hasValidClassPrefix($className)
		);
	}

	/**
	 * @return array
	 */
	public function invalidClassPrefixDataProvider() {
		return array(
			array(''),
			array('ab_c'),
			array('txfoo'),
			array('Txfoo'),
			array('userfoo'),
			array('User_foo'),
		);
	}

	/**
	 * @test
	 * @dataProvider invalidClassPrefixDataProvider
	 * @param string $className Class name to test
	 */
	public function hasValidClassPrefixRefusesInvalidPrefixes($className) {
		$this->assertFalse(
			t3lib_div::hasValidClassPrefix($className)
		);
	}

	/**
	 * @test
	 */
	public function hasValidClassPrefixAcceptsAdditionalPrefixes() {
		$this->assertTrue(
			t3lib_div::hasValidClassPrefix('customPrefix_foo', array('customPrefix_'))
		);
	}

	///////////////////////////////////////////////////
	// Tests concerning substUrlsInPlainText
	///////////////////////////////////////////////////

	/**
	 * @return array
	 */
	public function substUrlsInPlainTextDataProvider() {
		$urlMatch = 'http://example.com/index.php\?RDCT=[0-9a-z]{20}';
		return array(
			array('http://only-url.com', '|^' . $urlMatch . '$|'),
			array('https://only-secure-url.com', '|^' . $urlMatch . '$|'),
			array('A http://url in the sentence.', '|^A ' . $urlMatch . ' in the sentence\.$|'),
			array('URL in round brackets (http://www.example.com) in the sentence.', '|^URL in round brackets \(' . $urlMatch . '\) in the sentence.$|'),
			array('URL in square brackets [http://www.example.com/a/b.php?c[d]=e] in the sentence.', '|^URL in square brackets \[' . $urlMatch . '\] in the sentence.$|'),
			array('URL in square brackets at the end of the sentence [http://www.example.com/a/b.php?c[d]=e].', '|^URL in square brackets at the end of the sentence \[' . $urlMatch . '].$|'),
			array('Square brackets in the http://www.url.com?tt_news[uid]=1', '|^Square brackets in the ' . $urlMatch . '$|'),
			array('URL with http://dot.com.', '|^URL with ' . $urlMatch . '.$|'),
			array('URL in <a href="http://www.example.com/">a tag</a>', '|^URL in <a href="' . $urlMatch . '">a tag</a\>$|'),
			array('URL in HTML <b>http://www.example.com</b><br />', '|^URL in HTML <b>' . $urlMatch . '</b><br />$|'),
			array('URL with http://username@example.com/', '|^URL with ' . $urlMatch . '$|'),
			array('Secret in URL http://username:secret@example.com', '|^Secret in URL ' . $urlMatch . '$|'),
			array('URL in quotation marks "http://example.com"', '|^URL in quotation marks "' . $urlMatch . '"$|'),
			array('URL with umlauts http://müller.de', '|^URL with umlauts ' . $urlMatch . '$|'),
			array("Multiline\ntext with a http://url.com", "|^Multiline\ntext with a " . $urlMatch . '$|s'),
			array('http://www.shout.com!', '|^' . $urlMatch . '!$|'),
			array('And with two URLs http://www.two.com/abc http://urls.com/abc?x=1&y=2', '|^And with two URLs ' . $urlMatch . ' ' . $urlMatch . '$|'),
		);
	}

	/**
	 * @test
	 * @dataProvider substUrlsInPlainTextDataProvider
	 * @param string $input Text to recognise URLs from
	 * @param string $expected Text with correctly detected URLs
	 */
	public function substUrlsInPlainText($input, $expectedPreg) {
		$this->assertTrue(preg_match($expectedPreg, t3lib_div::substUrlsInPlainText($input, 1, 'http://example.com/index.php')) == 1);
	}
}
?>
