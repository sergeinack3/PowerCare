{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=bs_id value=$blood_salvage->_id}}

<form name="cell-saver-id{{$blood_salvage->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="bloodSalvage" />
  <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
  {{mb_key object=$blood_salvage}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    <tr>
      <th class="category" colspan="2">{{tr}}CCellSaver{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <select name="cell_saver_id" onchange="this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CCellSaver{{/tr}}</option>
          {{foreach from=$list_cell_saver key=id item=cell_saver}}
            <option value="{{$id}}" {{if $id == $blood_salvage->cell_saver_id}}selected{{/if}}>{{$cell_saver}}</option>
          {{/foreach}}
        </select>
      </td>
      <td>
        {{mb_label object=$blood_salvage field=wash_kit_ref}}
        {{mb_field object=$blood_salvage field=wash_kit_ref style="text-transform:uppercase;" size=10 form="cell-saver-id$bs_id"}}
        
        {{mb_label object=$blood_salvage field=wash_kit_lot}}
        {{mb_field object=$blood_salvage field=wash_kit_lot style="text-transform:uppercase;" size=10}}
        
        <button class="tick notext" type="button" onclick="this.form.onsubmit();"></button>
        <button class="cancel notext" type="button"
                onclick="this.form.wash_kit_ref.value=''; this.form.wash_kit_lot.value=''; this.form.onsubmit();"></button>
      </td>
    </tr>
  </table>
</form>
