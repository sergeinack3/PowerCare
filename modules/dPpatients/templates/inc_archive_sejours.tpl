{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextStepSejours = function () {
    var form = getForm("export-sejours-form");
    $V(form.start, parseInt($V(form.start)) + parseInt($V(form.step)));

    if ($V(form.auto)) {
      form.onsubmit();
    }
  };

  Main.add(function () {
    var sejourForm = getForm("export-sejours-form");
    Calendar.regField(sejourForm.date_min);
    Calendar.regField(sejourForm.date_max);
  });
</script>

<form name="export-sejours-form" method="post" onsubmit="return onSubmitFormAjax(this, {useDollarV: true}, 'export-log-sejours')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_make_sejour_archives" />
  <input type="hidden" name="praticien_id" value=""/>

  <table class="main form">
    <tr>
      <th class="narrow">Date début</th>
      <td class="narrow"><input type="hidden" name="date_min" class="dateTime" /></td>
      <th class="narrow">Date fin</th>
      <td class="narrow"><input type="hidden" name="date_max" class="dateTime" /></td>
      <td class="narrow"></td>
      <td></td>
    </tr>

    <tr>
      <th>
        <label for="start">Début</label>
      </th>
      <td>
        <input type="text" name="start" value="{{$start}}" size="4" />
      </td>

      <th>
        <label for="step">Pas</label>
      </th>
      <td>
        <input type="text" name="step" value="{{$step}}" size="4" />
      </td>

      <th>
        <label for="auto">Avance auto.</label>
      </th>
      <td>
        <input type="checkbox" name="auto" value="1" />
      </td>
    </tr>

    <tr>
      <td colspan="6">
        <button class="change">{{tr}}Export{{/tr}}</button>
      </td>
    </tr>
  </table>

  <div id="export-log-sejours"></div>
</form>
