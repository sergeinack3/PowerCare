{{*
 * @package Mediboard\Hospi
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

{{assign var="affectations" value=$object}}
<table class="tbl tooltip">
  <tr>
    <th class="title text">
      {{$object->_view}}
    </th>
  </tr>
  {{if is_array($affectations) && count($affectations) > 0}}
    {{foreach from=$affectations item=affectation}}
      <tr>
        <td>
          <strong>{{mb_label object=$affectation field=uf_id}}</strong> :
          <em>{{$affectation->_ref_uf->_view}}</em>
        </td>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <td>
        <strong>{{mb_label object=$affectations field=uf_id}}</strong> :
        <em>{{mb_value object=$affectations field=uf_id}}</em>
      </td>
    </tr>
  {{/if}}
</table>