{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value="souhait"}}

<input type="radio" style="display: none;" value=""
       name="liaisons_j[{{$prestation_id}}][{{$_date}}][{{$type}}][{{$liaison->_id}}][item_{{$type}}_id]"
       onclick="this.up('td').select('.sous_item').each(function(input) {
                 input.checked = false;
               });" />

{{foreach from=$_prestation->_ref_items item=_item}}
  {{if (isset($_item->_refs_sous_items|smarty:nodefaults) && !empty($_item->_refs_sous_items|smarty:nodefaults)) && $_item->_refs_sous_items|@count && $type == "souhait"}}
    <fieldset class="me-special-fielset" style="display: inline-block;" {{if !$_item->actif}}class="opacity-60"{{/if}}>
      <legend>
        <label>
          <input type="radio"
                 name="liaisons_j[{{$prestation_id}}][{{$_date}}][{{$type}}][{{$liaison->_id}}][item_{{$type}}_id]"
                 style="display: none;"
                 value="{{$_item->_id}}"
                 {{if ($type == "souhait" && $liaison->item_souhait_id == $_item->_id) ||
                      ($type == "realise" && $liaison->item_realise_id == $_item->_id)}}checked{{/if}} />
          {{$_item->nom}}
        </label>
      </legend>
      <span {{if !$_item->actif}}class="hatching opacity-60"{{/if}}>
        {{foreach from=$_item->_refs_sous_items item=_sous_item}}
          <label>
            <span {{if !$_sous_item->actif}}class="hatching opacity-60"{{/if}}>
              <input type="radio" class="sous_item"
                     name="liaisons_j[{{$prestation_id}}][{{$_date}}][{{$type}}][{{$liaison->_id}}][sous_item_id]"
                     value="{{$_sous_item->_id}}"
                     onclick="
                       switchToNewSousItem(this);
                       {{if $type == "souhait" && $_prestation->desire}}
                         autoRealiser(this.up('fieldset').down('legend').down('input'));
                       {{/if}}"
                     "
                     {{if $liaison->sous_item_id == $_sous_item->_id && ($type == "souhait" || $liaison->_ref_item_realise->_id)
                       && (($type == "souhait" && $liaison->_ref_sous_item->item_prestation_id == $liaison->item_souhait_id) ||
                           ($type == "realise" && $liaison->_ref_sous_item->item_prestation_id == $liaison->item_realise_id))}}checked{{/if}} />
              {{$_sous_item->nom}}
            </span>
          </label>
        {{/foreach}}
      </span>
    </fieldset>
  {{else}}
    <span {{if !$_item->actif}}class="hatching opacity-60"{{/if}}>
      <label>
        <input type="radio"
               name="liaisons_j[{{$prestation_id}}][{{$_date}}][{{$type}}][{{$liaison->_id}}][item_{{$type}}_id]"
               value="{{$_item->_id}}"
               onclick="
                 this.up('td').select('.sous_item').each(function(input) {
                   input.checked = false;
                 });
               {{if $liaison->_id == "temp"}}
                 switchToNew(this);
               {{/if}}
               {{if $type == "souhait" && $_prestation->desire}}
                 autoRealiser(this);
               {{/if}}"
               {{if ($type == "souhait" && $liaison->item_souhait_id == $_item->_id) ||
                    ($type == "realise" && $liaison->item_realise_id == $_item->_id)}}checked{{/if}} />
        <span {{if $_item->color}}class="mediuser" style="border-left-color: #{{$_item->color}}"{{/if}}>{{$_item->nom}}</span>
      </label>
    </span>
  {{/if}}
{{/foreach}}
