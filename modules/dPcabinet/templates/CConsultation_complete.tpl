{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=limit_prise_rdv value=$app->user_prefs.limit_prise_rdv}}
<script>
  newExam = function(sAction, consultation_id) {
    if (sAction) {
      var url = new Url("cabinet", sAction);
      url.addParam("consultation_id", consultation_id);
      url.popup(900, 600, "Examen");
    }
  };
  printFiche = function(dossier_anesth_id) {
    var url = new Url("cabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("print", true);
    url.popup(700, 500, "printFiche");
  };
</script>

{{assign var=consult_anesth value=$object->_ref_consult_anesth}}

{{if "dPcabinet CConsultation verification_access"|gconf
     && (!$object->sejour_id && (!$consult_anesth->_id || !$consult_anesth->sejour_id) && !$object->_can->edit)}}
  <div class="small-info">
    Le droit en écriture sur le praticien de la consultation est nécessaire pour accéder à cette information.
  </div>
  {{mb_return}}
{{/if}}

<table class="form">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400 object=$object}}
      {{mb_include module=system template=inc_object_history    object=$object}}
      {{mb_include module=system template=inc_object_notes      object=$object}}

      {{if !$limit_prise_rdv}}
        {{foreach from=$object->_refs_dossiers_anesth item=_dossier_anesth}}
          <button class="print" type="button" style="float: right;" onclick="printFiche('{{$_dossier_anesth->_id}}');">
            {{tr}}CConsultation-Print the card{{/tr}}
            {{if $_dossier_anesth->_ref_operation->_id}}
              {{tr var1=$_dossier_anesth->_ref_operation->_datetime_best|date_format:$conf.datetime}}COperation-for the intervention of the %s{{/tr}}
            {{/if}}
          </button>
        {{/foreach}}
      {{/if}}
      {{$object->_view}}
    </th>
  </tr>
  <tr>
    <td>
      <strong>{{tr}}CConsultation-_date{{/tr}} :</strong>
      <i>{{tr var1=$object->_ref_plageconsult->date|date_format:"%d %B %Y" var2=$object->heure|date_format:$conf.time}}common-the %s at %s{{/tr}}</i>
    </td>
    <td>
      <strong>{{tr}}CConsultation-_prat_id{{/tr}} :</strong>
      <i>{{if $object->_ref_plageconsult->_ref_chir->isPraticien()}}{{tr}}CMedecin.titre.dr{{/tr}}{{/if}} {{$object->_ref_plageconsult->_ref_chir->_view}}</i>
    </td>
  </tr>
  <tr>
    <td class="text">
      <strong {{if $limit_prise_rdv}}style="display: none;"{{/if}}>{{tr}}CConsultation-motif{{/tr}} :</strong>
      <i {{if $limit_prise_rdv}}style="display: none;"{{/if}}>{{mb_value object=$object field=motif}}</i>
    </td>
    <td class="text">
      <strong>{{tr}}CConsultation-rques{{/tr}} :</strong>
      <i>{{mb_value object=$object field=rques}}</i>
    </td>
  </tr>
  {{if !$limit_prise_rdv}}
    {{assign var=show_histoire_maladie value="dPcabinet CConsultation show_histoire_maladie"|gconf}}
    {{if $show_histoire_maladie || "dPcabinet CConsultation show_examen"|gconf}}
      <tr>
        {{if $show_histoire_maladie}}
          <td class="text">
            <strong>{{mb_label object=$object field=histoire_maladie}} :</strong>
            <i>{{mb_value object=$object field=histoire_maladie}}</i>
          </td>
        {{/if}}

        {{if "dPcabinet CConsultation show_examen"|gconf}}
          <td class="text">
            <strong>{{tr}}mod-dPcabinet-tab-ajax_vw_examens{{/tr}} :</strong>
            <i>{{mb_value object=$object field=examen}}</i>
          </td>
        {{else}}
          <td colspan="2"></td>
        {{/if}}
        {{if !$show_histoire_maladie}}
          <td></td>
        {{/if}}
      </tr>
    {{/if}}
    <tr>
      <td class="text">
        <strong>{{tr}}CTraitement{{/tr}} :</strong>
        <i>{{mb_value object=$object field=traitement}}</i>
      </td>
      <td></td>
    </tr>
    <tr>
      <td class="text">
        <strong>{{tr}}CConsultation-conclusion{{/tr}} :</strong>
        <i>{{$object->conclusion|nl2br}}</i>
      </td>
      <td></td>
    </tr>
    {{if $object->_ref_examaudio->examaudio_id}}
      <tr>
        <td>
          <a href="#" onclick="newExam('exam_audio', {{$object->consultation_id}})">
            <strong>{{tr}}CExamAudio-long{{/tr}}</strong>
          </a>
        </td>
      </tr>
    {{/if}}
  {{/if}}

  {{if $object->annule}}
    <tr>
      <td>
        <strong>{{mb_title object=$object field=motif_annulation}}</strong>
        <i>{{mb_value object=$object field=motif_annulation}}</i>
      </td>
    </tr>
  {{/if}}
</table>

{{if !$limit_prise_rdv}}
  <table class="tbl">
    {{mb_include module=cabinet template=inc_list_actes_ccam subject=$object vue=complete}}
  </table>
  {{if ($object->_ref_plageconsult->chir_id == $app->user_id || $can->admin)}}
    <table class="form">
      <tr>
        <th class="category" colspan="2">
          {{tr}}CConsultation-part-Billing{{/tr}}
        </th>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-date_reglement{{/tr}} :</strong>
          {{if $object->_ref_facture->patient_date_reglement}}
            <i>{{mb_value object=$object->_ref_facture field=patient_date_reglement}}</i>
          {{else}}
            <i>{{tr}}CConsultation-Unpaid{{/tr}}</i>
          {{/if}}
        </td>
        <td rowspan="3">
          <table class="tbl">
            <tr>
              <th class="category">{{tr}}CReglement-mode{{/tr}}</th>
              <th class="category">{{tr}}CReglement-montant{{/tr}}</th>
              <th class="category">{{tr}}CReglement-date{{/tr}}</th>
              <th class="category">{{tr}}CReglement-banque_id{{/tr}}</th>
            </tr>
            {{foreach from=$object->_ref_facture->_ref_reglements item=reglement}}
              <tr>
                <td>{{tr}}CReglement.mode.{{$reglement->mode}}{{/tr}}</td>
                <td>{{mb_value object=$reglement field=montant}}</td>
                <td>{{mb_value object=$reglement field=date}}</td>
                <td>{{$reglement->_ref_banque->_view}}</td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="4">{{tr}}CConsultation-No payment made{{/tr}}</td>
              </tr>
            {{/foreach}}
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-Part agreement{{/tr}} :</strong>
          <i>{{mb_value object=$object field=secteur1}}</i>
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-Excess of fees{{/tr}} :</strong>
          <i>{{mb_value object=$object field=secteur2}}</i>
        </td>
      </tr>
    </table>
  {{/if}}
{{/if}}