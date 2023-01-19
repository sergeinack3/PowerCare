{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    openFileErrorList = function (object_class, error_type) {
      var url = new Url('dPfiles', 'vw_list_error_files');
      url.addParam('object_class', object_class);
      url.addParam('error_type', error_type);
      url.requestModal("70%", "75%");
    };
  })
</script>

<div class="small-info">
{{if !$file_report->report}}
  Merci de lancer la vérification de l'intégrité pour remplir le tableau de bord
{{else}}
  <p><strong>{{$total_file_entries_count|integer}}</strong> fichiers présents sur le système de fichiers.</p>
  <p><strong>{{$total_file_db_count|integer}}</strong> fichiers présents en base de données.</p>
{{/if}}
</div>

{{if $trash_file_count > 0}}
  <div class="small-warning">
    Il y a <strong>{{$trash_file_count}}</strong> fichiers en attente de suppression définitive.
  </div>
{{/if}}

<table class="main tbl">
  <tr>
    <th>{{mb_label object=$file_report field=object_class}}</th>
    <th>{{mb_label object=$file_report field=db_unfound}}</th>
    <th>{{mb_label object=$file_report field=file_unfound}}</th>
    <th>{{mb_label object=$file_report field=date_mismatch}}</th>
    <th>{{mb_label object=$file_report field=size_mismatch}}</th>
    <th>
      <label title="Total (Pourcentage du nombre de fichiers en erreur en fonction du nombre de fichiers associé à la classe">
        {{tr}}Total{{/tr}}
      </label>
    </th>
  </tr>

  {{foreach from=$file_report->report key=_class item=_errors}}
    <tr>
      <td>{{$_class}}</td>
      {{foreach from='Ox\Mediboard\Files\CFileReport'|static:error_types item=_error_type}}
        {{if array_key_exists($_error_type, $_errors)}}
          <td style="text-align: right;">
            {{$_errors.$_error_type|integer}}
            <button class="search notext" onclick="openFileErrorList('{{$_class}}','{{$_error_type }}')"></button>
          </td>
        {{else}}
          <td style="text-align: right;">&ndash;</td>
        {{/if}}
      {{/foreach}}
      <td style="text-align: right;">
        <strong>{{$file_report->_error_count_by_class.$_class|integer}}</strong>
        {{if $_class}}
          ({{math equation="(x / y) * 100" x=$file_report->_error_count_by_class.$_class y=$file_count_by_class.$_class format="%.2f"}}%)
        {{/if}}
        <button class="search notext" onclick="openFileErrorList('{{$_class}}')"></button>
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td>
      <label title="Total (Pourcentage du nombre d'erreur par type en fonction du nombre de fichier en base">
        <strong>{{tr}}Total{{/tr}}</strong>
      </label>
    </td>
    {{foreach from=$file_report->_error_count_by_type key=_type item=_count}}
      <td style="text-align: right;">
        <strong>{{$_count|integer|nozero}}</strong>
        ({{math equation="(x / y) * 100" x=$_count y=$total_file_entries_count format="%.2f"}}%)
        {{if $_count > 0}}
          <button class="search notext" onclick="openFileErrorList(null, '{{$_type}}')"></button>
        {{/if}}
      </td>
    {{/foreach}}
  </tr>
</table>
