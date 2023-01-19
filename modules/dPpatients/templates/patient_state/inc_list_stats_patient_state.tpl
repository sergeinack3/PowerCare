{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        PatientState.drawGraph({{$state_graph|@json}}, {{$identity_graph|@json}});
    });
</script>

<div class="small-info">
    {{tr var1=$state_graph.count var2=$total_patient}}
        CPatientState-msg-There are %s patients whose identity status is known out of the %s patients in the instance.
    {{/tr}}
</div>

<table class="layout">
    <tr>
        <td>
            <p class="me-text-align-center me-font-weight-bold">
                {{tr}}{{$state_graph.title}}{{/tr}} &bull; {{$state_graph.count}} {{$state_graph.unit}}
            </p>

            <div style="height: 500px;" id="state_graph"></div>
        </td>

        <td>
            <p class="me-text-align-center me-font-weight-bold">
                {{tr}}{{$identity_graph.title}}{{/tr}} &bull; {{$identity_graph.count}} {{$identity_graph.unit}}
            </p>

            <div style="height: 500px;" id="identity_graph"></div>

            {{if $_merge_patient}}
                <div class="small-info">
                    {{tr}}CPatientState-msg-Click on bars in order to show merge details.{{/tr}}
                </div>
            {{/if}}
        </td>
    </tr>
</table>
