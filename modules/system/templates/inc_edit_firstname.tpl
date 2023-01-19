{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit_firstname" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="m" value="system"/>
  {{mb_class object=$object}}
  {{mb_key object=$object}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$object field=firstname}}</th>
      <td>{{mb_field object=$object field=firstname}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$object field=sex}}</th>
      <td>{{mb_field object=$object field=sex}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$object field=language}}</th>
      <td>{{mb_field object=$object field=language}}</td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>