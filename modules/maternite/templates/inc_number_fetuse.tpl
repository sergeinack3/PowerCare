{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editFormFetuse" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
    {{mb_class object=$grossesse}}
    {{mb_key   object=$grossesse}}
  <table class="main form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CSurvEchoGrossesse-msg-Please indicate the number of fetuses in this multiple pregnancy{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$grossesse field=nb_foetus}}</th>
      <td>{{mb_field object=$grossesse field=nb_foetus increment=true min=2 form=editFormFetuse}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Confirm{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
