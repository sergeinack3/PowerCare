{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("Examen-{{$examen->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  refreshExamens = function () {
    DossierMater.refresh();
    Control.Modal.close();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    includeForms();
    DossierMater.prepareAllForms();
  });
</script>

{{assign var=naissance value=$examen->_ref_naissance}}

<form name="Examen-{{$examen->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$examen}}
  {{mb_key   object=$examen}}
  <input type="hidden" name="grossesse_id" value="{{$examen->grossesse_id}}" />
  <input type="hidden" name="_count_changes" value="0" />

  <table class="main layout">
    <tr>
      <td colspan="4">
        <table class="form">
          <tr>
            <td colspan="6" class="button">
              <button type="button" class="save" onclick="submitAllForms(refreshExamens);">
                Enregistrer et fermer
              </button>
              <button type="button" class="close" onclick="Control.Modal.close();">
                Fermer
              </button>
            </td>
          </tr>
          <tr>
            <th style="width: 17.5%">{{mb_label object=$examen field=date}}</th>
            <td style="width: 16.5%">
              {{mb_field object=$examen field=date form=Examen-`$examen->_guid` register=true class=notNull}}
            </td>
            <th style="width: 16.5%">{{mb_label object=$examen field=examinateur_id}}</th>
            <td style="width: 16.5%">
              {{mb_field object=$examen field=examinateur_id style="width: 16em;" options=$listConsultants}}
            </td>
            <th style="width: 16.5%">{{mb_label object=$examen field=naissance_id}}</th>
            <td style="width: 16.5%">
              <select name="naissance_id" style="width: 16em;" class="notNull">
                <option value="">&mdash; Choisir un enfant</option>
                {{foreach from=$enfants item=enfant key=naissance_id}}
                  <option value="{{$naissance_id}}" {{if $examen->naissance_id == $naissance_id}}selected="selected"{{/if}}>
                    {{$enfant}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="4">
        <table class="form">
          <tr>
            <th class="category" colspan="8">Mensurations</th>
          </tr>
          <tr>
            <th style="width: 12.5%">{{mb_label object=$examen field=poids}}</th>
            <td style="width: 12.5%">{{mb_field object=$examen field=poids}} g</td>
            <th style="width: 12.5%">{{mb_label object=$examen field=taille}}</th>
            <td style="width: 12.5%">{{mb_field object=$examen field=taille}} cm</td>
            <th style="width: 12.5%">{{mb_label object=$examen field=pc}}</th>
            <td style="width: 12.5%">{{mb_field object=$examen field=pc}} cm</td>
            <th style="width: 12.5%">{{mb_label object=$examen field=bip}}</th>
            <td style="width: 12.5%">{{mb_field object=$examen field=bip}} mm</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="quarterPane">
        <table class="form">
          <tr>
            <th class="category" colspan="2">Inspection</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=coloration_globale}}</th>
            <td>{{mb_field object=$examen field=coloration_globale}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=revetement_cutane}}</th>
            <td>{{mb_field object=$examen field=revetement_cutane}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=etat_trophique}}</th>
            <td>{{mb_field object=$examen field=etat_trophique}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">Examen cardio-pulmonaire</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=auscultation}}</th>
            <td>{{mb_field object=$examen field=auscultation}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=pouls_femoraux}}</th>
            <td>{{mb_field object=$examen field=pouls_femoraux}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=ta}}</th>
            <td>{{mb_field object=$examen field=ta}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CExamenNouveauNe-Hearing Exam{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=test_audition}}</th>
            <td>{{mb_field object=$examen field=test_audition}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=oreille_droite}}</th>
            <td>{{mb_field object=$examen field=oreille_droite emptyLabel="CDossierPerinat.dev_ponderal."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=oreille_gauche}}</th>
            <td>{{mb_field object=$examen field=oreille_gauche emptyLabel="CDossierPerinat.dev_ponderal."}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=rdv_orl}}</th>
            <td>{{mb_field object=$examen field=rdv_orl form=Examen-`$examen->_guid` register=true}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">{{tr}}CExamenNouveauNe-Guthrie Exam{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=guthrie_datetime}}</th>
            <td>{{mb_field object=$examen field=guthrie_datetime form=Examen-`$examen->_guid` register=true}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=guthrie_user_id}}</th>
            <td>{{mb_field object=$examen field=guthrie_user_id options=$listConsultants}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=guthrie_envoye}}</th>
            <td>{{mb_field object=$examen field=guthrie_envoye}}</td>
          </tr>
        </table>
      </td>
      <td class="quarterPane">
        <table class="form">
          <tr>
            <th class="category" colspan="2">Tête</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=crane}}</th>
            <td>{{mb_field object=$examen field=crane}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=face_yeux}}</th>
            <td>{{mb_field object=$examen field=face_yeux}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=cavite_buccale}}</th>
            <td>{{mb_field object=$examen field=cavite_buccale}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=fontanelles}}</th>
            <td>{{mb_field object=$examen field=fontanelles}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=sutures}}</th>
            <td>{{mb_field object=$examen field=sutures}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=cou}}</th>
            <td>{{mb_field object=$examen field=cou}}</td>
          </tr>
        </table>
      </td>
      <td class="quarterPane">
        <table class="form">
          <tr>
            <th class="category" colspan="2">Abdomen</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=foie}}</th>
            <td>{{mb_field object=$examen field=foie}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=rate}}</th>
            <td>{{mb_field object=$examen field=rate}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=reins}}</th>
            <td>{{mb_field object=$examen field=reins}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=ombilic}}</th>
            <td>{{mb_field object=$examen field=ombilic}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=orifices_herniaires}}</th>
            <td>{{mb_field object=$examen field=orifices_herniaires}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=ligne_mediane_posterieure}}</th>
            <td>{{mb_field object=$examen field=ligne_mediane_posterieure}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=region_sacree}}</th>
            <td>{{mb_field object=$examen field=region_sacree}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$examen field=anus}}</th>
            <td>{{mb_field object=$examen field=anus}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">Organes génitaux externes</th>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=jet_mictionnel}}</th>
            <td>{{mb_field object=$examen field=jet_mictionnel}}</td>
          </tr>
        </table>
      </td>
      <td class="quarterPane">
        <table class="form">
          <tr>
            <th class="category" colspan="2">Examen orthopédique</th>
          <tr>
            <th>{{mb_label object=$examen field=clavicules}}</th>
            <td>{{mb_field object=$examen field=clavicules}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=hanches}}</th>
            <td>{{mb_field object=$examen field=hanches}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=mains}}</th>
            <td>{{mb_field object=$examen field=mains}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=pieds}}</th>
            <td>{{mb_field object=$examen field=pieds}}</td>
          </tr>
          <tr>
            <th class="category" colspan="2">Examen neurologique</th>
          <tr>
            <th>{{mb_label object=$examen field=cri}}</th>
            <td>{{mb_field object=$examen field=cri}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=reactivite}}</th>
            <td>{{mb_field object=$examen field=reactivite}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=tonus_axial}}</th>
            <td>{{mb_field object=$examen field=tonus_axial}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=tonus_membres}}</th>
            <td>{{mb_field object=$examen field=tonus_membres}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$examen field=reflexes_archaiques}}</th>
            <td>{{mb_field object=$examen field=reflexes_archaiques}}</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="4">
        <table class="form">
          <tr>
            <th class="category" colspan="8">Estimation du développement foetal</th>
          </tr>
          <tr>
            <th style="width: 12.5%">{{mb_label object=$examen field=est_age_gest}}</th>
            <td style="width: 12.5%">{{mb_field object=$examen field=est_age_gest}} SA</td>
            <th style="width: 12.5%">{{mb_label object=$examen field=dev_ponderal}}</th>
            <td style="width: 12.5%">
              {{mb_field object=$examen field=dev_ponderal
              style="width: 12em;" emptyLabel="CDossierPerinat.dev_ponderal."}}
            </td>
            <th style="width: 12.5%">{{mb_label object=$examen field=croiss_ponderale}}</th>
            <td style="width: 12.5%">
              {{mb_field object=$examen field=croiss_ponderale
              style="width: 12em;" emptyLabel="CDossierPerinat.croiss_ponderale."}}
            </td>
            <th style="width: 12.5%">{{mb_label object=$examen field=croiss_staturale}}</th>
            <td style="width: 12.5%">
              {{mb_field object=$examen field=croiss_staturale
              style="width: 12em;" emptyLabel="CDossierPerinat.croiss_staturale."}}
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="4">
        <table class="form">
          <tr>
            <th class="category" colspan="2">{{tr}}CExamenNouveauNe-commentaire-desc{{/tr}}</th>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$examen field=commentaire}}</th>
            <td>{{mb_field object=$examen field=commentaire}}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
