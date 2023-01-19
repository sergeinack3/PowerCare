{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=bs_id value=$blood_salvage->_id}}

<form name="anticoagulant{{$blood_salvage->_id}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="bloodSalvage" />
  <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
  <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
  <input type="hidden" name="del" value="0" />

  <table class="form me-no-box-shadow">
    <tr>
      <th class="category" colspan="6">{{tr}}msg-CBloodSalvage.Edibles{{/tr}}</th>
    </tr>
    <tr>
      <th style="width: 10%;">{{tr}}CCellSaver-modele{{/tr}}</th>
      <td>
        <select name="cell_saver_id" onchange="this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CCellSaver.name{{/tr}}</option>
          {{foreach from=$list_cell_saver key=id item=cell_saver}}
            <option value="{{$id}}" {{if $id == $blood_salvage->cell_saver_id}}selected{{/if}}>{{$cell_saver}}</option>
          {{/foreach}}
        </select>
      </td>
      <th>{{mb_label object=$blood_salvage field=receive_kit_ref}}</th>
      <td>
        {{mb_field object=$blood_salvage field=receive_kit_ref style="text-transform:uppercase;" size=10 form="anticoagulant$bs_id"}}
        <button class="tick notext" type="button" onclick="this.form.onsubmit();"></button>
        <button type="button" class="cancel notext"
                onclick="this.form.receive_kit_ref.value='';this.form.receive_kit_lot.value=''; this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th style="width:10%">{{mb_label object=$blood_salvage field=anticoagulant_cip}}</th>
      <td>
        <select name="anticoagulant_cip" onchange="this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CBloodSalvage-anticoagulant_cip{{/tr}}</option>
          {{foreach from=$anticoagulant_list key=key item=anticoag}}
            {{if "dPmedicament"|module_active}}
              {{if $inLivretTherapeutique}}
                <option value="{{$anticoag->code_cip}}"
                        {{if $anticoag->code_cip == $blood_salvage->anticoagulant_cip}}selected{{/if}}>{{$anticoag->_ref_produit->libelle}}</option>
              {{else}}
                <option value="{{$anticoag->code_cip}}"
                        {{if $anticoag->code_cip == $blood_salvage->anticoagulant_cip}}selected{{/if}}>{{$anticoag->libelle}}</option>
              {{/if}}
            {{else}}
              <option value="{{$key}}" {{if $key == $blood_salvage->anticoagulant_cip}}selected{{/if}}>{{$anticoag}}</option>
            {{/if}}
          {{/foreach}}
        </select>
      </td>
      <th>{{mb_label object=$blood_salvage field=receive_kit_lot}}</th>
      <td>{{mb_field object=$blood_salvage field=receive_kit_lot style="text-transform:uppercase;" size=10}}</td>
    </tr>
  </table>
</form>
