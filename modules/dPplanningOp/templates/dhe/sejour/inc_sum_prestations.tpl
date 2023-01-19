{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-aide_organisee" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-aide_organisee{{/tr}}: {{mb_value object=$sejour field=aide_organisee}}"{{if !$sejour->aide_organisee}} style="display: none;"{{/if}}>
  Aide orga.
</span>

<span id="sejour-television" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-television-desc{{/tr}}"{{if !$sejour->television}} style="display: none;"{{/if}}>
  TV
</span>

<span id="sejour-lit_accompagnant" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-lit_accompagnant-desc{{/tr}}"{{if !$sejour->lit_accompagnant}} style="display: none;"{{/if}}>
  Lit acc.
</span>

<span id="sejour-facturable" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-facturable-desc{{/tr}}"{{if $sejour->facturable != '0'}} style="display: none;"{{/if}}>
  Non facturable
</span>

{{if 'dPhospi prestations systeme_prestations'|gconf == 'standard'}}
  <span id="sejour-chambre_seule" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-chambre_seule-desc{{/tr}}"{{if !$sejour->chambre_seule}} style="display: none;"{{/if}}>
    Chambre part.
  </span>

  <span id="sejour-prestation_id" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-prestation_id-desc{{/tr}}"{{if !$sejour->prestation_id}} style="display: none;"{{/if}}>
    {{if $sejour->prestation_id}}
      {{$sejour->_ref_prestation}}
    {{/if}}
  </span>
{{else}}
  {{foreach from=$sejour->_back.items_liaisons item=_link}}
    {{mb_ternary var=_item test=$_link->_ref_item value=$_link->_ref_item other=$_link->_ref_item_realise}}
    <span id="sejour-items_liaisons_{{$_item->_id}}" class="dhe_flag dhe_flag_info" title="Prestation: {{$_item->nom}}" data-id="{{$_item->_id}}">
      {{$_item->nom}}
    </span>
  {{/foreach}}
{{/if}}