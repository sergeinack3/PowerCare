{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="FrmSelectArchive" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="dPrepas" />
  <input type="hidden" name="dosql" value="repas_offline" />
  <table class="form">
    <tr>
      <th>
        <label for="indexFile_1">Fichier index.html</label>
      </th>
      <td>
        <label><input type="radio" name="indexFile" value="1" checked="checked" /> Oui</label>
        <label><input type="radio" name="indexFile" value="0" /> Non</label>
      </td>
      <th>
        <label for="style_1">Fichier Style</label>
      </th>
      <td>
        <label><input type="radio" name="style" value="1" checked="checked" /> Oui</label>
        <label><input type="radio" name="style" value="0" /> Non</label>
      </td>
    </tr>
    <tr>
      <th>
        <label for="image_1">Dossier images</label>
      </th>
      <td>
        <label><input type="radio" name="image" value="1" checked="checked" /> Oui</label>
        <label><input type="radio" name="image" value="0" /> Non</label>
      </td>
      <th>
        <label for="javascript_1">Fichiers Javascripts</label>
      </th>
      <td>
        <label><input type="radio" name="javascript" value="1" checked="checked" /> Oui</label>
        <label><input type="radio" name="javascript" value="0" /> Non</label>
      </td>
    </tr>
    <tr>
      <th>
        <label for="lib_1">Libairies</label>
      </th>
      <td>
        <label><input type="radio" name="lib" value="1" checked="checked" /> Oui</label>
        <label><input type="radio" name="lib" value="0" /> Non</label>
      </td>
      <th>
        <label for="typeArch_zip">Archive</label>
      </th>
      <td>
        <label><input type="radio" name="typeArch" value="zip" checked="checked" /> Zip</label>
        <label><input type="radio" name="typeArch" value="tar" /> Tar</label>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button class="submit" type="button" onclick="onSubmitFormAjax(this.form, 'createArchive');">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
  <div id="createArchive"></div>
</form>