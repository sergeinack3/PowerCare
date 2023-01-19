{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("result-sets-tab", true);
  });
</script>

<table class="main layout">
  <tr>
    <td class="narrow">
      <ul class="control_tabs_vertical small" id="result-sets-tab">
        {{foreach from=$result_sets item=_result_set}}
          {{assign var=log value=$_result_set->_ref_last_log}}

          <li>
            <a href="#result-set-{{$_result_set->_id}}" style="white-space: nowrap;">
              {{mb_value object=$_result_set field=datetime}} ({{$log->_ref_user->_view}})
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{foreach from=$result_sets item=_result_set}}
        <table class="main layout" id="result-set-{{$_result_set->_id}}" style="display: none;">
          <tr>
            <td style="width: 50%;">
              <table class="main tbl">
                <tr>
                  <th class="section" colspan="5">{{$_result_set->_ref_context->_view}}</th>
                </tr>
                <tr>
                  <th>{{mb_title class=CObservationResult field=value_type_id}}</th>
                  <th>{{mb_title class=CObservationResult field=value}}</th>
                  <th>{{mb_title class=CObservationResult field=unit_id}}</th>
                  <th>{{mb_title class=CObservationResult field=method}}</th>
                  <th>{{mb_title class=CObservationResult field=status}}</th>
                </tr>
                {{foreach from=$_result_set->_ref_results item=_result}}
                  {{foreach from=$_result->_ref_values item=_value}}
                    <tr>
                      <td>{{mb_value object=$_result field=value_type_id tooltip=true}}</td>
                      <td>{{mb_value object=$_result field=value}}</td>
                      <td>{{mb_value object=$_result field=unit_id tooltip=true}}</td>
                      <td>{{mb_value object=$_result field=method}}</td>
                      <td>[{{$_result->status}}] {{mb_value object=$_result field=status}}</td>
                    </tr>
                  {{/foreach}}
                  {{foreachelse}}
                  <tr>
                    <td colspan="5" class="empty">{{tr}}CObservationResult.none{{/tr}}</td>
                  </tr>
                {{/foreach}}
              </table>
            </td>
            <td>
              <!-- TODO: Graphique -->
            </td>
          </tr>
        </table>
        {{foreachelse}}
        <div class="empty">{{tr}}CObservationResultSet.none{{/tr}}</div>
      {{/foreach}}
    </td>
  </tr>
</table>
