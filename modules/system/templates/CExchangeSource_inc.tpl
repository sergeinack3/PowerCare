{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=true}}

<tr>
  <th class="category" colspan="2"> {{tr}}CExchangeSource-settings{{/tr}} </th>
</tr>

<tr {{if !$can->admin}}style="display:none;"{{/if}}>
  <th>{{mb_label object=$source field="name"}}</th>
  <td><input type="text" {{if $readonly}}readonly="readonly"{{/if}} name="name" value="{{$source->name}}" size="50"/></td>
</tr>

<tr>
  <th>{{mb_label object=$source field="libelle"}}</th>
  <td>{{mb_field object=$source field="libelle" size="50"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$source field="active"}}</th>
  <td>{{mb_field object=$source field="active"}}</td>
</tr>

<tr {{if !$can->admin}}style="display:none;"{{/if}}>
  <th>{{mb_label object=$source field="role"}}</th>
  <td>{{mb_field object=$source field="role" typeEnum="radio"}}</td>
</tr>

<tr {{if !$can->admin}}style="display:none;"{{/if}}>
  <th>{{mb_label object=$source field="loggable"}}</th>
  <td>{{mb_field object=$source field="loggable"}}</td>
</tr>

<tr>
  <th class="category" colspan="2"> {{tr}}{{$source->_class}}-settings{{/tr}} </th>
</tr>

<tr>
  <th>{{mb_label object=$source field="host"}}</th>
  <td>{{mb_field object=$source field="host"}}</td>
</tr>
