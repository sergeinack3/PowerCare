 {{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css          value=""}}

<script>
  listForms = [
    getForm("edit_grossesse_light"),
    getForm("edit_folder_light"),
    getForm("edit_last_screenings"),
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllFormsLight = function () {
    includeForms();
    DossierMater.listForms.each(function (form) {
      form.onsubmit();
    });
  };

  Main.add(function () {
    includeForms();
    DossierMater.prepareAllForms();
  });
</script>

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6">
      <span class="title_h6_spacing">{{tr}}CDossierPerinat{{/tr}}</span>
      <hr class="hr_menu" />
    </th>
  </tr>
  <tr>
    <td class="title_chapters">
      <div id="menu_current_pregnancy" class="title_menu_container title_menu_selected">
        <a href="#current_pregnancy" onclick="DossierMater.selectedMenu(this);">{{tr}}CGrossesse-Current pregnancy{{/tr}}</a>
      </div>
      <div id="menu_antecedents_traitements" class="title_menu_container">
        <a href="#antecedents_traitements" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Antecedents and risk factors{{/tr}}</a>
      </div>
      <div id="menu_immuno_hemotology" class="title_menu_container">
        <a href="#immuno_hemotology" onclick="DossierMater.selectedMenu(this);">{{tr}}CDepistageGrossesse-Immuno-hematology{{/tr}}</a>
      </div>
      <div id="menu_screenings" class="title_menu_container">
        <a href="#screenings" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-debut_grossesse-depistages{{/tr}}</a>
      </div>
      <div id="menu_toxics" class="title_menu_container">
        <a href="#toxics" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Toxic{{/tr}}</a>
      </div>
      <div id="menu_fetal_samples" class="title_menu_container">
        <a href="#fetal_samples" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Fetal samples{{/tr}}</a>
      </div>
      <div id="menu_ultrasounds" class="title_menu_container">
        <a href="#ultrasounds" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Ultrasounds{{/tr}}</a>
      </div>
      <div id="menu_pelvimetry" class="title_menu_container">
        <a href="#pelvimetry" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Pelvimetry{{/tr}}</a>
      </div>
      <div id="menu_birth_plan" class="title_menu_container">
        <a href="#birth_plan" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-Birth plan and behavior to follow{{/tr}}</a>
      </div>
      <div id="menu_general_informations" class="title_menu_container">
        <a href="#general_informations" onclick="DossierMater.selectedMenu(this);">{{tr}}CDossierPerinat-action-General informations{{/tr}}</a>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button">
      <hr class="hr_menu" />
      <button class="close me-tertiary" type="button" onclick="DossierMater.checkAction(1);">
        {{tr}}Cancel{{/tr}}
      </button>
      <button class="edit me-primary big-buttons" type="button" onclick="submitAllFormsLight();">
        {{tr}}Save{{/tr}}
      </button>
    </td>
  </tr>
</table>
