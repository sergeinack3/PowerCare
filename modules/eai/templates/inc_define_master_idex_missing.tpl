{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="defineMasterIdexMissing" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="exchange_guid" value="{{$exchange->_guid}}" />

  <input type="hidden" name="dosql" value="do_define_idex_missing" />

  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}CExchangeDataFormat-msg-define idex master missing{{/tr}}</th>
    </tr>

    {{if $master_IPP_missing}}
    <tr>
      <th>{{tr}}CExchangeDataFormat-master_IPP_missing{{/tr}}</th>
      <td>
        <input type="text" name="IPP" value="{{$patient->_IPP}}" {{if !$patient->_IPP}}readonly{{/if}}
                 placeholder="{{tr}}CExchangeDataFormat-msg-IPP none{{/tr}}"/>
        <button class="hslip notext" {{if !$patient->_IPP}}disabled{{/if}}
                title="{{tr}}CExchangeDataFormat-msg-inject IPP in the message{{/tr}}" type="submit">
          {{tr}}CExchangeDataFormat-msg-inject IPP in the message{{/tr}}
        </button>
      </td>
    </tr>
    {{/if}}

    {{if $master_NDA_missing}}
      <tr>
        <th>{{tr}}CExchangeDataFormat-master_NDA_missing{{/tr}}</th>
        <td>
          <input type="text" name="NDA" value="{{$sejour->_NDA}}" {{if !$sejour->_NDA}}readonly{{/if}}
                   placeholder="{{tr}}CExchangeDataFormat-msg-NDA none{{/tr}}"/>
          <button class="hslip notext" {{if !$sejour->_NDA}}disabled{{/if}}
                  title="{{tr}}CExchangeDataFormat-msg-inject NDA in the message{{/tr}}" type="submit">
            {{tr}}CExchangeDataFormat-msg-inject NDA in the message{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>