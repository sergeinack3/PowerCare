{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$messages item=message}}
  {{mb_include module=jfse template=inc_message type=$message.type message=$message.text}}
{{/foreach}}
