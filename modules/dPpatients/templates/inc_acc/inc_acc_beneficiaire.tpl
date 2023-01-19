{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $conf.ref_pays != 1}}
  <table class="form me-margin-0 me-no-box-shadow">
    <tr>
      <th>{{mb_label object=$patient field=assurance_invalidite}}</th>
      <td>{{mb_field object=$patient field=assurance_invalidite emptyLabel="CPatient.assurance_invalidite."}}</td>
      <th>{{mb_label object=$patient field=niveau_prise_en_charge}}</th>
      <td>{{mb_field object=$patient field=niveau_prise_en_charge emptyLabel="CPatient.niveau_prise_en_charge."}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=decision_assurance_invalidite}}</th>
      <td>{{mb_field object=$patient field=decision_assurance_invalidite emptyLabel="CPatient.decision_assurance_invalidite."}}</td>
      <td colspan="2"></td>
    </tr>
  </table>
  {{mb_return}}
{{/if}}

<script>
  Main.add(Patient.checkFinAmo);
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <table class="form me-no-box-shadow me-margin-8">
        <col style="width: 50%;" />

        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="code_regime"}}
            {{mb_field object=$patient field="code_regime"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="caisse_gest"}}
            {{mb_field object=$patient field="caisse_gest"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="centre_gest"}}
            {{mb_field object=$patient field="centre_gest" onchange="Patient.checkCentreGestionnaire(this);"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="code_gestion"}}
            {{mb_field object=$patient field="code_gestion"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="centre_carte"}}
            {{mb_field object=$patient field="centre_carte"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="regime_sante"}}
            {{mb_field object=$patient field="regime_sante"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="deb_amo"}}
            {{mb_field object=$patient field="deb_amo" form="editFrm" register=true}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="fin_amo"}}
            {{mb_field object=$patient field="fin_amo" form="editFrm" register=true onchange="Patient.checkFinAmo()"}} {{* event observer doesn't work :( *}}
            <div class="warning" id="fin_amo_warning" style="display: none;">
              Période de droits terminée
            </div>
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="code_exo"}}
            {{mb_field object=$patient field="code_exo"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="code_sit"}}
            {{mb_field object=$patient field="code_sit"}}
          {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field layout=1 nb_cells=2 mb_object=$patient mb_field="medecin_traitant_declare"}}
            {{mb_field object=$patient field="medecin_traitant_declare" typeEnum=radio}}
            {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="regime_am"}}
            {{mb_field object=$patient field="regime_am"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="acs"}}
            {{mb_field object=$patient field=acs}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="acs_type"}}
            {{mb_field object=$patient field=acs_type emptyLabel='CPatient.acs_type.'}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assurance_invalidite"}}
            {{mb_field object=$patient field=assurance_invalidite emptyLabel="CPatient.assurance_invalidite."}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="decision_assurance_invalidite"}}
            {{mb_field object=$patient field=decision_assurance_invalidite emptyLabel="CPatient.decision_assurance_invalidite."}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="niveau_prise_en_charge"}}
            {{mb_field object=$patient field=niveau_prise_en_charge emptyLabel="CPatient.niveau_prise_en_charge."}}
          {{/me_form_field}}
        </tr>
        {{if $patient->_ref_last_ins}}
          <tr>
            {{me_form_field nb_cells=2 mb_object=$patient mb_field="ins"}}
              {{mb_value object=$patient->_ref_last_ins field="ins"}} ({{$patient->_ref_last_ins->date|date_format:$conf.date}})
            {{/me_form_field}}
          </tr>
        {{/if}}
      </table>
    </td>
    <td>
      <table class="form me-no-box-shadow me-margin-0">
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="ald"}}
            {{mb_field object=$patient field="ald"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="incapable_majeur"}}
            {{mb_field object=$patient field="incapable_majeur"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="c2s"}}
            {{mb_field object=$patient field="c2s" onchange="Patient.calculFinAmo();"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="ame"}}
            {{mb_field object=$patient field="ame"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="ATNC"}}
            {{mb_field object=$patient field="ATNC"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_bool nb_cells=2 mb_object=$patient mb_field="is_smg"}}
            {{mb_field object=$patient field="is_smg"}}
          {{/me_form_bool}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="fin_validite_vitale"}}
            {{mb_field object=$patient field="fin_validite_vitale" form="editFrm" register=true}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="mutuelle_types_contrat"}}
            {{*
            <script type="text/javascript">
              Main.add(function(){
                window.mutuelleToken = new TokenField(getForm("editFrm").mutuelle_types_contrat);
              });
            </script>
            {{mb_field object=$patient field="mutuelle_types_contrat" hidden=true}}

            <div id="mutuelle-types-contrats">
              {{foreach from=$patient->_mutuelle_types_contrat item=_type_contrat}}
                <div>
                  <button type="button" class="remove notext" onclick="mutuelleToken.remove(this.innerHTML); $(this).up().remove()">{{$_type_contrat}}</button> {{$_type_contrat}}
                </div>
              {{/foreach}}
              <button type="button" class="add notext" onclick="var n=$(this).next(); mutuelleToken.add(n.value); n.value=''"></button>
              <input type="text" />
            </div>
             *}}
            {{mb_field object=$patient field="mutuelle_types_contrat"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="notes_amo"}}
            {{mb_field object=$patient field="notes_amo"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="libelle_exo"}}
            {{mb_field object=$patient field="libelle_exo" onblur="Patient.tabs.changeTabAndFocus('correspondance', getForm('editCorrespondant_prevenir').nom)"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="notes_amc"}}
            {{mb_field object=$patient field="notes_amc"}}
          {{/me_form_field}}
        </tr>
      </table>
    </td>
  </tr>
</table>
