{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=vw_traitement_texte_libre value=1}}
{{mb_default var=addform                   value=""}}
{{mb_default var=callback                  value=""}}
{{mb_default var=gestion_tp                value=""}}
{{mb_default var=sejour_id                 value=""}}
{{mb_default var=reload                    value=""}}
{{mb_default var=type_see                  value=""}}
{{mb_default var=dossier_anesth_id         value=""}}

{{mb_script module=prescription script=prescription        ajax=1}}
{{mb_script module=medicament   script=medicament_selector ajax=1}}

<script>
  Main.add(function() {
    if (window.DossierMedical) {
      if (!DossierMedical.patient_id) {
        DossierMedical.sejour_id  = '{{$sejour_id}}';
        {{if isset($_is_anesth|smarty:nodefaults)}}
        DossierMedical._is_anesth = '{{$_is_anesth}}';
        {{/if}}
        DossierMedical.patient_id = '{{$patient->_id}}';
        DossierMedical.dossier_anesth_id = '{{$dossier_anesth_id}}';
      }
      {{if $reload}}
      DossierMedical.reloadDossierPatient('{{$reload}}', '{{$type_see}}');
      {{/if}}
    }

    if ($('tab_traitements_perso{{$addform}}')) {
      Control.Tabs.create('tab_traitements_perso{{$addform}}', false);
    }
  });
</script>

<div id="legend_actions_tp" style="display: none;">
  <table class="form">
    <tr>
      <th colspan="2" class="title">
        {{tr}}Legend{{/tr}}
      </th>
    </tr>
    <tr>
      <td style="height: 40px"><button class="stop">{{tr}}Arreter{{/tr}}</button></td>
      <td class="text">
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-prescribe_stop_tp{{/tr}}</li>
      </td>
    </tr>
    <tr>
      <td class="text" style="height: 40px"><button class="edit">{{tr}}Represcription.edit{{/tr}}</button></td>
      <td>
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-open_line_for_edit{{/tr}}</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td style="height: 40px"><button class="right">{{tr}}Poursuivre{{/tr}}</button></td>
      <td class="text">
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp_without_modif{{/tr}}</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td style="height: 50px"><button class="hslip">{{tr}}Relai{{/tr}}</button></td>
      <td class="text">
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-prescribe_stop_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-prescribe_other_product_relay{{/tr}}</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td style="height: 50px"><button class="pause">{{tr}}Pause{{/tr}}</button></td>
      <td class="text">
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-prescribe_stop_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-represcribe_same_product_later{{/tr}}</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td style="height: 50px">
        <a href="#" class="button fa fa-stop" style="color: #666 !important;">
          <span style="color: black !important;">{{tr}}Suspendre{{/tr}}</span>
        </a>
      </td>
      <td class="text">
        <ul>
          <li>{{tr}}CPrescription-represcribe_tp{{/tr}}</li>
          <li>{{tr}}CPrescription-prescribe_suspend_tp{{/tr}}</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>

{{assign var=traitement_enabled value="dPpatients CTraitement enabled"|gconf}}

{{if "dPpatients CAntecedent create_treatment_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_treatment &&
  !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  <div class="small-info text">{{tr}}CTraitement-msg-You are not allowed to enter personal treatment{{/tr}}</div>
{{/if}}
<fieldset id="inc_ant_consult_fieldset_trt_perso{{$addform}}" class="me-margin-bottom-12 me-padding-top-22"
          {{if "dPpatients CAntecedent create_treatment_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_treatment &&
            !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}style="display:none;"{{/if}}>
  <legend>{{tr}}CPrescriptionLineMedicament-traitement-personnel{{/tr}}</legend>
  <table class="layout main">
    <tr>
      <td class="text">
        <ul id="tab_traitements_perso{{$addform}}" class="control_tabs small">
          {{if "dPprescription"|module_active && "dPprescription show_chapters tp med"|gconf}}
            <li><a href="#tp_base_med{{$addform}}">{{tr}}CPrescriptionLineMedicament-bdd-medicament{{/tr}}</a></li>
          {{/if}}
          {{if $traitement_enabled && $vw_traitement_texte_libre}}
            <li><a href="#tp_texte_simple{{$addform}}">{{tr}}CPrescriptionLineMedicament-free-text{{/tr}}</a></li>
          {{/if}}
          {{if "dPprescription"|module_active && "dPprescription CPrescription show_element_tp"|gconf}}
            <li><a href="#tp_nomenclature{{$addform}}">{{tr}}CPrescriptionLineElement-nomenclature_elements{{/tr}}</a></li>
          {{/if}}
        </ul>
      </td>
    </tr>

    {{if "dPprescription"|module_active && "dPprescription show_chapters tp med"|gconf}}
      <tr id="tp_base_med{{$addform}}">
        <td class="text">
          {{mb_include module=cabinet template=inc_antecedent_bdm}}
        </td>
      </tr>
    {{/if}}

    <!-- Traitements -->
    {{if $traitement_enabled && $vw_traitement_texte_libre}}
      <tr id="tp_texte_simple{{$addform}}">
        <td class="text">
          {{mb_include module=cabinet template=inc_traitement_texte_libre}}
        </td>
      </tr>
    {{/if}}

    {{if "dPprescription"|module_active && "dPprescription CPrescription show_element_tp"|gconf}}
      {{mb_include module=prescription template=vw_add_line_element_tp}}
    {{/if}}
  </table>
</fieldset>