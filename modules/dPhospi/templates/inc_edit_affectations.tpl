{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=affectation ajax=1}}

{{assign var=affectations value=$sejour->_ref_affectations}}

<table class="tbl">
  <tr>
    <th class="title" {{if $affectations|@count}}colspan="16"{{/if}}>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
        {{$sejour}}
      </span>
    </th>
  </tr>

  {{if $affectations|@count}}
    <tr>
      <th class="narrow" rowspan="2"></th>
      <th class="narrow" rowspan="2" colspan="2">
        {{mb_label class=CAffectation field=entree}}
      </th>
      <th class="narrow" rowspan="2" colspan="2">
        {{mb_title class=CAffectation field=sortie}}
      </th>
      <th style="width: 15%;" colspan="2">
        {{mb_title class=CAffectation field=service_id}}
      </th>
      <th class="text" style="width: 15%;" rowspan="2">
        {{mb_title class=CAffectation field=uf_soins_id}}
      </th>
      <th class="text" style="width: 15%;" rowspan="2">
        {{mb_title class=CAffectation field=uf_medicale_id}}
      </th>
      <th class="text" style="width: 15%;" rowspan="2">
        {{mb_title class=CAffectation field=uf_hebergement_id}}
      </th>
      <th class="text" style="width: 15%;" rowspan="2">
        {{mb_title class=CAffectation field=praticien_id}}
      </th>
      <th colspan="5">
        {{tr}}CSejour-back-movements{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{mb_title class=CService field=nom}}</th>
      <th>{{mb_title class=CAffectation field=lit_id}}</th>
      <th style="width: 8%;"></th>
      <th style="width: 8%;">{{mb_title class=CMovement field=movement_type}}</th>
      <th>{{mb_title class=CMovement field=original_trigger_code}}</th>
      <th class="text">{{mb_title class=CMovement field=start_of_movement}}</th>
      <th class="text">{{mb_title class=CMovement field=last_update}}</th>
    </tr>
  {{/if}}

  {{foreach from=$affectations item=_affectation name=affectation}}
    {{assign var=movements value=$_affectation->_ref_movements}}

    {{mb_ternary var=rowspan test=$movements|@count value=$movements|@count other=1}}

    <tr>
    <td rowspan="{{$rowspan}}">
      <button type="button" class="edit notext"
        {{if $smarty.foreach.affectation.first}}
         onclick="Affectation.edit('{{$_affectation->_id}}', null, null, null, Control.Modal.refresh);"
        {{else}}
         disabled
        {{/if}}>{{tr}}Edit{{/tr}}</button>
    </td>
    <td rowspan="{{$rowspan}}"
        style="text-align: center;">{{mb_ditto name=entree_date value=$_affectation->entree|date_format:$conf.date}}</td>
    <td rowspan="{{$rowspan}}"
        style="text-align: center;">{{mb_ditto name=entree_time value=$_affectation->entree|date_format:$conf.time}}
    <td rowspan="{{$rowspan}}"
        style="text-align: center;">{{mb_ditto name=sortie_date value=$_affectation->sortie|date_format:$conf.date}}</td>
    <td rowspan="{{$rowspan}}"
        style="text-align: center;">{{mb_ditto name=sortie_time value=$_affectation->sortie|date_format:$conf.time}}</td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">{{mb_ditto name=service_id  value=$_affectation->_ref_service->_view}}</td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">
      {{if $_affectation->lit_id}}
        {{mb_ditto name=lit_id value=$_affectation->_ref_lit->_view}}
      {{/if}}
    </td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">
      {{if $_affectation->uf_soins_id}}
        {{mb_ditto name=uf_soins_id value=$_affectation->_ref_uf_soins->_view}}
      {{/if}}
    </td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">
      {{if $_affectation->uf_medicale_id}}
        {{mb_ditto name=uf_medicale_id value=$_affectation->_ref_uf_medicale->_view}}
      {{/if}}
    </td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">
      {{if $_affectation->uf_hebergement_id}}
        {{mb_ditto name=uf_hebergement_id value=$_affectation->_ref_uf_hebergement->_view}}
      {{/if}}
    </td>
    <td rowspan="{{$rowspan}}" style="text-align: center;">
      {{mb_ternary var=praticien_id
      test=$_affectation->praticien_id value=$_affectation->_ref_praticien->_view other=$sejour->_ref_praticien->_view}}
      {{mb_ditto name=praticien_id value=$praticien_id}}
    </td>
    {{foreach from=$movements item=_movement}}
      <td {{if $_movement->cancel}}class="hatching"{{/if}} style="text-align: center;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_movement->_guid}}');">
            {{$_movement}}
          </span>
      </td>
      <td {{if $_movement->cancel}}class="hatching"{{/if}} style="text-align: center;">
        {{mb_value object=$_movement field=movement_type}}
      </td>
      <td {{if $_movement->cancel}}class="hatching"{{/if}} style="text-align: center;">
        {{mb_value object=$_movement field=original_trigger_code}}
      </td>
      <td {{if $_movement->cancel}}class="hatching"{{/if}} style="text-align: center;">
        {{mb_value object=$_movement field=start_of_movement}}
      </td>
      <td {{if $_movement->cancel}}class="hatching"{{/if}} style="text-align: center;">
        {{mb_value object=$_movement field=last_update format=relative}}
      </td>
      </tr>
      <tr>
      {{foreachelse}}
      <td rowspan="{{$rowspan}}" colspan="5" class="empty" style="text-align: center;">
        {{tr}}CAffectation-back-movements.empty{{/tr}}
      </td>
    {{/foreach}}
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty">
        {{tr}}CSejour-back-affectations.empty{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>