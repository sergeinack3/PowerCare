{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=categorie value=$object}}

{{if !$categorie->_can->read}}
  <div class="small-info">
    {{tr}}{{$categorie->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400 object=$categorie}}
      {{mb_include module=system template=inc_object_history    object=$categorie}}
      {{mb_include module=system template=inc_object_notes      object=$categorie}}
      {{$categorie}} {{if !$categorie->actif}}({{tr}}CObjectifSoinCategorie-inactif-court{{/tr}}){{/if}}
    </th>
  </tr>
  <tr>
    <td>
      {{if $categorie->description}}
        <span>{{$categorie->description}}</span>
      {{else}}
        <span class="empty">{{tr}}CObjectifSoinCategorie-description.none{{/tr}}</span>
      {{/if}}
    </td>
  </tr>
</table>