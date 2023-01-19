{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="changePageSociete"
total=$list_count current=$start step=30}}

<style type="text/css">
  .societe-type {
    width: 15px;
    height: 15px;
    background-color: #eee;
    color: #999;
    text-align: center;
    display: inline-block;
    border: 1px solid #ccc;
  }

  .societe-type.active {
    background-color: #73BF2F;
    color: #000;
  }

  td.inactive .societe-type {
    background-color: #FFD6BF;
  }
</style>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CSociete field=name}}</th>
    <th>{{mb_title class=CSociete field=contact_name}}</th>
    <th>{{mb_title class=CSociete field=phone}}</th>
    <th class="narrow"></th>
  </tr>
  
  {{foreach from=$list item=curr}}
    <tr>
      <td>
        <a href="#1" onclick="editSociete({{$curr->_id}})">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr->_guid}}')">
          {{$curr}}
        </span>
        </a>
      </td>
      <td>{{mb_value object=$curr field=contact_name}}</td>
      <td>{{mb_value object=$curr field=phone}}</td>
      <td {{if !$curr->_count.products && !$curr->_count.product_references}}class="inactive"{{/if}}>
        <span class="societe-type {{if $curr->_count.products}}active{{/if}}" title="Fabricant">F</span>
        <span class="societe-type {{if $curr->_count.product_references}}active{{/if}}" title="Distributeur">D</span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CSociete.none{{/tr}}</td>
    </tr>
  {{/foreach}}

</table>