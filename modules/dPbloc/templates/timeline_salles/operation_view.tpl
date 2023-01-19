{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <div>
    <span style="font-weight: bold;" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
      {{if $conf.reservation.ipp_patient_anonyme && $patient->vip}}
        {{$patient->_IPP}}
      {{else}}
        {{$patient->_view}}
      {{/if}}
    </span>
    ({{$patient->sexe}})
    [{{mb_value object=$patient field=naissance}}]
    {{if $operation->annulee}}
      <span class="circled" style="color: firebrick; border-color: firebrick; float: right;">{{tr}}COperation-annulee-court{{/tr}}</span>
    {{/if}}
  </div>

  <div>
    {{if $operation->libelle}}
      {{mb_value object=$operation field=libelle}}
    {{else}}
      Pas de libellé
    {{/if}}
    {{if $operation->cote}}
      - {{mb_value object=$operation field=cote}}
    {{/if}}
  </div>
  <hr/>
  <div>
    {{if $operation->type_anesth}}
      {{$operation->_ref_type_anesth}}
    {{/if}}
  </div>
  <div class="hidden_content">
    {{if $operation->chir_2_id || $operation->chir_3_id || $operation->chir_4_id}}Chir. principal : {{/if}}
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_chir}}
    <br/>
    {{if $operation->chir_2_id}}
      Chir 2 : {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_chir_2}}
      <br/>
    {{/if}}
    {{if $operation->chir_3_id}}
      Chir 3 : {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_chir_3}}
      <br/>
    {{/if}}
    {{if $operation->chir_4_id}}
      Chir 4 : {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_chir_4}}
      <br/>
    {{/if}}
    {{if $operation->debut_op}}
      {{mb_value object=$operation field=debut_op}}
    {{else}}
      {{mb_value object=$operation field=time_operation}}
    {{/if}}
    &mdash;
    {{if $operation->fin_op}}
      {{mb_value object=$operation field=fin_op}}
    {{else}}
      {{mb_value object=$operation field=_fin_prevue}}
    {{/if}}
    <hr/>
    {{if $operation->rques}}
      {{mb_value object=$operation field=rques}}
      <br>
    {{/if}}
    {{if "dPbloc CPlageOp systeme_materiel"|gconf == 'expert' && $operation->_ref_besoins|@count}}
      <span class='compact'>
        {{foreach from=$operation->_ref_besoins item=besoin}}
          {{$besoin->_ref_type_ressource->libelle}},
        {{/foreach}}
      </span>
    {{elseif "dPbloc CPlageOp systeme_materiel"|gconf == 'standard' && $operation->materiel}}
      <span>
        {{mb_value object=$operation field=materiel}}
      </span>
    {{/if}}
    <br>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_ref_sejour->_guid}}');">
      {{$operation->_ref_sejour}}
    </span>
    <br>
    {{if $operation->_ref_sejour->rques}}
      {{mb_value object=$operation->_ref_sejour field=rques}}
      <br>
    {{/if}}
    {{if $operation->_ref_anesth && $operation->_ref_anesth->_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$operation->_ref_anesth}}
    {{/if}}
  </div>
</div>