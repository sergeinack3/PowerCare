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
        <legend>Sérodiagnostics en fin de grossesse</legend>
        <table class="form me-no-align me-no-box-shadow me-small-form">
          <tr>
            <th></th>
            <td></td>
            <td></td>
            <td>
              <span class="compact">
                Si séroconversion,
                <br />
                AG au diagnostic
              </span>
            </td>
          </tr>
          <tr>
            <th class="quarterPane">{{mb_label object=$dossier field=rai_fin_grossesse}}</th>
            <td class="quarterPane">
              {{mb_field object=$dossier field=rai_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.rai_fin_grossesse."}}
            </td>
            <td class="narrow"></td>
            <td></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=rubeole_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=rubeole_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.rubeole_fin_grossesse."}}
            </td>
            <td>
              {{mb_field object=$dossier field=seroconv_rubeole typeEnum=checkbox}}
              {{mb_label object=$dossier field=seroconv_rubeole}}
            </td>
            <td>
              {{mb_label object=$dossier field=ag_seroconv_rubeole style="display:none"}}
              {{mb_field object=$dossier field=ag_seroconv_rubeole}} SA
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=toxoplasmose_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=toxoplasmose_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.toxoplasmose_fin_grossesse."}}
            </td>
            <td>
              {{mb_field object=$dossier field=seroconv_toxoplasmose typeEnum=checkbox}}
              {{mb_label object=$dossier field=seroconv_toxoplasmose}}
            </td>
            <td>
              {{mb_label object=$dossier field=ag_seroconv_toxoplasmose style="display:none"}}
              {{mb_field object=$dossier field=ag_seroconv_toxoplasmose}} SA
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=syphilis_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=syphilis_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.syphilis_fin_grossesse."}}
            </td>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=vih_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=vih_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.vih_fin_grossesse."}}
            </td>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=hepatite_b_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=hepatite_b_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.hepatite_b_fin_grossesse."}}
            </td>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>
              <span class="compact">{{mb_label object=$dossier field=hepatite_b_aghbspos_fin_grossesse}}</span>
            </th>
            <td>
              {{mb_field object=$dossier field=hepatite_b_aghbspos_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.hepatite_b_aghbspos_fin_grossesse."}}
            </td>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=hepatite_c_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=hepatite_c_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.hepatite_c_fin_grossesse."}}
            </td>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=cmvg_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=cmvg_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.cmvg_fin_grossesse."}}
            </td>
            <td rowspan="2">
              {{mb_field object=$dossier field=seroconv_cmv typeEnum=checkbox}}
              {{mb_label object=$dossier field=seroconv_cmv}}
            </td>
            <td rowspan="2">
              {{mb_label object=$dossier field=ag_seroconv_cmv style="display:none"}}
              {{mb_field object=$dossier field=ag_seroconv_cmv}} SA
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=cmvm_fin_grossesse}}</th>
            <td>
              {{mb_field object=$dossier field=cmvm_fin_grossesse
              style="width: 16em;" emptyLabel="CDossierPerinat.cmvm_fin_grossesse."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossier field=autre_serodiag_fin_grossesse}}</th>
            <td colspan="3">
              {{if !$print}}
                {{mb_field object=$dossier field=autre_serodiag_fin_grossesse form=SyntheseGrossesse-`$dossier->_guid`}}
              {{else}}
                {{mb_value object=$dossier field=autre_serodiag_fin_grossesse}}
              {{/if}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Dépistages sériques précédents</legend>
        <table class="form me-no-align me-no-box-shadow">
          <tr>
            <td class="button">
              <button type="button" class="search" onclick="DossierMater.openPage({{$grossesse->_id}}, 'depistages');">
                Dépistages
              </button>
            </td>
          </tr>
          {{foreach from=$grossesse->_back.depistages item=depistage}}
            <tr>
              <td>
                {{tr}}CDepistageGrossesse{{/tr}} -
                {{mb_value object=$depistage field=date}} -
                {{$depistage->_sa}} SA
              </td>
            </tr>
          {{/foreach}}
        </table>
      </fieldset>
    </td>
  </tr>
</table>
