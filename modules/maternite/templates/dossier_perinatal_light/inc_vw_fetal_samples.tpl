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
        {{tr}}CDossierPerinat-action-Fetal samples{{/tr}}
      </span>
      <span style="float: right">
          <a href="#toxics"
             onclick="DossierMater.selectedMenu($('menu_toxics').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#ultrasounds"
             onclick="DossierMater.selectedMenu($('menu_ultrasounds').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=indication_prelevements_foetaux class="card_input"}}
    {{mb_field object=$dossier field=indication_prelevements_foetaux emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=biopsie_trophoblaste class="card_input"}}
    {{mb_field object=$dossier field=biopsie_trophoblaste onchange="DossierMater.ShowElements(this, 'biopsie_trophoblaste'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="biopsie_trophoblaste" style="{{if !$dossier->biopsie_trophoblaste}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_biopsie_trophoblaste class="card_input"}}
    {{mb_field object=$dossier field=resultat_biopsie_trophoblaste onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=rques_biopsie_trophoblaste class="card_input"}}
    {{mb_field object=$dossier field=rques_biopsie_trophoblaste onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=amniocentese class="card_input"}}
    {{mb_field object=$dossier field=amniocentese onchange="DossierMater.ShowElements(this, 'amniocentese'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="amniocentese" style="{{if !$dossier->amniocentese}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_amniocentese class="card_input"}}
    {{mb_field object=$dossier field=resultat_amniocentese onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=rques_amniocentese class="card_input"}}
    {{mb_field object=$dossier field=rques_amniocentese onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=cordocentese class="card_input"}}
    {{mb_field object=$dossier field=cordocentese onchange="DossierMater.ShowElements(this, 'cordocentese'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="cordocentese" style="{{if !$dossier->cordocentese}}display: none;{{/if}}">
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=resultat_cordocentese class="card_input"}}
    {{mb_field object=$dossier field=resultat_cordocentese onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=rques_cordocentese class="card_input"}}
    {{mb_field object=$dossier field=rques_cordocentese onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
</table>
