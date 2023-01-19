{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Calendar.regField(getForm('changeDateList').date, null, {noView: true});
  });
</script>
<table class="tbl">
  <tr>
    <th class="title">
      <a style="display: inline" href="#1" onclick="Blocage.refreshList('', '{{$date_before}}')">&lt;&lt;&lt;</a>
      {{$date_replanif|date_format:"%b %G"}}
      <form name="changeDateList" class="prepared" method="get">
      <input type="hidden" name="date" value="{{$date_replanif}}" onchange="Blocage.refreshList('', this.value)"/>
      </form>
      <a style="display: inline" href="#1" onclick="Blocage.refreshList('', '{{$date_after}}')">&gt;&gt;&gt;</a>
    </th>
  </tr>
  {{foreach from=$blocs item=_bloc key=_bloc_id}}
    <tr>
      <th class="category">
        {{$_bloc}}
      </th>
    </tr>
    {{foreach from=$salles.$_bloc_id item=_salle key=salle_id}}
      <tr>
        <th class="section">
          {{$_salle->nom}}
        </th>
      </tr>
      {{foreach from=$blocages.$salle_id item=_blocage}}
        <tr {{if $blocage_id == $_blocage->_id}}class="selected"{{/if}}>
          <td>
            <a href="#1" onclick="Blocage.updateSelected(this.up('tr')); Blocage.edit('{{$_blocage->_id}}')">
              {{$_blocage}}
            </a>
          </td>
        </tr>
      {{foreachelse}}
        <tr>
          <td class="empty">{{tr}}CBlocage.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    {{foreachelse}}
      <tr>
        <td class="empty">{{tr}}CSalle.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
