{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td class="halfPane">
      <fieldset>
        <legend>Contexte psycho-social</legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=profession_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=profession_pdt_grossesse style="width: 16em;"}}</td>
          </tr>
          <tr>
            <th><span class="compact">Si oui, {{mb_label object=$dossier field=ag_date_arret_travail}}</span></th>
            <td>{{mb_field object=$dossier field=ag_date_arret_travail}} SA</td>
          </tr>
          <tr>
            <th class="text" style="text-align: right;">{{mb_label object=$dossier field=situation_pb_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=situation_pb_pdt_grossesse default=""}}</td>
          </tr>
          <tr>
            <th>Si oui,</th>
            <td></td>
          </tr>
          <tr>
            <th class="compact">{{mb_field object=$dossier field=separation_pdt_grossesse typeEnum=checkbox}}</th>
            <td class="compact">{{mb_label object=$dossier field=separation_pdt_grossesse}}</td>
          </tr>
          <tr>
            <th class="compact">{{mb_field object=$dossier field=deces_fam_pdt_grossesse typeEnum=checkbox}}</th>
            <td class="compact">{{mb_label object=$dossier field=deces_fam_pdt_grossesse}}</td>
          </tr>
          <tr>
            <th class="compact">{{mb_label object=$dossier field=autre_evenement_fam_pdt_grossesse}}</th>
            <td class="compact">{{mb_field object=$dossier field=autre_evenement_fam_pdt_grossesse style="width: 16em;"}}</td>
          </tr>
          <tr>
            <th class="compact">{{mb_field object=$dossier field=perte_emploi_pdt_grossesse typeEnum=checkbox}}</th>
            <td class="compact">{{mb_label object=$dossier field=perte_emploi_pdt_grossesse}}</td>
          </tr>
          <tr>
            <th class="compact">{{mb_label object=$dossier field=autre_evenement_soc_pdt_grossesse}}</span></th>
            <td class="compact">{{mb_field object=$dossier field=autre_evenement_soc_pdt_grossesse style="width: 16em;"}}</td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Informations saisies dans les renseignements généraux</legend>
        <table class="form me-no-align me-no-box-shadow me-small-form">
          <tr>
            <th class="title me-text-align-center" colspan="4">Sur le plan social</th>
          </tr>
          <tr>
            <th class="halfPane" colspan="2">{{mb_label object=$patient field=ressources_financieres}} de la mère</th>
            <td class="halfPane" colspan="2">{{mb_value object=$patient field=ressources_financieres}}</td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$patient field=regime_sante}}</th>
            <td colspan="2">{{mb_value object=$patient field=regime_sante}}</td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$patient field=c2s}}</th>
            <td colspan="2">{{mb_value object=$patient field=c2s}}</td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$patient field=ame}}</th>
            <td colspan="2">{{mb_value object=$patient field=ame}}</td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$patient field=hebergement_precaire}}</th>
            <td colspan="2">{{mb_value object=$patient field=hebergement_precaire}}</td>
          </tr>
          {{if $grossesse->pere_id}}
            <tr>
              <th colspan="2">{{mb_label object=$pere field=ressources_financieres}} du père</th>
              <td colspan="2">{{mb_value object=$pere field=ressources_financieres}}</td>
            </tr>
          {{/if}}
          <tr>
            <th colspan="2">{{mb_label object=$dossier field=rques_social}}</th>
            <td class="text" colspan="2">{{mb_value object=$dossier field=rques_social}}</td>
          </tr>
          <tr>
            <th class="title me-text-align-center" colspan="4">Sur le plan psychologique (mère)</th>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$dossier field=enfants_foyer}}</th>
            <td class="quarterPane">{{mb_value object=$dossier field=enfants_foyer}}</td>
            <th class="quarterPane"></th>
            <td class="quarterPane"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=situation_part_enfance}}</th>
            <td>{{mb_value object=$dossier field=situation_part_enfance}}</td>
            <th>{{mb_label object=$dossier field=situation_part_familiale}}</th>
            <td>{{mb_value object=$dossier field=situation_part_familiale}}</td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$dossier field=spe_perte_parent}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spe_perte_parent}}</span></td>
            <th><span class="compact">{{mb_label object=$dossier field=spf_violences_conjugales}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spf_violences_conjugales}}</span></td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$dossier field=spe_maltraitance}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spe_maltraitance}}</span></td>
            <th><span class="compact">{{mb_label object=$dossier field=spf_mere_isolee}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spf_mere_isolee}}</span></td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$dossier field=spe_mere_placee_enfance}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spe_mere_placee_enfance}}</span></td>
            <th><span class="compact">{{mb_label object=$dossier field=spf_absence_entourage_fam}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spf_absence_entourage_fam}}</span></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=situation_part_adolescence}}</th>
            <td>{{mb_value object=$dossier field=situation_part_adolescence}}</td>
            <th>{{mb_label object=$dossier field=stress_agression}}</th>
            <td>{{mb_value object=$dossier field=stress_agression}}</td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$dossier field=spa_anorexie_boulimie}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spa_anorexie_boulimie}}</span></td>
            <th><span class="compact">{{mb_label object=$dossier field=sa_agression_physique}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=sa_agression_physique}}</span></td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$dossier field=spa_depression}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=spa_depression}}</span></td>
            <th><span class="compact">{{mb_label object=$dossier field=sa_agression_sexuelle}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=sa_agression_sexuelle}}</span></td>
          </tr>
          <tr>
            <th></th>
            <td></td>
            <th><span class="compact">{{mb_label object=$dossier field=sa_harcelement_travail}}</span></th>
            <td><span class="compact">{{mb_value object=$dossier field=sa_harcelement_travail}}</span></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=rques_psychologie}}</th>
            <td colspan="3" class="text">{{mb_value object=$dossier field=rques_psychologie}}</td>
          </tr>
          <tr>
            <th class="title me-text-align-center" colspan="4">Conclusion</th>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$dossier field=situation_accompagnement}}</th>
            <td colspan="2">{{mb_value object=$dossier field=situation_accompagnement}}</td>
          </tr>
          <tr>
            <th colspan="2">{{mb_label object=$dossier field=rques_accompagnement}}</th>
            <td class="text" colspan="2">{{mb_value object=$dossier field=rques_accompagnement}}</td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>
