<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig['admin'] = [
    'CUser'                   => [
        'strong_password'                                 => '1',
        'apply_all_users'                                 => '0',
        'enable_admin_specific_strong_password'           => '0',
        'max_login_attempts'                              => '5',
        'lock_expiration_time'                            => '60',
        'allow_change_password'                           => '1',
        'force_changing_password'                         => '0',
        'password_life_duration'                          => '3 month',
        'reuse_password_probation_period'                 => 'none',
        'coming_password_expiration_threshold'            => '',
        'custom_password_recommendations'                 => '',
        'force_inactive_old_authentification'             => 180,
        'probability_force_inactive_old_authentification' => 100,
    ],
    'LDAP'                    => [
        'ldap_connection'                => '0',
        'ldap_tag'                       => 'ldap',
        'object_guid_mode'               => 'hexa',
        'allow_change_password'          => '0',
        'allow_login_as_admin'           => '0',
        'check_ldap_password_expiration' => '1',
    ],
    'CPasswordSpec'           => [
        'strong_password_min_length'    => '6',
        'strong_password_alpha_chars'   => '1',
        'strong_password_upper_chars'   => '0',
        'strong_password_num_chars'     => '1',
        'strong_password_special_chars' => '0',

        'admin_strong_password_min_length'    => '6',
        'admin_strong_password_alpha_chars'   => '1',
        'admin_strong_password_upper_chars'   => '0',
        'admin_strong_password_num_chars'     => '1',
        'admin_strong_password_special_chars' => '0',
    ],
    'CKerberosLdapIdentifier' => [
        'enable_kerberos_authentication' => '0',
        'enable_login_button'            => '0',
        'enable_automapping'             => '0',
    ],
    'ProSanteConnect'         => [
        'enable_psc_authentication' => '0',
        'enable_login_button'       => '0',
        'enable_automapping'        => '0',
        'session_mode'              => '0',
        'client_id'                 => '',
        'client_secret'             => '',
        'redirect_uri'              => '',
    ],
    'FranceConnect'           => [
        'enable_fc_authentication' => '0',
        'enable_login_button'      => '0',
        'client_id'                => '',
        'client_secret'            => '',
        'redirect_uri'             => '',
        'logout_redirect_uri'      => '',
    ],
    'CRGPDConsent'            => [
        'user_id' => '',
    ],
    'CViewAccessToken'        => [
        'cron_name' => 'cronmajauto',
        'modules'   => 'monitorClient',
    ],
];
