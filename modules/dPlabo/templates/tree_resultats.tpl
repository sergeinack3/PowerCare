{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=level value=$_catalogue->_level+1}}
<tr class="catalogue-{{$level}}">
  <th colspan="10">
    {{$_catalogue->libelle}}
  </th>
</tr>

{{foreach from=$_catalogue->_ref_prescription_items item=_item}}
{{assign var=analyse value=$_item->_ref_examen_labo}}
{{assign var=msgClass value=""}}
{{if $analyse->type == "num" || $analyse->type == "float"}}
  {{mb_ternary var=msgClass test=$_item->_hors_limite value=warning other=message}}
{{/if}}

<tr>
  <td>
    <img src="images/icons/anteriorite.png" onclick="Anteriorite.viewItem({{$_item->_id}})" />
  </td>
  <td>{{$analyse->libelle}}</td>

  <td>
  {{if $_item->date}}
    <div class="{{$msgClass}}">
      {{mb_value object=$_item field=resultat prop=$analyse->type}}
    </div>
  {{else}}
    <em>En attente</em>
  {{/if}}
  </td>

  {{if $analyse->type == "num" || $analyse->type == "float"}}
  <td>{{$analyse->unite}}</td>
  <td>{{$analyse->min}} &ndash; {{$analyse->max}}</td>
  {{else}}
  <td colspan="2">{{mb_value object=$analyse field="type"}}</td>
  {{/if}}
  <td>{{$_item->date}}</td>
  <td class="text">
    <div class="{{$msgClass}}">
      {{$_item->commentaire|nl2br}}
    </div>
  </td>
</tr>
{{/foreach}}

{{foreach from=$_catalogue->_ref_catalogues_labo item=_catalogue}}
{{mb_include module=labo template=tree_resultats}}
{{/foreach}}
