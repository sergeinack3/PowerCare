{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextStepPatients = function () {
    var form = getForm("import-patients-form");
    $V(form.start, parseInt($V(form.start)) + parseInt($V(form.step)));

    if ($V(form.auto)) {
      form.onsubmit();
    }
  };

  resetFormUf = function () {
    var form = getForm('import-patients-form');
    $V(form.uf_replace, '');
    $V(form.keywords, '');
  };

  checkDirectory = function (input, message) {
    var url = new Url("patients", "ajax_check_import_dir");
    url.addParam("directory", $V(input));
    url.requestUpdate(message);
  };

  Main.add(function () {
    Control.Tabs.create('import-patients-tabs');
    var form = getForm('import-patients-form');
    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);

    var url = new Url('dPhospi', 'ajax_autocomplete_uf');
    url.addParam('uf_type', 'medicale');
    url.autoComplete(form.keyword, null, {
      minChars:      2,
      method:        'get',
      select:        'view',
      dropdown:      true,
      updateElement: function (selected) {
        var uf_name = selected.get('view');
        var uf_id = selected.get('id');

        $V(getForm('import-patients-form').keyword, uf_name);
        $V(getForm('import-patients-form').uf_replace, uf_id);
      }
    });
  });
</script>

<ul class="control_tabs" id="import-patients-tabs">
  <li><a href="#import-patients">{{tr}}Import{{/tr}}</a></li>
  <li><a href="#config-import">{{tr}}Configure{{/tr}}</a></li>
</ul>

<div id="import-patients" style="display: none;">
  <form name="import-patients-form" method="post" onsubmit="return onSubmitFormAjax(this, {useDollarV: true}, 'import-log-patients')">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="dosql" value="do_import_patients_xml" />
    <input type="hidden" name="uf_replace" value="" />

    <table class="main form">
      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import base{{/tr}}</th>
      </tr>

      <tr>
        <th>
          <label for="directory">{{tr}}dPpatients-import-Source directory{{/tr}}</label>
        </th>
        <td colspan="5">
          <input type="text" name="directory" value="{{$directory}}" size="60" onchange="checkDirectory(this, this.next())" />
          <div></div>
        </td>
      </tr>

      <tr>
        <th>
          <label for="files_directory">{{tr}}dPpatients-import-Files directory{{/tr}}</label>
        </th>
        <td colspan="5">
          <input type="text" name="files_directory" value="{{$files_directory}}" size="60"
                 onchange="checkDirectory(this, this.next())" />
          <div></div>
        </td>
      </tr>

      <tr>
        <th class="narrow">
          <label for="start">{{tr}}Start{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="text" name="start" value="{{$start}}" size="4" />
        </td>
      </tr>

      <tr>
        <th class="narrow">
          <label for="step">{{tr}}Step{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="text" name="step" value="{{$step}}" size="4" />
        </td>
      </tr>

      <tr>
        <th class="narrow">
          <label for="auto">{{tr}}Continue{{/tr}}</label>
        </th>
        <td>
          <input type="checkbox" name="auto" value="1" />
        </td>
      </tr>

      <tr>
        <th>
          <label for="patient_id">{{tr}}CPatient-patient_id{{/tr}}</label>
        </th>
        <td>
          <input type="text" name="patient_id" value="{{$patient_id}}" size="6" />
        </td>
      </tr>

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import options{{/tr}}</th>
      </tr>

      <tr>
        <th>
          <label for="update_data">{{tr}}dPpatients-import-Update data{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="update_data" value="1" {{if $update_data}}checked{{/if}} />
        </td>
      </tr>

      <tr>
        <th>
          <label for="import_presc">{{tr}}dPpatients-import-Import patient presc{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="import_presc" value="1" />
        </td>
      </tr>

      <tr>
        <th>
          <label for="exclude_duplicate">{{tr}}dPpatients-import-Exclude duplicates{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="exclude_duplicate" value="1" />
        </td>
      </tr>

      <tr>
        <th>
          <label for="link_files_to_op">{{tr}}dPpatients-import-Avoid link files to patient{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="link_files_to_op" value="1" {{if $link_files_to_op}}checked{{/if}}>
        </td>
      </tr>

      <tr>
        <th>
          <label for="correct_files">{{tr}}CExternalDBImport-Correct files{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="correct_files" value="1" {{if $correct_files}}checked{{/if}}>
        </td>
      </tr>

      <tr>
        <th>
          <label for="date_min">{{tr}}dPpatients-import-Date min max{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="hidden" class="date" name="date_min" value="{{$date_min}}">
          >
          <input type="hidden" class="date" name="date_max" value="{{$date_max}}">
        </td>
      </tr>

      <tr>
        <th>{{tr}}dPpatients-import-force uf{{/tr}}</th>
        <td>
          <input class="autocomplete" type="text" name="keyword" value="" />
          <button type="button" class="erase notext" onclick="resetFormUf();"></button>
        </td>
      </tr>

      <tr>
        <th>
          <label for="ignore_classes">{{tr}}dPpatients-import-Ignore classes{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="text" name="ignore_classes" value="" size="80">
        </td>
      </tr>

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import interop{{/tr}}</th>
      </tr>

      <tr>
        <th>
          <label for="handlers">{{tr}}CExternalDBImport-Active handlers{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="handlers" value="1" {{if $handlers}}checked{{/if}}>
        </td>
      </tr>

      <tr>
        <th>
          <label for="keep_sync">{{tr}}CExternalDBImport-Keep sync active{{/tr}}</label>
        </th>
        <td class="narrow">
          <input type="checkbox" name="keep_sync" value="1">
        </td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          <button class="change">{{tr}}Import{{/tr}}</button>
        </td>
      </tr>
    </table>

  </form>

  <div id="import-log-patients"></div>
</div>

<div id="config-import" style="display: none;">
  <form name="editConfig" action="?m=patients&tab=vw_import_patients" method="post" onsubmit="return checkForm(this)">
    {{mb_configure module=patients}}

    <table class="main form">
      {{mb_include module=system template=inc_config_str var=import_tag}}
      {{mb_include module=system template=inc_config_date var=file_date_min}}
      {{mb_include module=system template=inc_config_date var=file_date_max}}
      <tr>
        <th></th>
        <td>
          <button class="save">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>

  </form>
</div>