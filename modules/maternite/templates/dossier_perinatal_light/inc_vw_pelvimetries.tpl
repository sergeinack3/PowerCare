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
        {{tr}}CDossierPerinat-action-Pelvimetry{{/tr}}
      </span>
      <span style="float: right">
          <a href="#ultrasounds"
             onclick="DossierMater.selectedMenu($('menu_ultrasounds').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#birth_plan"
             onclick="DossierMater.selectedMenu($('menu_birth_plan').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=pelvimetrie class="card_input"}}
    {{mb_field object=$dossier field=pelvimetrie emptyLabel="CGrossesse.pelvimetrie." onchange="DossierMater.ShowElements(this, 'pelvimetrie'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr id="pelvimetrie" style="{{if $dossier->pelvimetrie != 'anorm'}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=desc_pelvimetrie class="card_input"}}
    {{mb_field object=$dossier field=desc_pelvimetrie onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=diametre_transverse_median class="card_input"}}
    {{mb_field object=$dossier field=diametre_transverse_median onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} cm
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=diametre_promonto_retro_pubien class="card_input"}}
    {{mb_field object=$dossier field=diametre_promonto_retro_pubien onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} cm
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=indice_magnin class="card_input"}}
    {{mb_field object=$dossier field=indice_magnin onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} cm
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=diametre_bisciatique class="card_input"}}
    {{mb_field object=$dossier field=diametre_bisciatique onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} cm
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=bip_fin_grossesse class="card_input"}}
    {{mb_field object=$dossier field=bip_fin_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}} mm
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=date_echo_fin_grossesse class="me-large-datetime card_input"}}
    {{mb_field object=$dossier field=date_echo_fin_grossesse register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=4 mb_object=$dossier mb_field=appreciation_clinique_etat_bassin class="card_input"}}
    {{mb_field object=$dossier field=appreciation_clinique_etat_bassin emptyLabel="CGrossesse.appreciation_clinique_etat_bassin." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
</table>
