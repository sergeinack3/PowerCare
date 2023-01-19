{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    Calendar.regField(getForm("transfered_constants").date);
  });

  window.constantsUpdater = null;

  restoreConstants = function (start) {
    var url = new Url('patients', 'ajax_restore_transfered_constants');
    var form = getForm('transfered_constants');
    url.addParam('step', $V(form.step));
    if (start) {
      url.addParam('start_restorer_constants', 0);
    }

    url.requestUpdate('status_transfered_constants');
  };

  startRestoreConstants = function (start) {
    window.constantsUpdater = setInterval(restoreConstants.curry(), 1000);
    restoreConstants(getForm('transfered_constants').start.checked);
    $('pause_correction').enable();
    $('start_correction').disable();
  };

  stopRestoreConstants = function () {
    clearInterval(window.constantsUpdater);
    $('start_correction').enable();
    $('pause_correction').disable();
  };
</script>

<h2>Actions sur les constantes</h2>

<table class="tbl">
  <tr>
    <th class="category">{{tr}}Action{{/tr}}</th>
    <th class="category">{{tr}}Status{{/tr}}</th>
  </tr>

  <tr>
    <td>
      <form name="transfered_constants" method="get" action="?" onsubmit="">
        <h3>Récupération des valeurs perdues lors des fusion de dossier</h3>
        <br />
        Nombre de'objet constante traités par passage : <input type="number" name="step" value="100" size="4" />
        <br />
        Reprendre à zéro : <input type="checkbox" name="start" />
        <br /><br />
        <button id="start_correction" type="button" class="play" onclick="startRestoreConstants();">Récupérer les valeurs</button>
        <button id="pause_correction" type="button" class="pause notext" onclick="stopRestoreConstants();"
                disabled="disabled"></button>
      </form>
    </td>
    <td id="status_transfered_constants">

    </td>
  </tr>
</table>