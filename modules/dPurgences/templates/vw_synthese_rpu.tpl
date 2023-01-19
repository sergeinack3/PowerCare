{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences   script=urgences ajax=1}}
{{mb_script module=urgences   script=contraintes_rpu ajax=1}}
{{mb_script module=patients   script=pat_selector ajax=1}}
{{mb_script module=soins      script=soins ajax=1}}

{{mb_script module=cabinet    script=reglement ajax=1}}
{{mb_script module=cim10      script=CIM ajax=1}}
{{mb_script module=urgences   script=CCirconstance ajax=1}}

{{assign var=sejour  value=$rpu->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=consult value=$rpu->_ref_consult}}

{{if !$consult->_id}}
  {{mb_script module=compteRendu script=document ajax=1}}
  {{mb_script module=files       script=file ajax=1}}
  {{mb_script module=patients    script=patient ajax=1}}
{{/if}}

{{assign var=submit_ajax value="this.form.onsubmit();"}}
{{assign var=tab_mode value=0}}

{{assign var="do_subject_aed" value="do_consultation_aed"}}
{{assign var=object value=$consult}}
{{mb_include module=salleOp template=js_codage_ccam}}

{{assign var=is_inf value=0}}

{{if $app->_ref_user->isInfirmiere()}}
  {{assign var=is_inf value=1}}
{{/if}}

<script>
  submitSuivi = function(form) {
    onSubmitFormAjax(form, Soins.callbackClose);
  };

  submitSejour = function(sejour_id) {
    var oForm = getForm(sejour_id ? 'editDP_RPU' : 'editSejour');
    return onSubmitFormAjax(oForm, function() {
      if (sejour_id != null) {
        reloadDiagnostic(sejour_id);
      }
    });
  };

  Main.add(function() {
    Urgences.from_synthese = true;

    Urgences.nb_printers = {{$nb_printers}};

    ViewPort.SetAvlHeight("timeline_sejour", 0.8);

    Urgences.timelineSejour('{{$sejour->_id}}');

    Soins.callbackClose = function() {
      Control.Modal.close();
      Urgences.timelineSejour('{{$sejour->_id}}');
    };
  });
</script>

<div id="sortie_rpu" style="display: none;">
  {{mb_include module=urgences template=rpu/inc_fieldset_precision_sortie}}

  {{mb_include module=urgences template=rpu/inc_fieldset_sortie}}

  <table class="main">
    <tr>
      <td class="button">
        <button type="button" class="tick" onclick="onSubmitFormAjax(getForm('editSejour'), Control.Modal.close);">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>

