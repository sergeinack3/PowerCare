{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning ajax=true}}
{{mb_script module=ssr script=trame_collective ajax=true}}
{{mb_script module=ssr script=seance_collective ajax=true}}

<script>
  Main.add(function () {
    TrameCollective.current_m = '{{$m}}';
  });
</script>

<form name="planning_collectif_filter" method="get">
  <table class="form">
    <tr>
      <td class="narrow">
        {{if $app->user_prefs.edit_planning_collectif}}
          <button type="button" class="new" onclick="TrameCollective.editTrame('0');">
            {{tr}}CTrameSeanceCollective-title-create{{/tr}}
          </button>
          <button type="button" class="new" onclick="TrameCollective.editPlage('0');">
            {{tr}}CPlageSeanceCollective-title-create{{/tr}}
          </button>
        {{/if}}
      </td>
      <td>
        <select name="function_id" onchange="TrameCollective.refreshAllPlannings();"
                style="float: right">
          <option value=""> &mdash; {{tr}}CFunctions.select{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$functions selected=$function_id}}
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="2">
      </td>
    </tr>
  </table>
</form>

<div class="small-info" id="message_info_no_filter" style="display: none;">{{tr}}CPlageSeanceCollective-no_filter_for_planning{{/tr}}</div>

<div style="height: 500px;" id="planning_collectif" class="me-padding-0">
  {{mb_include module=ssr template=vw_planning_collectif_trame}}
</div>