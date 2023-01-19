<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig['eai'] = array (
  'exchange_format_delayed'        => '30',
  'max_files_to_process'           => '50',
  'max_reprocess_retries'          => '5',
  'use_domain'                     => '0',
  'use_routers'                    => '0',
  'send_messages_with_same_group'  => '0',
  'tunnel_pass'                    => '0',
  'message_supported'              => '0',
  'nb_max_export_csv'              => '100',
  'nb_files_retention_mb_excludes' => '30',
  'CExchangeDataFormat' => array(
    'purge_probability'      => '100',
    'purge_empty_threshold'  => '28',
    'purge_delete_threshold' => '168'
  ),
  'CExchangeTransportLayer' => array(
    'purge_probability'      => '100',
    'purge_empty_threshold'  => '28',
    'purge_delete_threshold' => '168'
  ),
);
