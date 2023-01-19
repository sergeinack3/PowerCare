{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=ImportUsers ajax=true}}

<form name="check-import-profiles" method="post" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, null, 'result-check-import-profiles');">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="dosql" value="inc_check_import_profiles"/>

  <table class="main form">
    <tr>
      <td class="button" colspan="2">
        <h2>{{tr}}CUser-import-profile|pl{{/tr}}</h2>
      </td>
    </tr>

    <tr class="me-row-valign">
      <th class="me-flex-1">
        <label for="directory">{{tr}}common-directory-source{{/tr}}</label>
      </th>
      <td class="me-flex-1">
        {{mb_include module=system template=inc_inline_upload paste=false extensions="zip xml" multi=false}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="import">{{tr}}CUser-check-import-profile|pl{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import-exist-profile"></div>
<div id="result-check-import-profiles"></div>
