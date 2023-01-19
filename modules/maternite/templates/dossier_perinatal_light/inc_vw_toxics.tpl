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
        {{tr}}CDossierPerinat-action-Toxic{{/tr}}
      </span>
      <span style="float: right">
          <a href="#screenings"
             onclick="DossierMater.selectedMenu($('menu_screenings').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#fetal_samples"
             onclick="DossierMater.selectedMenu($('menu_fetal_samples').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=tabac_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=tabac_avant_grossesse typeEnum=radio onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}

    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=tabac_debut_grossesse class="card_input"}}
    {{mb_field object=$dossier field=tabac_debut_grossesse typeEnum=radio onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=alcool_debut_grossesse class="card_input"}}
    {{mb_field object=$dossier field=alcool_debut_grossesse typeEnum=radio onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}

    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=canabis_debut_grossesse class="card_input"}}
    {{mb_field object=$dossier field=canabis_debut_grossesse typeEnum=radio onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=subst_avant_grossesse typeEnum=radio onchange="DossierMater.ShowElements(this, 'subst_avant_grossesse'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="subst_avant_grossesse" style="{{if !$dossier->subst_avant_grossesse}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=mode_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=mode_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=nom_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=nom_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=subst_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=subst_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=subst_debut_grossesse class="card_input"}}
    {{mb_field object=$dossier field=subst_debut_grossesse typeEnum=radio onchange="DossierMater.ShowElements(this, 'subst_debut_grossesse'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="subst_debut_grossesse" style="{{if !$dossier->subst_debut_grossesse}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=mode_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=mode_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=nom_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=nom_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=subst_subst_avant_grossesse class="card_input"}}
    {{mb_field object=$dossier field=subst_subst_avant_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
</table>
