{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uid}}

<form name="set-session-receiver{{$uid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="dosql" value="do_set_session_receiver_aed" />
  <input type="hidden" name="m" value="hl7" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    <tr>
      <td colspan="11">
        <label> {{tr}}CReceiverHL7v2{{/tr}} :</label>
        <select name="cn_receiver_guid" onchange="this.form.onsubmit()">
          <option value="none">{{tr}}Choose{{/tr}}</option>

          {{foreach from=$receivers item=_receiver}}
            {{if is_array($_receiver)}}
              {{foreach from=$_receiver item=_item_receiver}}
                <option value="{{$_item_receiver->_guid}}" {{if $_item_receiver->_guid == $cn_receiver_guid}}selected{{/if}}>
                  {{$_item_receiver->_view}}
                </option>
              {{foreachelse}}
                <option value="none" disabled>{{tr}}CReceiverHL7v2.none{{/tr}}</option>
              {{/foreach}}
            {{else}}
              <option value="{{$_receiver->_guid}}" {{if $_receiver->_guid == $cn_receiver_guid}}selected{{/if}}>
                {{$_receiver->_view}}
              </option>
            {{/if}}
          {{foreachelse}}
            <option value="none" disabled>{{tr}}CReceiverHL7v2.none{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  </table>
</form>