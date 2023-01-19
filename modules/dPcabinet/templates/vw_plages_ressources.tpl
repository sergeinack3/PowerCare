{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=plage_ressource}}
{{mb_script module=ssr     script=planning}}

<script>
  Main.add(function() {
    var form = getForm("filterPlanning");
    Calendar.regField(form.date);
    form.function_id_cab.onchange();
  });
</script>

<form name="filterPlanning" method="get">
  <table class="main">
    <tr>
      <td class="halfPane">
        <select name="function_id_cab" onchange="PlageRessource.viewPlannings(this.value);">
          {{mb_include module=mediusers template=inc_options_function list=$functions selected=$function_id}}
        </select>
      </td>
      <td>
        <strong>
          <a href="#1" onclick="PlageRessource.changeDate(-7);">&lt;&lt;&lt;</a>
        </strong>
        <input type="hidden" name="date" class="date notNull" value="{{$date}}"
               onchange="PlageRessource.viewPlannings(null, this.value);" />
        <strong>
          <a href="#1" onclick="PlageRessource.changeDate(7);">&gt;&gt;&gt;</a>
        </strong>
      </td>
    </tr>
  </table>
</form>

<div id="plannings_area"></div>