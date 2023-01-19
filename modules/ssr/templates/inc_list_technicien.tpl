{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Technicien.updateTab.curry({{$plateau->_ref_techniciens|@count}}));
</script>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CTechnicien field=kine_id}}</th>
    <th class="narrow">{{mb_title class=CTechnicien field=_count_sejours_date}}</th>
    <th class="narrow">{{mb_title class=CTechnicien field=actif}}</th>
  </tr>

  {{foreach from=$plateau->_ref_techniciens item=_technicien}}
  <tr {{if $technicien->_id == $_technicien->_id}}class="selected"{{/if}}>
    <td>
      <a href="#Edit-{{$_technicien->_guid}}" onclick="Technicien.edit('{{$plateau->_id}}', '{{$_technicien->_id}}')">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_technicien->_ref_kine}}
      </a>
    </td>

    <td style="text-align: center;">
      {{mb_value object=$_technicien field=_count_sejours_date}}
    </td>

    <td style="text-align: center;" {{if !$_technicien->actif}}class="cancelled"{{/if}}>
      {{mb_value object=$_technicien field=actif}}
    </td>

  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="3" class="empty">{{tr}}None{{/tr}}</td>
  </tr>   
  {{/foreach}}
</table>