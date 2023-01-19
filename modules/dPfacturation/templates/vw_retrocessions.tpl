{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
function changeDate(sDebut, sFin){ 
  var oForm = document.printFrm;
  oForm._date_min.value = sDebut;
  oForm._date_max.value = sFin;
  oForm._date_min_da.value = Date.fromDATE(sDebut).toLocaleDate();
  oForm._date_max_da.value = Date.fromDATE(sFin).toLocaleDate();  
}
function reloadRetro(form) {
  var url = new Url('facturation', 'ajax_vw_retrocessions');
  url.addElement(form.chir);
  url.addElement(form._date_min);
  url.addElement(form._date_max);
  url.requestUpdate("retrocessions");
  return false;
}
</script>

{{if count($listPrat)}}
  <form name="printFrm" action="?" method="get" onSubmit="return reloadRetro(this);">
    <input type="hidden" name="a" value="" />
    <input type="hidden" name="dialog" value="1" />
    <table class="form">
      <tr>
        <th class="category" colspan="3">{{tr}}common-period-choice{{/tr}}</th>
        <th class="category">{{mb_label object=$filter field="_prat_id"}}</th>
      </tr>
      <tr>
        <th>{{mb_label object=$filter field="_date_min"}}</th>
        <td>{{mb_field object=$filter field="_date_min" form="printFrm" canNull="false" register=true}}</td>
        <td rowspan="2" style="max-width:200px;">
          <table>
            <tr>
              <td>
                <input type="radio" name="select_days" onclick="changeDate('{{$now}}','{{$now}}');"  value="day" checked="checked"/>
                <label for="select_days_day">{{tr}}Current-day{{/tr}}</label>
                <br/>
                <input type="radio" name="select_days" onclick="changeDate('{{$yesterday}}','{{$yesterday}}');"  value="yesterday"/>
                <label for="select_days_yesterday">{{tr}}Yesterday{{/tr}}</label>
                <br/>
                <input type="radio" name="select_days" onclick="changeDate('{{$week_deb}}','{{$week_fin}}');" value="week"/>
                <label for="select_days_week">{{tr}}Current-week{{/tr}}</label>
                <br/>
              </td>
              <td>
                <input type="radio" name="select_days" onclick="changeDate('{{$month_deb}}','{{$month_fin}}');" value="month"/>
                <label for="select_days_month">{{tr}}Current-month{{/tr}}</label>
                <br/>
                <input type="radio" name="select_days" onclick="changeDate('{{$three_month_deb}}','{{$month_fin}}');" value="three_month"/>
                <label for="select_days_three_month">3 {{tr}}latest-month{{/tr}}</label>
              </td>
            </tr>
          </table>
        </td>
        <td rowspan="2">
          <select name="chir">
            {{if $listPrat|@count > 1}}
            <option value="">&mdash; {{tr}}All{{/tr}}</option>
            {{/if}}
            {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat selected=$prat->_id}}
          </select>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$filter field="_date_max"}}</th>
        <td>{{mb_field object=$filter field="_date_max" form="printFrm" canNull="false" register=true}} </td>
      </tr>
      <tr>
        <td colspan="5" class="button">
          <button type="submit" class="submit">{{tr}}Validate{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
  <div id="retrocessions" class="me-padding-0">
    {{mb_include module=facturation template=inc_vw_retrocessions}}
  </div>
{{else}}
  <div class="big-info">
    {{tr}}Compta.no_acces{{/tr}}
  </div>
{{/if}}