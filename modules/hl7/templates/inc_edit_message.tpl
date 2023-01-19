{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=exchange_data_format ajax=true}}

<button class="fas fa-sync" style="color: blue !important;" type="button" {{if $exchange->reprocess >= $conf.eai.max_reprocess_retries}}disabled{{/if}}
        onclick="ExchangeDataFormat.reprocessAndExchangeDetails('{{$exchange->_guid}}')"
        title="{{tr}}Reprocess{{/tr}} ({{$exchange->reprocess}}/{{$conf.eai.max_reprocess_retries}} fois)"> {{tr}}Reprocess{{/tr}}
</button>