{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id && !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title text">
      {{mb_include module=system template=inc_object_history}}
      
      {{$object}}
    </th>
  </tr>
  <tr>
    <td>
      {{foreach from=$object->_specs key=prop item=spec}}
			{{mb_include module=system template=inc_field_view}}
      {{/foreach}}
    </td>
  </tr>
</table>