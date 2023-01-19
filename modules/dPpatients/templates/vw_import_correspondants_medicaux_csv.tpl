{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=import_medecin_analyzer ajax=true}}
{{mb_script module=system script=import_analyzer ajax=true}}

<script>
  Main.add(function () {
    ImportMedecinAnalyzer.medecin_specs = {{$specs|@json}};

    Control.Tabs.create('tabs-analyse-import');
  });
</script>

<style>
#medecinImportDescription li {
  list-style: none;
}
</style>

<h2>Import de correspondants médicaux</h2>
{{mb_include module=system template=inc_import_csv_info_intro}}</ol></div>
<div class="big-info">
<table id="medecinImportDescription" class="main">
  <tr>
    <td>
      <strong>{{tr}}CMedecin-import-desc1{{/tr}}</strong>
    </td>
    <td>
      <strong>{{tr}}CMedecin-import-desc2{{/tr}}</strong>
    </td>
  </tr>
  <tr>
    <td>
        {{foreach from=$fields item=_field}}
          <li>
            <label> {{$_field}}</label>
          </li>
        {{/foreach}}
    </td>
    <td>
        <li><strong>{{mb_label class=CMedecin field=nom}} *</strong></li>
        <li>{{mb_label class=CMedecin field=prenom}}</li>
        <li>{{mb_label class=CMedecin field=jeunefille}}</li>
        <li>{{mb_label class=CMedecin field=sexe}} (u, f ou m)</li>
        <li>{{mb_label class=CMedecin field=actif}} (0, 1)</li>
        <li>{{mb_label class=CMedecin field=titre}} (m, mme, dr ou pr)</li>
        <li>{{mb_label class=CMedecin field=adresse}}</li>
        <li>{{mb_label class=CMedecin field=ville}}</li>
        <li>{{mb_label class=CMedecin field=cp}}</li>
        <li>{{mb_label class=CMedecin field=tel}}</li>
        <li>{{mb_label class=CMedecin field=fax}}</li>
        <li>{{mb_label class=CMedecin field=portable}}</li>
        <li>{{mb_label class=CMedecin field=email}}</li>
        <li>{{mb_label class=CMedecin field=disciplines}}</li>
        <li>{{mb_label class=CMedecin field=orientations}}</li>
        <li>{{mb_label class=CMedecin field=complementaires}}</li>
        <li>{{mb_label class=CMedecin field=type}} (medecin, kine, sagefemme, infirmier, dentiste, podologue, pharmacie, maison_medicale ou
          autre)
        </li>
        <li>{{mb_label class=CMedecin field=adeli}}</li>
        <li>{{mb_label class=CMedecin field=rpps}}</li>
    </td>
  </tr>
</table>

<ol>
    {{mb_include module=system template=inc_import_csv_info_outro}}
<ul id="tabs-analyse-import" class="control_tabs">
  <li><a href="#tab-analyse">{{tr}}Analyse{{/tr}}</a></li>
  <li><a href="#tab-import">{{tr}}Import{{/tr}}</a></li>
</ul>

<div id="tab-analyse" style="display: none;">
  <input type="file" id="import_CMedecin_file" name="import_file" accept=".csv" />

  <button type="button" class="tick" onclick="ImportMedecinAnalyzer.parseMedecinCSV();">{{tr}}Analyse{{/tr}}</button>

  <div id="CMedecin_import_results_loading" class="small-info" style="display: none;">Analyse en cours</div>
  <div id="CMedecin_import_results_header"></div>
  <div id="CMedecin_import_results_data"></div>
</div>

<div id="tab-import" style="display: none;">
  <form method="post" action="?m=dPpatients&a=ajax_import_correspondants_medicaux_csv" name="import-medecin-csv"
        enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-import')">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="a" value="ajax_import_correspondants_medicaux_csv" />
    <input type="hidden" name="force_update" value="0" />

    <table class="main form">
      <tr>
        <th width="50%">{{tr}}CFile{{/tr}}</th>
        <td>
          {{mb_include module=system template=inc_inline_upload lite=true multi=false extensions=csv}}
        </td>
      </tr>

      <tr>
        <th>
          <label title="{{tr}}CMedecin.force_update.desc{{/tr}}" for="force_update_view">
            {{tr}}CCorrespondantPatient.force_update{{/tr}}
          </label>
        </th>
        <td>
          <input type="checkbox" name="force_update_view" value="0" onchange="$V(this.form.force_update, this.checked ? '1' : '0');" />
        </td>
      </tr>

      <tr>
        <td colspan="2" class="button">
          <button type="submit" class="button submit">{{tr}}Import{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

  <div id="result-import"></div>
</div>



