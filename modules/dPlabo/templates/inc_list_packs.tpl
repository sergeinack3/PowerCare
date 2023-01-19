{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Liste des packs disponibles -->
<table class="tbl">
  <tr>
    <th class="halfPane">{{mb_title class=CPackExamensLabo field=libelle}}</th>
    <th>{{tr}}CPackExamensLabo-back-items_examen_labo{{/tr}}</th>
  </tr>
  {{foreach from=$listPacks item="curr_pack"}}
    <tr {{if $curr_pack->_id == $pack_examens_labo_id}}class="selected"{{/if}}>
      <td>
        <a href="#1" onclick="Pack.edit('{{$curr_pack->_id}}');">
          {{$curr_pack->libelle}}
        </a>
      </td>
      <td>{{$curr_pack->_count.items_examen_labo}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">
        {{tr}}CPackExamensLabo.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
