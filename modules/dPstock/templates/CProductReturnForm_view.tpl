{{*
 * @package Mediboard\Stock
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

{{mb_script module=stock script=return_form ajax=true}}

<table class="main form">
  <tr>
    <th class="title" colspan="5">
      <button type="button" class="print notext" style="float: left;"
              onclick="ReturnForm.print({{$object->_id}})">{{tr}}Print{{/tr}}</button>
      {{tr}}CProductReturnForm{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label object=$object field=datetime}}</th>
    <td>{{mb_value object=$object field=datetime}}</td>
    
    <th>{{mb_label object=$object field=return_number}}</th>
    <td>{{mb_value object=$object field=return_number}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$object field=supplier_id}}</th>
    <td>{{mb_value object=$object field=supplier_id}}</td>
    
    <th>{{mb_label object=$object field=comments}}</th>
    <td>{{mb_value object=$object field=comments}}</td>
  </tr>
</table>

{{mb_include module=stock template=inc_outputs_list screen=true return_form=$object}}
