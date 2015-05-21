<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Testcase for the t3lib_TCEmain class in the TYPO3 core.
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class t3lib_tcemainTest extends tx_phpunit_testcase {

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

	/**
	 * @var t3lib_TCEmain
	 */
	private $fixture;

	/**
	 * @var t3lib_beUserAuth a mock logged-in back-end user
	 */
	private $backEndUser;

	public function setUp() {
		$this->backEndUser = $this->getMock('t3lib_beUserAuth');

		$this->fixture = new t3lib_TCEmain();
		$this->fixture->start(array(), '', $this->backEndUser);
	}

	public function tearDown() {
		unset(
			$this->fixture->BE_USER, $this->fixture, $this->backEndUser
		);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function fixtureCanBeCreated() {
		$this->assertTrue(
			$this->fixture instanceof t3lib_TCEmain
		);
	}


	//////////////////////////////////////////
	// Test concerning checkModifyAccessList
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function adminIsAllowedToModifyNonAdminTable() {
		$this->fixture->admin = true;

		$this->assertTrue(
			$this->fixture->checkModifyAccessList('tt_content')
		);
	}

	/**
	 * @test
	 */
	public function nonAdminIsNorAllowedToModifyNonAdminTable() {
		$this->fixture->admin = false;

		$this->assertFalse(
			$this->fixture->checkModifyAccessList('tt_content')
		);
	}

	/**
	 * @test
	 */
	public function nonAdminWithTableModifyAccessIsAllowedToModifyNonAdminTable() {
		$this->fixture->admin = false;
		$this->backEndUser->groupData['tables_modify'] = 'tt_content';

		$this->assertTrue(
			$this->fixture->checkModifyAccessList('tt_content')
		);
	}

	/**
	 * @test
	 */
	public function adminIsAllowedToModifyAdminTable() {
		$this->fixture->admin = true;

		$this->assertTrue(
			$this->fixture->checkModifyAccessList('be_users')
		);
	}

	/**
	 * @test
	 */
	public function nonAdminIsNotAllowedToModifyAdminTable() {
		$this->fixture->admin = false;

		$this->assertFalse(
			$this->fixture->checkModifyAccessList('be_users')
		);
	}

	/**
	 * @test
	 */
	public function nonAdminWithTableModifyAccessIsNotAllowedToModifyAdminTable() {
		$this->fixture->admin = false;
		$this->backEndUser->groupData['tables_modify'] = 'be_users';

		$this->assertFalse(
			$this->fixture->checkModifyAccessList('be_users')
		);
	}

	/**
	 * @test
	 */
	public function evalCheckValueDouble2() {
		$testData = array (
						'-0,5' => '-0.50',
						'1000' => '1000.00',
						'1000,10' => '1000.10',
						'1000,0' => '1000.00',
						'600.000.000,00' => '600000000.00',
						'60aaa00' => '6000.00',
						);
		foreach ($testData as $value => $expectedReturnValue){
			$returnValue = $this->fixture->checkValue_input_Eval($value, array('double2'), '');
			$this->assertSame(
			$returnValue['value'],
			$expectedReturnValue
			);
		}
	}

	/**
	 * Data provider for inputValueCheckRecognizesStringValuesAsIntegerValuesCorrectly
	 *
	 * @return array
	 */
	public function inputValuesStringsDataProvider() {
		return array(
			'"0" returns zero as integer' => array(
				'0',
				0
			),
			'"-1999999" is interpreted correctly as -1999999 and is lot lower then -200000' => array(
				'-1999999',
				-1999999
			),
			'"3000000" is interpreted correctly as 3000000 but is higher then 200000 and set to 200000' => array(
				'3000000',
				2000000
			),
		);
	}

	/**
	 * @test
	 * @dataProvider inputValuesStringsDataProvider
	 */
	public function inputValueCheckRecognizesStringValuesAsIntegerValuesCorrectly($value, $expectedReturnValue) {
		$tcaFieldConf = array(
			'input' => array(),
			'eval' => 'int',
			'range' => array(
				'lower' => '-2000000',
				'upper' => '2000000'
			)
		);
		$returnValue = $this->fixture->checkValue_input(array(), $value, $tcaFieldConf, array());
		$this->assertSame($returnValue['value'], $expectedReturnValue);
	}

	///////////////////////////////////////////
	// Tests concerning checkModifyAccessList
	///////////////////////////////////////////

	/**
	 * Tests whether a wrong interface on the 'checkModifyAccessList' hook throws an exception.
	 * @test
	 * @expectedException UnexpectedValueException
	 * @see t3lib_TCEmain::checkModifyAccessList()
	 */
	public function doesCheckModifyAccessListThrowExceptionOnWrongHookInterface() {
		$hookClass = uniqid('tx_coretest');
		eval('class ' . $hookClass . ' {}');

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkModifyAccessList'][] = $hookClass;

		$this->fixture->checkModifyAccessList('tt_content');
	}

	/**
	 * Tests whether the 'checkModifyAccessList' hook is called correctly.
	 * @test
	 * @see t3lib_TCEmain::checkModifyAccessList()
	 */
	public function doesCheckModifyAccessListHookGetsCalled() {
		$hookClass = uniqid('tx_coretest');
		$hookMock = $this->getMock(
			't3lib_TCEmain_checkModifyAccessListHook',
			array('checkModifyAccessList'),
			array(),
			$hookClass
		);
		$hookMock->expects($this->once())->method('checkModifyAccessList');

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkModifyAccessList'][] = $hookClass;
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hookMock;

		$this->fixture->checkModifyAccessList('tt_content');
	}

	/**
	 * Tests whether the 'checkModifyAccessList' hook modifies the $accessAllowed variable.
	 * @test
	 * @see t3lib_TCEmain::checkModifyAccessList()
	 */
	public function doesCheckModifyAccessListHookModifyAccessAllowed() {
		$hookClass = uniqid('tx_coretest');
		eval('
			class ' . $hookClass . ' implements t3lib_TCEmain_checkModifyAccessListHook {
				public function checkModifyAccessList(&$accessAllowed, $table, t3lib_TCEmain $parent) { $accessAllowed = true; }
			}
		');

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkModifyAccessList'][] = $hookClass;

		$this->assertTrue($this->fixture->checkModifyAccessList('tt_content'));
	}

	/////////////////////////////////////
	// Tests concerning log
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function logCallsWriteLogOfBackendUserIfLoggingIsEnabled() {
		$backendUser = $this->getMock('t3lib_beUserAuth');
		$backendUser->expects($this->once())->method('writelog');
		$this->fixture->enableLogging = TRUE;
		$this->fixture->BE_USER = $backendUser;
		$this->fixture->log('', 23, 0, 42, 0, 'details');
	}

	/**
	 * @test
	 */
	public function logDoesNotCallWriteLogOfBackendUserIfLoggingIsDisabled() {
		$backendUser = $this->getMock('t3lib_beUserAuth');
		$backendUser->expects($this->never())->method('writelog');
		$this->fixture->enableLogging = FALSE;
		$this->fixture->BE_USER = $backendUser;
		$this->fixture->log('', 23, 0, 42, 0, 'details');
	}

	/**
	 * @test
	 */
	public function logAddsEntryToLocalErrorLogArray() {
		$backendUser = $this->getMock('t3lib_beUserAuth');
		$this->fixture->BE_USER = $backendUser;
		$this->fixture->enableLogging = TRUE;
		$this->fixture->errorLog = array();
		$logDetailsUnique = uniqid('details');
		$this->fixture->log('', 23, 0, 42, 1, $logDetailsUnique);
		$this->assertStringEndsWith($logDetailsUnique, $this->fixture->errorLog[0]);
	}

	/**
	 * @test
	 */
	public function logFormatsDetailMessageWithAdditionalDataInLocalErrorArray() {
		$backendUser = $this->getMock('t3lib_beUserAuth');
		$this->fixture->BE_USER = $backendUser;
		$this->fixture->enableLogging = TRUE;
		$this->fixture->errorLog = array();
		$logDetails = uniqid('details');
		$this->fixture->log('', 23, 0, 42, 1, '%1s' . $logDetails . '%2s', -1, array('foo', 'bar'));
		$expected = 'foo' . $logDetails . 'bar';
		$this->assertStringEndsWith($expected, $this->fixture->errorLog[0]);
	}

	/**
	 * @return array
	 */
	public function checkValue_checkReturnsExpectedValuesDataProvider() {
		return array(
			'None item selected' => array(
				0,
				0
			),
			'All items selected' => array(
				7,
				7
			),
			'Item 1 and 2 are selected' => array(
				3,
				3
			),
			'Value is higher than allowed' => array(
				15,
				7
			),
			'Negative value' => array(
				-5,
				0
			)
		);
	}

	/**
	 * @param string $value
	 * @param string $expectedValue
	 *
	 * @dataProvider checkValue_checkReturnsExpectedValuesDataProvider
	 * @test
	 */
	public function checkValue_checkReturnsExpectedValues($value, $expectedValue) {
		$expectedResult = array(
			'value' => $expectedValue
		);
		$result = array();
		$tcaFieldConfiguration = array(
			'items' => array(
				array('Item 1', 0),
				array('Item 2', 0),
				array('Item 3', 0)
			)
		);
		$this->assertSame($expectedResult, $this->fixture->checkValue_check($result, $value, $tcaFieldConfiguration, array()));
	}
}
?>