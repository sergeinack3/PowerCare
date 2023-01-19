{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="3">{{$object->code}}</th>
  </tr>
  <tr>
    <td> <strong>{{mb_label object=$object field=short_name}}</strong> : {{$object->short_name}}</td>
  </tr>
  <tr>
    <td> <strong>{{mb_label object=$object field=complete_name}}</strong> : {{$object->complete_name}}</td>
  </tr>
  <tr>
    <td> <strong>{{mb_label object=$object field=type}}</strong> : {{tr}}CCIM10.type.{{$object->type}}{{/tr}}</td>
    </tr>
</table>