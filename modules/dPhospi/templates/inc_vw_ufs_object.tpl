{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=uf_secondaire value=false}}
<tr>
  {{if $uf_secondaire}}
    <th style="width: 20%;"></th>
    <th style="width: 20%;"><i>UF secondaires</i></th>
  {{else}}
    <th style="width: 20%;">
      <strong>
        {{if $object->_class != "CMediusers"}}
          {{tr}}{{$object->_class}}{{/tr}}
        {{else}}
          {{$name}}
        {{/if}}
      </strong>
    </th>
    <th style="width: 20%;">{{mb_value object=$object}}</th>
  {{/if}}

  <td>
    {{foreach from=$ufs item=_uf name=ufs}}
      {{$_uf}}
      {{if !$smarty.foreach.ufs.last}}&mdash;{{/if}}
      {{foreachelse}}
      <div class="empty">{{tr}}CUniteFonctionnelle.none{{/tr}}</div>
    {{/foreach}}
  </td>
</tr>
