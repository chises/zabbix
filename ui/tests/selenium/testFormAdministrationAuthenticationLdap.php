<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/CWebTest.php';
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';
require_once dirname(__FILE__).'/traits/TableTrait.php';

/**
 * @backup config, userdirectory, usrgrp
 */
class testFormAdministrationAuthenticationLdap extends CWebTest {

	use TableTrait;

	private $remove_ldap = true;

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [
			CMessageBehavior::class
		];
	}

	public function getTestData() {
		return [
			// #0 test without Host, Base DN and Search attribute.
			[
				[
					'ldap_settings' => [],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "host": cannot be empty.',
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			// #1 test without Base DN and Search attribute.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com'
					],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			// #2 test without Search attribute.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com'
					],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			// #3 test with empty credentials.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid'
					],
					'test_settings' => [
						'Login' => '',
						'User password' => ''
					],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "test_username": cannot be empty.',
						'Incorrect value for field "test_password": cannot be empty.'
					]
				]
			],
			// #4 test with empty password field.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid'
					],
					'test_settings' => [
						'Login' => 'galieleo',
						'User password' => ''
					],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "test_password": cannot be empty.'
					]
				]
			],
			// #5 test with empty username field.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid'
					],
					'test_settings' => [
						'Login' => '',
						'User password' => 'password'
					],
					'test_error' => 'Invalid LDAP configuration',
					'test_error_details' => [
						'Incorrect value for field "test_username": cannot be empty.'
					]
				]
			],
			// #6 test with incorrect username and password values.
			[
				[
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid'
					],
					'test_settings' => [
						'Login' => 'test',
						'User password' => 'test'
					],
					'test_error' => 'Login failed',
					'test_error_details' => [
						'Incorrect user name or password or account is temporarily blocked.'
					]
				]
			],
			// #7 test with incorrect LDAP settings.
			[
				[
					'ldap_settings' => [
						'Host' => 'test',
						'Base DN' => 'test',
						'Search attribute' => 'test'
					],
					'test_settings' => [
						'Login' => 'test',
						'User password' => 'test'
					],
					'test_error' => 'Login failed',
					'test_error_details' => [
						'Cannot bind anonymously to LDAP server.'
					]
				]
			],
			// #8 test with all available values.
			[
				[
					'ldap_settings' => [
						'Name' => 'Test Name',
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid',
						'Bind DN' => 'test_DN',
						'Bind password' => 'test_password',
						'Description' => 'Test description',
						'Advanced configuration' => true,
						'StartTLS' => true,
						'Search filter' => 'filter'
					],
					'test_settings' => [
						'Login' => 'galieleo',
						'User password' => 'password'
					],
					'test_error' => 'Login failed',
					'test_error_details' => [
						'Starting TLS failed.'
					]
				]
			],
			// #9 test with Bind DN and Bind password.
			[
				[
					'ldap_settings' => [
						'Name' => 'Test Name',
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid',
						'Bind DN' => 'test_DN',
						'Bind password' => 'test_password',
						'Description' => 'Test description'
					],
					'test_settings' => [
						'Login' => 'galieleo',
						'User password' => 'password'
					],
					'test_error' => 'Login failed',
					'test_error_details' => [
						'Cannot bind to LDAP server.'
					]
				]
			],
			// #10 test with correct LDAP settings and credentials.
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						'Host' => 'ldap.forumsys.com',
						'Base DN' => 'dc=example,dc=com',
						'Search attribute' => 'uid'
					],
					'test_settings' => [
						'Login' => 'galieleo',
						'User password' => 'password'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getTestData
	 *
	 * Test LDAP settings.
	 */
	public function testFormAdministrationAuthenticationLdap_Test($data) {
		$form = $this->openLdapForm('Internal');
		$form->query('button:Add')->one()->click();
		COverlayDialogElement::find()->waitUntilReady()->asForm()->one()->fill($data['ldap_settings']);
		$this->query('button:Test')->waitUntilClickable()->one()->click();

		// Fill login and user password in Test authentication form.
		if (array_key_exists('test_settings', $data)) {
			$test_form = COverlayDialogElement::find()->waitUntilReady()->asForm()->all()->last();
			$test_form->fill($data['test_settings'])->submit()->waitUntilReady();
		}

		// Check error messages testing LDAP settings.
		if (CTestArrayHelper::get($data, 'expected', TEST_BAD) === TEST_GOOD) {
			$this->assertMessage(TEST_GOOD, 'Login successful');
		}
		else {
			$this->assertMessage(TEST_BAD, $data['test_error'], $data['test_error_details']);
		}
	}

	/**
	 * Check that remove button works.
	 */
	public function testFormAdministrationAuthenticationLdap_Remove() {
		$form = $this->openLdapForm('Internal');
		$table = $form->query('id:ldap-servers')->asTable()->one();

		// Add new LDAP server if it is not present.
		if ($table->getRows()->count() === 0) {
			$this->setLdap([], 'button:Add', true, false);
			$form->submit();
			$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
			$form->selectTab('LDAP settings');
		}

		// Check headers.
		$this->assertEquals(['Name', 'Host', 'User groups', 'Default', ''], $table->getHeadersText());

		// Check that LDAP server added in DB.
		$this->assertEquals(1, CDBHelper::getCount('SELECT * FROM userdirectory'));

		// Click on remove button and check that LDAP server NOT removed from DB.
		$table->query('button:Remove')->one()->click();
		$this->query('id:ldap_configured')->asCheckbox()->one()->set(false);
		$this->assertEquals(1, CDBHelper::getCount('SELECT * FROM userdirectory'));

		// Submit changes and check that LDAP server removed.
		$form->submit();
		$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
		$this->assertEquals(0, CDBHelper::getCount('SELECT * FROM userdirectory'));
	}

	/**
	 * Check default LDAP server change.
	 */
	public function testFormAdministrationAuthenticationLdap_Default() {
		$form = $this->openLdapForm('Internal');
		$table = $form->query('id:ldap-servers')->asTable()->one();

		// To check default we need at least 2 LDAP servers.
		for ($i = 0; $i <=1; $i++) {
			if ($table->getRows()->count() < 2) {
				$this->setLdap([], 'button:Add', true, false, 'test_'.$i);
				$form->submit();
				$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
				$form->selectTab('LDAP settings');
			}
			else {
				break;
			}
		}

		$hosts = $this->getTableResult('Host', 'id:ldap-servers');
		foreach ($hosts as $host) {
			$radio = $table->findRow('Host', $host)->getColumn('Default');

			// Check if LDAP server is set as Default.
			if ($radio->query('name:ldap_default_row_index')->one()->isAttributePresent('checked') === true) {
				$user_directoryid = CDBHelper::getValue('SELECT userdirectoryid FROM userdirectory WHERE host='.zbx_dbstr($host));
				$this->assertEquals($user_directoryid, CDBHelper::getValue('SELECT ldap_userdirectoryid FROM config'));
			}
			else {
				// Set another LDAP server as default.
				$user_directoryid = CDBHelper::getValue('SELECT userdirectoryid FROM userdirectory WHERE host='.zbx_dbstr($host));
				$this->assertNotEquals($user_directoryid, CDBHelper::getValue('SELECT ldap_userdirectoryid FROM config'));
				$radio->query('name:ldap_default_row_index')->one()->click();
				$form->submit();
				$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
				$this->assertEquals($user_directoryid, CDBHelper::getValue('SELECT ldap_userdirectoryid FROM config'));
			}
		}

		// Default LDAP server host name.
		$hostname = CDBHelper::getValue('SELECT host FROM userdirectory ud INNER JOIN config co ON '.
				'ud.userdirectoryid = co.ldap_userdirectoryid');
		$form->selectTab('LDAP settings');

		// Find default LDAP server, delete it and check that another LDAP server set as default.
		$table->findRow('Host', $hostname)->getColumn('')->query('button:Remove')->one()->click();
		$form->submit();
		$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
		$new_hostname = CDBHelper::getValue('SELECT host FROM userdirectory ud INNER JOIN config co ON '.
				'ud.userdirectoryid = co.ldap_userdirectoryid');

		// Check that old LDAP server (by host name) is not default now.
		$this->assertNotEquals($hostname, $new_hostname);
	}

	public function getUpdateData() {
		return [
			[
				[
					'ldap_settings' => [
						[
							'Name' => '',
							'Host' => '',
							'Base DN' => '',
							'Search attribute' => ''
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "host": cannot be empty.',
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			[
				[
					'ldap_settings' => [
						[
							'Name' => '',
							'Host' => 'updated_host',
							'Base DN' => '',
							'Search attribute' => ''
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			[
				[
					'ldap_settings' => [
						[
							'Name' => '',
							'Host' => 'updated_host',
							'Base DN' => 'updated_dn',
							'Search attribute' => ''
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					]
				]
			],
			[
				[
					'ldap_settings' => [
						[
							'Name' => '',
							'Host' => 'updated_host',
							'Base DN' => 'updated_dn',
							'Search attribute' => 'updated_search'
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.'
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						[
							'Name' => 'updated_name',
							'Host' => 'updated_host',
							'Port' => '777',
							'Base DN' => 'updated_dn',
							'Search attribute' => 'updated_search',
							'Bind DN' => 'updated_bin_dn',
							'Description' => 'updated_description',
							'Advanced configuration' => true,
							'StartTLS' => true,
							'Search filter' => 'updated_filter'
						]
					],
					'db_check' => [
						'name' => 'updated_name',
						'host' => 'updated_host',
						'port' => '777',
						'base_dn' => 'updated_dn',
						'bind_dn' => 'updated_bin_dn',
						'description' => 'updated_description',
						'search_attribute' => 'updated_search',
						'search_filter' => 'updated_filter',
						'start_tls' => '1'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getUpdateData
	 *
	 * Update LDAP server settings.
	 */
	public function testFormAdministrationAuthenticationLdap_Update($data) {
		if (CDBHelper::getCount('SELECT * FROM userdirectory') === 0) {
			$ldap_settings = [
				'ldap_settings' => [
					[
						'Name' => 'test_update',
						'Host' => 'test_update',
						'Base DN' => 'test_update',
						'Search attribute' => 'test_update'
					]
				]
			];

			$this->checkLdap($ldap_settings, 'button:Add');
			$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
		}

		if (!array_key_exists('expected', $data)) {
			$hash_before = CDBHelper::getHash('SELECT * FROM userdirectory');
		}

		$this->checkLdap($data, 'xpath://table[@id="ldap-servers"]//a[contains(text(), "test_")]');
		$this->assertMessage(TEST_GOOD, 'Authentication settings updated');

		if (!array_key_exists('expected', $data)) {
			$this->assertEquals($hash_before, CDBHelper::getHash('SELECT * FROM userdirectory'));
		}
		else {
			$sql = 'SELECT name, host, port, base_dn, bind_dn, description, search_attribute, '.
					'search_filter, start_tls FROM userdirectory';
			$result = CDBHelper::getRow($sql);
			$this->assertEquals($data['db_check'], $result);
		}
	}

	public function getCreateData() {
		return [
			// #0 Only default authentication added.
			[
				[
					'error' => 'Incorrect value for field "authentication_type": LDAP is not configured.'
				]
			],
			// #1 LDAP server without any parameters.
			[
				[
					'ldap_settings' => [[]],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "host": cannot be empty.',
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					],
					'error' => 'At least one LDAP server must exist.'
				]
			],
			// #2 LDAP server without name, Base DN and Search attribute.
			[
				[
					'ldap_settings' => [
						[
							'Host' => 'ldap.forumsys.com'
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "base_dn": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					],
					'error' => 'At least one LDAP server must exist.'
				]
			],
			// #3 LDAP server without name and search attribute.
			[
				[
					'ldap_settings' => [
						[
							'Host' => 'ldap.forumsys.com',
							'Base DN' => 'dc=example,dc=com'
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.',
						'Incorrect value for field "search_attribute": cannot be empty.'
					],
					'error' => 'At least one LDAP server must exist.'
				]
			],
			//#4 LDAP server without name.
			[
				[
					'ldap_settings' => [
						[
							'Host' => 'ldap.forumsys.com',
							'Base DN' => 'dc=example,dc=com',
							'Search attribute' => 'uid'
						]
					],
					'ldap_error' => 'Invalid LDAP configuration',
					'ldap_error_details' => [
						'Incorrect value for field "name": cannot be empty.'
					],
					'error' => 'At least one LDAP server must exist.'
				]
			],
			// #5 Two LDAP servers with same names.
			// TODO: Uncomment this part after ZBX-21061 fix.
//			[
//				[
//					'ldap_settings' => [
//						[
//							'Name' => 'TEST',
//							'Host' => 'ldap.forumsys.com',
//							'Base DN' => 'dc=example,dc=com',
//							'Search attribute' => 'uid'
//						],
//						[
//							'Name' => 'TEST',
//							'Host' => 'ldap.forumsys.com',
//							'Base DN' => 'dc=example,dc=com',
//							'Search attribute' => 'uid'
//						]
//					],
//					'error' => 'Invalid parameter "/2": value (name)=(TEST) already exists.'
//				]
//			],
			// #6 Using cyrillic in settings.
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						[
							'Name' => 'кириллица',
							'Host' => 'кириллица',
							'Base DN' => 'кириллица',
							'Search attribute' => 'кириллица'
						]
					],
					'db_check' => [
						'name' => 'кириллица',
						'host' => 'кириллица',
						'port' => '389',
						'base_dn' => 'кириллица',
						'bind_dn' => '',
						'bind_password' => '',
						'search_attribute' => 'кириллица'
					]
				]
			],
			// #7 Using symbols in settings.
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						[
							'Name' => '@#$%^&*.',
							'Host' => '@#$%^&*.',
							'Base DN' => '@#$%^&*.',
							'Search attribute' => '@#$%^&*.'
						]
					],
					'db_check' => [
						'name' => '@#$%^&*.',
						'host' => '@#$%^&*.',
						'port' => '389',
						'base_dn' => '@#$%^&*.',
						'bind_dn' => '',
						'bind_password' => '',
						'search_attribute' => '@#$%^&*.'
					]
				]
			],
			// #8 Long values.
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						[
							'Name' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value'.
									'_long_value_long_value_long_value_long_value_long_va',
							'Host' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value'.
									'_long_value_long_value_long_value_long_value_long_valong_value_long_value_long'.
									'_value_long_value_long_value_long_value_long_value_long_value_long_value_long_'.
									'value_long_value_long_v',
							'Base DN' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value'.
									'_long_value_long_value_long_value_long_value_long_valong_value_long_value_long_'.
									'value_long_value_long_value_long_value_long_value_long_value_long_value_long_'.
									'value_long_value_long_v',
							'Search attribute' => 'long_value_long_value_long_value_long_value_long_value_long_value_'.
									'long_value_long_value_long_value_long_value_long_value_long_va'
						]
					],
					'db_check' => [
						'name' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value_long_'.
								'value_long_value_long_value_long_value_long_va',
						'host' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value_long_'.
								'value_long_value_long_value_long_value_long_valong_value_long_value_long_value_long_'.
								'value_long_value_long_value_long_value_long_value_long_value_long_value_long_value_long_v',
						'port' => '389',
						'base_dn' => 'long_value_long_value_long_value_long_value_long_value_long_value_long_value_'.
								'long_value_long_value_long_value_long_value_long_valong_value_long_value_long_value_'.
								'long_value_long_value_long_value_long_value_long_value_long_value_long_value_long_value_long_v',
						'bind_dn' => '',
						'bind_password' => '',
						'search_attribute' => 'long_value_long_value_long_value_long_value_long_value_long_value_'.
								'long_value_long_value_long_value_long_value_long_value_long_va'
					]
				]
			],
			// #9 LDAP server with every field filled.
			[
				[
					'expected' => TEST_GOOD,
					'ldap_settings' => [
						[
							'Name' => 'AAAA',
							'Host' => 'ldap.forumsys.com',
							'Port' => '389',
							'Base DN' => 'dc=example,dc=com',
							'Search attribute' => 'uid',
							'Bind DN' => 'cn=read-only-admin,dc=example,dc=com',
							'Bind password' => 'password',
							'Description' => 'description',
							'Advanced configuration' => true,
							'StartTLS' => true,
							'Search filter' => 'filter'
						]
					],
					'db_check' => [
						'name' => 'AAAA',
						'host' => 'ldap.forumsys.com',
						'port' => '389',
						'base_dn' => 'dc=example,dc=com',
						'bind_dn' => 'cn=read-only-admin,dc=example,dc=com',
						'bind_password' => 'password',
						'search_attribute' => 'uid'
					]
				]
			]
		];
	}

	/**
	 *
	 * @dataProvider getCreateData
	 *
	 * Check authentication with LDAP settings.
	 */
	public function testFormAdministrationAuthenticationLdap_Create($data) {
		if ($this->remove_ldap) {
			$this->removeAllLdap();
			$this->remove_ldap = false;
		}

		$this->checkLdap($data, 'button:Add');

		// Check error messages.
		if (CTestArrayHelper::get($data, 'expected', TEST_BAD) === TEST_GOOD) {
			$this->assertMessage(TEST_GOOD, 'Authentication settings updated');

			// Check DB configuration.
			$sql = 'SELECT name, host, port, base_dn, bind_dn, bind_password, search_attribute FROM'.
					' userdirectory WHERE name ='.zbx_dbstr($data['db_check']['name']);
			$this->assertEquals($data['db_check'], CDBHelper::getRow($sql));
		}
		else {
			$this->assertMessage(TEST_BAD, $data['error']);
		}
	}

	/**
	 * Check that User Group value in table changes after adding LDAP server to any user group.
	 */
	public function testFormAdministrationAuthenticationLdap_UserGroups() {
		$form = $this->openLdapForm('Internal');
		$table = $form->query('id:ldap-servers')->asTable()->one();

		// Add new LDAP server if it is not present.
		if ($table->getRows()->count() === 0) {
			$this->setLdap([], 'button:Add', true, false);
			$form->submit();
			$this->assertMessage(TEST_GOOD, 'Authentication settings updated');
			$form->selectTab('LDAP settings');
		}

		$ldap_name = $this->getTableResult('Name', 'id:ldap-servers');

		// Check that there is no User groups with added LDAP server.
		$this->assertEquals(['0'], $this->getTableResult('User groups', 'id:ldap-servers'));

		// Open existing User group and change it LDAP server.
		$this->page->open('zabbix.php?action=usergroup.edit&usrgrpid=16')->waitUntilReady();
		$this->query('name:userdirectoryid')->asDropdown()->one()->fill($ldap_name[0]);
		$this->query('button:Update')->one()->click();

		// Check that value in table is changed and display that there exists group with LDAP server.
		$this->page->open('zabbix.php?action=authentication.edit')->waitUntilReady();
		$form->selectTab('LDAP settings');
		$this->assertEquals(['1'], $this->getTableResult('User groups', 'id:ldap-servers'));
		$this->assertFalse($this->query('xpath://button[text()="Remove"][1]')->one()->isEnabled());
	}

	/**
	 * Function for opening LDAP configuration form.
	 *
	 * @param string $auth			   default authentication field value
	 * @param boolean $check_header    true if need to check title and header
	 */
	private function openLdapForm($auth, $check_header = false) {
		$this->page->login()->open('zabbix.php?action=authentication.edit');

		if ($check_header) {
			$this->page->assertHeader('Authentication');
			$this->page->assertTitle('Configuration of authentication');
		}

		$form = $this->query('id:authentication-form')->asForm()->one();
		$form->fill(['Default authentication' => $auth]);
		$form->selectTab('LDAP settings');

		return $form;
	}

	/**
	 * Removes all existing LDAP servers.
	 */
	private function removeAllLdap() {
		$form = $this->openLdapForm('Internal');
		$form->fill(['Enable LDAP authentication' => false]);
		$table = $form->query('id:ldap-servers')->asTable()->one();
		$rows_amount = $table->getRows()->count();

		for ($i = $rows_amount; $i > 0; $i--) {
			$this->query('xpath://button[text()="Remove"]['.$i.']')->one()->click();
		}

		$form->submit();

		if ($this->page->isAlertPresent()) {
			$this->page->acceptAlert();
		}
	}

	/**
	 * Fill and submit LDAP server settings.
	 *
	 * @param string $ldaps			 	  form parameters to fill
	 * @param string $query			      object to click for LDAP creating or updating
	 * @param boolean $ldap_configured    enable/Disable LDAP authentication checkbox
	 * @param boolean $exists			  if no LDAP server present but we need one - set false
	 * @param string  $values			  simple LDAP server values
	 */
	private function setLdap($ldaps, $query, $ldap_configured = true, $exists = true, $values = 'atest') {
		$form = $this->query('id:authentication-form')->asForm()->one();

		// Select LDAP setting tab if it is not selected.
		if ($form->getSelectedTab() !== 'LDAP settings') {
			$form->selectTab('LDAP settings');
		}

		// Open and fill LDAP settings form.
		$this->query('id:ldap_configured')->asCheckbox()->one()->set($ldap_configured);
		if (!$exists) {
			$ldaps = [
				[
					'Name' => $values,
					'Host' => $values,
					'Base DN' => $values,
					'Search attribute' => $values
				]
			];
		}

		// Fill LDAP server form.
		foreach ($ldaps as $ldap) {
			$form->query($query)->one()->click();
			$ldap_form = COverlayDialogElement::find()->waitUntilReady()->asForm()->one();
			$ldap_form->fill($ldap)->submit();
		}
	}

	/**
	 * Create or update LDAP server values.
	 *
	 * @param array $data	   data provider
	 * @param string $query    object to click for LDAP creating or updating
	 */
	private function checkLdap($data, $query) {
		$form = $this->openLdapForm('LDAP', true);

		// Configuration at 'LDAP settings' tab.
		if (array_key_exists('ldap_settings', $data)) {
			$this->setLdap($data['ldap_settings'], $query);

			// Check error message in ldap creation form.
			if (array_key_exists('ldap_error', $data)) {
				$this->assertMessage(TEST_BAD, $data['ldap_error'], $data['ldap_error_details']);
				COverlayDialogElement::find()->one()->close();
			}
		}

		$form->submit();

		if ($this->page->isAlertPresent()) {
			$this->page->acceptAlert();
		}
	}
}
