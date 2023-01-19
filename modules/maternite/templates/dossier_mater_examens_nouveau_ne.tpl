{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="main">
  <tr>
    <td colspan="2" class="button">
      <button type="button" class="add not-printable" onclick="DossierMater.addExamenNouveauNe(null, '{{$grossesse->_id}}');"
              {{if $grossesse->_ref_naissances|@count == 0}}disabled="disabled" title="{{tr}}CNaissance.none{{/tr}}"{{/if}}>
        {{tr}}Add{{/tr}} {{tr}}CExamenNouveauNe.one{{/tr}}
      </button>
      <button type="button" class="close not-printable" id="close_dossier_perinat" onclick="Control.Modal.close();">
        Fermer
      </button>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Mensurations</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="width: 15em;"></td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <th style="width: 23em;">
                <button type="button" class="edit notext not-printable" style="float: right;"
                        onclick="DossierMater.addExamenNouveauNe('{{$examen->_id}}', '{{$grossesse->_id}}');">
                  {{tr}}Edit{{/tr}}
                </button>
                {{mb_value object=$examen field=date}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$examen->_ref_examinateur}}
                <br />
                {{mb_value object=$examen field=_jours}} j
                <br />
                {{$examen->_ref_naissance->_ref_sejour_enfant->_ref_patient}}
              </th>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=poids}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td>{{if $examen->poids}}{{mb_value object=$examen field=poids}} g{{/if}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=taille}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td>{{if $examen->taille}}{{mb_value object=$examen field=taille}} cm{{/if}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=pc}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td>{{if $examen->pc}}{{mb_value object=$examen field=pc}} cm{{/if}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=bip}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td>{{if $examen->bip}}{{mb_value object=$examen field=bip}} mm{{/if}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Inspection</legend>
        <table class="tbl me-no-align me-no-box-shadow">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=coloration_globale}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=coloration_globale}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=revetement_cutane}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=revetement_cutane}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=etat_trophique}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=etat_trophique}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Examen cardio-pulmonaire</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=auscultation}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=auscultation}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=pouls_femoraux}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=pouls_femoraux}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=ta}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=ta}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Tête</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=crane}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=crane}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=face_yeux}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=face_yeux}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=cavite_buccale}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=cavite_buccale}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=fontanelles}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=fontanelles}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=sutures}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=sutures}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=cou}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=cou}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Abdomen</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=foie}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=foie}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=rate}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=rate}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=reins}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=reins}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=ombilic}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=ombilic}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=orifices_herniaires}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=orifices_herniaires}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=ligne_mediane_posterieure}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=ligne_mediane_posterieure}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=region_sacree}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=region_sacree}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=anus}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=anus}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Organes génitaux externes</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=jet_mictionnel}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=jet_mictionnel}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Examen orthopédique</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=clavicules}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=clavicules}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=hanches}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=hanches}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=mains}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=mains}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=pieds}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=pieds}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Examen neurologique</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=cri}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=cri}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=reactivite}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=reactivite}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=tonus_axial}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=tonus_axial}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=tonus_membres}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=tonus_membres}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=reflexes_archaiques}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=reflexes_archaiques}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>{{tr}}CExamenNouveauNe-Hearing Exam{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=test_audition}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=test_audition}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=oreille_droite}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=oreille_droite}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=oreille_gauche}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=oreille_gauche}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=rdv_orl}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=rdv_orl}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>{{tr}}CExamenNouveauNe-Guthrie Exam{{/tr}}</legend>
        <table class="tbl me-no-align me-no-box-shadow">
          <tr>
            <td style="text-align: right; width: 15em;" title="{{tr}}CExamenNouveauNe-Guthrie realized-desc{{/tr}}">
              {{tr}}CExamenNouveauNe-Guthrie realized{{/tr}}
            </td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
                {{if $examen->guthrie_datetime && $examen->guthrie_user_id}}
                  <td class="text">
                    <i class="fa fa-check" style="color: forestgreen;"></i>
                      {{tr}}common-Yes{{/tr}}
                  </td>
                {{else}}
                  <td class="text">
                    <i class="fa fa-times" style="color: #820001;"></i>
                      {{tr}}common-No{{/tr}}
                  </td>
                {{/if}}
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=guthrie_datetime}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=guthrie_datetime}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=guthrie_user_id}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=guthrie_user_id tooltip=true}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=guthrie_envoye}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text" style="width: 23em;">{{mb_value object=$examen field=guthrie_envoye}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Estimation du développement foetal</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=est_age_gest}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td style="width: 23em;">{{if $examen->est_age_gest}}{{mb_value object=$examen field=est_age_gest}} SA{{/if}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=dev_ponderal}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=dev_ponderal}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=croiss_ponderale}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=croiss_ponderale}}</td>
            {{/foreach}}
            <td></td>
          </tr>
          <tr>
            <td style="text-align: right;">{{mb_label class=CExamenNouveauNe field=croiss_staturale}}</td>
            {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
              <td class="text">{{mb_value object=$examen field=croiss_staturale}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>{{tr}}CExamenNouveauNe-commentaire-desc{{/tr}}</legend>
        <table class="tbl me-no-box-shadow me-no-align">
          <tr>
            <td style="text-align: right; width: 15em;">{{mb_label class=CExamenNouveauNe field=commentaire}}</td>
              {{foreach from=$grossesse->_back.examens_nouveau_ne item=examen}}
                <td class="text" style="width: 23em;">{{mb_value object=$examen field=commentaire}}</td>
              {{/foreach}}
            <td></td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>
