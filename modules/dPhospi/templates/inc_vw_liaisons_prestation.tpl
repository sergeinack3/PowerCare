{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$liaisons item=liaison}}
  {{assign var=item_presta value=$liaison->_ref_item}}
  {{assign var=item_presta_realise value=$liaison->_ref_item_realise}}
  {{assign var=sous_item value=$liaison->_ref_sous_item}}
  <strong
    title="{{if $item_presta->_id}}{{tr}}CItemLiaison-item_souhait_id{{/tr}} [{{$item_presta->nom}}]{{/if}} - {{if $item_presta_realise->_id}}{{tr}}CItemLiaison-item_realise_id{{/tr}} [{{$item_presta_realise->nom}}]{{/if}}"
    {{if $item_presta->_id && $item_presta_realise->_id}}
      class="{{if $item_presta->rank == $item_presta_realise->rank}}
               item_egal
             {{elseif $item_presta->rank > $item_presta_realise->rank}}
               item_inferior
             {{else}}
               item_superior
             {{/if}}"
    {{/if}}
    style="border: 2px solid #{{if $item_presta_realise->_id}}{{$item_presta_realise->color}}{{else}}{{$item_presta->color}}{{/if}}; margin-right: 1px;">

    <!-- display -->
    {{if $item_presta->_id == $item_presta_realise->_id || "dPhospi prestations show_souhait_placement"|gconf}}
      {{if $sous_item->_id}}
          {{$sous_item->nom}}
      {{else}}
          {{if $item_presta->nom_court}}
            {{$item_presta->nom_court}}
          {{else}}
            {{$item_presta->nom|truncate:20}}
          {{/if}}
      {{/if}}
    {{else}}
      {{if $item_presta_realise->nom}}
          {{if $item_presta_realise->nom_court}}
              {{$item_presta_realise->nom_court}}
          {{else}}
              {{$item_presta_realise->nom|truncate:20}}
          {{/if}}
      {{else}}
        {{if $sous_item->_id}}
          {{$sous_item->nom}}
        {{else}}
            {{if $item_presta->nom_court}}
                {{$item_presta->nom_court}}
            {{else}}
                {{$item_presta->nom|truncate:20}}
            {{/if}}
        {{/if}}
      {{/if}}
    {{/if}}

  </strong>
{{/foreach}}
