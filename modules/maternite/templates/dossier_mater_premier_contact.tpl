{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=pere    value=$grossesse->_ref_pere}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

<script>
  listForms = [
    getForm("Premier-contact-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  addCountForm = function (form) {
    var newCount = parseInt($V(form._count_changes)) + 1;
    return $V(form._count_changes, newCount);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });

</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<form name="Premier-contact-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />

  <table class="main">
    <tr>
      <td>
        <fieldset>
          <legend>Contexte</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$dossier field=date_premier_contact}}</th>
              <td class="quarterPane">
                {{mb_field object=$dossier field=date_premier_contact form=Premier-contact-`$dossier->_guid` register=true
                onchange="addCountForm(this.form); submitAllForms(DossierMater.refresh);" class=notNull}}
              </td>
              <th class="quarterPane">{{mb_label object=$dossier field=consultant_premier_contact_id}}</th>
              <td class="quarterPane">
                {{mb_field object=$dossier field=consultant_premier_contact_id style="width: 12em;"
                options=$listConsultants class=notNull}}
              </td>
            </tr>
            <tr>
              <th>Age gestationnel au premier contact</th>
              <td colspan="3">{{$age_gest}} SA</td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>

  <script>
    Main.add(function () {
      Control.Tabs.create('tab-premier_contact', false, {foldable: true {{if $print}}, unfolded: true{{/if}}});
    });
  </script>

  <ul id="tab-premier_contact" class="control_tabs">
    <li><a href="#provenance">Provenance</a></li>
    <li><a href="#consultation">{{tr}}CSuiviGrossesse{{/tr}}</a></li>
    <li><a href="#informations">Informations / recommandations</a></li>
    <li><a href="#conclusion">Conclusion</a></li>
  </ul>

  <div id="provenance" style="display: none;">
    <table class="form me-no-align me-no-box-shadow">
      <tr>
        <th class="quarterPane">{{mb_label object=$dossier field=provenance_premier_contact}}</th>
        <td class="quarterPane">
          {{mb_field object=$dossier field=provenance_premier_contact
          style="width: 12em;" emptyLabel="CDossierPerinat.provenance_premier_contact."}}
        </td>
        <th class="quarterPane">{{mb_label object=$dossier field=nb_consult_ant_premier_contact}}</th>
        <td class="quarterPane">{{mb_field object=$dossier field=nb_consult_ant_premier_contact}}</td>
      </tr>
      <tr>
        <th><span class="compact">{{mb_label object=$dossier field=mater_provenance_premier_contact_id}}</span></th>
        <td>
          {{mb_field object=$dossier field=mater_provenance_premier_contact_id
          form="Premier-contact-`$dossier->_guid`" autocomplete="true,1,50,true,true"}}
        </td>
        <th><span class="compact">{{mb_label object=$dossier field=sa_consult_ant_premier_contact}}</span></th>
        <td>{{mb_field object=$dossier field=sa_consult_ant_premier_contact}}</td>
      </tr>
      <tr>
        <th><span class="compact">{{mb_label object=$dossier field=nivsoins_provenance_premier_contact}}</span></th>
        <td>
          {{mb_field object=$dossier field=nivsoins_provenance_premier_contact
          style="width: 12em;" emptyLabel="CDossierPerinat.nivsoins_provenance_premier_contact."}}
        </td>
        <th><span class="compact">{{mb_label object=$dossier field=surveillance_ant_premier_contact}}</span></th>
        <td>{{mb_field object=$dossier field=surveillance_ant_premier_contact}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=motif_premier_contact}}</th>
        <td>
          {{mb_field object=$dossier field=motif_premier_contact
          style="width: 12em;" emptyLabel="CDossierPerinat.motif_premier_contact."}}
        </td>
        <th><span class="compact">{{mb_label object=$dossier field=type_surv_ant_premier_contact}}</span></th>
        <td>
          {{mb_field object=$dossier field=type_surv_ant_premier_contact
          style="width: 12em;" emptyLabel="CDossierPerinat.type_surv_ant_premier_contact."}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=date_declaration_grossesse}}</th>
        <td>{{mb_field object=$dossier field=date_declaration_grossesse form=Premier-contact-`$dossier->_guid` register=true}}</td>
        <th>{{mb_label object=$dossier field=rques_provenance}}</th>
        <td>
          {{if !$print}}
            {{mb_field object=$dossier field=rques_provenance form=Premier-contact-`$dossier->_guid`}}
          {{else}}
            {{mb_value object=$dossier field=rques_provenance}}
          {{/if}}
        </td>
      </tr>
      <tr>
      </tr>
    </table>
  </div>

  <div id="consultation" style="display: none;">
    {{mb_include module=maternite template=inc_gestion_suivi_grossesse}}
  </div>

  <div id="informations" style="display: none;">
    <table class="main layout">
      <tr>
        <td class="halfPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="category" colspan="2">
                Recommandations remises
              </th>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_aucune typeEnum=checkbox}}</th>
              <td class="halfPane">{{mb_label object=$dossier field=reco_aucune}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_tabac typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_tabac}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_rhesus_negatif typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_rhesus_negatif}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_toxoplasmose typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_toxoplasmose}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_alcool typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_alcool}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_vaccination typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_vaccination}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_hygiene_alim typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_hygiene_alim}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_toxicomanie typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_toxicomanie}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=reco_brochure typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=reco_brochure}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=reco_autre}}</th>
              <td>{{mb_field object=$dossier field=reco_autre}}</td>
            </tr>
            <tr>
              <th class="category" colspan="2">
                Conseils minimal en cas d'addiction
              </th>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=souhait_arret_addiction}}</th>
              <td>{{mb_field object=$dossier field=souhait_arret_addiction default=""}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=souhait_aide_addiction}}</th>
              <td>{{mb_field object=$dossier field=souhait_aide_addiction default=""}}</td>
            </tr>
          </table>
        </td>
        <td class="halfPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="category" colspan="2">
                Informations fournies sur
              </th>
            </tr>
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=info_echographie}}</th>
              <td>{{mb_field object=$dossier field=info_echographie default=""}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=info_despistage_triso21}}</th>
              <td>{{mb_field object=$dossier field=info_despistage_triso21 default=""}}</td>
            </tr>
            <tr>
              <th><span class="compact">{{mb_label object=$dossier field=test_triso21_propose}}</span></th>
              <td>
                {{mb_field object=$dossier field=test_triso21_propose
                style="width: 12em;" emptyLabel="CDossierPerinat.test_triso21_propose."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=info_orga_maternite}}</th>
              <td>{{mb_field object=$dossier field=info_orga_maternite default=""}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=info_orga_reseau}}</th>
              <td>{{mb_field object=$dossier field=info_orga_reseau default=""}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=info_lien_pmi}}</th>
              <td>{{mb_field object=$dossier field=info_lien_pmi default=""}}</td>
            </tr>
            <tr>
              <th class="category" colspan="2">
                Projet de naissance initial
              </th>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=projet_lieu_accouchement}}</th>
              <td>{{mb_field object=$dossier field=projet_lieu_accouchement}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=projet_analgesie_peridurale}}</th>
              <td>
                {{mb_field object=$dossier field=projet_analgesie_peridurale
                style="width: 12em;" emptyLabel="CDossierPerinat.projet_analgesie_peridurale."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=projet_allaitement_maternel}}</th>
              <td>
                {{mb_field object=$dossier field=projet_allaitement_maternel
                style="width: 12em;" emptyLabel="CDossierPerinat.projet_allaitement_maternel."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=projet_preparation_naissance}}</th>
              <td>{{mb_field object=$dossier field=projet_preparation_naissance}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=projet_entretiens_proposes}}</th>
              <td>{{mb_field object=$dossier field=projet_entretiens_proposes default=""}}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <div id="conclusion" style="display: none;">
    <table class="main layout">
      <tr>
        <td class="halfPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=bas_risques}}</th>
              <td>{{mb_field object=$dossier field=bas_risques default=""}}</td>
            </tr>
            <tr>
              <th class="category" colspan="2">Si non, pourquoi ?</th>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_atcd_maternel_med typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_atcd_maternel_med}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_atcd_obst typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_atcd_obst}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_atcd_familiaux typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_atcd_familiaux}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_patho_mater_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_patho_mater_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_patho_foetale_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_patho_foetale_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_psychosocial_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_psychosocial_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=risque_grossesse_multiple typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=risque_grossesse_multiple}}</td>
            </tr>
          </table>
        </td>
        <td>
          <table class="form me-no-box-shadow me-no-align">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=type_surveillance}}</th>
              <td>
                {{mb_field object=$dossier field=type_surveillance
                style="width: 12em;" emptyLabel="CDossierPerinat.type_surveillance."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=lieu_surveillance}}</th>
              <td>
                {{mb_field object=$dossier field=lieu_surveillance
                style="width: 12em;" emptyLabel="CDossierPerinat.lieu_surveillance."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=lieu_accouchement_prevu}}</th>
              <td>{{mb_field object=$dossier field=lieu_accouchement_prevu}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=niveau_soins_prevu}}</th>
              <td>
                {{mb_field object=$dossier field=niveau_soins_prevu
                style="width: 12em;" emptyLabel="CDossierPerinat.niveau_soins_prevu."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=conclusion_premier_contact}}</th>
              <td>
                {{if !$print}}
                  {{mb_field object=$dossier field=conclusion_premier_contact form=Premier-contact-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=conclusion_premier_contact}}
                {{/if}}
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</form>
