{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(
    function () {
      var cont = $('patient_groups_container'),
        element = cont.down('input[type=hidden]'),
        tokenField = new TokenField(element, {
          onChange: function () {
          }.bind(element)
        });

      window.patient_groups_token_field = tokenField;
    }
  );
</script>

<form name="manage_patient_groups" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="dosql" value="do_patient_groups" />
  <input type="hidden" name="patient_id" value="{{$patient->_id}}" />

  <table class="main form">
    <tr>
      <th colspan="2">
        <h1>{{$patient}}</h1>
      </th>
    </tr>

    <tr>
      <th class="narrow">
        <label title="{{tr}}CPatientGroup-Data sharing-desc{{/tr}}">
          {{tr}}CPatientGroup-Data sharing{{/tr}}
        </label>
      </th>

      <td colspan="2" id="patient_groups_container">
        <input type="hidden" name="patient_groups" value="{{$active_patient_groups}}" />

        <div class="columns-2">
          {{foreach from=$patient_groups key=_group_id item=_group_data}}
            {{assign var=patient_group_checked value='0'}}

            {{if ($_group_data.share === null && $g == $_group_id) || ($_group_data.share|instanceof:'Ox\Mediboard\Patients\CPatientGroup' && $_group_data.share->share)}}
              {{assign var=patient_group_checked value='1'}}
            {{/if}}
            <label>
              <input type="checkbox" name="chkbx_patient_group" onclick="window.patient_groups_token_field.toggle('{{$_group_id}}');"
                     {{if $patient_group_checked}}checked{{/if}} />

              {{$_group_data.label}}

              {{if $_group_data.share|instanceof:'Ox\Mediboard\Patients\CPatientGroup'}}
                <span class="compact">
                  (
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_group_data.share->_ref_user->_guid}}');">
                    {{$_group_data.share->_ref_user->_shortview}}
                  </span>
                  &bull; {{mb_value object=$_group_data.share field=last_modification}} )
                </span>
              {{else}}
                <span class="compact">(<i class="fa fa-ban" style="color: firebrick;"></i>)</span>
              {{/if}}
            </label>
            <br />
          {{/foreach}}
        </div>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button class="tick">{{tr}}common-action-Validate{{/tr}}</button>
        <button type="button" class="help notext" onclick="$('data-sharing-help').toggle();">{{tr}}Help{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{mb_include module=dPpatients template=inc_vw_data_sharing_help}}