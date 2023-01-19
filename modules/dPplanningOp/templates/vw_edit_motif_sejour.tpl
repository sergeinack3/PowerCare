{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit_motif_sejour_{{$sejour->_id}}" method="post" onsubmit="return Sejour.onSubmitMotif(this);">
  {{mb_class object=$sejour}}
  {{mb_key object=$sejour}}
  {{mb_field object=$sejour field=praticien_id hidden=1}}
  <table class="form main">
    <tr>
      <th colspan="2" class="title">
        {{tr}}CSejour-libelle-edit{{/tr}} :<br/>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
          {{$sejour->_view}}
        </span>
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour field=libelle}}</th>
      <td>
        {{mb_field object=$sejour field=libelle form="edit_motif_sejour_`$sejour->_id`" style="width: 20em" autocomplete="true,1,50,true,true" min_length=2}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
