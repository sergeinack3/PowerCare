{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  importMediusers = function() {
    $('wait-import-mediusers').innerText = $T('CMediusers-import-in-progress');
    getForm('import-mediusers').onsubmit();
  };
</script>

<form name="import-mediusers" method="post" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, null, 'result-import-mediusers');">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="dosql" value="do_import_mediusers"/>

  <table class="main form">
    <tr>
      <td align="center" colspan="4" class="button"><h2>{{tr}}CMediusers-import-xml{{/tr}}</h2></td>
    </tr>

    <tr>
      <th><label for="directory">{{tr}}common-directory-source{{/tr}}</label></th>
      <td colspan="3">
        {{mb_include module=system template=inc_inline_upload paste=false extensions="zip xml" multi=false}}
      </td>
    </tr>

    <tr>
      <th><label for="functions">{{tr}}CMediusers-import-functions{{/tr}}</label></th>
      <td colspan="3"><input type="checkbox" name="functions" value="1"/></td>
    </tr>

    <tr>
      <th><label for="ufs">{{tr}}CMediusers-import-ufs{{/tr}}</label></th>
      <td colspan="3"><input type="checkbox" name="ufs" value="1"/></td>
    </tr>

    <tr>
      <th class="section" colspan="4">
        {{tr}}CMediusers-import-Section Perms{{/tr}}
      </th>
    </tr>

    <tr>
      <th><label for="perms">{{tr}}CMediusers-import-perms{{/tr}}</label></th>
      <td><input type="checkbox" name="perms" value="1"/></td>

      <th><label for="default_prefs">{{tr}}CMediusers-import-default-prefs{{/tr}}</label></th>
      <td><input type="checkbox" name="default_prefs" value="1"/></td>
    </tr>

    <tr>
      <th><label for="update_perms">{{tr}}CMediusers-import-update-perms{{/tr}}</label></th>
      <td><input type="checkbox" name="update_perms" value="1"/></td>

      <th><label for="update_default_prefs">{{tr}}CMediusers-import-update-default-prefs{{/tr}}</label></th>
      <td><input type="checkbox" name="update_default_prefs" value="1"/></td>
    </tr>

    <tr>
      <th colspan="4"></th>
    </tr>

    <tr>
      <th><label for="prefs">{{tr}}CMediusers-import-prefs{{/tr}}</label></th>
      <td><input type="checkbox" name="prefs" value="1"/></td>

      <th><label for="perms_functionnal">{{tr}}CMediusers-import-perms-functionnal{{/tr}}</label></th>
      <td><input type="checkbox" name="perms_functionnal" value="1"/></td>
    </tr>

    <tr>
      <th><label for="update_prefs">{{tr}}CMediusers-import-update-prefs{{/tr}}</label></th>
      <td><input type="checkbox" name="update_prefs" value="1"/></td>

      <th><label for="update_perms_functionnal">{{tr}}CMediusers-import-update-perms-functionnal{{/tr}}</label></th>
      <td><input type="checkbox" name="update_perms_functionnal" value="1"/></td>
    </tr>

    <tr>
      <th class="section" colspan="4">
        {{tr}}CMediusers-import-Section others{{/tr}}
      </th>
    </tr>

    <tr>
      <th><label for="tarification">{{tr}}CMediusers-import-tarification{{/tr}}</label></th>
      <td><input type="checkbox" name="tarification" value="1"/></td>

      <th><label for="planning">{{tr}}CMediusers-import-planning{{/tr}}</label></th>
      <td><input type="checkbox" name="planning" value="1"/></td>
    </tr>

    <tr>
      <th><label for="update_tarification">{{tr}}CMediusers-import-update-tarification{{/tr}}</label></th>
      <td colspan="3"><input type="checkbox" name="update_tarification" value="1"/></td>
    </tr>

    <tr>
      <td class="button" align="center" colspan="4">
        <button type="button" class="import" onclick="importMediusers();">{{tr}}Import{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="wait-import-mediusers"></div>
<div id="result-import-mediusers"></div>
