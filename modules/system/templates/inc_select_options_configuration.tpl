{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$items item=_item}}
  <option value="{{$_item.object->_guid}}" {{if $level == 0}}style="font-weight: bold;"{{/if}} {{if $_item.object|instanceof:'Ox\Mediboard\Etablissement\CGroups' && $_item.object->_id == $g}} selected {{/if}}>
    {{"&nbsp;&nbsp;&nbsp;"|str_repeat:$level}}{{if $level > 0}}|&ndash;{{/if}}
    {{$_item.object->_view}}
  </option>
  
  {{mb_include module=system template=inc_select_options_configuration items=$_item.children level=$level+1}}
{{/foreach}}
