{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}

<script>
  listForms = [
    getForm("Conduite-accouchement-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<form name="Conduite-accouchement-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />

  <table class="main">
    <tr>
      <td class="halfPane">
        <fieldset class="me-small">
          <legend>
            Présentation en fin de grossesse
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=presentation_fin_grossesse}}</th>
              <td>
                {{mb_field object=$dossier field=presentation_fin_grossesse
                style="width: 20em;" emptyLabel="CGrossesse.presentation_fin_grossesse."}}
                <br />
                {{mb_label object=$dossier field=autre_presentation_fin_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_presentation_fin_grossesse"}}
                {{mb_field object=$dossier field=autre_presentation_fin_grossesse
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>Si présentation du siège ou transverse,</th>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th>
                <span class="compact">{{mb_label object=$dossier field=version_presentation_manoeuvre_ext}}</span>
              </th>
              <td colspan="2">
                {{mb_field object=$dossier field=version_presentation_manoeuvre_ext
                style="width: 20em;" emptyLabel="CGrossesse.version_presentation_manoeuvre_ext."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=rques_presentation_fin_grossesse}}</th>
              <td colspan="2">
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_presentation_fin_grossesse form=Conduite-accouchement-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_presentation_fin_grossesse}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset class="me-small">
          <legend>
            Uterus
          </legend>
          <table class="form me-no-box-shadow me-no-align me-small-form">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=etat_uterus_fin_grossesse}}</th>
              <td>
                {{mb_field object=$dossier field=etat_uterus_fin_grossesse
                style="width: 20em;" emptyLabel="CGrossesse.etat_uterus_fin_grossesse."}}
                <br />
                {{mb_label object=$dossier field=autre_anomalie_uterus_fin_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_anomalie_uterus_fin_grossesse"}}
                {{mb_field object=$dossier field=autre_anomalie_uterus_fin_grossesse
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>Si utérus cicatriciel,</th>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th>
                <span class="compact">{{mb_label object=$dossier field=nb_cicatrices_uterus_fin_grossesse}}</span>
              </th>
              <td colspan="2">{{mb_field object=$dossier field=nb_cicatrices_uterus_fin_grossesse}}</td>
            </tr>
            <tr>
              <th>
                <span class="compact">{{mb_label object=$dossier field=date_derniere_hysterotomie}}</span>
              </th>
              <td
                colspan="2">{{mb_field object=$dossier field=date_derniere_hysterotomie form=Conduite-accouchement-`$dossier->_guid` register=true}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=rques_etat_uterus}}</th>
              <td colspan="2">
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_etat_uterus form=Conduite-accouchement-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_etat_uterus}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <fieldset class="me-small">
          <legend>Confrontation céphalo-pelvienne</legend>
          <table class="main layout">
            <tr>
              <td class="halfPane">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="title me-text-align-center" colspan="2">
                      Bassin
                      <div style="display: none">
                        http://www.aly-abbara.com/livre_gyn_obs/termes/pelvimetrie/pelvimetrie.html
                        <br /><br />
                        TM : Diamètre transverse médian en moyenne 125 mm
                        <br />
                        PRP : Diamètre promonto-rétro-pubien > 105 mm
                        <br /><br />
                        Indice de Magnin : (PRP + TM)
                        <br />
                        - normal > 230 mm
                        <br />
                        - favorable > 220 mm
                        <br />
                        - pronostic incertain entre 210 mm et 220 mm
                        <br />
                        - médiocre entre 200 mm et 210 mm
                        <br />
                        - et mauvais si < 200 mm
                        <br /><br />
                        Diamètre bisciatique (bi-épineux) : en moyenne 100 à 105 mm
                        <br /><br />
                        http://umvf.omsk-osma.ru/campus-gyneco-obstetrique/cycle3/MTO/poly/16000fra-2.html
                      </div>
                    </th>
                  </tr>
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=etat_uterus_fin_grossesse}}</th>
                    <td>
                      {{mb_field object=$dossier field=appreciation_clinique_etat_bassin
                      style="width: 20em;" emptyLabel="CGrossesse.appreciation_clinique_etat_bassin."}}
                      <br />
                      {{mb_label object=$dossier field=desc_appreciation_clinique_etat_bassin style="display:none"}}
                      {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_appreciation_clinique_etat_bassin"}}
                      {{mb_field object=$dossier field=desc_appreciation_clinique_etat_bassin
                      style="width: 20em;" placeholder=$placeholder}}
                    </td>
                  </tr>
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=pelvimetrie}}</th>
                    <td>
                      {{mb_field object=$dossier field=pelvimetrie
                      style="width: 20em;" emptyLabel="CGrossesse.pelvimetrie."}}
                      <br />
                      {{mb_label object=$dossier field=desc_pelvimetrie style="display:none"}}
                      {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_pelvimetrie"}}
                      {{mb_field object=$dossier field=desc_pelvimetrie
                      style="width: 20em;" placeholder=$placeholder}}
                    </td>
                  </tr>
                  <tr>
                    <th>Si anomalie</th>
                    <td></td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=diametre_transverse_median}}</th>
                    <td>{{mb_field object=$dossier field=diametre_transverse_median}} cm</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=diametre_promonto_retro_pubien}}</th>
                    <td>{{mb_field object=$dossier field=diametre_promonto_retro_pubien}} cm</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=diametre_bisciatique}}</th>
                    <td>{{mb_field object=$dossier field=diametre_bisciatique}} cm</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=indice_magnin}}</th>
                    <td>{{mb_field object=$dossier field=indice_magnin}} (TM + PRP)</td>
                  </tr>
                </table>
              </td>
              <td class="halfPane">
                <table class="form me-small-form me-no-box-shadow me-no-align">
                  <tr>
                    <th class="title me-text-align-center" colspan="3">Echographie fin de grossesse</th>
                  </tr>
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=date_echo_fin_grossesse}}</th>
                    <td>{{mb_field object=$dossier field=date_echo_fin_grossesse form=Conduite-accouchement-`$dossier->_guid` register=true}}</td>
                    <td>
                      {{mb_label object=$dossier field=sa_echo_fin_grossesse style="display:none"}}
                      {{mb_field object=$dossier field=sa_echo_fin_grossesse}} SA
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=bip_fin_grossesse}}</th>
                    <td colspan="2">{{mb_field object=$dossier field=bip_fin_grossesse}} mm</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=est_pond_fin_grossesse}}</th>
                    <td colspan="2">{{mb_field object=$dossier field=est_pond_fin_grossesse}} g</td>
                  </tr>
                  <tr>
                    <th>Si grossesse multiple, {{mb_label object=$dossier field=est_pond_2e_foetus_fin_grossesse}}</th>
                    <td colspan="2">{{mb_field object=$dossier field=est_pond_2e_foetus_fin_grossesse}} g</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <fieldset class="me-small">
          <legend>Conclusion <span style="margin-left: 5px;">{{mb_include module=system template=inc_object_history object=$dossier}}</span></legend>
          <table class="form me-no-box-shadow me-no-align me-small-form">
            <tr>
              <th class="quarterPane">{{mb_label object=$dossier field=conduite_a_tenir_acc}}</th>
              <td class="quarterPane">
                {{mb_field object=$dossier field=conduite_a_tenir_acc
                style="width: 20em;" emptyLabel="CGrossesse.conduite_a_tenir_acc."}}
              </td>
              <td class="halfPane">{{mb_label object=$dossier field=rques_conduite_a_tenir}}</td>
            </tr>
            <tr>
              <th>Si césarienne, {{mb_label object=$dossier field=niveau_alerte_cesar}}</th>
              <td>
                {{mb_field object=$dossier field=niveau_alerte_cesar
                style="width: 20em;" emptyLabel="CGrossesse.niveau_alerte_cesar."}}
              </td>
              <td rowspan="2">
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_conduite_a_tenir form=Conduite-accouchement-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_conduite_a_tenir}}
                {{/if}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=date_decision_conduite_a_tenir_acc}}</th>
              <td>
                {{mb_field object=$dossier field=date_decision_conduite_a_tenir_acc
                form=Conduite-accouchement-`$dossier->_guid` register=true class=notNull}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=valid_decision_conduite_a_tenir_acc_id}}</th>
              <td>
                {{mb_field object=$dossier field=valid_decision_conduite_a_tenir_acc_id style="width: 20em;"
                options=$listConsultants class=notNull}}
              </td>
               <td>{{mb_label object=$dossier field=facteur_risque}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=motif_conduite_a_tenir_acc}}</th>
              <td>{{mb_field object=$dossier field=motif_conduite_a_tenir_acc style="width: 20em;"}}</td>
              <td rowspan="2">
                {{if !$print}}
                  {{mb_field object=$dossier field=facteur_risque form=Conduite-accouchement-`$dossier->_guid`
                  aidesaisie="validateOnBlur: 0"}}
                {{else}}
                  {{mb_value object=$dossier field=facteur_risque}}
                {{/if}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=date_prevue_interv}}</th>
              <td>{{mb_field object=$dossier field=date_prevue_interv form=Conduite-accouchement-`$dossier->_guid` register=true}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</form>
