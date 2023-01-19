{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=block_resource value=0}}
{{mb_default var=refresh value=0}}

{{foreach from=$resources item=_resource}}
  {{assign var=is_resource_patient value=0}}

  {{assign var=patient_id value=$appointment->patient_id}}
  {{if (isset($patients_ids_resources.$patient_id|smarty:nodefaults) && in_array($_resource->_id, $patients_ids_resources.$patient_id))
  || (!$patient_id && $appointment->reunion_id)}}
    {{assign var=is_resource_patient value=1}}
  {{/if}}

  {{* Put it differently if it isn't an available ressource *}}
  {{if $_resource|in_array:$unavailable_resources && !$is_resource_patient}}
    <div style="display: inline-block; margin-right: 10px; opacity: 0.7;" title="{{tr}}Unavailable{{/tr}}">
      <input id="resource_{{$_resource->_id}}"
             type="checkbox"
             name="ressources_ids[{{$_resource->_id}}]"
             value="{{$_resource->_id}}"
             class="ressource"
             {{if $block_resource}}disabled{{/if}}>
      <label for="resource_{{$_resource->_id}}"
             style="border-bottom: 1px dotted #bbb; cursor: help; text-decoration: line-through;">
        {{$_resource}}
      </label>
    </div>
  {{else}}
    <div style="display: inline-block; margin-right: 10px;">
      <input id="editFrm_resource_{{$_resource->_id}}"
             type="checkbox"
             name="ressources_ids[{{$_resource->_id}}]"
             value="{{$_resource->_id}}"
             class="ressource"
             {{if $is_resource_patient}}checked{{/if}}>
      <label for="{{if $refresh}}editFrm_{{/if}}resource_{{$_resource->_id}}">{{$_resource}}</label>
    </div>
  {{/if}}
{{foreachelse}}
  {{tr}}CRessourceMaterielle.none{{/tr}}
{{/foreach}}
