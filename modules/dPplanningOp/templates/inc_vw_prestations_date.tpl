{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="edit_{{$_date}}" style="display: none;">
  <table class="form">
    <th class="title" colspan="3">
      {{$_date|date_format:$conf.date}}
    </th>
    {{foreach from=$prestations_j item=_prestation key=prestation_id}}
      {{if isset($liaisons_j.$_date.$prestation_id|smarty:nodefaults)}}
        {{assign var=liaison value=$liaisons_j.$_date.$prestation_id}}
      {{else}}
        {{assign var=liaison value=$empty_liaison}}
      {{/if}}
      <tr>
        <th class="section" colspan="3">
          {{if $liaison->_id !== "temp"}}
            {{mb_include module=system template=inc_object_history object=$liaison}}
          {{/if}}
          {{$_prestation}}
          {{if $_prestation->type_hospi && $_prestation->type_hospi != $sejour->type}}
            <div class="small-warning" style="text-transform: none; font-size: 11px;">
              Le type d'hospitalisation paramétré sur cette prestation ne correspond pas à celui du séjour.
            </div>
          {{/if}}
        </th>
      </tr>
      <tr>
        <th class="narrow" style="vertical-align: middle;">
          {{if $liaison->item_souhait_id}}
            <button type="button" class="trash notext"
                    onclick="removeLiaison('{{$liaison->_id}}', '{{$liaison->item_realise_id}}',  '{{$liaison->_ref_item->object_id}}', '{{$_date}}', 'souhait', '{{$liaison->sous_item_id}}');">{{tr}}Delete{{/tr}}</button>
          {{/if}}
        </th>
        <th style="vertical-align: middle;">
          Souhait
        </th>
        <td class="text">
          {{mb_include module=planningOp template=inc_vw_prestations_line}}
        </td>
      </tr>
      <tr {{if !"dPhospi prestations show_realise"|gconf}}style="display: none;"{{/if}}>
        <th class="narrow">
          {{if $liaison->item_realise_id}}
            <button type="button" class="trash notext"
                    onclick="removeLiaison('{{$liaison->_id}}', '{{$liaison->item_souhait_id}}', '{{$liaison->_ref_item_realise->object_id}}', '{{$_date}}', 'realise');">{{tr}}Delete{{/tr}}</button>
          {{/if}}
        </th>
        <th style="vertical-align: middle;">
          Réalisé
        </th>
        <td class="text">
          {{mb_include module=planningOp template=inc_vw_prestations_line type=realise}}
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="3">
        <button type="button" class="tick me-primary" onclick="onSubmitLiaisons(this.form, Control.Modal.close)">
            {{tr}}Validate{{/tr}}
        </button>
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>
