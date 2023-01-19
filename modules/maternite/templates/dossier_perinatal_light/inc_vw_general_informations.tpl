{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}
{{assign var=dossier_medical_pere value=$pere->_ref_dossier_medical}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6" colspan="6">
      <span class="title_h6_spacing">
        {{tr}}CDossierPerinat-action-General informations{{/tr}}
      </span>
      <span style="float: right">
          <a href="#birth_plan"
             onclick="DossierMater.selectedMenu($('menu_birth_plan').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <i class="fas fa-chevron-down empty_arrow"></i>
        </span>
    </th>
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CGrossesse-Mother{{/tr}}</span></th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=activite_pro class="card_input"}}
    {{mb_field object=$dossier field=activite_pro emptyLabel="common-Not specified" onchange="DossierMater.ShowElements(this, 'activite_pro'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr id="activite_pro" style="{{if !$dossier->activite_pro || ($dossier->activite_pro != "a")}}display: none;{{/if}}">
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=fatigue_travail class="card_input"}}
    {{mb_field object=$dossier field=fatigue_travail onchange="DossierMater.ShowElements(this, 'pelvimetrie'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CDossierPerinat-Psycho-social context{{/tr}}</span></th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=situation_accompagnement class="card_input"}}
    {{mb_field object=$dossier field=situation_accompagnement emptyLabel="common-Not specified" onchange="DossierMater.ShowElements(this, null, '.situation_accompagnement'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{assign var=style_css value=""}}
    {{if !$dossier->situation_accompagnement || ($dossier->situation_accompagnement == 'n')}}
      {{assign var=style_css value="display: none;"}}
    {{/if}}
    
    {{me_form_field animated=true nb_cells=2 mb_object=$dossier mb_field=rques_accompagnement style_css="$style_css" class="card_input situation_accompagnement"}}
    {{mb_field object=$dossier field=rques_accompagnement onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CGrossesse-pere_id{{/tr}}</span></th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier_medical_pere mb_field=groupe_sanguin class="card_input"}}
    {{mb_field object=$dossier_medical_pere field=groupe_sanguin emptyLabel="common-Not specified" onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=true nb_cells=2 mb_object=$dossier mb_field=pere_ant_autre class="card_input"}}
    {{mb_field object=$dossier field=pere_ant_autre onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
</table>
