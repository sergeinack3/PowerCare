{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $("list-backprop-{{$back_name}}").fixedTableHeaders();
  });
</script>

<div id="list-backprop-{{$back_name}}">
  <table class="tbl">
    <tbody>
    {{if array_key_exists($back_name, $old_patient->_back)}}
      {{foreach from=$old_patient->_back[$back_name] key=_id item=_obj}}
        <tr>
          <td class="narrow">
            <input type="radio" name="{{$back_name}}-{{$_id}}" value="{{$old_patient->_id}}"
                   onclick="PatientUnmerge.countRadioClick('{{$back_name}}-{{$_id}}')" />
          </td>
          {{if !array_key_exists($_id, $merged_backs) && $back_name != 'identifiants'}}
            <td class="narrow warning">
              &larr;
            </td>
          {{else}}
            <td class="narrow"></td>
          {{/if}}

          <td align="center">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_obj->_guid}}')">
              {{$_obj}}
            </span>
          </td>

          {{if array_key_exists($_id, $merged_backs) && $merged_backs[$_id] == 'new'}}
            <td class="narrow warning">
              &rarr;
            </td>
          {{else}}
            <td class="narrow"></td>
          {{/if}}
          <td class="narrow">
            <input type="radio" name="{{$back_name}}-{{$_id}}" value="{{$new_patient->_id}}"
                   onclick="PatientUnmerge.countRadioClick('{{$back_name}}-{{$_id}}')" />
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td class="empty">{{tr}}mod-dPpatients-backprop.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    {{else}}
      <tr>
        <td class="empty">{{tr}}mod-dPpatients-backprop.none{{/tr}}</td>
      </tr>
    {{/if}}
    </tbody>
    <thead>
    <tr>
      <th colspan="5">
        <span style="float: left; padding-left: 5%" onmouseover="ObjectTooltip.createEx(this, '{{$old_patient->_guid}}')">
          {{tr}}CPatient-unmerge-old{{/tr}}
          <br />
          {{$old_patient}}
          <br />
          [#{{$old_patient->_id}}] [IPP: {{$old_patient->_IPP}}]
        </span>

        <span style="float: right; padding-right: 5%" onmouseover="ObjectTooltip.createEx(this, '{{$new_patient->_guid}}')">
          {{tr}}CPatient-unmerge-new{{/tr}}
          <br />
          {{$new_patient}}
          <br />
          [#{{$new_patient->_id}}] [IPP: {{$new_patient->_IPP}}]
        </span>
      </th>
    </tr>
    </thead>
  </table>
</div>