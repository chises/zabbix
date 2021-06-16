<?php declare(strict_types = 1);
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
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


/**
 * Class containing methods for operations with authentication parameters.
 */
class CAuthentication extends CApiService {

	public const ACCESS_RULES = [
		'get' => ['min_user_type' => USER_TYPE_SUPER_ADMIN],
		'update' => ['min_user_type' => USER_TYPE_SUPER_ADMIN]
	];

	/**
	 * @var string
	 */
	protected $tableName = 'config';

	/**
	 * @var string
	 */
	protected $tableAlias = 'c';

	/**
	 * @var array
	 */
	private $output_fields = ['authentication_type', 'http_auth_enabled', 'http_login_form', 'http_strip_domains',
		'http_case_sensitive', 'ldap_configured', 'ldap_host', 'ldap_port', 'ldap_base_dn', 'ldap_search_attribute',
		'ldap_bind_dn', 'ldap_case_sensitive', 'ldap_bind_password', 'saml_auth_enabled', 'saml_idp_entityid',
		'saml_sso_url', 'saml_slo_url', 'saml_username_attribute', 'saml_sp_entityid', 'saml_nameid_format',
		'saml_sign_messages', 'saml_sign_assertions', 'saml_sign_authn_requests', 'saml_sign_logout_requests',
		'saml_sign_logout_responses', 'saml_encrypt_nameid', 'saml_encrypt_assertions', 'saml_case_sensitive',
		'passwd_min_length', 'passwd_check_rules'
	];

	/**
	 * Get authentication parameters.
	 *
	 * @param array $options
	 *
	 * @throws APIException if the input is invalid.
	 *
	 * @return array
	 */
	public function get(array $options): array {
		$api_input_rules = ['type' => API_OBJECT, 'fields' => [
			'output' =>	['type' => API_OUTPUT, 'in' => implode(',', $this->output_fields),
				'default' => API_OUTPUT_EXTEND]
		]];
		if (!CApiInputValidator::validate($api_input_rules, $options, '/', $error)) {
			self::exception(ZBX_API_ERROR_PARAMETERS, $error);
		}

		if ($options['output'] === API_OUTPUT_EXTEND) {
			$options['output'] = $this->output_fields;
		}

		$db_auth = [];

		$result = DBselect($this->createSelectQuery($this->tableName(), $options));
		while ($row = DBfetch($result)) {
			$db_auth[] = $row;
		}
		$db_auth = $this->unsetExtraFields($db_auth, ['configid'], []);

		return $db_auth[0];
	}

	/**
	 * Update authentication parameters.
	 *
	 * @param array  $auth
	 *
	 * @return array
	 */
	public function update(array $auth): array {
		$db_auth = $this->validateUpdate($auth);

		$upd_config = [];

		// strings
		$field_names = ['http_strip_domains', 'ldap_host', 'ldap_base_dn', 'ldap_search_attribute', 'ldap_bind_dn',
			'ldap_bind_password'
		];
		foreach ($field_names as $field_name) {
			if (array_key_exists($field_name, $auth) && $auth[$field_name] !== $db_auth[$field_name]) {
				$upd_config[$field_name] = $auth[$field_name];
			}
		}

		// integers
		$field_names = ['authentication_type', 'http_auth_enabled', 'http_login_form', 'http_case_sensitive',
			'ldap_configured', 'ldap_port', 'ldap_case_sensitive', 'saml_auth_enabled', 'saml_idp_entityid',
			'saml_sso_url', 'saml_slo_url', 'saml_username_attribute', 'saml_sp_entityid', 'saml_nameid_format',
			'saml_sign_messages', 'saml_sign_assertions', 'saml_sign_authn_requests', 'saml_sign_logout_requests',
			'saml_sign_logout_responses', 'saml_encrypt_nameid', 'saml_encrypt_assertions', 'saml_case_sensitive'
		];
		foreach ($field_names as $field_name) {
			if (array_key_exists($field_name, $auth) && $auth[$field_name] != $db_auth[$field_name]) {
				$upd_config[$field_name] = $auth[$field_name];
			}
		}

		if ($upd_config) {
			DB::update('config', [
				'values' => $upd_config,
				'where' => ['configid' => $db_auth['configid']]
			]);
		}

		$this->addAuditBulk(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_AUTHENTICATION,
			[['configid' => $db_auth['configid']] + $auth], [$db_auth['configid'] => $db_auth]
		);

		return array_keys($auth);
	}

