{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Calendar.regField(getForm("changeDate").date_replanif, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <th class="title button">
      <a href="#1" onclick="refreshReplanif('{{$date_replanif_before}}')">&lt;&lt;&lt;</a>
      {{$date_replanif|date_format:$conf.longdate}}
      <form name="changeDate" method="get">
        <input type="hidden" name="date_replanif" class="date" value="{{$date_replanif}}" onchange="refreshReplanif(this.value)"/>
      </form>
      <a href="#1" onclick="refreshReplanif('{{$date_replanif_after}}')">&gt;&gt;&gt;</a>
    </th>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        {{foreach from=$plages_by_salle item=_plages_by_salle key=salle_id}}
          <tr>
            <th class="title">
              {{$salles.$salle_id}}
            </th>
          </tr>
          {{foreach from=$_plages_by_salle item=_plage}}
            <tr>
              <th>
                {{$_plage}} &mdash; {{$_plage->_ref_chir}}
              </th>
            </tr>
            {{foreach from=$_plage->_ref_operations item=_operation}}
              <tr>
                <td>
                  <a href="?m=dPplanningOp&tab=vw_edit_planning&operation_id={{$_operation->_id}}">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
                      {{mb_value object=$_operation field=time_operation}} &mdash; {{$_operation->_ref_patient}}
                    </span>
                  </a>
                </td>
              </tr>
            {{/foreach}}
          {{/foreach}}
        {{foreachelse}}
          <tr>
            <td class="empty">{{tr}}CPlageOp.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>