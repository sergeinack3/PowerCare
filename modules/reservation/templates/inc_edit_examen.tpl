{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editExamen" method="post" onsubmit="onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  {{mb_class object=$examen_op}}
  {{mb_key   object=$examen_op}}
  <input type="hidden" name="callback" value="afterSaveExamen" />
  <table class="form">
    <tr>
      <th class="title" colspan="6">
        Examens (ont été faits {{mb_field object=$examen_op field=completed}})
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$examen_op field=acheminement}}
      </th>
      <td colspan="5">
        {{mb_field object=$examen_op field=acheminement typeEnum=radio}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$examen_op field=labo}}
      </th>
      <td colspan="5">
        {{mb_field object=$examen_op field=labo form=editExamen}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$examen_op field=groupe_rh}}
      </th>
      <td>
        {{mb_field object=$examen_op field=groupe_rh}}
      </td>
      <th>
        {{mb_label object=$examen_op field=flacons}}
      </th>
      <td>
        {{mb_field object=$examen_op field=flacons size=2 increment=true form=editExamen}} flacon(s)
      </td>
      <th>
        {{mb_label object=$examen_op field=auto_transfusion}}
      </th>
      <td>
        {{mb_field object=$examen_op field=auto_transfusion}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$examen_op field=ecg}}
      </th>
      <td>
        {{mb_field object=$examen_op field=ecg}}
      </td>
      <td colspan="4"></td>
    </tr>
    <tr>
      <th>
        Radiologie :
      </th>
      <th>
        {{mb_label object=$examen_op field=radio_thorax}}
      </th>
      <td>
        {{mb_field object=$examen_op field=radio_thorax}}
      </td>
      <th>
        {{mb_label object=$examen_op field=radios_autres}}
      </th>
      <td colspan="2">
        {{mb_field object=$examen_op field=radios_autres form=editExamen}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$examen_op field=physio_preop}}
      </th>
      <td colspan="2">
        {{mb_field object=$examen_op field=physio_preop form=editExamen}}
      </td>
      <th>
        {{mb_label object=$examen_op field=physio_postop}}
      </th>
      <td colspan="2">
        {{mb_field object=$examen_op field=physio_postop form=editExamen}}
      </td>
    </tr>
    <tr>
      <td colspan="6" class="button">
        <button type="button" class="save"   onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
