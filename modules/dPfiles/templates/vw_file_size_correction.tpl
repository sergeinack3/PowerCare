{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  detailFiles = function() {
    var form = getForm('file-size-correction');
    form.action = "?m=files&a=ajax_vw_details_files";
    $V(form.a, 'ajax_vw_details_files');
    form.onsubmit();
  }
</script>

<form name="file-size-correction" method="post" action="?m=files&a=ajax_correct_files" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result_correct_files')">
  <input type="hidden" name="m" value="files"/>
  <input type="hidden" name="a" value="ajax_correct_files"/>

  <table class="main form">
    <tr>
      <th class="narrow">
        <h2>{{tr}}dPfiles-correct file size{{/tr}}</h2>
      </th>
      <td></td>
    </tr>

    <tr>
      <th>
        <label for="file" title="Fichier">{{tr}}File{{/tr}}</label>
      </th>
      <td>
        {{mb_include module=system template=inc_inline_upload lite=true multi=false}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button id="import_button" type="submit" class="import">
          {{tr}}common-action-Correct{{/tr}}
        </button>

        <button type="button" class="lookup" onclick="detailFiles();">
          Détails des fichiers
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="result_correct_files"></div>