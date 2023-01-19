{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this, document.location.reload);">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=inc_config_str var=sejour_csv_path size=50}}
    <tr>
      <td class="button" colspan="6">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if !$conf.dPpatients.imports.sejour_csv_path}}
  <div class="small-error">
    <strong>
      {{tr}}CCSVImportPatients-csv.none{{/tr}}
    </strong>
  </div>
{{else}}
  <form name="do-import-cegi-sejour" method="post" onsubmit="return onSubmitFormAjax(this, null, 'do-import-cegi-sejour-log')">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="dosql" value="do_import_sejour" />
    <input type="hidden" name="callback" value="SejoursImportCSV.updateCountsSejour" />

    <table class="main form" style="table-layout: fixed;">
      <tr>
        <th colspan="2" class="title">
          Importation de séjours
        </th>
      </tr>

      <tr>
        <td colspan="2">
          {{if !$conf.dPpatients.imports.sejour_csv_path}}
            <div class="small-error">
              <strong>
                Il faut définir le chemin du fichier CSV à importer dans l'onglet <a
                  href="?m=dPpatients&tab=configure">Configurer</a>
              </strong>
            </div>
          {{else}}
            <div class="small-info">Importation du fichier <code>{{$conf.dPpatients.imports.sejour_csv_path}}</code></div>
          {{/if}}
        </td>
      </tr>

      {{mb_include module=dPpatients template=inc_import_field_num field='start' trad='config-dPpatients-imports-pat_start'
      value="$start_pat"}}

      {{mb_include module=dPpatients template=inc_import_field_num field='count' trad='config-dPpatients-imports-pat_count'
      value="$count_pat"}}

      {{mb_include module=dPpatients template=inc_import_field_checkbox field='auto' trad='CCSVObjectImport-auto' checked=false}}

      {{foreach from=$fields_import_sejour key=_option item=_value}}
        {{mb_include module=dPpatients template=inc_import_field_checkbox field=$_option trad="CCSVImportSejours-$_option"
        checked=$_value}}
      {{/foreach}}

      <tr>
        <td colspan="2" style="text-align: center;">
          <button type="submit" class="tick" onclick="$V(this.form.elements.dosql, 'do_import_sejour_qd');"
                  {{if !$conf.dPpatients.imports.sejour_csv_path}}disabled="disabled"{{/if}}>
            Importer par NDA
          </button>
        </td>
      </tr>
    </table>
  </form>
  <div id="do-import-cegi-sejour-log"></div>
{{/if}}