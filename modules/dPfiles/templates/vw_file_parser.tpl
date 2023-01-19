{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="file-parser" method="post" action="?m=files&a=ajax_parse_files" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result_parse_files')">
  <input type="hidden" name="m" value="files" />
  <input type="hidden" name="a" value="ajax_parse_files" />

  <table class="main form">
    <tr>
      <th class="narrow">
        <h2>{{tr}}dPfiles-file-parser{{/tr}}</h2>
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
          {{tr}}common-action-Parse{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="result_parse_files"></div>