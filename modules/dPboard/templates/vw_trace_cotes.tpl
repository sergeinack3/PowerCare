{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm('changeDate').date_interv, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <th class="button">
      <a href="#" onclick="BoardStats.refreshViewTracesCotes('{{$prec}}')" style="float: left;">&lt;&lt;&lt;</a>
      <a href="#" onclick="BoardStats.refreshViewTracesCotes('{{$suiv}}')" style="float: right">&gt;&gt;&gt;</a>
      <form name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="tab" value="{{$tab}}"/>
        <input type="hidden" name="praticien_id" value="{{$praticien_id}}"/>
          {{$date_interv|date_format:$conf.longdate}}
        <input type="hidden" name="date_interv" class="date" value="{{$date_interv}}"
               onchange="BoardStats.refreshViewTracesCotes($V(this))"/>
      </form>
    </th>
  </tr>
  
  <tr>
    <td colspan="3">
      <table class="tbl">
        <tr>
          <th class="title" colspan="6">
              {{tr}}viewStats-title-view traces cotes{{/tr}}
          </th>
        </tr>
        
        <tr>
          <th>{{tr}}Date{{/tr}}</th>
          <th>{{tr}}COperation-event-dhe{{/tr}}</th>
          <th>{{tr}}Admission{{/tr}}</th>
          <th>{{tr}}CConsultAnesth{{/tr}}</th>
          <th>{{tr}}CService|pl{{/tr}}</th>
          <th>{{tr}}common-Operating bloc-court{{/tr}}</th>
        </tr>

          {{foreach from=$listIntervs item=_interv}}
            <tr>
              <td>{{$_interv->_view}}</td>
              <td>
                <strong>{{mb_value object=$_interv field="cote"}}</strong>
              </td>
              <td
                class="{{if !$_interv->cote_admission}}warning{{elseif $_interv->cote_admission != $_interv->cote}}error{{else}}ok{{/if}}">
                  {{mb_value object=$_interv field="cote_admission"}}
              </td>
              <td
                class="{{if !$_interv->cote_consult_anesth}}warning{{elseif $_interv->cote_consult_anesth != $_interv->cote}}error{{else}}ok{{/if}}">
                  {{mb_value object=$_interv field="cote_consult_anesth"}}
              </td>
              <td
                class="{{if !$_interv->cote_hospi}}warning{{elseif $_interv->cote_hospi != $_interv->cote}}error{{else}}ok{{/if}}">
                  {{mb_value object=$_interv field="cote_hospi"}}
              </td>
              <td
                class="{{if !$_interv->cote_bloc}}warning{{elseif $_interv->cote_bloc != $_interv->cote}}error{{else}}ok{{/if}}">
                  {{mb_value object=$_interv field="cote_bloc"}}
              </td>
            </tr>
              {{foreachelse}}
            <tr>
              <td colspan="6" class="empty">{{tr}}None{{/tr}}</td>
            </tr>
          {{/foreach}}
      </table>
    </td>
  </tr>
</table>

