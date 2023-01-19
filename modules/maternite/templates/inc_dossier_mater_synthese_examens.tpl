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
        <legend>Echographies</legend>
        <table class="form me-no-align me-no-box-shadow me-small-form">
          <tr>
            <th>
              {{mb_label object=$dossier field=nb_total_echographies}}
              <br />
              <em>(y compris hors maternité)</em>
            </th>
            <td colspan="3">{{mb_field object=$dossier field=nb_total_echographies}}</td>
          </tr>
          <tr>
            <th colspan="4" class="category">1er trimestre</th>
          </tr>
          <tr>
            <th class="quarterPane">
              {{mb_label object=$dossier field=echo_1er_trim}}
              {{mb_field object=$dossier field=echo_1er_trim typeEnum=checkbox}}
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_echo_1er_trim}}</span>
              {{mb_field object=$dossier field=resultat_echo_1er_trim
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_echo_1er_trim."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=resultat_autre_echo_1er_trim style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-resultat_autre_echo_1er_trim"}}
              {{mb_field object=$dossier field=resultat_autre_echo_1er_trim
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_echo_1er_trim style="display:none"}}
              {{mb_field object=$dossier field=ag_echo_1er_trim}} SA
            </td>
          </tr>
          <tr>
            <th colspan="4" class="category">2ème trimestre</th>
          </tr>
          <tr>
            <th>
              {{mb_label object=$dossier field=echo_2e_trim}}
              {{mb_field object=$dossier field=echo_2e_trim typeEnum=checkbox}}
            </th>
            <td>
              <span style="display:none">{{mb_label object=$dossier field=resultat_echo_2e_trim}}</span>
              {{mb_field object=$dossier field=resultat_echo_2e_trim
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_echo_2e_trim."}}
            </td>
            <td>
              {{mb_label object=$dossier field=resultat_autre_echo_2e_trim style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-resultat_autre_echo_2e_trim"}}
              {{mb_field object=$dossier field=resultat_autre_echo_2e_trim
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td>
              {{mb_label object=$dossier field=ag_echo_2e_trim style="display:none"}}
              {{mb_field object=$dossier field=ag_echo_2e_trim}} SA
            </td>
          </tr>
          <tr>
            <th>
              {{mb_label object=$dossier field=doppler_2e_trim}}
              {{mb_field object=$dossier field=doppler_2e_trim typeEnum=checkbox}}
            </th>
            <td>
              <span style="display:none">{{mb_label object=$dossier field=resultat_doppler_2e_trim}}</span>
              {{mb_field object=$dossier field=resultat_doppler_2e_trim
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_doppler_2e_trim."}}
            </td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th colspan="4" class="category">3ème trimestre</th>
          </tr>
          <tr>
            <th>
              {{mb_label object=$dossier field=echo_3e_trim}}
              {{mb_field object=$dossier field=echo_3e_trim typeEnum=checkbox}}
            </th>
            <td>
              <span style="display:none">{{mb_label object=$dossier field=resultat_echo_3e_trim}}</span>
              {{mb_field object=$dossier field=resultat_echo_3e_trim
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_echo_3e_trim."}}
            </td>
            <td>
              {{mb_label object=$dossier field=resultat_autre_echo_3e_trim style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-resultat_autre_echo_3e_trim"}}
              {{mb_field object=$dossier field=resultat_autre_echo_3e_trim
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td>
              {{mb_label object=$dossier field=ag_echo_3e_trim style="display:none"}}
              {{mb_field object=$dossier field=ag_echo_3e_trim}} SA
            </td>
          </tr>
          <tr>
            <th>
              {{mb_label object=$dossier field=doppler_3e_trim}}
              {{mb_field object=$dossier field=doppler_3e_trim typeEnum=checkbox}}
            </th>
            <td>
              <span style="display:none">{{mb_label object=$dossier field=resultat_doppler_3e_trim}}</span>
              {{mb_field object=$dossier field=resultat_doppler_3e_trim
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_doppler_3e_trim."}}
            </td>
            <td></td>
            <td></td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend class="me-small-input">
          {{mb_label object=$dossier field=prelevements_foetaux}} :
          {{mb_field object=$dossier field=prelevements_foetaux
          style="width: 16em;" emptyLabel="CDossierPerinat.prelevements_foetaux."}}
        </legend>
        <table class="form me-no-align me-small-form me-no-box-shadow">
          <tr>
            <th>{{mb_label object=$dossier field=indication_prelevements_foetaux}}</th>
            <td colspan="3">
              {{mb_field object=$dossier field=indication_prelevements_foetaux
              style="width: 16em;" emptyLabel="CDossierPerinat.indication_prelevements_foetaux."}}
            </td>
          </tr>
          <tr>
            <th>Si faits lesquels</th>
            <td colspan="3"></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=biopsie_trophoblaste}}
                {{mb_field object=$dossier field=biopsie_trophoblaste typeEnum=checkbox}}
              </span>
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_biopsie_trophoblaste}}</span>
              {{mb_field object=$dossier field=resultat_biopsie_trophoblaste
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_biopsie_trophoblaste."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=rques_biopsie_trophoblaste style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_biopsie_trophoblaste"}}
              {{mb_field object=$dossier field=rques_biopsie_trophoblaste
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_biopsie_trophoblaste style="display:none"}}
              {{mb_field object=$dossier field=ag_biopsie_trophoblaste}} SA
            </td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=amniocentese}}
                {{mb_field object=$dossier field=amniocentese typeEnum=checkbox}}
              </span>
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_amniocentese}}</span>
              {{mb_field object=$dossier field=resultat_amniocentese
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_amniocentese."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=rques_amniocentese style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_amniocentese"}}
              {{mb_field object=$dossier field=rques_amniocentese
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_amniocentese style="display:none"}}
              {{mb_field object=$dossier field=ag_amniocentese}} SA
            </td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=cordocentese}}
                {{mb_field object=$dossier field=cordocentese typeEnum=checkbox}}
              </span>
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_cordocentese}}</span>
              {{mb_field object=$dossier field=resultat_cordocentese
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_cordocentese."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=rques_cordocentese style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_cordocentese"}}
              {{mb_field object=$dossier field=rques_cordocentese
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_cordocentese style="display:none"}}
              {{mb_field object=$dossier field=ag_cordocentese}} SA
            </td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=autre_prelevements_foetaux}}
                {{mb_field object=$dossier field=autre_prelevements_foetaux typeEnum=checkbox}}
              </span>
            </th>
            <td colspan="2">
              {{mb_label object=$dossier field=rques_autre_prelevements_foetaux style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_autre_prelevements_foetaux"}}
              {{mb_field object=$dossier field=rques_autre_prelevements_foetaux
              style="width: 33em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_autre_prelevements_foetaux style="display:none"}}
              {{mb_field object=$dossier field=ag_autre_prelevements_foetaux}} SA
            </td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend class="me-small-input">
          {{mb_label object=$dossier field=prelevements_bacterio_mater}} :
          {{mb_field object=$dossier field=prelevements_bacterio_mater
          style="width: 16em;" emptyLabel="CDossierPerinat.prelevements_bacterio_mater."}}
        </legend>
        <table class="form me-no-box-shadow me-no-align me-small-form">
          <tr>
            <th>Si faits lesquels</th>
            <td colspan="3"></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=prelevement_vaginal}}
                {{mb_field object=$dossier field=prelevement_vaginal typeEnum=checkbox}}
              </span>
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_prelevement_vaginal}}</span>
              {{mb_field object=$dossier field=resultat_prelevement_vaginal
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_prelevement_vaginal."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=rques_prelevement_vaginal style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_prelevement_vaginal"}}
              {{mb_field object=$dossier field=rques_prelevement_vaginal
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_prelevement_vaginal style="display:none"}}
              {{mb_field object=$dossier field=ag_prelevement_vaginal}} SA
            </td>
          </tr>
          <tr>
            <th class="quarterPane">
              <span class="compact">
                {{mb_label object=$dossier field=prelevement_urinaire}}
                {{mb_field object=$dossier field=prelevement_urinaire typeEnum=checkbox}}
              </span>
            </th>
            <td class="quarterPane">
              <span style="display:none">{{mb_label object=$dossier field=resultat_prelevement_urinaire}}</span>
              {{mb_field object=$dossier field=resultat_prelevement_urinaire
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_prelevement_urinaire."}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=rques_prelevement_urinaire style="display:none"}}
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-rques_prelevement_urinaire"}}
              {{mb_field object=$dossier field=rques_prelevement_urinaire
              style="width: 16em;" placeholder=$placeholder}}
            </td>
            <td class="quarterPane">
              {{mb_label object=$dossier field=ag_prelevement_urinaire style="display:none"}}
              {{mb_field object=$dossier field=ag_prelevement_urinaire}} SA
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend class="me-small-input">
          {{mb_label object=$dossier field=marqueurs_seriques}} :
          {{mb_field object=$dossier field=marqueurs_seriques
          style="width: 16em;" emptyLabel="CDossierPerinat.marqueurs_seriques."}}
        </legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">
              <span class="compact">Si faits, {{mb_label object=$dossier field=resultats_marqueurs_seriques}}</span>
            </th>
            <td class="halfPane">
              {{mb_field object=$dossier field=resultat_prelevement_urinaire
              style="width: 16em;" emptyLabel="CDossierPerinat.resultats_marqueurs_seriques."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=rques_marqueurs_seriques}}</th>
            <td>{{mb_field object=$dossier field=rques_marqueurs_seriques}}</td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend class="me-small-input">
          {{mb_label object=$dossier field=depistage_diabete}} :
          {{mb_field object=$dossier field=depistage_diabete
          style="width: 16em;" emptyLabel="CDossierPerinat.depistage_diabete."}}
        </legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <th class="halfPane">{{mb_label object=$dossier field=resultat_depistage_diabete}}</th>
            <td class="halfPane">
              {{mb_field object=$dossier field=resultat_depistage_diabete
              style="width: 16em;" emptyLabel="CDossierPerinat.resultat_depistage_diabete."}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>