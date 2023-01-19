{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=maintenanceConfig}}

{{mb_default var=separation_cab value=false}}
{{mb_default var=separation_group value=false}}

<script>
  function purgePatients() {
    new Url("patients", "ajax_purge_patients")
      .addParam("qte", 5)
      .requestUpdate("purge_patients", repeatPurge);
  }

  function repeatPurge() {
    if ($V($("check_repeat_purge"))) {
      purgePatients();
    }
  }

  function vwIdentitoVigilance() {
    new Url("patients", "vw_identito_vigilance_pat").redirect();
  }

  function restorePatient() {
    WaitingMessage.cover($("patients_area"));
    new Url("patients", "ajax_restore_patients")
      .addParam("limit", $V($("pas_restorePat")))
      .requestUpdate("patients_area", {
        insertion:  function (element, content) {
          window.save_content = content;
          element.innerHTML = content;
        },
        onComplete: function () {
          if (window.save_content.indexOf("0 / 0") == -1 && $("auto_restorePat").checked) {
            restorePatient();
          }
        }
      });
  }

  function showExportHM() {
    var url = new Url('dPpatients', 'vw_export_patients_hm');
    url.requestModal('50%', '50%');
  }

  showPurgePat = function () {
    var url = new Url('dPpatients', 'vw_purge');
    url.requestModal('90%', '90%')
  };

  Main.add(function () {
    $("pas_restorePat").addSpinner({min: 0, step: 1000});
  });
</script>

<h2>Actions sur les patients</h2>

<table class="tbl">
  <tr>
    <th class="section" style="width: 50%">{{tr}}Action{{/tr}}</th>
    <th class="section">{{tr}}Status{{/tr}}</th>
  </tr>

  <tr>
    <td>
      <button class="search" onclick="Actions.civilite('check')">
        Vérifier les civilités
      </button>
      <br/>
      <button class="change" onclick="Actions.civilite('repair')">
        Corriger les civilités
      </button>
    </td>
    <td id="ajax_civilite">
    </td>
  </tr>
  {{if $app->_ref_user->isAdmin() && !($separation_cab || $separation_group)}}
    <tr>
      <td>
        <button class="search" type="button" onclick="vwIdentitoVigilance();">
          Voir les doublons patient
        </button>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td>
      <button class="search" type="button" onclick="editAntecedent('check');">Vérifier les dossiers médicaux</button>
    </td>
    <td></td>
  </tr>
  <tr>
    <td>
      <label><input type="radio" name="state" value="PROV" checked> {{tr}}CPatient.status.PROV{{/tr}}</label>
      <label><input type="radio" name="state" value="VALI"> {{tr}}CPatient.status.VALI{{/tr}}</label><br/>
      <button type="button" class="search" onclick="Actions.patientState('verifyStatus')">
        Vérifier le nombre de patients sans statut
      </button>
      <button type="button" class="send" onclick="Actions.patientState('createStatus')">
        Placer le statut provisoire pour les patients sans statut
      </button>
    </td>
    <td id="result_tools_patient_state"></td>
  </tr>
  <tr>
    <td>
      <label>
        Pas : <input type="text" id="pas_restorePat" size="4" value="1000"/>
      </label>
      <label>
        <input type="checkbox" name="auto" id="auto_restorePat"/> Auto
      </label>
      <button type="button" class="search" onclick="restorePatient();">Corriger la phonétique des patients</button>
    </td>
    <td id="patients_area"></td>
  </tr>
  <tr>
    <td>
      <button type="button" class="fas fa-external-link-alt" onclick="showExportHM()">
        {{tr}}dPpatients-export-hm{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td>
      <button type="button" class="fas edit" onclick="MaintenanceConfig.editConsentement()">
        {{tr}}dPpatients-edit-consentement{{/tr}}
      </button>
    </td>
    </td>
  </tr>
</table>

<h2>Purge des patients</h2>

<div class="small-error">
  La purge des patients est une action irreversible qui supprime aléatoirement
  une partie des dossiers patients de la base de données et toutes les données
  qui y sont associées.
  <strong>
    N'utilisez cette fonctionnalité que si vous savez parfaitement ce que vous faites
  </strong>
</div>

<button type="button" class="search" onclick="showPurgePat();">Afficher la purge spécifique</button>

<table class="tbl">
  <tr>
    <th>
      Purge des patients (par 5)
      <button type="button" class="tick" onclick="purgePatients();">
        GO
      </button>
      <br/>
      <input type="checkbox" name="repeat_purge" id="check_repeat_purge"/> Relancer automatiquement
    </th>
  </tr>
  <tr>
    <td id="purge_patients">
      <div class="small-info">{{$nb_patients}} patients dans la base</div>
    </td>
  </tr>
</table>
