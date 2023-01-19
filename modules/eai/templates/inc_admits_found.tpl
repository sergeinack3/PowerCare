{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CExchangeDataFormat-msg-Admit found|pl{{/tr}}</legend>
  {{if !$nda_message}}
    <table class="main tbl">
      {{foreach from=$admits_found item=_admit_found}}
        <tr>
          <td>
            <input type="radio" name="input_nda_admit" onchange="valueInput('new_nda', '{{$_admit_found->_ref_NDA->id400}}')"/>
            <strong>{{tr}}CSejour{{/tr}}</strong> :
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_admit_found->_guid}}');">{{$_admit_found->_view}}</span>
            <br/>
            <strong style="margin-left : 15px;">{{tr}}NDA{{/tr}}</strong> : {{$_admit_found->_ref_NDA->id400}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td>
            <div class="small-warning">{{tr}}CExchangeDataFormat-msg-None admit for this patient{{/tr}}</div>
          </td>
        </tr>
      {{/foreach}}
    </table>
  {{else}}
    {{if $admit_found->_id}}
      <div class="small-info">{{tr}}CExchangeDataFormat-msg-Admit found by NDA message{{/tr}}</div>
      <strong>{{tr}}CSejour{{/tr}}</strong> :
      <span onmouseover="ObjectTooltip.createEx(this, '{{$admit_found->_guid}}');">
        {{$admit_found->_view}}
      </span>
      <br/>
      <strong>{{tr}}NDA{{/tr}}</strong> : {{$admit_found->_ref_NDA->id400}}
    {{else}}
      <div class="small-warning">{{tr}}CExchangeDataFormat-msg-Admit not found by NDA message{{/tr}}</div>
    {{/if}}
  {{/if}}
</fieldset>