{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this, document.location.reload);">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=inc_config_str var=pat_csv_path size=50}}
    <tr>
      <td class="button" colspan="6">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if !$conf.dPpatients.imports.pat_csv_path}}
  <div class="small-error">
    <strong>
      {{tr}}CCSVImportPatients-csv.none{{/tr}}
    </strong>
  </div>
{{else}}
  <form name="do-import-patient-pat" method="post" onsubmit="return onSubmitFormAjax(this, null, 'do-import-patient-pat-log')">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="dosql" value="do_import_patient_csv" />
    <input type="hidden" name="callback" value="PatientsImportCSV.updateCountsPat" />

    <table class="main form" style="table-layout: fixed;">
      <tr>
        <td colspan="2">
          <div class="small-info">{{tr}}CCSVImportPatients-file-import{{/tr}} <code>{{$conf.dPpatients.imports.pat_csv_path}}</code>
          </div>
          <div class="small-warning">
            {{tr}}CCSVImportPatients-group-attention{{/tr}} !
            <br />
            {{tr}}CCSVImportPatients-log-attention{{/tr}}
          </div>
        </td>
      </tr>

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import base{{/tr}}</th>
      </tr>

      {{mb_include module=dPpatients template=inc_import_field_num field='start' trad='config-dPpatients-imports-pat_start'
      value="$start_pat"}}

      {{mb_include module=dPpatients template=inc_import_field_num field='count' trad='config-dPpatients-imports-pat_count'
      value="$count_pat"}}

      {{mb_include module=dPpatients template=inc_import_field_checkbox field='auto' trad='CCSVObjectImport-auto' checked=false}}

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import found{{/tr}}</th>
      </tr>

      {{foreach from=$patient_found item=_option}}
        <tr>
          <th><label for="found-{{$_option}}">{{tr}}CCSVImportPatients-{{$_option}}{{/tr}}</label></th>
          <td>
            <input type="radio" name="patient_found" id="found-{{$_option}}" value="{{$_option}}"
                   {{if $_option == "replace_empty"}}checked{{/if}}/>
          </td>
        </tr>
      {{/foreach}}

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import interop{{/tr}}</th>
      </tr>

      {{foreach from=$patient_interop key=_option item=_value}}
        {{assign var=interop_title value=false}}
        {{if $_option == 'disable_handlers'}}{{assign var=interop_title value=true}}{{/if}}

        {{mb_include module=dPpatients template=inc_import_field_checkbox field=$_option trad="CCSVImportPatients-$_option"
        checked=$_value title=$interop_title}}
      {{/foreach}}

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import options{{/tr}}</th>
      </tr>

      {{foreach from=$patient_options key=_option item=_value}}
        {{mb_include module=dPpatients template=inc_import_field_checkbox field=$_option trad="CCSVImportPatients-$_option"
        checked=$_value}}
      {{/foreach}}

      <tr>
        <th class="section" colspan="2">{{tr}}CCSVImportPatients-import identitovigilance{{/tr}}</th>
      </tr>

      {{foreach from=$patient_identito_main key=_option item=_value}}
        {{assign var=identito_title value=false}}
        {{if $_option == 'nom' || $_option == 'nom_jeune_fille'}}{{assign var=identito_title value=true}}{{/if}}

        {{mb_include module=dPpatients template=inc_import_field_checkbox field="identito_$_option" trad="CCSVImportPatients-$_option"
        checked=$_value title=$identito_title}}
      {{/foreach}}

      <tr>
        <th class="section" colspan="2">
          {{tr}}CCSVImportPatients-import identitovigilance secondary{{/tr}}
          (<label style="margin-left: 5px;"><strong>{{tr}}AND{{/tr}}</strong><input type="radio" name="secondary_operand"
                                                                                    value="and" /></label>
          <label><strong>{{tr}}OR{{/tr}}</strong><input type="radio" name="secondary_operand" value="or" checked /></label>)
        </th>
      </tr>

      {{foreach from=$patient_identito_secondary key=_option item=_value}}
        {{mb_include module=dPpatients template=inc_import_field_checkbox field="identito_$_option" trad="CCSVImportPatients-$_option"
        checked=$_value}}
      {{/foreach}}

      <tr>
        <td colspan="2" style="text-align: center;">
          <button type="submit" class="change">
            {{tr}}Import{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
  <div id="do-import-patient-pat-log">
  </div>
{{/if}}