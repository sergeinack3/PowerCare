{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=cell_style value=false}}

<td{{if $cell_style}} style="{{$cell_style}}"{{/if}}>
  {{foreach from=$operations item=curr_op}}
    {{assign var=depassement_chir value=0}}
    {{assign var=depassement_chir_reel value=false}}
    {{assign var=depassement_anesth value=0}}
    {{assign var=depassement_anesth_reel value=0}}
    {{if $curr_op->_ref_actes_ccam|@count}}
      {{foreach from=$curr_op->_ref_actes_ccam item=_acte}}
        {{if $_acte->montant_depassement}}
          {{if $_acte->executant_id == $curr_op->chir_id}}
            {{assign var=depassement_chir_reel value=true}}
            {{math assign=depassement_chir equation="x+y" x=$depassement_chir y=$_acte->montant_depassement}}
          {{elseif $_acte->executant_id == $curr_op->anesth_id || $_acte->executant_id == $curr_op->_ref_plageop->anesth_id}}
            {{assign var=depassement_anesth_reel value=true}}
            {{math assign=depassement_anesth equation="x+y" x=$depassement_anesth y=$_acte->montant_depassement}}
          {{/if}}
        {{/if}}
      {{/foreach}}
    {{/if}}

    {{if !$depassement_chir_reel}}
      {{assign var=depassement_chir value=$curr_op->depassement}}
    {{/if}}

    {{if $depassement_chir}}
      <div>
        DH chir : {{$depassement_chir|currency}} <br>
        {{if $curr_op->reglement_dh_chir == 'non_regle'}}
          <span class="circled" style="border-color: firebrick; color: firebrick;">
            {{tr}}COperation.reglement_dh_chir.non_regle{{/tr}}
          </span>
        {{else}}
          <span class="circled" style="border-color: forestgreen; color: forestgreen; background-color: #b7d0df;">
            {{tr}}COperation.reglement_dh_chir.{{$curr_op->reglement_dh_chir}}{{/tr}}
          </span>
        {{/if}}
      </div>
    {{/if}}

    {{if !$depassement_anesth_reel}}
      {{assign var=depassement_anesth value=$curr_op->depassement_anesth}}
    {{/if}}

    {{if $depassement_anesth}}
      <div>
        DH anesth : {{$depassement_anesth|currency}} <br>
        {{if $curr_op->reglement_dh_anesth == 'non_regle'}}
          <span class="circled" style="border-color: firebrick; color: firebrick;">
            {{tr}}COperation.reglement_dh_anesth.non_regle{{/tr}}
          </span>
        {{else}}
          <span class="circled" style="border-color: forestgreen; color: forestgreen; background-color: #b7d0df;">
            {{tr}}COperation.reglement_dh_anesth.{{$curr_op->reglement_dh_anesth}}{{/tr}}
          </span>
        {{/if}}
      </div>
    {{/if}}
  {{/foreach}}

  {{if $sejour->frais_sejour}}
    <div>
      Frais séjour : {{$sejour->frais_sejour|currency}} <br>
      {{if $sejour->reglement_frais_sejour == 'non_regle'}}
        <span class="circled" style="border-color: firebrick; color: firebrick;">
          {{tr}}CSejour.reglement_frais_sejour.non_regle{{/tr}}
        </span>
      {{else}}
        <span class="circled" style="border-color: forestgreen; color: forestgreen; background-color: #b7d0df;">
          {{tr}}CSejour.reglement_frais_sejour.{{$sejour->reglement_frais_sejour}}{{/tr}}
        </span>
      {{/if}}
    </div>
  {{/if}}
</td>
<td{{if $cell_style}} style="{{$cell_style}}"{{/if}} class="narrow">
  <button type="button" class="edit notext not-printable" onclick="Admissions.editReglementFraisSejour('{{$sejour->_id}}');">{{tr}}CSejour-action-edit_reglement{{/tr}}</button>
</td>
