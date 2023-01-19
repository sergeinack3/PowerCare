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
        <legend>Surveillance de la grossesse</legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">
              {{mb_label object=$dossier field=nb_consult_total_prenatal}}
              <br />
              <em>(y compris hors maternité)</em>
            </th>
            <td>{{mb_field object=$dossier field=nb_consult_total_prenatal}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=nb_consult_total_equipe}}</th>
            <td>{{mb_field object=$dossier field=nb_consult_total_equipe}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=entretien_prem_trim}}</th>
            <td>{{mb_field object=$dossier field=entretien_prem_trim default=""}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=hospitalisation}}</th>
            <td>
              {{mb_field object=$dossier field=hospitalisation
              style="width: 16em;" emptyLabel="CDossierPerinat.hospitalisation."}}
            </td>
          </tr>
          <tr>
            <th><span class="compact">Si oui, {{mb_label object=$dossier field=nb_sejours}}</span></th>
            <td><span class="compact">{{mb_field object=$dossier field=nb_sejours}}</span></td>
          </tr>
          <tr>
            <th><span class="compact">Si oui, {{mb_label object=$dossier field=nb_total_jours_hospi}}</span></th>
            <td><span class="compact">{{mb_field object=$dossier field=nb_total_jours_hospi}}</span></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=sage_femme_domicile}}</th>
            <td>{{mb_field object=$dossier field=sage_femme_domicile default=""}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=transfert_in_utero}}</th>
            <td>{{mb_field object=$dossier field=transfert_in_utero default=""}}</td>
          </tr>
          <tr>
          <tr>
            <th>{{mb_label object=$dossier field=consult_preanesth}}</th>
            <td>{{mb_field object=$dossier field=consult_preanesth default=""}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=consult_centre_diag_prenat}}</th>
            <td>
              {{mb_field object=$dossier field=consult_centre_diag_prenat
              style="width: 16em;" emptyLabel="CDossierPerinat.consult_centre_diag_prenat."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=preparation_naissance}}</th>
            <td>
              {{mb_field object=$dossier field=preparation_naissance
              style="width: 16em;" emptyLabel="CDossierPerinat.preparation_naissance."}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Informations présentes dans le dossier</legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">Nombre de consultations dans la maternité</th>
            <td>{{$grossesse->_ref_consultations|@count}}</td>
          </tr>
          <tr>
            <th class="halfPane">Nombre de séjour dans la maternité</th>
            <td>{{$grossesse->_ref_sejours|@count}}</td>
          </tr>
          <tr>
            <th class="halfPane">Nombre de jours d'hospitalisation dans la maternité</th>
            <td>{{$grossesse->_nb_jours_hospi}}</td>
          </tr>
          <tr>
            <th class="halfPane">Consultation préanesthésique</th>
            <td>
              {{if $grossesse->_ref_last_consult_anesth->_id}}
                {{$grossesse->_ref_last_consult_anesth}}
              {{else}}
                -
              {{/if}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>