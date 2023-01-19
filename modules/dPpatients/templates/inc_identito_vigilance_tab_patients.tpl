{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.setTabCount('tab_duplicates_patients', {{$duplicates.count_duplicates}});

    var export_btn = $('export-button');

    export_btn.observe(
      'click',
      function (e) {
        if (export_btn.get('confirmed') !== '1') {
          Event.stop(e);

          Modal.confirm(
            'Cette opération peut se révéler coûteuse vis-à-vis des performances de l\'application.\n' +
            'Ne poursuivez que si vous êtes certain de votre action.',
            {
              yesLabel: $T('common-action-Export'),
              noLabel:  $T('Cancel'),
              onOK:     function () {
                export_btn.set('confirmed', '1');
                export_btn.click();
                export_btn.set('confirmed', '0');
              }
            }
          );
        }
      });
  });
</script>

<table class="main tbl">
  <tr>
    <td colspan="7">
      <a id="export-button" data-confirmed="0" class="button fas fa-external-link-alt singleclick" target="_blank"
         href="?m=patients&raw=ajax_export_duplicates">
        {{tr}}common-action-Export{{/tr}}
      </a>
    </td>
  </tr>

  <tr>
    <td colspan="7">
      {{mb_include module=system template=inc_pagination total=$duplicates.count_duplicates current=$start_duplicates step=$step change_page="PatientSignature.pageChange" change_page_arg="duplicates"}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}CPatient|pl{{/tr}}</th>
    <th class="narrow">{{mb_title class=CPatient field=naissance}}</th>
    <th class="narrow">{{mb_title class=CPatient field=creation_date}}</th>
    <th class="narrow">{{mb_title class=CPatient field=creator_id}}</th>
    <th class="narrow">{{tr}}CPatientSignature-merge{{/tr}}</th>
    <th class="narrow">{{tr}}CPatientSignature-homonyme-ok{{/tr}}</th>
  </tr>

  {{foreach from=$duplicates.duplicates item=_doublon}}
    <tr>
      <td>
        {{foreach from=$_doublon.patients item=_patient}}

          {{if $_doublon.patients|@count > 2}}
            <input class="select-pat-{{$_doublon.signature}}" type="checkbox" value="{{$_patient->_id}}"
                   onclick="PatientSignature.checkOnlyTwoSelected(this, '{{$_doublon.signature}}');" />
          {{/if}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
            {{$_patient->_view}}
          </span>
          <br />
        {{/foreach}}
      </td>

      <td style="text-align: center;">
        {{$_doublon.naissance|date_format:$conf.date}}
      </td>

      <td style="text-align: center;">
        {{foreach from=$_doublon.patients item=_patient}}
          {{mb_value object=$_patient field=creation_date}}
          <br />
        {{/foreach}}
      </td>

      <td>
        {{foreach from=$_doublon.patients item=_patient}}
          {{mb_value object=$_patient field=creator_id tooltip=true}}
          <br />
        {{/foreach}}
      </td>

      <td>
        <form name="merge-patients-{{$_doublon.signature}}" method="get" action="?"
              onsubmit="return PatientSignature.mergePatientsModal(this, '{{$_doublon.signature}}')">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="a" value="object_merger" />
          <input type="hidden" name="objects_class" value="CPatient" />
          <input type="hidden" name="readonly_class" value="1" />
          {{if $_doublon.patients|@count == 2}}
            <input type="hidden" name="objects_id[0]" value="{{$_doublon.ids.0}}" />
            <input type="hidden" name="objects_id[1]" value="{{$_doublon.ids.1}}" />
            <input type="hidden" name="fields_ok" value="1" />
          {{/if}}

          <button class="change" type="submit">{{tr}}CPatientSignature-merge{{/tr}}</button>
        </form>
      </td>
      <td>
        <form name="create-homonymes-{{$_doublon.signature}}" action="?" method="post"
              onsubmit="{{if $_doublon.patients|@count > 2}}PatientSignature.setPatientsHomonymes(this, '{{$_doublon.signature}}');{{/if}}
                return onSubmitFormAjax(this, function() { return getForm('show_table_duplicates_patients').onsubmit();
                })">
          <input type="hidden" name="m" value="patients" />
          <input type="hidden" name="dosql" value="do_create_homonymes_aed" />
          <input type="hidden" class="pat-homonyme-{{$_doublon.signature}}" name="patient_1"
                 {{if $_doublon.patients|@count == 2}}value="{{$_doublon.ids.0}}"{{/if}}/>
          <input type="hidden" class="pat-homonyme-{{$_doublon.signature}}" name="patient_2"
                 {{if $_doublon.patients|@count == 2}}value="{{$_doublon.ids.1}}"{{/if}}/>

          <button class="far fa-bookmark" type="submit">{{tr}}CPatientSignature-homonyme-ok{{/tr}}</button>
        </form>
      </td>
    </tr>
  {{/foreach}}
</table>
