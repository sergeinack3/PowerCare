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
        {{tr}}CDossierPerinat-action-Antecedents and risk factors{{/tr}}
      </span>
      <span style="float: right">
          <a href="#current_pregnancy"
             onclick="DossierMater.selectedMenu($('menu_current_pregnancy').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#immuno_hemotology"
             onclick="DossierMater.selectedMenu($('menu_immuno_hemotology').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    <td class="card_button">
      <button type="button" class="edit me-secondary" onclick="DossierMater.editAtcd('{{$patient->_id}}', '');">
        {{tr}}CDossierPerinat-debut_grossesse-antecedents{{/tr}}
      </button>

      <button type="button" class="edit me-secondary" onclick="DossierMater.editTP('{{$patient->_id}}');">
        {{tr}}CDossierPerinat-traitements_sejour_mere{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    {{me_form_field animated=true nb_cells=2 mb_object=$dossier mb_field=facteur_risque class="card_input"}}
    {{mb_field object=$dossier field=facteur_risque onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CGrossesse-Pregnancy pathology{{/tr}}</span>
    </th>
  </tr>
  <tr>
    {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=pathologie_grossesse class="card_input"}}
    {{mb_field object=$dossier field=pathologie_grossesse onchange="DossierMater.ShowElements(this, 'dossier_pathologies'); DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_bool}}
  </tr>
  <tr id="dossier_pathologies" style="{{if !$dossier->pathologie_grossesse}}display: none;{{/if}}">
    <td class="card_input_pathology">
      <div id="pathologies_tags">
        {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_mother_pathologies_tags}}
      </div>
      <div class="pathology_autocomplete">
        <div><input type="text" name="_pathology_name" class="autocomplete" value="" style="width: 300px;" /></div>
        <div style="display: none; top:50px;" class="autocomplete" id="pathology_list"></div>
      </div>
    </td>
  </tr>
</table>
