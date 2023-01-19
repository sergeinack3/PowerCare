{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var=operation value=$object}}
{{assign var=sejour    value=$object->_ref_sejour}}
{{assign var=patient   value=$sejour->_ref_patient}}
{{assign var=multi_label value="dPplanningOp COperation multiple_label"|gconf}}
{{assign var=limit_prise_rdv value=$app->user_prefs.limit_prise_rdv}}

{{mb_script module=hospi script=modele_etiquette register=true}}

<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="3">
      {{mb_include module=system template=inc_object_notes     }}
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history   }}
      {{$object}}
    </th>
  </tr>
  <tr>
    <td rowspan="3" style="width: 1px;">
      {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
    </td>
    <td>
      {{mb_value object=$patient}}
    </td>
    <td>
      Le <strong {{if $object->plageop_id}}onmouseover="ObjectTooltip.createEx(this, 'CPlageOp-{{$object->plageop_id}}')"{{/if}}>
        {{mb_value object=$object field=_datetime_best}}</strong>
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_praticien}}
    </td>
    <td>
      <span{{if $object->_ref_salle && $object->_ref_salle->color}} style="border-left: 4px solid #{{$object->_ref_salle->color}}; padding-left: 4px;"{{/if}}>
        {{$object->_ref_salle}}
      </span>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="text">
      <strong {{if $limit_prise_rdv}}style="display: none;" {{/if}}>
      {{if $object->libelle}}
        {{$object->libelle}}<br />
      {{/if}}
      {{foreach from=$object->_ext_codes_ccam item=_code name=codes}}
      {{$_code->code}}
      {{if !$smarty.foreach.codes.last}}&mdash;{{/if}}
      {{/foreach}}
      </strong>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {{mb_label object=$object field=cote}} {{mb_value object=$object field=cote}}
    </td>
    <td>{{mb_label object=$object field=_time_op}} {{mb_value object=$object field=_time_op}}</td>
  </tr>
    {{if $object->_ref_chir_2->_id}}
      <tr>
        <td colspan="3">
          {{mb_label object=$object field=chir_2_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_chir_2}}
        </td>
      </tr>
    {{/if}}
    {{if $object->_ref_chir_3->_id}}
      <tr>
        <td colspan="3">
          {{mb_label object=$object field=chir_3_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_chir_3}}
        </td>
      </tr>
    {{/if}}
    {{if $object->_ref_chir_4->_id}}
      <tr>
        <td colspan="3">
          {{mb_label object=$object field=chir_4_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_chir_4}}
        </td>
      </tr>
    {{/if}}
  {{if $object->_ref_anesth->_id}}
  <tr>
    <td colspan="3">
      {{mb_label object=$object field=anesth_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_anesth}}
    </td>
  </tr>
  {{/if}}
  {{if $object->type_anesth}}
  <tr>
    <td colspan="3">
      {{mb_value object=$object field=type_anesth}}
    </td>
  </tr>
  {{/if}}
  <tr>
    <td colspan="3">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
        Séjour : {{$sejour->_shortview}}
      </span>
    </td>
  </tr>
  {{if !$limit_prise_rdv}}
    {{if $object->examen}}
      <tr>
        <td colspan="3" class="text">
          {{mb_label object=$object field=examen}} : {{mb_value object=$object field=examen}}
        </td>
      </tr>
    {{/if}}
    {{if $object->materiel}}
      <tr>
        <td colspan="3" class="text">
          {{mb_label object=$object field=materiel}} : {{mb_value object=$object field=materiel}}
        </td>
      </tr>
    {{/if}}
    {{if $object->exam_per_op}}
      <tr>
        <td colspan="3" class="text">
          {{mb_label object=$object field=exam_per_op}} : {{mb_value object=$object field=exam_per_op}}
        </td>
      </tr>
    {{/if}}
    {{if $object->rques}}
      <tr>
        <td colspan="3" class="text">
          {{mb_label object=$object field=rques}} : {{mb_value object=$object field=rques}}
        </td>
      </tr>
    {{/if}}
    {{if $operation->debut_op}}
      <tr>
        <td colspan="3">
          {{mb_label object=$object field=debut_op}} : {{mb_value object=$operation field=debut_op}}
        </td>
      </tr>
    {{/if}}
    {{if $operation->fin_op}}
      <tr>
        <td colspan="3">
          {{mb_label object=$object field=fin_op}} : {{mb_value object=$operation field=fin_op}}
        </td>
      </tr>
    {{/if}}
  {{/if}}

  {{if $operation->annulee == 1}}
  <tr>
    <th class="category cancelled" colspan="3">
    {{tr}}COperation-annulee{{/tr}}
    </th>
  </tr>
  {{/if}}
  
  <tr>
    <td class="button" colspan="3">
      {{mb_script module="dPplanningOp" script="operation" ajax="true"}}

      {{if $object->_can->edit}}
        <button type="button" class="edit" onclick="Operation.editModal('{{$operation->_id}}', '{{$operation->plageop_id}}', window.operationEditCallback);">
          {{tr}}Modify{{/tr}}
        </button>
      {{/if}}
      {{if "dPsalleOp"|module_active && $modules.dPsalleOp->_can->edit}}
        <button type="button" class="search" onclick="Operation.dossierBloc('{{$operation->_id}}', Prototype.emptyFunction);">
          {{tr}}COperation-action-Block file{{/tr}}
        </button>
      {{/if}}

      <button type="button" class="print" onclick="Operation.print('{{$operation->_id}}');">
        {{tr}}Print{{/tr}}
      </button>

      {{if @$modules.dPhospi->_can->read && "ambu"|module_active && "ambu General module_actif"|gconf}}
        <button type="button" class="print"
          onclick="ModeleEtiquette.chooseModele('{{$operation->_class}}', '{{$operation->_id}}')">
          {{tr}}CModeleEtiquette-court{{/tr}}
        </button>
      {{/if}}

      {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
        {{mb_script module=brancardage script=brancardage ajax=true}}
        <button type="button" class="lookup" onmousedown="Brancardage.jumelles('{{$operation->_guid}}');">Brancardage</button>
      {{/if}}

      {{if $multi_label}}
        <button class="edit" type="button" onclick="LiaisonOp.edit('{{$operation->_id}}');">Modifier les libellés</button>
      {{/if}}
    </td>
  </tr>
</table>

{{if !$limit_prise_rdv}}
  <table class="tbl tooltip">
    {{mb_include module=cabinet template=inc_list_actes_ccam subject=$operation vue=view}}
  </table>
{{/if}}
