{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  repairPatientsCivilite = function() {
    var url = new Url('system', 'do_repair_patients_civilite', 'dosql');
    url.addElement($('repair-civilite-step'), 'step');
    url.addElement($('repair-civilite-continue'), 'continue');
    url.requestUpdate('result-change-civilite', {method: "post"});
  };

  Main.add(function() {
    var form = getForm('display-patients-civilite');
    form.onsubmit();
  });
</script>


<div style="float: left; width:33%">
  <form name="display-patients-civilite" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-patients-civilite')">
    <input type="hidden" name="m" value="system"/>
    <input type="hidden" name="a" value="ajax_repair_civilite"/>

    <table class="main form">
      <tr>
        <th>
          <label for="step">{{tr}}mod-system-repair-civilite-step{{/tr}}</label>
        </th>
        <td>
          <input id="repair-civilite-step" name="step" type="text" value="100" size="5"/>
        </td>
      </tr>

      <tr>
        <th>
          <label for="continue">{{tr}}mod-system-repair-civilite-continue{{/tr}}</label>
        </th>
        <td>
          <input id="repair-civilite-continue" name="continue" type="checkbox" value="1"/>
        </td>
      </tr>

      <tr>
        <td></td>
        <td class="button">
          <button class="button tick" type="button" onclick="repairPatientsCivilite()">{{tr}}mod-system-repair-civilite-repair{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="result-patients-civilite" style="float: right; width: 67%">
</div>

<div id="result-change-civilite" style="width:33%">
</div>
