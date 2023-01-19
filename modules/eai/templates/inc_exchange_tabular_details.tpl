{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $exchange|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2'}}
  {{mb_include template=inc_exchange_er7_details}}
{{elseif $exchange|instanceof:'Ox\Interop\Hprim21\CEchangeHprim21' || $exchange|instanceof:'Ox\Interop\Hprimsante\CExchangeHprimSante'}}
  {{mb_include template=inc_exchange_hpr_details}}
{{/if}}
      