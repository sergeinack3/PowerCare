{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $rpu->_id || $protocoles|@count <= 1}}
    {{mb_return}}
{{/if}}

{{mb_script module=urgences script=protocole_rpu}}

<script>
  ProtocoleRPU.protocoles = {{$protocoles|@json}};
</script>
<span class="me-white-context">
  <button type="button" class="search me-white" style="float: left;" onclick="Modal.open('protocoles_rpu');">
    {{tr}}CProtocoleRPU.choose_protocole{{/tr}}
  </button>
</span>

<div id="protocoles_rpu" style="display: none;">
  <table class="tbl">
    <tr>
      <th>
          {{tr}}CProtocoleRPU.choose_protocole{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        <select name="protocole_id" style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$protocoles item=_protocole}}
              <option value="{{$_protocole->_id}}" {{if $_protocole->default}}selected{{/if}}>{{$_protocole}}</option>
            {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="tick" onclick="ProtocoleRPU.applyProtocole();">{{tr}}Apply{{/tr}}</button>
        <button type="button" class="cancel" onclick="ProtocoleRPU.cancelProtocole();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>
