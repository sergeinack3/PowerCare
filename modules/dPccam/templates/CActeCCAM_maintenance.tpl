{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Calendar.regField(getForm("update_montants").date);
  });

  window.montantsUpdater = null;

  updateMontantBase = function(start) {
    var url = new Url('ccam', 'updateMontantBase');
    var form = getForm('update_montants');
    url.addParam('date', $V(form.date));
    url.addParam('step', $V(form.step));
    url.addParam('codable_class', $V(form.codable_class));
    if (start) {
      url.addParam('start_update_montant', 0);
    }

    url.requestUpdate('status_update_montant');
  };

  startUpdateMontant = function(start) {
    window.montantsUpdater = setInterval(updateMontantBase.curry(), 60000);
    updateMontantBase(getForm('update_montants').start.checked);
    $('pause_correction').enable();
    $('start_correction').disable();
  };

  stopUpdateMontant = function() {
    clearInterval(window.montantsUpdater);
    $('start_correction').enable();
    $('pause_correction').disable();
  };
</script>

<h2>Actions sur les actes CCAM</h2>

<table class="tbl">
  <tr>
    <th class="category">{{tr}}Action{{/tr}}</th>
    <th class="category">{{tr}}Status{{/tr}}</th>
  </tr>

  <tr>
    <td>
      <form name="update_montants" method="get" action="?" onsubmit="">
        Traiter les actes créés après le : <input type="hidden" name="date" value="{{'Ox\Core\CMbDT::date'|static_call:null}}"/>
        <br/>
        Nombre d'actes traités par passage : <input type="number" name="step" value="100" size="4"/>
        <br/>
        Reprendre à zéro : <input type="checkbox" name="start"/>
        <br/>
        Type d'objet codable :
        <select name="codable_class">
          <option value="0">Tous</option>
          <option value="CConsultation">Consultation</option>
          <option value="COperation">Opération</option>
          <option value="CSejour">Séjour</option>
        </select>
        <br/>
        <button id="start_correction" type="button" class="play" onclick="startUpdateMontant();">Corriger les montants</button>
        <button id="pause_correction" type="button" class="pause notext" onclick="stopUpdateMontant();" disabled="disabled"></button>
      </form>
    </td>
    <td id="status_update_montant">

    </td>
  </tr>
</table>
