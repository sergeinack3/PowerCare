{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextStep = function (keep_going) {
    var form = getForm('cron-synchronize');
    if (keep_going && $V(form.continue)) {
      form.onsubmit();
    }
  }
</script>

<form name="cron-synchronize" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-cron-synchronize')">
  <input type="hidden" name="m" value="rpps"/>
  <input type="hidden" name="a" value="cron_synchronize_medecin"/>

  <table class="main form">
    <tr>
      <th>{{tr}}Step{{/tr}}</th>
      <td><input type="number" name="step" value="50"/></td>
    </tr>

    <tr>
      <th>{{tr}}CExternalMedecinSync-Type{{/tr}}</th>
      <td>
        {{tr}}CExternalMedecinSync.type.rpps{{/tr}}
        <input type="radio" name="type" value="rpps" checked/>

        {{tr}}CExternalMedecinSync.type.adeli{{/tr}}
        <input type="radio" name="type" value="adeli"/>
      </td>
    </tr>

    <tr>
      <th>{{tr}}CExternalMedecinSync-Code{{/tr}}</th>
      <td><input type="text" name="codes" value=""/></td>
    </tr>

    <tr>
      <th>{{tr}}Auto{{/tr}}</th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit">Go !</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-cron-synchronize"></div>
