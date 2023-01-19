{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextStepPatients = function () {
    var form = getForm("export-patients-form");
    $V(form.start, parseInt($V(form.start)) + parseInt($V(form.step)));

    if ($V(form.auto)) {
      form.onsubmit();
    }
  };

  Main.add(function () {
    var patientForm = getForm("export-patients-form");
    Calendar.regField(patientForm.date_min);
    Calendar.regField(patientForm.date_max);
  });
</script>

<div class="small-info">
  <ul>
    <li>{{tr}}dPpatients-export-Infos 1{{/tr}}</li>
    <li>{{tr}}dPpatients-export-Infos 3{{/tr}}</li>
  </ul>
</div>

<form name="export-patients-form" method="post" onsubmit="return onSubmitFormAjax(this, {useDollarV: true}, 'export-log-patients')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_export_patients" />
  <input type="hidden" name="praticien_id" value=""/>

  <table class="main form">
    <tr>
      <th class="section" colspan="4">{{tr}}dPpatients-export-Basic infos{{/tr}}</th>
    </tr>

    <tr>
      <th>
        <label for="directory">{{tr}}dPpatients-export-Target directory{{/tr}}</label>
      </th>
      <td colspan="3">
        <input type="text" name="directory" value="{{$directory}}" size="60" onchange="ExportPatients.checkDirectory(this)" />
        <div id="directory-check"></div>
      </td>
    </tr>

    <tr>
      <th>
        <label for="directory_name">{{tr}}dPpatients-export-Directory name{{/tr}}</label>
      </th>
      <td colspan="3">
        <input type="text" name="directory_name" value="{{$directory_name}}" size="30"/>
      </td>
    </tr>

    <tr>
      <th class="narrow">
        <label for="start">{{tr}}Start{{/tr}}</label>
      </th>
      <td class="narrow">
        <input type="text" name="start" value="{{$start}}" size="4" />
      </td>

      <th class="narrow">
        <label for="step">{{tr}}Step{{/tr}}</label>
      </th>
      <td class="narrow">
        <input type="text" name="step" value="{{$step}}" size="4" />
      </td>
    </tr>

    <tr>
      <th class="narrow">
        <label for="auto">{{tr}}Auto{{/tr}}</label>
      </th>
      <td colspan="3">
        <input type="checkbox" name="auto" value="1" />
      </td>
    </tr>

    <tr>
      <th class="section" colspan="4">
        {{tr}}dPpatients-export-Date options{{/tr}}
      </th>
    </tr>

    <tr>
      <th>
        <label for="date_min">{{tr}}dPpatients-export-Date min{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="date_min" value="{{$date_min}}" />
      </td>

      <th>
        <label for="date_max">{{tr}}dPpatients-export-Date max{{/tr}}</label>
      </th>
      <td colspan="3">
        <input type="hidden" name="date_max" value="{{$date_max}}" />
      </td>
    </tr>

    <tr>
      <th colspan="4" class="section">{{tr}}dPpatients-export-Other options{{/tr}}</th>
    </tr>

    <tr>
      <th>
        <label for="patient_id">Patient Id</label>
      </th>
      <td>
        <input type="text" size="5" name="patient_id" value="{{$patient_id}}" />
      </td>

      <th>
        <label for="ignore_consult_tag">{{tr}}CMbObjectExport-Option-Ignore futur with tag{{/tr}}</label>
      </th>
      <td >
        <input type="checkbox" name="ignore_consult_tag" value="1" {{if $ignore_consult_tag}}checked{{/if}}/>
      </td>
    </tr>

    <tr>
      <th colspan="4" class="section">{{tr}}dPpatients-export-Archive options{{/tr}}</th>
    </tr>

    <tr>
      <th>
        <label for="archive_sejour">{{tr}}dPpatients-export-Archive sejours{{/tr}}</label>
      </th>
      <td>
        <input type="checkbox" name="archive_sejour" value="1" />
      </td>

      <th>
        <label for="archive_mode">{{tr}}dPpatients-export-Archive mode{{/tr}}</label>
      </th>
      <td>
        <input type="checkbox" name="archive_mode" value="1" />
      </td>
    </tr>

    <tr>
      <th>
        <label for="archive_type">{{tr}}CXMLPatientExport-archive_type{{/tr}}</label>
      </th>
      <td colspan="3">
        <label>
          <input type="radio" name="archive_type" value="none" {{if $archive_type === 'none'}}checked{{/if}}>
            {{tr}}CXMLPatientExport.archive_type.none{{/tr}}
        </label>
        <label>
          <input type="radio" name="archive_type" value="tar" {{if $archive_type === 'tar'}}checked{{/if}}>
            {{tr}}CXMLPatientExport.archive_type.tar{{/tr}}
        </label>
        {{if $zip_available}}
          <label>
            <input type="radio" name="archive_type" value="zip" {{if $archive_type === 'zip'}}checked{{/if}}>
              {{tr}}CXMLPatientExport.archive_type.zip{{/tr}}
          </label>
        {{/if}}
      </td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button class="fas fa-external-link-alt">{{tr}}CPatient-action Export XML{{/tr}}</button>
      </td>
    </tr>
  </table>

</form>

<div id="export-log-patients"></div>
