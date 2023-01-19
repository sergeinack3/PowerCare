{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="7">
      {{$sejours|@count}}
      {{tr var1=$date_min|date_format:$conf.datetime var2=$date_max|date_format:$conf.datetime}}mod-hospi-regulation-title{{/tr}}
    </th>
  </tr>
  <tr>
    <th style="width: 10%">{{tr}}CLit{{/tr}}</th>
    <th class="narrow"></th>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th class="narrow">
      <input type="text" id="filter-patient" size="3" onkeyup="Admissions.filter(this, this.up('table'));" />
    </th>
    <th style="width: 10%;">{{tr}}CSejour-entree{{/tr}}</th>
    <th style="width: 10%;">{{tr}}CSejour-libelle{{/tr}}</th>
    <th class="narrow">{{tr}}common-Practitioner-court{{/tr}}</th>
  </tr>
  {{foreach from=$sejours item=_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    {{assign var=curr_aff value=$_sejour->_ref_curr_affectation}}
    <tr>
      <td>
        {{if $curr_aff->lit_id}}
          {{$curr_aff->_ref_lit}}
        {{/if}}
      </td>
      <td class="narrow">
        {{mb_include module=patients template=inc_vw_photo_identite size=32}}
      </td>
      <td colspan="2">
        {{mb_include module=ssr template=inc_view_patient onclick="Regulation.showDossierSoins('`$_sejour->_id`','$dnow', '');"}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{mb_value object=$_sejour field=entree format=$conf.date}}
        </span>

        <div style="position: relative">
          <div class="ecap-sejour-bar"
               title="{{tr var1=$_sejour->_entree_relative var2=$_sejour->_sortie_relative}}CSejour-progress_bar-arrivee_depart{{/tr}}
                      ({{mb_value object=$_sejour field=sortie}})">
            {{assign var=progress_bar_width value=0}}
            {{if $_sejour->_duree}}
              {{math assign=progress_bar_width equation='100*(-entree / (duree))' entree=$_sejour->_entree_relative duree=$_sejour->_duree format='%.2f'}}
            {{/if}}

            <div style="width: {{if $_sejour->_duree && $progress_bar_width <= 100}}{{$progress_bar_width}}{{else}}100{{/if}}%;"></div>
          </div>
        </div>
      </td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{mb_value object=$_sejour field=_motif_complet}}
        </span>
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>