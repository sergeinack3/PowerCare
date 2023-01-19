{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
    EAITransformationRule.unserialize(EAITransformationRule.action_type_selected);
</script>
{{if $action_type == 'map' || $action_type == 'insert'}}
  <tr>
    <th><label for="param1">{{tr}}CTransformationRule.params.{{$action_type}}.param{{/tr}}</label></th>
    <td><input size="25" class="actionParams notNull" type="text" name="param1"></td>
  </tr>
  {{if $action_type == 'map'}}
  <tr>
    <td></td>
    <td>
        <div class="small-info">{{tr}}CTransformationRule-msg-Example map{{/tr}}</div>
    </td>
  </tr>
  {{/if}}
{{elseif $action_type == 'trim'}}
  <tr>
    <th><label for="param1">{{tr}}CTransformationRule.params.trim.param1{{/tr}}</label></th>
    <td>
      <select class="actionParams" type="text" name="param1">
        <option value="">{{tr}}CTransformationRule-msg-Choose trim type{{/tr}}</option>
        <option value="rtrim">{{tr}}CTransformationRule.params.trim.param1.opt1{{/tr}}</option>
        <option value="ltrim">{{tr}}CTransformationRule.params.trim.param1.opt2{{/tr}}</option>
        <option value="trim">{{tr}}CTransformationRule.params.trim.param1.opt3{{/tr}}</option>
      </select>
    </td>
  </tr>
{{elseif $action_type == 'sub'}}
  <tr>
    <th><label for="param1">{{tr}}CTransformationRule.params.sub.param1{{/tr}}</label></th>
    <td><input class="actionParams" type="number" name="param1"></td>
  </tr>
  <tr>
    <th><label for="param2">{{tr}}CTransformationRule.params.sub.param2{{/tr}}</label></th>
    <td><input class="actionParams" type="number" name="param2"></td>
  </tr>
{{elseif $action_type == 'pad'}}
  <tr>
    <th><label for="param1">{{tr}}CTransformationRule.params.pad.param1{{/tr}}</label></th>
    <td><input class="actionParams num" type="number" name="param1"></td>
  </tr>
  <tr>
    <th><label for="param2">{{tr}}CTransformationRule.params.pad.param2{{/tr}}</label></th>
    <td><input size="25" class="actionParams" type="text" name="param2"></td>
  </tr>
  <tr>
    <th><label for="param3">{{tr}}CTransformationRule.params.pad.param3{{/tr}}</label></th>
    <td>
      <select class="actionParams" type="text" name="param3">
        <option value="STR_PAD_RIGHT">{{tr}}CTransformationRule.params.pad.param3.opt1{{/tr}}</option>
        <option value="STR_PAD_LEFT">{{tr}}CTransformationRule.params.pad.param3.opt2{{/tr}}</option>
        <option value="STR_PAD_BOTH">{{tr}}CTransformationRule.params.pad.param3.opt3{{/tr}}</option>
      </select>
    </td>
  </tr>
{{elseif $action_type == 'copy'}}
    <tr>
        <th><label for="param1">{{tr}}CTransformationRule.params.copy.param1{{/tr}}</label></th>
        <td>
            <select class="actionParams" type="text" name="param1">
                <option value="">{{tr}}CTransformationRule-msg-Choose copy type{{/tr}}</option>
                <option value="all">{{tr}}CTransformationRule.params.copy.param1.opt1{{/tr}}</option>
                <option value="group">{{tr}}CTransformationRule.params.copy.param1.opt2{{/tr}}</option>
                <option value="segment">{{tr}}CTransformationRule.params.copy.param1.opt3{{/tr}}</option>
            </select>
        </td>
    </tr>
{{elseif $action_type == 'concat'}}
    <tr>
        <th><label for="param1">{{tr}}CTransformationRule.params.concat.param1{{/tr}}</label></th>
        <td>
            <select class="actionParams" type="text" name="param1">
                <option value="">{{tr}}CTransformationRule-msg-Choose concat type{{/tr}}</option>
                <option value="all">{{tr}}CTransformationRule.params.concat.param1.opt1{{/tr}}</option>
                <option value="group">{{tr}}CTransformationRule.params.concat.param1.opt2{{/tr}}</option>
                <option value="segment">{{tr}}CTransformationRule.params.concat.param1.opt3{{/tr}}</option>
            </select>
        </td>
    </tr>
{{/if}}
<tr>
  <td class="button" colspan="2">
    <button class="tick button" type="button" onclick="EAITransformationRule.serializeParams();">
      {{tr}}CTransformationRule.params.serialize{{/tr}}
    </button>
  </td>
</tr>
