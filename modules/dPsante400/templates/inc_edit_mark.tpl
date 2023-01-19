{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog={{$dialog}}" method="post" onsubmit="return checkForm(this)">

  {{mb_class object=$mark}}
  {{mb_key   object=$mark}}

  <table class="form">

    <tr>
      {{if $mark->_id}}
        <th class="title modify text" colspan="2">
          {{tr}}CTriggerMark-title-modify{{/tr}}
          <div>'{{$mark}}'</div>
        </th>
      {{else}}
        <th class="title text me-th-new" colspan="2">
          {{tr}}CTriggerMark-title-create{{/tr}}
        </th>
      {{/if}}

    <tr>
      <th>{{mb_label object=$mark field=trigger_class}}</th>
      <td>
        <select name="trigger_class" class="{{$mark->_props.trigger_class}}">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$trigger_classes item=_class}}
            <option value="{{$_class}}" {{if $_class == $mark->trigger_class}} selected="selected" {{/if}}>
              {{tr}}{{$_class}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$mark field=trigger_number}}</th>
      <td>{{mb_field object=$mark field=trigger_number size=10}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$mark field=when}}</th>
      <td>{{mb_field object=$mark field=when form=Edit register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$mark field=mark}}</th>
      <td>{{mb_field object=$mark field=mark}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$mark field=done}}</th>
      <td>{{mb_field object=$mark field=done}}</td>
    </tr>


    <tr>
      <td class="button" colspan="2">
        {{if $mark->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {
            typeName: 'La Marque',
            objName: '{{$mark->_view|smarty:nodefaults|JSAttribute}}'
            })">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

  </table>

</form>
