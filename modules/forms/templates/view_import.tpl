{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  onUploadComplete = function(message) {
    SystemMessage.notify(message);
  }
</script>

<form name="import-form" action="?m=forms&a=do_import" method="post"
      onsubmit="return checkForm(this)" target="upload_iframe" enctype="multipart/form-data">
  <input type="hidden" name="m" value="forms" />
  <input type="hidden" name="a" value="do_import" />
  <input type="hidden" name="suppressHeaders" value="1" />

  <table class="main form" style="table-layout: fixed;">
    <tr>
      <th class="title" colspan="2">
        Importation
      </th>
    </tr>

    <tr>
      <th>
        <label for="object_class">Type d'éléments à importer</label>
      </th>
      <td>
        <select name="object_class" class="notNull">
          <option value=""> &ndash; Choisir un type d'élement à importer </option>
          {{foreach from=$classes item=_class}}
            <option value="{{$_class}}">{{tr}}{{$_class}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>
        <label for="import">Fichier</label>
      </th>
      <td>
        <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
        <input type="file" name="import" style="width: 20em;" class="notNull" />
      </td>
    </tr>

    <tr>
      <th></th>
      <td>
        <button class="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<iframe name="upload_iframe" style="display: none;"></iframe>
