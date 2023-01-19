{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="cell-saver-infos{{$blood_salvage->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="bloodSalvage" />
  <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
  <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    <tr>
      <th class="category" colspan="8">{{tr}}CBloodSalvage.volumes{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$blood_salvage field=wash_volume}}</th>
      <td>
        {{mb_field object=$blood_salvage field=wash_volume size=4}} ml
        <button type="button" class="cancel notext"
                onclick="this.form.wash_volume.value = ''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
      <th>{{mb_label object=$blood_salvage field=hgb_pocket}}</th>
      <td>
        {{mb_field object=$blood_salvage field=hgb_pocket size=4}} g/dl
        <button type="button" class="cancel notext"
                onclick="this.form.hgb_pocket.value = ''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$blood_salvage field=saved_volume}}</th>
      <td>
        {{mb_field object=$blood_salvage field=saved_volume size=4}} ml
        <button type="button" class="cancel notext"
                onclick="this.form.saved_volume.value = ''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
      <th>{{mb_label object=$blood_salvage field=hgb_patient}}</th>
      <td>
        {{mb_field object=$blood_salvage field=hgb_patient size=4}} g/dl
        <button type="button" class="cancel notext"
                onclick="this.form.hgb_patient.value = ''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$blood_salvage field=transfused_volume}}</th>
      <td>
        {{mb_field object=$blood_salvage field=transfused_volume size=4}} ml
        <button type="button" class="cancel notext"
                onclick="this.form.transfused_volume.value = ''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
      <td colspan="2" class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        <button type="button" class="cancel"
                onclick="
                  this.form.transfused_volume.value = '';
                  this.form.wash_volume.value = '';
                  this.form.hgb_pocket.value = '';
                  this.form.saved_volume.value = '';
                  this.form.hgb_patient.value = '';
                  this.form.onsubmit();">
          {{tr}}CBloodSalvage-transfusion.erase_all{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