	/**
	 * Validate updated authentication parameters.
	 *
	 * @param array  $auth
	 *
	 * @throws APIException if the input is invalid.
	 *
	 * @return array
	 */
	protected function validateUpdate(array $auth): array {
		$api_input_rules = ['type' => API_OBJECT, 'flags' => API_NOT_EMPTY, 'fields' => [
			'authentication_type' =>		['type' => API_INT32, 'in' => ZBX_AUTH_INTERNAL.','.ZBX_AUTH_LDAP],
			'http_auth_enabled' =>			['type' => API_INT32, 'in' => ZBX_AUTH_HTTP_DISABLED.','.ZBX_AUTH_HTTP_ENABLED],
			'http_login_form' =>			['type' => API_INT32, 'in' => ZBX_AUTH_FORM_ZABBIX.','.ZBX_AUTH_FORM_HTTP],
			'http_strip_domains' =>			['type' => API_STRING_UTF8, 'length' => DB::getFieldLength('config', 'http_strip_domains')],
			'http_case_sensitive' =>		['type' => API_INT32, 'in' => ZBX_AUTH_CASE_INSENSITIVE.','.ZBX_AUTH_CASE_SENSITIVE],
			'ldap_configured' =>			['type' => API_INT32, 'in' => ZBX_AUTH_LDAP_DISABLED.','.ZBX_AUTH_LDAP_ENABLED],
			'ldap_host' =>					['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'ldap_host')],
			'ldap_port' =>					['type' => API_INT32, 'in' => '0:65535'],
			'ldap_base_dn' =>				['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'ldap_base_dn')],
			'ldap_search_attribute' =>		['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'ldap_search_attribute')],
			'ldap_bind_dn' =>				['type' => API_STRING_UTF8, 'length' => DB::getFieldLength('config', 'ldap_bind_dn')],
			'ldap_case_sensitive' =>		['type' => API_INT32, 'in' => ZBX_AUTH_CASE_INSENSITIVE.','.ZBX_AUTH_CASE_SENSITIVE],
			'ldap_bind_password' =>			['type' => API_STRING_UTF8, 'length' => DB::getFieldLength('config', 'ldap_bind_password')],
			'saml_auth_enabled' =>			['type' => API_INT32, 'in' => ZBX_AUTH_HTTP_DISABLED.','.ZBX_AUTH_HTTP_ENABLED],
			'saml_idp_entityid' =>			['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'saml_idp_entityid')],
			'saml_sso_url' =>				['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'saml_sso_url')],
			'saml_slo_url' =>				['type' => API_STRING_UTF8, 'length' => DB::getFieldLength('config', 'saml_slo_url')],
			'saml_username_attribute' =>	['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'saml_username_attribute')],
			'saml_sp_entityid' =>			['type' => API_STRING_UTF8, 'flags' => API_NOT_EMPTY, 'length' => DB::getFieldLength('config', 'saml_sp_entityid')],
			'saml_nameid_format' =>			['type' => API_STRING_UTF8, 'length' => DB::getFieldLength('config', 'saml_nameid_format')],
			'saml_sign_messages' =>			['type' => API_INT32, 'in' => '0,1'],
			'saml_sign_assertions' =>		['type' => API_INT32, 'in' => '0,1'],
			'saml_sign_authn_requests' =>	['type' => API_INT32, 'in' => '0,1'],
			'saml_sign_logout_requests' =>	['type' => API_INT32, 'in' => '0,1'],
			'saml_sign_logout_responses' =>	['type' => API_INT32, 'in' => '0,1'],
			'saml_encrypt_nameid' =>		['type' => API_INT32, 'in' => '0,1'],
			'saml_encrypt_assertions' =>	['type' => API_INT32, 'in' => '0,1'],
			'saml_case_sensitive' =>		['type' => API_INT32, 'in' => ZBX_AUTH_CASE_INSENSITIVE.','.ZBX_AUTH_CASE_SENSITIVE],
			'passwd_min_length' =>			['type' => API_INT32, 'in' => '1:255', 'default' => DB::getDefault('config', 'passwd_min_length')],
			'passwd_check_rules' =>			['type' => API_INT32, 'in' => PASSWD_CHECK_LENGTH.':'.(PASSWD_CHECK_LENGTH | PASSWD_CHECK_CASE | PASSWD_CHECK_DIGITS | PASSWD_CHECK_SPECIAL | PASSWD_CHECK_SIMPLE), 'default' => DB::getDefault('config', 'passwd_check_rules')],
		]];
		if (!CApiInputValidator::validate($api_input_rules, $auth, '/', $error)) {
			self::exception(ZBX_API_ERROR_PARAMETERS, $error);
		}

		// Check permissions.
		if (self::$userData['type'] != USER_TYPE_SUPER_ADMIN) {
			self::exception(ZBX_API_ERROR_PERMISSIONS, _('No permissions to referred object or it does not exist!'));
		}

		// Check if password minimal length is sufficient to validate multiple rules at once.
		if (($auth['passwd_min_length'] == 1
					&& ((($auth['passwd_check_rules'] & PASSWD_CHECK_CASE) == PASSWD_CHECK_CASE
						&& ($auth['passwd_check_rules'] & PASSWD_CHECK_DIGITS) == PASSWD_CHECK_DIGITS)
					|| (($auth['passwd_check_rules'] & PASSWD_CHECK_CASE) == PASSWD_CHECK_CASE
						&& ($auth['passwd_check_rules'] & PASSWD_CHECK_SPECIAL) == PASSWD_CHECK_SPECIAL)
					|| (($auth['passwd_check_rules'] & PASSWD_CHECK_DIGITS) == PASSWD_CHECK_DIGITS
						&& ($auth['passwd_check_rules'] & PASSWD_CHECK_SPECIAL) == PASSWD_CHECK_SPECIAL)))
				|| ($auth['passwd_min_length'] == 2
					&& (($auth['passwd_check_rules'] & PASSWD_CHECK_CASE) == PASSWD_CHECK_CASE
						&& ($auth['passwd_check_rules'] & PASSWD_CHECK_DIGITS) == PASSWD_CHECK_DIGITS
						&& ($auth['passwd_check_rules'] & PASSWD_CHECK_SPECIAL) == PASSWD_CHECK_SPECIAL))) {
			self::exception(ZBX_API_ERROR_PARAMETERS,
				_s('Invalid parameter "%1$s": %2$s.', '/passwd_check_rules',
					_('length is not sufficient for selected password requirements')
				)
			);
		}

		$output_fields = $this->output_fields;
		$output_fields[] = 'configid';

		return DB::select('config', ['output' => $output_fields])[0];
	}
}
