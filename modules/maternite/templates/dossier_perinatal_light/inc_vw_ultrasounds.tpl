{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6" colspan="6">
      <span class="title_h6_spacing">
        {{tr}}CDossierPerinat-action-Ultrasounds{{/tr}}
      </span>
      <span style="float: right">
          <a href="#fetal_samples"
             onclick="DossierMater.selectedMenu($('menu_fetal_samples').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#pelvimetry"
             onclick="DossierMater.selectedMenu($('menu_pelvimetry').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    <th class="subtitle_chapter subtitle_color_black">
      {{tr}}CDossierPerinat-First trimester{{/tr}}
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_echo_1er_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_echo_1er_trim emptyLabel="CDossierPerinat.resultat_echo_1er_trim." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_autre_echo_1er_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_autre_echo_1er_trim onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 label="CGrossesse-_semaine_grossesse-court" title_label="CDossierPerinat-ag_echo_1er_trim-desc" class="card_input"}}
    {{mb_field object=$dossier field=ag_echo_1er_trim form="edit_perinatal_folder" increment=1 min=0 onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="subtitle_chapter subtitle_color_black">
      {{tr}}CDossierPerinat-Second trimester{{/tr}}
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_echo_2e_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_echo_2e_trim emptyLabel="CDossierPerinat.resultat_echo_2e_trim." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_autre_echo_2e_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_autre_echo_2e_trim onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 label="CGrossesse-_semaine_grossesse-court" title_label="CDossierPerinat-ag_echo_2e_trim-desc" class="card_input"}}
    {{mb_field object=$dossier field=ag_echo_2e_trim form="edit_perinatal_folder" increment=1 min=0 onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="subtitle_chapter subtitle_color_black">
      {{tr}}CDossierPerinat-Third trimester{{/tr}}
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_echo_3e_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_echo_3e_trim emptyLabel="CDossierPerinat.resultat_echo_3e_trim." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_autre_echo_3e_trim class="card_input"}}
    {{mb_field object=$dossier field=resultat_autre_echo_3e_trim onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 label="CGrossesse-_semaine_grossesse-court" title_label="CDossierPerinat-ag_echo_3e_trim-desc" class="card_input"}}
    {{mb_field object=$dossier field=ag_echo_3e_trim form="edit_perinatal_folder" increment=1 min=0 onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="subtitle_chapter subtitle_color_black">
      {{tr}}CDossierPerinat-End pregnancy{{/tr}}
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=est_pond_fin_grossesse class="card_input"}}
    {{mb_field object=$dossier field=est_pond_fin_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} g
    {{/me_form_field}}

    {{me_form_field animated=true nb_cells=2 mb_object=$echographique mb_field=pos_placentaire class="card_input"}}
    {{mb_field object=$echographique field=pos_placentaire onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=sa_echo_fin_grossesse class="card_input"}}
    {{mb_field object=$dossier field=sa_echo_fin_grossesse form="edit_perinatal_folder" increment=1 min=0 onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=est_pond_2e_foetus_fin_grossesse class="card_input"}}
    {{mb_field object=$dossier field=est_pond_2e_foetus_fin_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
<!--  <tr>
    <td class="card_button">
      <button type="button" class="new me-tertiary"
              onclick="">
        {{tr}}CDossierPerinat-action-New echography{{/tr}}
      </button>
    </td>
  </tr>-->
</table>
