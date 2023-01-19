{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextCorrection = function (start, next, new_count) {
    var form = getForm("repair-ex-refs");
    $V(form.start, start);
    $('error-count').innerHTML = new_count;

    if (next && $V(form.continue)) {
      form.onsubmit();
    }
  }
</script>

<form name="repair-ex-refs" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-repair-ex-refs')">
  <input type="hidden" name="m" value="forms"/>
  <input type="hidden" name="dosql" value="doRepairRefs"/>
  <input type="hidden" name="ex_class_id" value="{{$ex_class_id}}"/>

  <table class="main form">
    <tr>
      <th>Nombre d'erreurs</th>
      <td><span id="error-count">{{$data.errors|@count}}</span></td>
    </tr>

    <tr>
      <th>Début</th>
      <td><input type="number" name="start" value="0"/></td>
    </tr>

    <tr>
      <th>Pas</th>
      <td><input type="number" name="step" value="100"/></td>
    </tr>

    <tr>
      <th>Automatique</th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="change">Corriger</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-repair-ex-refs"></div>

