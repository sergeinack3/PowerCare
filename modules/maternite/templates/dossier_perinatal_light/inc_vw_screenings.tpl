{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6" colspan="8">
      <span class="title_h6_spacing">
        {{tr}}CDossierPerinat-debut_grossesse-depistages{{/tr}}
      </span>
      <span style="float: right">
          <a href="#immuno_hemotology"
             onclick="DossierMater.selectedMenu($('menu_immuno_hemotology').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#toxics"
             onclick="DossierMater.selectedMenu($('menu_toxics').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=_date_depistage class="me-large-datetime card_input"}}
    {{mb_field object=$last_depistage field=_date_depistage canNull=true register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rubeole class="card_input"}}
    {{mb_field object=$last_depistage field=rubeole emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=toxoplasmose class="card_input"}}
    {{mb_field object=$last_depistage field=toxoplasmose emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=syphilis class="card_input"}}
    {{mb_field object=$last_depistage field=syphilis emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=vih class="card_input"}}
    {{mb_field object=$last_depistage field=vih emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=hepatite_b class="card_input"}}
    {{mb_field object=$last_depistage field=hepatite_b emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=hepatite_c class="card_input"}}
    {{mb_field object=$last_depistage field=hepatite_c emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings')); DossierMater.verifyDateScreenings(this.form)"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <td colspan="8">
      <table class="main fieldset_other_screenings">
        <tr>
          <th class="subtitle_chapter subtitle_color_black">
            <input type="checkbox" name="all_other_depistages" onclick="$$('.other_screenings').invoke('toggle');" /> <span>{{tr}}CDossierPerinat-Others screenings{{/tr}}</span>
          </th>
        </tr>
        <tr>
          {{*{{me_form_field nb_cells=2 label="CGrossesse-back-depistages" class="card_input"}}
            <input type="hidden" name="depistage_grossesse_id" value=""/>

            <input type="text" name="depistage_grossesse_view" class="autocomplete" value="" style="text-align: left;"
                   onmousedown=""
                   placeholder="&mdash; {{tr}}CDossierPerinat-action-Search a screening{{/tr}}"/>
          {{/me_form_field}}*}}
          <div id="other_screenings_list"></div>
          <hr />
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=aci class="card_input"}}
          {{mb_field object=$last_depistage field=aci emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}

          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=test_kleihauer class="card_input"}}
          {{mb_field object=$last_depistage field=test_kleihauer emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=varicelle class="card_input"}}
          {{mb_field object=$last_depistage field=varicelle emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}

          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=parvovirus class="card_input"}}
          {{mb_field object=$last_depistage field=parvovirus emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=cmvg class="card_input"}}
          {{mb_field object=$last_depistage field=cmvg emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}

          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=cmvm class="card_input"}}
          {{mb_field object=$last_depistage field=cmvm emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=htlv class="card_input"}}
          {{mb_field object=$last_depistage field=htlv emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}

          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=vrdl class="card_input"}}
          {{mb_field object=$last_depistage field=vrdl emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=TPHA class="card_input"}}
          {{mb_field object=$last_depistage field=TPHA emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}

          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=strepto_b class="card_input"}}
          {{mb_field object=$last_depistage field=strepto_b emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
        <tr class="other_screenings" style="display: none;">
          {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=parasitobacteriologique class="card_input"}}
          {{mb_field object=$last_depistage field=parasitobacteriologique emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'));"}}
          {{/me_form_field}}
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <th class="subtitle_chapter subtitle_color_black_dark">
      {{tr}}CDossierPerinat-prelevements{{/tr}}
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=date_validation_synthese class="me-large-datetime card_input"}}
    {{mb_field object=$dossier field=date_validation_synthese register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <td class="halfPane" colspan="4">
      <table class="form fieldset_prelevement">
        <tr>
          <td class="title_section">
            <span class="title_section_spacing">
              {{tr}}CDepistageGrossesse-prelevement_vaginal{{/tr}}
            </span>
          </td>
        </tr>
        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_prelevement_vaginal class="card_input"}}
          {{mb_field object=$dossier field=resultat_prelevement_vaginal emptyLabel="common-Not specified" onchange="DossierMater.ShowElements(this, 'prelevement_vaginal_autre'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
          {{/me_form_field}}
        </tr>
        <tr id="prelevement_vaginal_autre"
            style="{{if $dossier->resultat_prelevement_vaginal != "autre"}}display: none;{{/if}}">
          {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=rques_prelevement_vaginal class="card_input"}}
          {{mb_field object=$dossier field=rques_prelevement_vaginal onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
          {{/me_form_field}}
        </tr>
<!--        <tr>
          <td class="card_button">
            <button type="button" class="new me-tertiary"
                    onclick="">
              {{tr}}CDossierPerinat-action-New prelevement{{/tr}}
            </button>
          </td>
        </tr>-->
      </table>
    </td>
    <td class="halfPane" colspan="4">
      <table class="form">
        <tr>
          <td class="title_section">
            <span class="title_section_spacing">
              {{tr}}CDossierPerinat-prelevement_urinaire{{/tr}}
            </span>
          </td>
        </tr>
        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_prelevement_urinaire class="card_input"}}
          {{mb_field object=$dossier field=resultat_prelevement_urinaire emptyLabel="common-Not specified" onchange="DossierMater.ShowElements(this, 'prelevement_urinaire_autre'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
          {{/me_form_field}}
        </tr>
        <tr id="prelevement_urinaire_autre"
            style="{{if $dossier->resultat_prelevement_urinaire != "autre"}}display: none;{{/if}}">
          {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=rques_prelevement_urinaire class="card_input"}}
          {{mb_field object=$dossier field=rques_prelevement_urinaire onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
          {{/me_form_field}}
        </tr>
<!--        <tr>
          <td class="card_button">
            <button type="button" class="new me-tertiary"
                    onclick="">
              {{tr}}CDossierPerinat-action-New prelevement{{/tr}}
            </button>
          </td>
        </tr>-->
      </table>
    </td>
  </tr>
</table>
