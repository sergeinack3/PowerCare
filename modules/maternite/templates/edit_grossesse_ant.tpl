{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("Grossesse-ant-{{$grossesseAnt->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  refreshGrossesseAnt = function () {
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

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<form name="Grossesse-ant-{{$grossesseAnt->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$grossesseAnt}}
  {{mb_key   object=$grossesseAnt}}
  <input type="hidden" name="grossesse_id" value="{{$grossesseAnt->grossesse_id}}" />
  <input type="hidden" name="_count_changes" value="0" />

  <table class="main layout">
    <tr>
      <td>
        <table class="form">
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="save" onclick="submitAllForms(refreshGrossesseAnt);">
                Enregistrer et fermer
              </button>
              <button type="button" class="close" onclick="Control.Modal.close();">
                Fermer
              </button>
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$grossesseAnt field=date}}</th>
            <td>{{mb_field object=$grossesseAnt field=date form=Grossesse-ant-`$grossesseAnt->_guid` register=true}}</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <fieldset>
          <legend>Déroulement de la grossesse antérieure</legend>
          <table class="form">
            <tr>
              <th class="quarterPane">{{mb_label object=$grossesseAnt field=issue_grossesse}}</th>
              <td class="quarterPane">{{mb_field object=$grossesseAnt field=issue_grossesse style="width: 25em;"}}</td>
              <th class="quarterPane">{{mb_label object=$grossesseAnt field=suite_couches}}</th>
              <td class="quarterPane">{{mb_field object=$grossesseAnt field=suite_couches style="width: 25em;"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=lieu}}</th>
              <td>{{mb_field object=$grossesseAnt field=lieu style="width: 25em;"}}</td>
              <th>{{mb_label object=$grossesseAnt field=anesthesie}}</th>
              <td>{{mb_field object=$grossesseAnt field=anesthesie style="width: 25em;"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=ag}}</th>
              <td>{{mb_field object=$grossesseAnt field=ag}}</td>
              <th>{{mb_label object=$grossesseAnt field=perinee}}</th>
              <td>{{mb_field object=$grossesseAnt field=perinee style="width: 25em;"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=grossesse_apres_amp}}</th>
              <td>{{mb_field object=$grossesseAnt field=grossesse_apres_amp typeEnum=checkbox}}</td>
              <th>{{mb_label object=$grossesseAnt field=delivrance}}</th>
              <td>{{mb_field object=$grossesseAnt field=delivrance style="width: 25em;"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=complic_grossesse}}</th>
              <td>{{mb_field object=$grossesseAnt field=complic_grossesse style="width: 25em;"}}</td>
              <th>{{mb_label object=$grossesseAnt field=vecu_grossesse}}</th>
              <td>{{mb_field object=$grossesseAnt field=vecu_grossesse style="width: 25em;"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=transfert_in_utero}}</th>
              <td>{{mb_field object=$grossesseAnt field=transfert_in_utero typeEnum=checkbox}}</td>
              <th rowspan="3">{{mb_label object=$grossesseAnt field=remarques}}</th>
              <td rowspan="3">{{mb_field object=$grossesseAnt field=remarques form=Grossesse-ant-`$grossesseAnt->_guid`}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=mode_debut_travail}}</th>
              <td>
                {{mb_field object=$grossesseAnt field=mode_debut_travail
                style="width: 25em;" emptyLabel="CGrossesseAnt.mode_debut_travail."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=mode_accouchement}}</th>
              <td>{{mb_field object=$grossesseAnt field=mode_accouchement style="width: 25em;"}}</td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{tr}}CGrossesseAnt-enfants{{/tr}}</legend>
          <table class="form">
            <tr>
              <th>{{mb_label object=$grossesseAnt field=grossesse_multiple}}</th>
              <td colspan="3">{{mb_field object=$grossesseAnt field=grossesse_multiple typeEnum=checkbox}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=nombre_enfants}}</th>
              <td colspan="3">{{mb_field object=$grossesseAnt field=nombre_enfants}}</td>
            </tr>
            <tr>
              <th class="quarterPane"></th>
              <th class="category quarterPane">{{tr}}CGrossesseAnt-enfant1{{/tr}}</th>
              <th class="category quarterPane">{{tr}}CGrossesseAnt-enfant2{{/tr}}</th>
              <th class="category quarterPane">{{tr}}CGrossesseAnt-enfant3{{/tr}}</th>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=sexe_enfant1}}</th>
              <td>
                {{mb_field object=$grossesseAnt field=sexe_enfant1
                style="width: 25em;" emptyLabel="CGrossesseAnt.sexe_enfant1."}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=sexe_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=sexe_enfant2
                style="width: 25em;" emptyLabel="CGrossesseAnt.sexe_enfant2."}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=sexe_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=sexe_enfant3
                style="width: 25em;" emptyLabel="CGrossesseAnt.sexe_enfant3."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=poids_naissance_enfant1}}</th>
              <td>
                {{mb_field object=$grossesseAnt field=poids_naissance_enfant1}} grammes
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=poids_naissance_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=poids_naissance_enfant2}} grammes
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=poids_naissance_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=poids_naissance_enfant3}} grammes
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=etat_nouveau_ne_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=etat_nouveau_ne_enfant1 style="width: 25em;"}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=etat_nouveau_ne_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=etat_nouveau_ne_enfant2 style="width: 25em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=etat_nouveau_ne_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=etat_nouveau_ne_enfant3 style="width: 25em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=allaitement_enfant1}}</th>
              <td>
                {{mb_field object=$grossesseAnt field=allaitement_enfant1 typeEnum=checkbox}}
                {{mb_field object=$grossesseAnt field=allaitement_enfant1_desc style="width: 23em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=allaitement_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=allaitement_enfant2 typeEnum=checkbox}}
                {{mb_field object=$grossesseAnt field=allaitement_enfant2_desc style="width: 23em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=allaitement_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=allaitement_enfant3 typeEnum=checkbox}}
                {{mb_field object=$grossesseAnt field=allaitement_enfant3_desc style="width: 23em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=malformation_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=malformation_enfant1 style="width: 25em;"}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=malformation_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=malformation_enfant2 style="width: 25em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=malformation_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=malformation_enfant3 style="width: 25em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=maladie_hered_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=maladie_hered_enfant1 style="width: 25em;"}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=maladie_hered_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=maladie_hered_enfant2 style="width: 25em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=maladie_hered_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=maladie_hered_enfant3 style="width: 25em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=pathologie_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=pathologie_enfant1 style="width: 25em;"}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=pathologie_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=pathologie_enfant2 style="width: 25em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=pathologie_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=pathologie_enfant3 style="width: 25em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=transf_mut_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=transf_mut_enfant1 style="width: 25em;"}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=transf_mut_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=transf_mut_enfant2 style="width: 25em;"}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=transf_mut_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=transf_mut_enfant3 style="width: 25em;"}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=deces_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=deces_enfant1 typeEnum=checkbox}}</td>
              <td>
                {{mb_label object=$grossesseAnt field=deces_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=deces_enfant2 typeEnum=checkbox}}
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=deces_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=deces_enfant3 typeEnum=checkbox}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$grossesseAnt field=age_deces_enfant1}}</th>
              <td>{{mb_field object=$grossesseAnt field=age_deces_enfant1}} jours</td>
              <td>
                {{mb_label object=$grossesseAnt field=age_deces_enfant2 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=age_deces_enfant2}} jours
              </td>
              <td>
                {{mb_label object=$grossesseAnt field=age_deces_enfant3 style="display: none;"}}
                {{mb_field object=$grossesseAnt field=age_deces_enfant3}} jours
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</form>