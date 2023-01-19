{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="import-translations-form" method="post" action="?m=system&a=ajax_import_translations" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-import-translations')">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_import_translations"/>

  <table class="main form">
    <tr>
      <th>
        <h2>{{tr}}system-import translations{{/tr}}</h2>
      </th>
      <td></td>
    </tr>

    <tr>
      <th>
        <label for="translation_file" title="{{tr}}File{{/tr}}">{{tr}}File{{/tr}}</label>
      </th>
      <td style="width: 50%">
        {{mb_include module=system template=inc_inline_upload extensions='csv' multi=false}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button id="import_button" type="submit" class="import">
          {{tr}}common-action-Import{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import-translations"></div>