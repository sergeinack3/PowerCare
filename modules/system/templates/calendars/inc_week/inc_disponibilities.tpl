{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=objectType value=CPlageconsult}}
{{mb_default var=mode value=calendar}}
{{mb_default var=width_mode value=0}}

{{if $object->_disponibilities|@count}}
  {{assign var=nb_dispo value=$object->_disponibilities|@count}}
  {{assign var=count_dispo value=$object->_disponibilities|@array_count_values}}
  <div {{if $width_mode}}style="width:100%;"{{/if}} class="progressBar_dispo" onmouseover="ObjectTooltip.createDOM(this, 'disponibility_{{if $mode == "calendar"}}{{$object->guid}}{{else}}{{$object->_guid}}{{/if}}')">
    {{foreach from=$object->_disponibilities key=time item=_dispo name=dispo_n}}
      <div {{if $width_mode}}style="width:{{math equation="(100/a)" b=$smarty.foreach.dispo_n.iteration a=$nb_dispo}}%"{{/if}} class="disponibility_bar {{if $_dispo >=2}}disponibility_planning_more{{else}}disponibility_planning_{{$_dispo}}{{/if}}" data-time="{{$time}}"></div>
    {{/foreach}}
  </div>
  <table id="disponibility_{{if $mode == "calendar"}}{{$object->guid}}{{else}}{{$object->_guid}}{{/if}}" style="display: none;" class="tbl">
    {{foreach from=$count_dispo key=type item=_dispo name=loop}}
      <tr>
        <th>{{tr}}{{$objectType}}_planning_disponibility_{{if $type >=2 }}surbooking{{else}}{{$type}}{{/if}}{{/tr}} {{if $type >=2 }}({{$type}}){{/if}}</th>
        <td>{{$_dispo}}</td>
      </tr>
    {{/foreach}}
    <tr>
    <th>Total</th>
    <td>{{$object->_disponibilities|@count}}</td>
  </table>
{{/if}}