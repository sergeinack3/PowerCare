{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning       ajax=true}}
{{mb_script module=ssr script=groupe_patient ajax=true}}

<script>
  Main.add(function () {
    GroupePatient.current_m = '{{$m}}';
    GroupePatient.date_planning = '{{$date}}';
  });
</script>

<form name="groupe_patient_filter" method="get">
  <table class="form">
    <tr>
      <td class="narrow">
        <button type="button" class="btn_groupe new primary" onclick="GroupePatient.editGroupCategory();">
          {{tr}}CCategorieGroupePatient-action-Create a group category{{/tr}}
        </button>
        <button type="button" class="btn_groupe new primary" onclick="GroupePatient.editGroupPlage();">
          {{tr}}CCategorieGroupePatient-action-Create a group range{{/tr}}
        </button>
      </td>
      <td>
        <div id="filter_range_disabled">
          <label style="float: right">
              {{tr}}CPlageGroupePatient-Action-Show ranges disabled{{/tr}}
            <input value="{{$show_inactive}}" type="checkbox" {{if $show_inactive}}checked{{/if}}
                   name="show_inactive" onchange="GroupePatient.refreshAllPlannings($V(this));" />
          </label>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
      </td>
    </tr>
  </table>
</form>

<div id="groupe_planning" class="me-padding-0">
  {{mb_include module=ssr template=vw_planning_groupe_patient}}
</div>
