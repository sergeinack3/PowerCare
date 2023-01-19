{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td class="text">
  {{if isset($liaisons_p.$_date|smarty:nodefaults)}}
    {{foreach from=$liaisons_p.$_date item=_liaisons_by_prestation key=prestation_id}}
      {{assign var=prestation value=$prestations_p.$prestation_id}}
      {{foreach from=$_liaisons_by_prestation item=_liaison}}
        {{assign var=_item value=$_liaison->_ref_item}}
        <div style="height: 2em; display: inline-block;" {{if !$_item->actif}}class="hatching opacity-60"{{/if}}>
          <input type="text" name="liaisons_p[{{$_liaison->_id}}]" value="{{$_liaison->quantite}}"
                 class="ponctuelle" size="1" onchange="this.form.onsubmit()"/>
          <script>
            Main.add(function() {
              getForm('edit_prestations').elements['liaisons_p[{{$_liaison->_id}}]'].addSpinner(
                {step: 1, min: 0});
            });
          </script>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_item->_guid}}');"
                {{if $_item->color}}class="mediuser" style="border-left-color: #{{$_item->color}}"{{/if}}>
            {{$_item}}
          </span>
        </div>
      {{/foreach}}
    {{/foreach}}
  {{/if}}
</td>