{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editPause" method="post" onsubmit="return onSubmitFormAjax(this, function() {
    Control.Modal.close();
    if (window.reloadPlanning) {
        window.reloadPlanning();
    }
    else {
      Control.Modal.refresh();
    }
  })">
  <input type="hidden" name="m" value="planningOp"/>
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$operation}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$operation field=pause}}</th>
      <td>{{mb_field object=$operation field=pause form=editPause}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit()">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>