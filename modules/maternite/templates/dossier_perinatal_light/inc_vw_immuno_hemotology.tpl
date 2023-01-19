{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}

{{assign var=class_immuno_hematology value=""}}

<script>
  Main.add(function () {
      {{if $last_depistage->rhesus}}
        $('rhesus_{{$last_depistage->rhesus}}').click();
      {{/if}}
  });
</script>

<table class="main" style="{{$css}}">
  <tr>
    <th class="title_h6" colspan="6">
      <span class="title_h6_spacing">
        {{tr}}CDepistageGrossesse-Immuno-hematology{{/tr}}
      </span>
      <span style="float: right">
          <a href="#antecedents_traitements"
             onclick="DossierMater.selectedMenu($('menu_antecedents_traitements').down('a'));">
            <i class="fas fa-chevron-up actif_arrow"></i>
          </a>
          <a href="#screenings"
             onclick="DossierMater.selectedMenu($('menu_screenings').down('a'));">
            <i class="fas fa-chevron-down actif_arrow"></i>
          </a>
        </span>
    </th>
  </tr>
  <tr>
      {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=date class="me-large-datetime card_input"}}
      {{mb_field object=$last_depistage field=date register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
      {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=groupe_sanguin class="card_input"}}
    {{mb_field object=$last_depistage field=groupe_sanguin
      onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))" emptyLabel="common-Not specified"}}
    {{/me_form_field}}

    <td class="card_input_rhesus">
        <div class="me-padding-bottom-5">{{tr}}CDepistageGrossesse-rhesus{{/tr}}</div>
        <label class="label_rhesus_pos">
          <input type="radio" id="rhesus_pos"
                 name="rhesus" value="pos"
                 onchange="DossierMater.changeColorRhesus(this); DossierMater.ShowElements(this, null, '.rhesus_neg'); DossierMater.bindingDatas(this, getForm('edit_last_screenings'))" />
          <span>{{tr}}CDepistageGrossesse.rhesus.pos-court{{/tr}}</span>
        </label>
        <label class="label_rhesus_neg">
          <input type="radio" id="rhesus_neg"
                 name="rhesus" value="neg"
                 onchange="DossierMater.changeColorRhesus(this); DossierMater.ShowElements(this, null, '.rhesus_neg'); DossierMater.bindingDatas(this, getForm('edit_last_screenings'))" />
          <span>{{tr}}CDepistageGrossesse.rhesus.neg-court{{/tr}}</span>
        </label>
    </td>
  </tr>
  {{if !$last_depistage->genotypage || !in_array($last_depistage->genotypage, array("fait", "controle"))}}
      {{assign var=class_immuno_hematology value="immuno-hematology-none"}}
  {{/if}}

  <tr class="rhesus_neg" style="display: none;">
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=genotypage class="card_input"}}
    {{mb_field object=$last_depistage field=genotypage emptyLabel="Choose" onchange="DossierMater.ShowElements(this, 'date_genotypage|rques_genotypage', null);DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
    {{/me_form_field}}

    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=date_genotypage id="date_genotypage" class="me-large-datetime card_input $class_immuno_hematology"}}
    {{mb_field object=$last_depistage field=date_genotypage register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
    {{/me_form_field}}
  </tr>

  <tr class="rhesus_neg" style="display: none;">
      {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rhesus_bb class="card_input"}}
      {{mb_field object=$last_depistage field=rhesus_bb emptyLabel="CDepistageGrossesse.rhesus." onchange="DossierMater.ShowElements(this);DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
      {{/me_form_field}}
  </tr>

    {{assign var=class_immuno_hematology value=""}}
    {{if !$last_depistage->genotypage || ($last_depistage->genotypage !== "nonfait")}}
        {{assign var=class_immuno_hematology value="immuno-hematology-none"}}
    {{/if}}

  <tr class="rhesus_neg" style="{{if $class_immuno_hematology}}display: none;{{/if}}">
      {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rques_genotypage id="rques_genotypage" class="me-large-datetime card_input $class_immuno_hematology"}}
      {{mb_field object=$last_depistage field=rques_genotypage register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
      {{/me_form_field}}
  </tr>
  <tr class="rhesus_bb_neg" style="display: none;" >
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rques_immuno class="card_input"}}
    {{mb_field object=$last_depistage field=rques_immuno onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
    {{/me_form_field}}
  </tr>

    {{assign var=class_immuno_hematology value=""}}
    {{if !$last_depistage->rhophylac || ($last_depistage->rhophylac !== "fait")}}
        {{assign var=class_immuno_hematology value="immuno-hematology-none"}}
    {{/if}}
  <tr class="rhesus_neg" style="display: none;">
      {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rhophylac class="card_input"}}
      {{mb_field object=$last_depistage field=rhophylac emptyLabel="Choose" onchange="DossierMater.ShowElements(this, 'date_rhophylac&quantite_rhophylac|rques_rhophylac', null);DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
      {{/me_form_field}}

      <td class="card_input me-form-group-container {{$class_immuno_hematology}}" colspan="2">
        <div class="me-form-group inanimate me-inline-block">
          {{mb_field object=$last_depistage field=date_rhophylac register=true id="date_rhophylac" form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
          <label for="edit_perinatal_folder_date_rhophylac" title="{{tr}}CDepistageGrossesse-date_rhophylac-desc{{/tr}}" id="labelFor_edit_perinatal_folder_date_rhophylac">
              {{tr}}CDepistageGrossesse-date_rhophylac-court{{/tr}}
          </label>
        </div>

        <div class=" me-form-group inanimate me-inline-block">
            {{mb_field object=$last_depistage field=quantite_rhophylac id="quantite_rhophylac" form="edit_perinatal_folder" class="me-margin-left-10" increment=1 min=0 onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
          <label for="edit_perinatal_folder_quantite_rhophylac" title="{{tr}}CDepistageGrossesse-quantite_rhophylac-desc{{/tr}}" id="labelFor_edit_perinatal_folder_quantite_rhophylac">
              {{tr}}CDepistageGrossesse-quantite_rhophylac-court{{/tr}}
          </label> µg
        </div>
      </td>
  </tr>

    {{assign var=class_immuno_hematology value=""}}
    {{if !$last_depistage->rhophylac || ($last_depistage->rhophylac !== "nonfait")}}
        {{assign var=class_immuno_hematology value="immuno-hematology-none"}}
    {{/if}}

  <tr class="rhesus_neg" style="display: none;">
      {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rques_rhophylac id="rques_rhophylac" class="me-large-datetime card_input $class_immuno_hematology"}}
      {{mb_field object=$last_depistage field=rques_rhophylac register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
      {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=datetime_1_determination class="card_input"}}
    {{mb_field object=$last_depistage field=datetime_1_determination register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=datetime_2_determination class="card_input"}}
    {{mb_field object=$last_depistage field=datetime_2_determination register=true form="edit_perinatal_folder" onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field animated=false nb_cells=2 mb_object=$last_depistage mb_field=rai class="card_input"}}
    {{mb_field object=$last_depistage field=rai
      onchange="DossierMater.bindingDatas(this, getForm('edit_last_screenings'))" emptyLabel="common-Not specified"}}
    {{/me_form_field}}
  </tr>
</table>
