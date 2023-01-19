{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if isset($liaisons_j.$_date.$prestation_id|smarty:nodefaults)}}
  {{assign var=liaison value=$liaisons_j.$_date.$prestation_id}}
{{else}}
  {{assign var=liaison value=$empty_liaison}}
{{/if}}
{{assign var=next_date value='Ox\Core\CMbDT::date'|static_call:"+1 day":$_date}}
{{if isset($liaisons_j.$next_date.$prestation_id|smarty:nodefaults)}}
  {{assign var=next_liaison value=$liaisons_j.$next_date.$prestation_id}}
{{else}}
  {{assign var=next_liaison value=$empty_liaison}}
{{/if}}

{{assign var=item_presta value=$liaison->_ref_item}}
{{assign var=item_presta_realise value=$liaison->_ref_item_realise}}

{{assign var=next_item_presta value=$next_liaison->_ref_item}}
{{assign var=next_item_presta_realise value=$next_liaison->_ref_item_realise}}

{{assign var=sous_item value=$liaison->_ref_sous_item}}

<td style="text-align: center;" class="text
            {{if $item_presta->_id && $item_presta_realise->_id}}
              {{if $item_presta->rank < $item_presta_realise->rank}}
                item_superior
              {{elseif $item_presta->rank == $item_presta_realise->rank}}
                item_egal
              {{else}}
                item_inferior
              {{/if}}
            {{/if}}">

  <div {{if ($item_presta->_id && !$item_presta->actif)
             || ($item_presta_realise->_id && !$item_presta_realise->actif)
             || ($sous_item && $sous_item->_id && !$sous_item->actif)}}
         class="hatching opacity-60"
       {{/if}}>

    {{if $item_presta->_id}}
      {{if $item_presta_realise->_id && $item_presta->nom != $item_presta_realise->nom && "dPhospi prestations show_realise"|gconf}}
        <span {{if $item_presta_realise->color}}class="mediuser" style="border-left-color: #{{$item_presta_realise->color}}"{{/if}}>
                    {{$item_presta_realise->nom}}
                  </span> <br />
        vs. <br />
        <span {{if $item_presta->color}}class="mediuser" style="border-left-color: #{{$item_presta->color}}"{{/if}}>
                    {{if $sous_item->item_prestation_id == $item_presta->_id}}{{$sous_item->nom}}{{else}}{{$item_presta->nom}}{{/if}}
                  </span>
      {{else}}
        <span {{if $item_presta->color}}class="mediuser" style="border-left-color: #{{$item_presta->color}}"{{/if}}>
                    {{if $sous_item->item_prestation_id == $item_presta->_id}}{{$sous_item->nom}}{{else}}{{$item_presta->nom}}{{/if}}
                  </span>
      {{/if}}
    {{elseif $item_presta_realise->_id && "dPhospi prestations show_realise"|gconf}}
      <span {{if $item_presta_realise->color}}class="mediuser" style="border-left-color: #{{$item_presta_realise->color}}"{{/if}}>
                  {{if $sous_item->item_prestation_id == $item_presta_realise->_id}}{{$sous_item->nom}}{{else}}{{$item_presta_realise->nom}}{{/if}}
                </span>
    {{/if}}
  </div>
</td>