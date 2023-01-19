{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  selectOperation = function(operation_id) {
    var oForm = getForm("addOpFrm");
    $V(oForm.operation_id, operation_id);
  }
  selectSejour = function(sejour_id) {
    var oForm = getForm("addOpFrm");
    $V(oForm.sejour_id, sejour_id);
  }
  selectSejourReprescription = function(sejour_id) {
    var url = new Url("prescription", "ajax_select_sejour_represcription");
    url.addParam("sejour_id", sejour_id);
    url.requestModal(500);
  };

  modalRePrescriptions = function(sejour_id, prescription_id, current_prat_id) {
    var url = new Url("prescription", "ajax_vw_represcription_sejour");
    url.addParam("sejour_id"      , sejour_id);
    url.addParam("prescription_id", prescription_id);
    {{if "mpm general role_propre"|gconf}}
      url.addParam("praticien_id", '{{$app->user_id}}');
    {{else}}
      url.addParam("praticien_id", current_prat_id);
    {{/if}}
    url.addParam("origine_consult", 1);
    url.requestModal('85%', '95%',
      {showReload: true});
  };

  Main.add(function() {
    {{if !$consult_anesth->libelle_interv && !$consult_anesth->operation_id && (!"maternite"|module_active || !$consult->grossesse_id)
         && ($nextSejourAndOperation.COperation->_id || $nextSejourAndOperation.CSejour->_id || ($consult->_ref_patient->_ref_next_grossesse && $consult->_ref_patient->_ref_next_grossesse->_id))}}
      GestionDA.edit();
    {{/if}}

    {{if $represcription && $consult_anesth->sejour_id}}
      selectSejourReprescription("{{$consult_anesth->sejour_id}}");
    {{/if}}

    {{if $consult->sejour_id && !$consult_anesth->operation_id}}
      DossierMedical.sejour_id = "{{$consult->sejour_id}}";
    {{/if}}
  });
</script>

<table class="form main" style="display: none;" id="evenement-chooser-modal">
  {{assign var=next_operation value=$nextSejourAndOperation.COperation}}
  {{assign var=next_sejour    value=$nextSejourAndOperation.CSejour   }}
  {{if $next_operation->_id}}
    <tr>
      <td colspan="2"> <div class="small-info">Une intervention à venir est présente pour ce patient</div></td>
    </tr>
    <tr>
      <td></td>
      <td><strong>{{$next_operation}}</strong></td>
    </tr>
    <tr>
      <th>{{mb_title object=$next_operation field=libelle}}</th>
      <td><strong>{{$next_operation->libelle}}</strong></td>
    </tr>
    <tr>
      <th>{{mb_title object=$next_operation field=cote}}</th>
      <td><strong>{{mb_value object=$next_operation field=cote}}</strong></td>
    </tr>
    <tr>
      <th>Prévue le </th>
      <td><strong>{{$next_operation->_datetime|date_format:$conf.date}}</strong></td>
    </tr>
    <tr>
      <th>Avec le Dr </th>
      <td><strong>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$next_operation->_ref_chir}}</strong></td>
    </tr>
    <tr>
      <td class="button" colspan="2"><button class="tick" onclick="selectOperation('{{$next_operation->_id}}');">Associer au dossier d'anesthésie</button>
        <button class="cancel" onclick="modalWindow.close();">Ne pas associer</button></td>
    </tr>
        {{elseif $next_sejour->_id}}
    <tr>
      <td colspan="2"> <div class="small-info">Un séjour à venir est présent dans le système pour ce patient</div></td>
    </tr>
    <tr>
      <td></td>
      <td><strong>{{$next_sejour}}</strong></td>
    </tr>
    <tr>
      <th>{{mb_title object=$next_sejour field=libelle}}</th>
      <td><strong>{{$next_sejour->libelle}}</strong></td>
    </tr>
    <tr>
      <th>Avec le Dr </th>
      <td><strong>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$next_sejour->_ref_praticien}}</strong></td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="tick" onclick="selectSejour('{{$next_sejour->_id}}');">Associer au dossier d'anesthésie</button>
      <button class="cancel" onclick="modalWindow.close();">Ne pas associer</button></td>
    </tr>
  {{/if}}
</table>

<div id="dossiers_anesth_area">
  <table class="main">
    <tr>
      <td style="width: 90%;" class="me-width-auto">
        {{mb_include module=cabinet template=inc_consult_anesth/inc_multi_consult_anesth}}
      </td>
      {{if $consult_anesth->operation_id}}
        <td>
          {{mb_include module=cabinet template=inc_consult_anesth/inc_depassement_anesth consult=true}}
        </td>
      {{/if}}
    </tr>
  </table>
</div>
