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
    getForm("Transfert-{{$dossier->_guid}}")
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

<form name="Transfert-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />

  <table class="main">
    <tr>
      <td colspan="2">
        <fieldset>
          <legend>
            Transfert *
          </legend>
          <table class="main layout">
            <tr>
              <td colspan="2">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <td colspan="2">*NB
                      : {{tr}}CGrossesse-msg-Patient hospitalized in an institution and transferred to another institution|f{{/tr}}</td>
                  </tr>
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=transf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=transf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.transf_antenat."}}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td class="halfPane">
                <table class="form me-no-box-shadow me-no-align">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=date_transf_antenat}}</th>
                    <td>{{mb_field object=$dossier field=date_transf_antenat form="Transfert-`$dossier->_guid`" register=true}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=lieu_transf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=lieu_transf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.lieu_transf_antenat."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=etab_transf_antenat}}</th>
                    <td>{{mb_field object=$dossier field=etab_transf_antenat style="width: 20em;"}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=nivsoins_transf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=nivsoins_transf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.nivsoins_transf_antenat."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=raison_transf_antenat_hors_reseau}}</th>
                    <td>
                      {{mb_field object=$dossier field=raison_transf_antenat_hors_reseau
                      style="width: 20em;" emptyLabel="CGrossesse.raison_transf_antenat_hors_reseau."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=raison_imp_transf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=raison_imp_transf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.raison_imp_transf_antenat."}}
                    </td>
                  </tr>
                </table>
              </td>
              <td class="halfPane">
                <table class="form me-no-align me-no-box-shadow">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=motif_tranf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=motif_tranf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.motif_tranf_antenat."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=type_patho_transf_antenat}}</th>
                    <td>
                      {{mb_field object=$dossier field=type_patho_transf_antenat
                      style="width: 20em;" emptyLabel="CGrossesse.type_patho_transf_antenat."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=rques_transf_antenat}}</th>
                    <td>
                      {{if !$print}}
                        {{mb_field object=$dossier field=rques_transf_antenat form=Transfert-`$dossier->_guid`}}
                      {{else}}
                        {{mb_value object=$dossier field=rques_transf_antenat}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>
            Conditions du transfert
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=mode_transp_transf_antenat}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=mode_transp_transf_antenat
                style="width: 20em;" emptyLabel="CGrossesse.mode_transp_transf_antenat."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=antibio_transf_antenat typeEnum=checkbox}}</th>
              <td class="narrow">{{mb_label object=$dossier field=antibio_transf_antenat}}</td>
              <td>{{mb_field object=$dossier field=nom_antibio_transf_antenat}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=cortico_transf_antenat typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=cortico_transf_antenat}}</td>
              <td>{{mb_field object=$dossier field=nom_cortico_transf_antenat}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=datetime_cortico_transf_antenat}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=datetime_cortico_transf_antenat form="Transfert-`$dossier->_guid`" register=true}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=tocolytiques_transf_antenat typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=tocolytiques_transf_antenat}}</td>
              <td>{{mb_field object=$dossier field=nom_tocolytiques_transf_antenat}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=antihta_transf_antenat typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=antihta_transf_antenat}}</td>
              <td>{{mb_field object=$dossier field=nom_antihta_transf_antenat}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_ttt_transf_antenat typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=autre_ttt_transf_antenat}}</td>
              <td>{{mb_field object=$dossier field=nom_autre_ttt_transf_antenat}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset>
          <legend>
            Retour à la maternité d'origine
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=retour_mater_transf_antenat}}</th>
              <td>
                {{mb_field object=$dossier field=retour_mater_transf_antenat
                style="width: 20em;" emptyLabel="CGrossesse.retour_mater_transf_antenat."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=date_retour_transf_antenat}}</th>
              <td>{{mb_field object=$dossier field=date_retour_transf_antenat form="Transfert-`$dossier->_guid`" register=true}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=devenir_retour_transf_antenat}}</th>
              <td>
                {{mb_field object=$dossier field=devenir_retour_transf_antenat
                style="width: 20em;" emptyLabel="CGrossesse.devenir_retour_transf_antenat."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=rques_retour_transf_antenat}}</th>
              <td>
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_retour_transf_antenat form=Transfert-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_retour_transf_antenat}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</form>
