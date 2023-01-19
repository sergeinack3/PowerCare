{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("DateSelect").date, null, { noView: true} );
  });
</script>

<table style="width: 100%;">
  <tr>
    <td style="width: 12em; text-align: left;">
      <button type="button" class="left singleclick me-tertiary" onclick="Planification.showWeek('{{$prev_week}}', '{{$view}}', '{{$sejour_id}}')">
        {{tr}}ssr-week_before{{/tr}}
      </button>
    </td>

    <td style="text-align: center; vertical-align: middle;">
      <big>
        <strong>
          {{tr}}Week{{/tr}} {{$week_number}},
          {{assign var=month_min value=$planning->date_min|date_format:'%B'}}
          {{assign var=month_max value=$planning->date_max|date_format:'%B'}}
          {{$month_min}}{{if $month_min != $month_max}}-{{$month_max}}{{/if}}
          {{$planning->date|date_format:'%Y'}}
        </strong>

        <form name="DateSelect" action="?" method="get" onsubmit="return Planification.showWeek($V(this.date), '{{$view}}', '{{$sejour_id}}')">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="date" class="date" value="{{$planning->date}}" onchange="this.form.onsubmit()" />
        </form>
      </big>
    </td>

    <td style="width: 12em; text-align: right;">
      <button type="button" class="right rtl singleclick me-tertiary" onclick="Planification.showWeek('{{$next_week}}', '{{$view}}', '{{$sejour_id}}')">
        {{tr}}ssr-week_after{{/tr}}
      </button>
    </td>
  </tr>
</table>