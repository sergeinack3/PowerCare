{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
function changePage(start) {
  $V(getForm("filterFrm").start, start);
}

function changeSort(sCol, sWay) {
  oForm = getForm("filterFrm");
  $V(oForm.order_col, sCol);
  $V(oForm.order_way, sWay);
  oForm.submit();
}

Main.add(function(){
  var form = getForm("filterFrm");
  form.getElements().each(function(e){
    e.observe("change", function(){
      $V(form.start, 0);
    });
  });
});
</script>

<form name="filterFrm" action="?m={{$m}}" method="get" onsubmit="return checkForm(this)">

<input type="hidden" name="m" value="{{$m}}" />
<input type="hidden" name="tab" value="{{$tab}}" />
<input type="hidden" name="start" value="{{$start|default:0}}" onchange="this.form.submit()" />
<input type="hidden" name="order_col" value="{{$order_col}}" />
<input type="hidden" name="order_way" value="{{$order_way}}" />

<table class="main form">
  <tr>
    <th>{{mb_label object=$filter field=user_id}}</th>
    <td>
      <select name="user_id" class="ref">
        <option value="">&mdash; Tous les utilisateurs</option>
        {{foreach from=$listUsers item=curr_user}}
        <option value="{{$curr_user->user_id}}" {{if $curr_user->user_id == $filter->user_id}}selected="selected"{{/if}}>
          {{$curr_user}}
        </option>
        {{/foreach}}
      </select>
    </td>
    <th>{{mb_label object=$filter field=date}}</th>
    <td>{{mb_field object=$filter field=date form="filterFrm" register=true}}</td>
    <th><label for="ip_address">Sous-réseau</label></th>
    <td><input type="text" class="notNull" name="ip_address" value="{{$filter->ip_address}}" /></td>
  </tr>
  <tr>
    <td class="button" colspan="2"><button type="submit" class="search">{{tr}}Filter{{/tr}}</button></td>
    <td class="button" colspan="4">
      <div class="small-info">La recherche s'effectue sur entre l'horodatage de référence et la semaine qui précède</div>
    </td>
  </tr>
</table>

</form>

{{mb_include module=system template=inc_pagination total=$list_count current=$start step=30 change_page='changePage'}}

<table class="tbl">
  <tr>
    <th colspan="4">
      <a href="#sorted_by_ip" {{if $order_col == "ip_address"}}class="sorted {{$order_way}}" onclick="changeSort('{{$order_col}}', '{{$order_way_alt}}');"
                              {{else}}                         class="sortable" onclick="changeSort('ip_address', 'ASC');"
                              {{/if}}">
      IP
      </a>
    </th>
    <th colspan="2">
      <a href="#sorted_by_date" {{if $order_col == "date_max"}}class="sorted {{$order_way}}" onclick="changeSort('{{$order_col}}', '{{$order_way_alt}}');"
                                {{else}}                       class="sortable" onclick="changeSort('{date_max', 'ASC');"
                                {{/if}}">
      Dernier log
      </a>
    </th>
    <th>Utilisateurs</th>
    <th>
      <a href="#sorted_by_qty" {{if $order_col == "total"}}class="sorted {{$order_way}}" onclick="changeSort('{{$order_col}}', '{{$order_way_alt}}');"
                              {{else}}                         class="sortable" onclick="changeSort('total', 'ASC');"
                              {{/if}}">
      Nb. de Logs
      </a>
    </th>
  </tr>
  {{foreach from=$total_list item=_log}}
  <tr>
    <td style="text-align: right;">{{$_log.ip_explode.0}}.</td>
    <td style="text-align: right;">{{$_log.ip_explode.1}}.</td>
    <td style="text-align: right;">{{$_log.ip_explode.2}}.</td>
    <td style="text-align: right;">{{$_log.ip_explode.3}}</td>
    <td class="button">{{mb_ditto value=$_log.date_max|date_format:$conf.date name=date}}</td>
    <td class="button">{{$_log.date_max|date_format:$conf.time}}</td>
    <td class="text" style="width: 100%;">
      {{foreach from=$_log.users item=_user}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user}}
      {{/foreach}}
    </td>
    <td style="text-align: right;">{{$_log.total}}</td>
  </tr>
  {{foreachelse}}
  <tr><td colspan="8" class="empty">{{tr}}CUserLog.none{{/tr}}</td></tr>
  {{/foreach}}
</table>