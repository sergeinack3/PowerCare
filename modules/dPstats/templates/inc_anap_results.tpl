{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script type="text/javascript">
  getVacationDetails = function (plage_id) {
    var url = new Url('stats', 'ajax_vacation_details');
    url.addParam('plage_id', plage_id);
    url.addParam('operations_to_display', '{{$stats->operations_to_display}}');
    url.addParam('plages_to_display', '{{$stats->plages_to_display}}');
    url.requestModal();
  };

  {{if array_key_exists('groupings', $results)}}
  toggleVacations = function (element, elementRow) {
    var lines = $$('tr.' + element);
    var visible = (lines[0].getStyle('visibility') != 'collapse');
    if (visible) {
      $('spinner_' + element).removeClassName('fa-chevron-circle-down');
      $('spinner_' + element).addClassName('fa-chevron-circle-right');
    }
    else {
      $('spinner_' + element).removeClassName('fa-chevron-circle-right');
      $('spinner_' + element).addClassName('fa-chevron-circle-down');
    }

    lines.each(function (line) {
      if (visible) {
        line.setStyle({'visibility': 'collapse'});
        line.remove();
        $('anap_results_table').insert({bottom: line});
      }
      else {
        line.setStyle({'visibility': 'visible'});
        line.remove();
        elementRow.insert({after: line});
      }
    });
  };
  {{/if}}
</script>

<table class="tbl" id="anap_results_table">
  <tr>
    <th class="title" colspan="19">
      {{tr}}Results{{/tr}}
    </th>
  </tr>
  <tr>
    {{if array_key_exists('groupings', $results)}}
      <th class="narrow"></th>
    {{/if}}
    <th class="narrow">
      {{tr}}CMediusers{{/tr}}
    </th>
    {{if array_key_exists('context_place', $results)}}
      <th>
        {{tr}}{{$results.context_place->_class}}{{/tr}}
      </th>
    {{/if}}
    <th>
      {{tr}}CBlocStatistics-date{{/tr}}
    </th>
    <th>
      {{tr}}CSalle{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.tvo-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.tvo{{/tr}}
    </th>

    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.tpos-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.tpos{{/tr}}
    </th>

    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.tros-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.tros{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.trov-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.trov{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txoc-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txoc{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.pot-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.pot{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.deb-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.deb{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txdeb-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txdeb{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txper-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txper{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txbeg-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txbeg{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txend-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txend{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txurg-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txurg{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.txpot-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.txpot{{/tr}}
    </th>
    <th class="narrow" title="{{tr}}CBlocStatistics-title-anap.evtvo-desc{{/tr}}" style="cursor: help;">
      {{tr}}CBlocStatistics-title-anap.evtvo{{/tr}}
    </th>
  </tr>
  {{if array_key_exists('groupings', $results)}}
    {{foreach from=$results.groupings key=_element item=_grouping}}
      {{mb_include module=stats template=inc_anap_result_group line=$_grouping group=$_element}}
      {{foreachelse}}
      <tr>
        <td class="empty" style="text-align: center;" colspan="19">
          {{tr}}No result{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    {{foreach from=$results.vacations key=_element item=_vacs_by_element}}
      {{foreach from=$_vacs_by_element key=_plage item=_result}}
        {{mb_include module=stats template=inc_anap_result_line line=$_result grouping=$_element plage=$_plage}}
      {{/foreach}}
    {{/foreach}}
  {{else}}
    {{foreach from=$results.vacations key=_plage item=_result}}
      {{mb_include module=stats template=inc_anap_result_line line=$_result plage=$_plage}}
      {{foreachelse}}
      <tr>
        <td class="empty" style="text-align: center;" colspan="19">
          {{tr}}No result{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>