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
            <div>
                <span class="title_h6_spacing">{{tr}}CGrossesse-Current pregnancy{{/tr}}</span>
                <span style="float: right">
          <i class="fas fa-chevron-up empty_arrow"></i>
          <a href="#antecedents_traitements"
             onclick="DossierMater.selectedMenu($('menu_current_pregnancy').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
            </div>
        </th>
    </tr>
    <tr>
        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=date_dernieres_regles class="me-large-datetime card_input"}}
        {{mb_field object=$grossesse field=date_dernieres_regles register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=date_debut_grossesse class="me-large-datetime card_input"}}
        {{mb_field object=$grossesse field=date_debut_grossesse register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=terme_prevu class="me-large-datetime card_input"}}
        {{mb_field object=$grossesse field=terme_prevu register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_bool mb_object=$grossesse mb_field=multiple nb_cells=2 class="card_input"}}
        {{mb_field object=$grossesse field=multiple typeEnum=checkbox onchange="DossierMater.ShowElements(this, 'grossesse_multiple'); DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_bool}}
    </tr>
    <tr id="grossesse_multiple" style="{{if !$grossesse->multiple}}display: none;{{/if}}">
        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=nb_foetus class="card_input"}}
        {{mb_field object=$grossesse field=nb_foetus form="edit_perinatal_folder" increment=1 min=1 onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=type_embryons_debut_grossesse class="card_input"}}
        {{mb_field object=$grossesse field=type_embryons_debut_grossesse emptyLabel="CGrossesse.type_embryons_debut_grossesse." onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}
    </tr>
    <tr>
        <td>
            <div class="me-margin-19"></div>
        </td>
    </tr>
    <tr>
        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=nb_grossesses_ant class="card_input"}}
        {{mb_field object=$grossesse field=nb_grossesses_ant onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 mb_object=$grossesse mb_field=nb_accouchements_ant class="card_input"}}
        {{mb_field object=$grossesse field=nb_accouchements_ant onchange="DossierMater.bindingDatas(this, getForm('edit_grossesse_light'));"}}
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 mb_object=$dossier mb_field=ant_obst_nb_gr_cesar class="card_input"}}
        {{mb_field object=$dossier field=ant_obst_nb_gr_cesar onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
        {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field animated=false nb_cells=2 label="CConsultationPostNatEnfant-poids-court" class="card_input"}}
        {{mb_field object=$constantes_maman field=poids readonly=true}} kg
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 label="CDossierPerinat-First weight" class="card_input"}}
        {{mb_field object=$constantes_maman field=poids_avant_grossesse readonly=true}} kg
        {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=souhait_grossesse class="card_input"}}
        {{mb_field object=$dossier field=souhait_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
        {{/me_form_bool}}
    </tr>
    <tr>
        {{me_form_bool animated=false nb_cells=2 mb_object=$dossier mb_field=grossesse_apres_traitement class="card_input"}}
        {{mb_field object=$dossier field=grossesse_apres_traitement onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
        {{/me_form_bool}}
    </tr>
    <tr>
        {{me_form_field animated=false nb_cells=2 label="CDossierPerinat-Treatment" class="card_input"}}
        {{mb_field object=$dossier field=type_traitement_grossesse emptyLabel="CDossierPerinat-Choose a treatment" onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
        {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field animated=true nb_cells=6 mb_object=$dossier mb_field=rques_traitement_grossesse class="card_input"}}
        {{mb_field object=$dossier field=rques_traitement_grossesse onchange="DossierMater.bindingDatas(this, getForm('edit_folder_light'));"}}
        {{/me_form_field}}
    </tr>
</table>
