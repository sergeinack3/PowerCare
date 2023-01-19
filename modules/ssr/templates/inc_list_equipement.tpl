{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Equipement.updateTab.curry({{$plateau->_ref_equipements|@count}}));
</script>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CEquipement field=nom}}</th>
    <th>{{mb_title class=CEquipement field=visualisable}}</th>
    <th>{{mb_title class=CEquipement field=actif}}</th>
  </tr>
  {{foreach from=$plateau->_ref_equipements item=_equipement}}
  <tr {{if $equipement->_id == $_equipement->_id}}class="selected"{{/if}}>
    <td>
      <a href="#Edit-{{$_equipement->_guid}}" onclick="Equipement.edit('{{$plateau->_id}}', '{{$_equipement->_id}}')">
        {{mb_value object=$_equipement field=nom}}
      </a>
    </td>
    
    <td style="text-align: center;">
      {{mb_value object=$_equipement field=visualisable}}
    </td>

    <td style="text-align: center;" {{if !$_equipement->actif}}class="cancelled"{{/if}}>
      {{mb_value object=$_equipement field=actif}}
    </td>
  </tr>   
  {{foreachelse}}
  <tr>
    <td colspan="3" class="empty">{{tr}}None{{/tr}}</td>
  </tr>   
  {{/foreach}}
</table>