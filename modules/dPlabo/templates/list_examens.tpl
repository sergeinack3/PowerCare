{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">{{tr}}CExamenLabo.list{{/tr}}</th>
  </tr>
  
  <tr>
    <th>{{mb_title class=CExamenLabo field=identifiant}}</th>
    <th>{{mb_title class=CExamenLabo field=libelle}}</th>
    <th>{{mb_title class=CExamenLabo field=unite}}</th>
    <th>{{tr}}CExamenLabo-References{{/tr}}</th>
  </tr>

  {{foreach from=$examens item=curr_examen}}
    <tr {{if $curr_examen->_id == $examen_id}}class="selected"{{/if}}>
      <td>
        <a href="#1" onclick="Examen.edit('{{$curr_examen->_id}}');">
          {{$curr_examen->identifiant}}
        </a>
      </td>
      <td>
        <a href="#1" onclick="Examen.edit('{{$curr_examen->_id}}');">
          {{$curr_examen->libelle}}
        </a>
      </td>
      {{if $curr_examen->type == "num" || $curr_examen->type == "float"}}
        <td>{{$curr_examen->unite}}</td>
        <td>{{$curr_examen->min}} &ndash; {{$curr_examen->max}}</td>
      {{else}}
        <td colspan="2">{{mb_value object=$curr_examen field="type"}}</td>
      {{/if}}
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CPackItemExamenLabo.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>