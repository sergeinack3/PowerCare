{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Vaccination.editMultipleVaccination();
    Vaccination.addMultipleVaccination();
    Vaccination.printOtherVaccinations();
  });
</script>

<table class="tbl" id="other-injections-table">
  <tr>
    <th class="title" colspan="10">{{tr}}List of injections{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}Name{{/tr}}</th>
    <th>{{tr}}Health professional{{/tr}}</th>
    <th>{{tr}}Injection{{/tr}}</th>
    <th>{{tr}}Speciality{{/tr}}</th>
    <th>{{tr}}Remarques{{/tr}}</th>
    {{if $user_can_do->edit}}
      <th class="not-printable">
        <button class="add notext"
                title="{{tr}}common-add{{/tr}}"
                data-patient-id="{{$patient->_id}}"
                data-recall-age="{{$empty_injection->recall_age}}"
                data-types='[
                      {{foreach from=$empty_injection->_ref_vaccinations item=_vaccination name=type}}
                        "{{$_vaccination->type}}"
                        {{if !$smarty.foreach.type.last}}
                          ,
                        {{/if}}
                      {{/foreach}}
                    ]'
                data-label-read-only="{{$label_read_only}}"
                data-repeat="{{$repeat}}"></button>
      </th>
    {{/if}}
  </tr>
    {{foreach from=$injections item=_injection}}
      <tr>
        <td>
            {{foreach from=$_injection->_ref_vaccinations item=_vaccination}}
                {{$_vaccination->type}}
            {{/foreach}}
        </td>
        <td>{{$_injection->practitioner_name}}</td>
        <td>{{$_injection->injection_date|date_format:$conf.datetime}}</td>
        <td>{{$_injection->speciality}}</td>
        <td>{{$_injection->remarques}}</td>
        {{if $user_can_do->edit}}
          <td class="not-printable">
            <button class="edit notext"
                    title="{{tr}}common-edit{{/tr}}"
                    data-types='[
                      {{foreach from=$empty_injection->_ref_vaccinations item=_vaccination name=type}}
                        "{{$_vaccination->type}}"
                        {{if !$smarty.foreach.type.last}}
                          ,
                        {{/if}}
                      {{/foreach}}
                    ]'
                    data-patient-id="{{$patient->_id}}"
                    data-injection-id="{{$_injection->_id}}"
                    data-label-read-only="{{$label_read_only}}"
                    data-recall-age="{{$_injection->recall_age}}"
                    data-repeat="{{$repeat}}"></button>
          </td>
        {{/if}}
      </tr>
    {{foreachelse}}
      <tr><td colspan="10">{{tr}}CInjection.none{{/tr}}</td></tr>
    {{/foreach}}
</table>
