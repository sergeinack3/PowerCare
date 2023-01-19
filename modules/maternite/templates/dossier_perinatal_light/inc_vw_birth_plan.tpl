{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}
{{assign var=allaitement value=$patient->_ref_last_allaitement}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6" colspan="6">
      <span class="title_h6_spacing">
        {{tr}}CDossierPerinat-Birth plan and what to do with childbirth{{/tr}}
      </span>
      <span style="float: right">
          <a href="#pelvimetry"
             onclick="DossierMater.selectedMenu($('menu_pelvimetry').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#general_informations"
             onclick="DossierMater.selectedMenu($('menu_general_informations').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CDossierPerinat-Initial birth plan{{/tr}}</th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=projet_analgesie_peridurale class="card_input"}}
    {{mb_field object=$dossier field=projet_analgesie_peridurale emptyLabel="CDossierPerinat.projet_analgesie_peridurale." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=projet_allaitement_maternel class="card_input"}}
    {{mb_field object=$dossier field=projet_allaitement_maternel emptyLabel="CDossierPerinat.projet_allaitement_maternel." onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">{{tr}}CAllaitement{{/tr}}</span>
    </th>
  </tr>
  <tr>
    <td class="card_input">
        {{mb_include module=maternite template=inc_vw_last_breastfeeding}}
    </td>
  </tr>
  <tr>
    <th class="title_section">
      <span class="title_section_spacing">
        {{tr}}CDossierPerinat-suivi_grossesse-conduite_accouchement{{/tr}}
      </span>
    </th>
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=motif_conduite_a_tenir_acc class="card_input"}}
    {{mb_field object=$dossier field=motif_conduite_a_tenir_acc onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
    {{/me_form_field}}
  </tr>
</table>
