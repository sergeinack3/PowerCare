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
        <legend>
          {{mb_label object=$dossier field=conso_toxique_pdt_grossesse}} :
          {{mb_field object=$dossier field=conso_toxique_pdt_grossesse default=""}}
        </legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=tabac_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=tabac_pdt_grossesse}} nombre de cg/jour</td>
          </tr>
          <tr>
            <th><span class="compact">Si tabac, {{mb_label object=$dossier field=sevrage_tabac_pdt_grossesse}}</span></th>
            <td>{{mb_field object=$dossier field=conso_toxique_pdt_grossesse default=""}}</td>
          </tr>
          <tr>
            <th><span class="compact">Si tabac, {{mb_label object=$dossier field=date_arret_tabac}}</span></th>
            <td>{{mb_field object=$dossier field=date_arret_tabac form=SyntheseGrossesse-`$dossier->_guid` register=true}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=alcool_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=alcool_pdt_grossesse}} nombre de verres/semaine</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=cannabis_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=cannabis_pdt_grossesse}} nombre de joints/semaine</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=autres_subst_pdt_grossesse}}</th>
            <td>{{mb_field object=$dossier field=autres_subst_pdt_grossesse default=""}}</td>
          </tr>
          <tr>
            <th>Si oui, <span class="compact">{{mb_label object=$dossier field=type_subst_pdt_grossesse}}</span></th>
            <td>
              {{if !$print}}
                {{mb_field object=$dossier field=type_subst_pdt_grossesse form=SyntheseGrossesse-`$dossier->_guid`}}
              {{else}}
                {{mb_value object=$dossier field=type_subst_pdt_grossesse}}
              {{/if}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Informations saisies dans les renseignements généraux</legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="title" colspan="2">Mère</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=tabac_avant_grossesse}}</th>
            <td>
              {{mb_value object=$dossier field=tabac_avant_grossesse}}
              {{if $dossier->qte_tabac_avant_grossesse}}
                ({{mb_value object=$dossier field=qte_tabac_avant_grossesse}} cg/jour)
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=tabac_debut_grossesse}}</th>
            <td>
              {{mb_value object=$dossier field=tabac_debut_grossesse}}
              {{if $dossier->qte_tabac_debut_grossesse}}
                ({{mb_value object=$dossier field=qte_tabac_debut_grossesse}} cg/jour)
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=alcool_debut_grossesse}}</th>
            <td>
              {{mb_value object=$dossier field=alcool_debut_grossesse}}
              {{if $dossier->alcool_debut_grossesse}}
                ({{mb_value object=$dossier field=qte_alcool_debut_grossesse}} verres/sem)
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=canabis_debut_grossesse}}</th>
            <td>
              {{mb_value object=$dossier field=canabis_debut_grossesse}}
              {{if $dossier->qte_canabis_debut_grossesse}}
                ({{mb_value object=$dossier field=qte_canabis_debut_grossesse}} joints/sem)
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=subst_avant_grossesse}}</th>
            <td class="text">
              {{mb_value object=$dossier field=subst_avant_grossesse}}
              {{if $dossier->mode_subst_avant_grossesse}}
                - {{mb_value object=$dossier field=mode_subst_avant_grossesse}}
              {{/if}}
              {{if $dossier->nom_subst_avant_grossesse}}
                - {{mb_value object=$dossier field=nom_subst_avant_grossesse}}
              {{/if}}
              {{if $dossier->subst_subst_avant_grossesse}}
                - substitué par {{mb_value object=$dossier field=subst_subst_avant_grossesse}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=subst_debut_grossesse}}</th>
            <td>{{mb_value object=$dossier field=subst_debut_grossesse}}</td>
          </tr>
          <tr>
            <th class="title" colspan="2">Père</th>
          </tr>
          {{if $grossesse->pere_id}}
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=tabac_pere}}</th>
              <td>
                {{mb_value object=$dossier field=tabac_pere}}
                {{if $dossier->coexp_pere}}
                  (CO expiré de {{mb_value object=$dossier field=coexp_pere}})
                {{/if}}
              </td>
            </tr>
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=alcool_pere}}</th>
              <td>{{mb_value object=$dossier field=alcool_pere}}</td>
            </tr>
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=toxico_pere}}</th>
              <td>{{mb_value object=$dossier field=toxico_pere}}</td>
            </tr>
          {{else}}
            <tr>
              <td colspan="2" class="empty">Père non renseigné</td>
            </tr>
          {{/if}}
          {{if $dossier->rques_toxico}}
            <tr>
              <th class="title" colspan="2">{{mb_label object=$dossier field=rques_toxico}}</th>
            </tr>
            <tr>
              <td colspan="2" class="text">{{mb_value object=$dossier field=rques_toxico}}</td>
            </tr>
          {{/if}}
        </table>
      </fieldset>
    </td>
  </tr>
</table>