<table class="main">
  {{assign var=switch_view value="pecMed"}}
  {{if $is_inf || !$consult->_id}}
    {{assign var=switch_view value="pecInf"}}
  {{/if}}

  <tr>
    <td colspan="2">
      {{mb_include module=soins template=inc_patient_banner object=$sejour patient=$patient}}
    </td>
  </tr>

  <tr>
    <td class="halfPane">
      {{if "syntheseMed"|module_active}}
        {{mb_script module=syntheseMed script=vue_medecin ajax=true}}
        {{mb_include module=syntheseMed template=inc_button_synthese}}
      {{/if}}

      <form name="editRPU" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$rpu}}
        {{mb_key object=$rpu}}

        <input type="hidden" name="postRedirect" value="m=urgences&dialog=vw_synthese_rpu" />
        <input type="hidden" name="_bind_sejour" value="1" />
        <input type="hidden" name="_entree_preparee" value="{{$sejour->entree_preparee}}" />
        <input type="hidden" name="_annule" value="0" />

        {{mb_include module=urgences template=rpu/inc_fieldset_pec_adm modal=1}}

        {{mb_include module=urgences template=rpu/inc_fieldset_geoloc modal=1}}

        {{mb_include module=urgences template=rpu/inc_fieldset_pec_inf modal=$consult->_id}}
      </form>

      {{if $consult->_id && !$is_inf}}
        <script>
          Reglement.consultation_id = '{{$consult->_id}}';
          Reglement.user_id = '{{$consult->_ref_chir->_id}}';
          Reglement.only_cotation = 1;

          Reglement.reload();
        </script>

        <div id="facturation"></div>
      {{/if}}

      <fieldset>
        <legend>{{tr}}CCompteRendu|pl{{/tr}}</legend>

        <table class="main">
          <tr>
            <td class="halfPane">
              <div id="Documents-{{$sejour->_guid}}">
                <script>
                  Document.register('{{$sejour->_id}}','{{$sejour->_class}}','{{$sejour->praticien_id}}', 'Documents-{{$sejour->_guid}}', 'normal');
                </script>
              </div>
            </td>
            <td>
              <div id="Files-{{$sejour->_guid}}">
                <script>
                  File.register('{{$sejour->_id}}','{{$sejour->_class}}', "Files-{{$sejour->_guid}}");
                </script>
              </div>
            </td>
          </tr>
          {{if $consult->_id}}
            <tr>
              <td class="halfPane">
                <div id="Documents-{{$consult->_guid}}">
                  <script>
                    Document.register('{{$consult->_id}}','{{$consult->_class}}','{{$consult->_praticien_id}}', 'Documents-{{$consult->_guid}}', 'normal');
                  </script>
                </div>
              </td>
              <td>
                <div id="Files-{{$consult->_guid}}">
                  <script>
                    File.register('{{$consult->_id}}','{{$consult->_class}}', "Files-{{$consult->_guid}}");
                  </script>
                </div>
              </td>
            </tr>
          {{/if}}
          <tr>
            <td class="halfPane">
              <div id="Documents-{{$patient->_guid}}">
                <script>
                  Document.register('{{$patient->_id}}','CPatient','{{$app->user_id}}', 'Documents-{{$patient->_guid}}', 'normal');
                </script>
              </div>
            </td>
            <td>
              <div id="Files-{{$patient->_guid}}">
                <script>
                  File.register('{{$patient->_id}}','{{$patient->_class}}', "Files-{{$patient->_guid}}");
                </script>
              </div>
            </td>
          </tr>
        </table>
      </fieldset>

      {{if "forms"|module_active}}
        <fieldset>
          <legend>{{tr}}CExClass|pl{{/tr}}</legend>
          <div id="ex_class-list">
            <script>
              Main.add(function() {
                ExObject.loadExObjects(
                  '{{$sejour->_class}}',
                  '{{$sejour->_id}}',
                  'ex_class-list',
                  0,
                  null,
                  {
                    readonly: 0,
                    creation_context_class: "{{$sejour->_class}}",
                    creation_context_id:    "{{$sejour->_id}}"
                  }
                );
              });
            </script>
          </div>
        </fieldset>
      {{/if}}
    </td>
    <td>
      {{mb_include module=hospi template=inc_add_trans_obs}}

      {{if $consult->_id && !$is_inf}}
        {{if "dPcabinet CConsultation show_projet_soins"|gconf}}
          <button type="button" class="add" onclick="Soins.modalConsult(null, '{{$consult->_id}}', 'exams', 'projet_soins');">
            {{tr}}CConsultation-projet_soins{{/tr}}
          </button>
        {{/if}}
        {{if "dPcabinet CConsultation show_conclusion"|gconf}}
          <button type="button" class="add" onclick="Soins.modalConsult(null, '{{$consult->_id}}', 'exams', 'conclusion')">
            {{tr}}CConsultAnesth-Conclusion{{/tr}}
          </button>
        {{/if}}
      {{/if}}

      <div id="timeline_sejour"></div>

      <fieldset>
        <legend>{{tr}}CRPU-back-attentes_rpu{{/tr}}</legend>

        {{mb_include module=urgences template=inc_vw_rpu_attente}}
      </fieldset>

      {{mb_include module=urgences template=rpu/inc_fieldset_pec_med}}

      {{mb_include module=urgences template=rpu/inc_fieldset_precision_sortie suffixe_form=rpu}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include module=urgences template=rpu/inc_fieldset_actions_adm}}
    </td>
    <td>
      {{if $view_mode !== "infirmier" && $consult->_id}}
        {{mb_include module=urgences template=rpu/inc_fieldset_actions_med redirect_synthese=1}}
      {{/if}}
    </td>
  </tr>
</table>

