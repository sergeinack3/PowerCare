{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  changePeriod = function (select) {
    var url = new Url('patients', 'ajax_custom_constants_graph');
    url.addParam('patient_id', '{{$patient->_id}}');
    url.addParam('context_guid', '{{$context_guid}}');
    url.addParam('constants', {{$constants|smarty:nodefaults|@json}});
    url.addParam('period', $V(select));
    url.requestUpdate('graph');
  };

  Main.add(function () {
    changePeriod($('period'));
  });
</script>

<table class="tbl">
  <tr>
    <th class="title">
      {{$patient->_view}} - {{mb_value object=$patient field=naissance}}
    </th>
  </tr>
</table>

<div style="text-align: center;">
  <span>{{tr}}Period{{/tr}} : </span>
  <select name="period" id="period" onchange="changePeriod(this);">
    <option value="week"{{if $period == 'week'}} selected="selected"{{/if}}>{{tr}}Week{{/tr}}</option>
    <option value="month"{{if $period == 'month'}} selected="selected"{{/if}}>{{tr}}Month{{/tr}}</option>
    <option value="year"{{if $period == 'year'}} selected="selected"{{/if}}>{{tr}}Year{{/tr}}</option>
    <option value="all"{{if $period == 'all'}} selected="selected"{{/if}}>{{tr}}Total{{/tr}}</option>
  </select>
</div>

<div id="graph">
</div>