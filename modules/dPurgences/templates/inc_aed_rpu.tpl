{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences   script=protocole_rpu   ajax=1}}
{{mb_script module=urgences   script=contraintes_rpu ajax=1}}
{{mb_script module=urgences   script=urgences        ajax=1}}
{{mb_script module=patients   script=pat_selector    ajax=1}}
{{mb_script module=admissions script=admissions      ajax=1}}

{{if "web100T"|module_active}}
  {{mb_script module=web100T script=web100T ajax=1}}
{{/if}}

{{assign var=consult value=$rpu->_ref_consult}}

{{mb_default var=view_mode value=infirmier}}

{{mb_default var=show_buttons_urgence value=false}}
{{if "ecap"|module_active}}
  {{assign var=show_buttons_urgence value="ecap Display show_buttons_urgence"|gconf}}
{{/if}}

{{assign var=save_ajax value=0}}
{{assign var=submit_ajax value=""}}
{{if $rpu->_id}}
  {{assign var=save_ajax value=1}}
  {{assign var=submit_ajax value="this.form.onsubmit();"}}
{{/if}}

<script>
  ContraintesRPU.contraintesProvenance  = {{$contrainteProvenance|@json}};
  ContraintesRPU.contraintesDestination = {{$contrainteDestination|@json}};
  ContraintesRPU.contraintesOrientation = {{$contrainteOrientation|@json}};

  afterEditCorrespondant = function() {
    new Url("patients", "ajax_refresh_correspondants")
      .addParam("patient_id", "{{$patient->_id}}")
      .addParam("show_close", 1)
      .requestUpdate("list_correspondants");
  };

  submitSejour = function(sejour_id) {
    var oForm = getForm(sejour_id ? "editDP_RPU" : "editSejour");
    return onSubmitFormAjax(oForm, function() {
      if (sejour_id != null) {
        reloadDiagnostic(sejour_id);
      }
    });
  };

  requestInfoPat = function() {
    var oForm = getForm("editRPU");
    var iPatient_id = $V(oForm._patient_id);
    if(!iPatient_id){
      return false;
    }
    var url = new Url("patients", "httpreq_get_last_refs");
    url.addParam("patient_id", iPatient_id);
    url.addParam("is_anesth", 0);
    url.addParam("show_dhe_ecap", '{{$show_buttons_urgence}}');
    url.requestUpdate("infoPat");
    return true;
  };

  Main.add(function() {
    var form = getForm("editRPU");

    if (form.elements._service_id) {
      var box = form.elements.box_id;
      Element.observe(box, "protocole:change", function(event) {
        var opt_group = box.options[box.selectedIndex].up("optgroup");
        if (!opt_group) {
          return;
        }
        var service_id = opt_group.get("service_id");
        $V(form.elements._service_id, service_id, false);

        {{if $save_ajax}}
          form.onsubmit();
        {{/if}}
      });
      Element.observe(box, "change", function(event) {
        box.fire("protocole:change");
      });
    }

    {{if $sejour->mode_entree}}
    // Lancement des fonctions de contraintes entre les champs
    ContraintesRPU.updateProvenance("{{$sejour->mode_entree}}");
    {{/if}}
  });
</script>

<form name="editRPU" method="post" action="?"
      onsubmit="
      {{if $save_ajax}}
        return onSubmitFormAjax(this);
      {{else}}
        if (checkForm(this)) {
          this.submit();
        }
      {{/if}}">
  {{mb_class object=$rpu}}
  {{mb_key   object=$rpu}}
  <input type="hidden" name="m" value="urgences" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="actif" value="1"/>
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_annule" value="{{$rpu->_annule|default:"0"}}" />
  {{if $view_mode == "infirmier"}}
    <input type="hidden" name="postRedirect" value="m=urgences&dialog=vw_aed_rpu" />
  {{else}}
    <input type="hidden" name="postRedirect" value="m=urgences&{{if $tab_mode}}tab{{else}}dialog{{/if}}=edit_consultation" />
  {{/if}}

  {{if !$show_rpu_consultation}}
    <input type="hidden" name="_bind_sejour" value="1" />
  {{/if}}
  <input type="hidden" name="_entree_preparee" value="{{$sejour->entree_preparee}}"/>
  {{mb_field object=$rpu field=motif hidden=true onchange=$submit_ajax}}

  <table class="form me-margin-top-0 me-margin-bottom-0 me-no-box-shadow">
    {{mb_include module=urgences template=rpu/inc_header_rpu}}

    {{if $rpu->_annule}}
      <tr>
        <th class="category cancelled" colspan="2">
          {{tr}}CRPU-_annule{{/tr}}
        </th>
      </tr>
    {{/if}}
  </table>
  <table class="main me-no-align">
    <tr>
      <td class="halfPane">
        {{mb_include module=urgences template=rpu/inc_fieldset_pec_adm}}

        {{mb_include module=urgences template=rpu/inc_fieldset_geoloc}}
      </td>
      <td>
        {{mb_include module=urgences template=rpu/inc_fieldset_pec_inf}}
      </td>
    </tr>
  </table>
</form>

<table class="main me-no-align">
  <tr>
    <td class="halfPane">
        {{mb_include module=urgences template=rpu/inc_fieldset_categories}}

        {{mb_include module=urgences template=rpu/inc_fieldset_sortie}}
    </td>
    <td>
      {{mb_include module=urgences template=rpu/inc_fieldset_pec_med}}

      {{mb_include module=urgences template=rpu/inc_fieldset_attentes}}

      {{mb_include module=urgences template=rpu/inc_fieldset_precision_sortie}}
    </td>
  </tr>
</table>
</fieldset>
<table class="main me-align-auto">
  <tr>
    <td class="{{if $view_mode != "infirmier"}} halfPane{{/if}}">
      {{mb_include module=urgences template=rpu/inc_fieldset_actions_adm}}
    </td>
    {{if $view_mode != "infirmier" && $rpu->_id}}
    <td>
      {{mb_include module=urgences template=rpu/inc_fieldset_actions_med}}
    </td>
    {{/if}}
  </tr>
</table>

{{if !$rpu->_id}}
  <fieldset class="me-small">
    <legend>{{tr}}CPatient.infos{{/tr}}</legend>
    <div class="text" id="infoPat">
      <div class="empty">{{tr}}CPatient.none_selected{{/tr}}</div>
    </div>
  </fieldset>
{{/if}}

<form name="categorieRPU" method="post">
    {{mb_class object=$link_cat}}
    {{mb_key   object=$link_cat}}
  <input type="hidden" name="del" value="0"/>
    {{mb_field object=$link_cat field=rpu_categorie_id hidden=1}}
    {{mb_field object=$link_cat field=rpu_id hidden=1}}
</form>

