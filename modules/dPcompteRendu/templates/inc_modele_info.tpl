{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  reloadHeadersFooters = function() {
    {{if $compte_rendu->_id}}
    if ($("headers") && $("footers") && $("prefaces") && $("endings")) {
      var oForm = getForm("editFrm");
      var compte_rendu_id = $V(oForm.compte_rendu_id);
      var object_class = $V(oForm.object_class);

      var url = new Url("compteRendu", "ajax_headers_footers");
      url.addParam("compte_rendu_id", compte_rendu_id);
      url.addParam("object_class", object_class);
      url.addParam("type", "header");
      url.requestUpdate(oForm.header_id);

      url.addParam("type", "preface");
      url.requestUpdate(oForm.preface_id);

      url.addParam("type", "ending");
      url.requestUpdate(oForm.ending_id);

      url.addParam("type", "footer");
      url.requestUpdate(oForm.footer_id);
    }
    {{/if}}
  };

  toggleAlertCreation = function(object_class) {
    var call = (object_class == "CConsultation" || object_class == "CConsultAnesth") ? "show" : "hide";
    $("alert_creation_area")[call]();

    var form = getForm("editFrm");

    if ($V(form.type) != 'body') {
      var file_category = form.file_category_id;
      var category_label = file_category.next('label');

      file_category.removeClassName('notNull');
      category_label.removeClassName('notNull');
    }
  };

  Main.add(function() {
    var form = getForm('editFrm');

    {{if $droit}}
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "user_id_view")
      .autoComplete(form.user_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.user_id, id);
        }
      });

    new Url("mediusers", "ajax_functions_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "function_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.function_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.function_id, id);
        }
      });

    new Url("etablissement", "ajax_groups_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "group_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.group_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.group_id, id);
        }
      });
    {{/if}}
  });
</script>

<table class="form" id="info" style="display: none;">
<tr>
  {{me_form_field animated=false nb_cells=2 mb_object=$compte_rendu mb_field="nom" class="me-padding-top-8"}}
    {{if $droit}}
      <div id="special_modele" class="me-field-content" {{if !$compte_rendu->_special_modele}}style="display: none;"{{/if}}>
        {{tr}}CCompteRendu.description_{{$compte_rendu->nom}}{{/tr}}
      </div>

      {{assign var=style_nom value=""}}
      {{if $compte_rendu->_special_modele}}
        {{assign var=style_nom value="display: none;"}}
      {{/if}}

      {{mb_field object=$compte_rendu field="nom" style="width: 12em; $style_nom"}}

      <button type="button" class="cancel notext me-tertiary" onclick="setTemplateName('', '', '');"
              style="float: right; {{if !$compte_rendu->_special_modele}}display: none;{{/if}}"
              title="{{tr}}Cancel{{/tr}}"></button>
      <button type="button" class="search notext" onclick="Modal.open('choose_template_name');"
              style="float: right;"
              title="Choisir un nom réservé"></button>
    {{else}}
      {{if $compte_rendu->_special_modele}}
        <div class="me-field-content">
          {{tr}}CCompteRendu.description_{{$compte_rendu->nom}}{{/tr}}
        </div>
      {{else}}
        {{mb_field object=$compte_rendu field="nom" readonly="readonly"}}
      {{/if}}
    {{/if}}
  {{/me_form_field}}
</tr>

  {{if $access_group}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="group_id"}}
        {{if $droit}}
          {{mb_field object=$compte_rendu field=group_id hidden=1
          onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             \$V(this.form.function_id_view, '', false);"}}
          <input type="text" name="group_id_view" value="{{$compte_rendu->_ref_group}}" />
        {{elseif $compte_rendu->group_id}}
          {{mb_field object=$compte_rendu field=group_id hidden=1}}
          {{$compte_rendu->_ref_group}}
        {{/if}}
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if $access_function}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="function_id"}}
        {{if $droit}}
          {{mb_field object=$compte_rendu field=function_id hidden=1
          onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             \$V(this.form.group_id_view, '', false);"}}
          <input type="text" name="function_id_view" value="{{$compte_rendu->_ref_function}}" />
        {{elseif $compte_rendu->function_id}}
          {{$compte_rendu->_ref_function}}
        {{/if}}
      {{/me_form_field}}
    </tr>
  {{/if}}

  <tr>
    {{if $droit}}
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="user_id"}}
        {{mb_field object=$compte_rendu field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             \$V(this.form.function_id_view, '', false);
             \$V(this.form.group_id, '', false);
             \$V(this.form.group_id_view, '', false);"}}
        <input type="text" name="user_id_view" value="{{$compte_rendu->_ref_user}}" />
      {{/me_form_field}}
    {{elseif $compte_rendu->user_id}}
      {{$compte_rendu->_ref_user}}
    {{/if}}
  </tr>

  {{if "printing"|module_active}}
  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field=printer_id}}
      <select name="printer_id">
        <option value="">{{tr}}Choose{{/tr}}</option>
        {{foreach from=$printers item=_printer}}
          <option value="{{$_printer->_id}}" {{if $_printer->_id == $compte_rendu->printer_id}}selected{{/if}}>
            {{$_printer->_ref_source->_view}}
          </option>
        {{/foreach}}
      </select>
    {{/me_form_field}}
  </tr>
  {{/if}}

  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="font"}}
      {{mb_field object=$compte_rendu field="font" emptyLabel="Choose" style="width: 15em"}}
    {{/me_form_field}}
  </tr>

  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="size"}}
      {{mb_field object=$compte_rendu field="size" emptyLabel="Choose" style="width: 15em"}}
    {{/me_form_field}}
  </tr>

  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="type"}}
      {{if $droit}}
        {{mb_field object=$compte_rendu field=type onchange="updateType();  Thumb.old(); Modele.categoryNullOrNotNull(this);" style="width: 15em;"}}
      {{else}}
        {{mb_field object=$compte_rendu field=type disabled="disabled" style="width: 15em;"}}
      {{/if}}

      <script>
        function updateType() {
          {{if $compte_rendu->_id}}
          var oForm = getForm("editFrm");
          var bBody = oForm.type.value == "body";
          var bHeader = oForm.type.value == "header";
          var bOther  = (oForm.type.value == "preface" || oForm.type.value == "ending");

          if (bHeader) {
            $("preview_page").insert({top   : $("header_footer_content").remove()});
            $("preview_page").insert({bottom: $("body_content").remove()});
          }
          else {
            $("preview_page").insert({bottom: $("header_footer_content").remove()});
            $("preview_page").insert({top   : $("body_content").remove()});
          }

          // General Layout
          $("layout").down('.fields').setVisible(!bOther);
          $("layout").down('.notice').setVisible(bOther);

          // Page layout
          if (window.Preferences.pdf_and_thumbs == 1) {
            $("page_layout").setVisible(bBody);
          }
          $("layout_header_footer").setVisible(!bBody && !bOther);


          // Height
          $("height").setVisible(!bBody && !bOther);
          if (bBody) $V(oForm.height, '');

          // Headers, Footers, Prefaces and Endings
          var oComponent = $("components");
          if (oComponent) {
            oComponent.setVisible(bBody);
            if (!bBody) {
              $V(oForm.header_id , '');
              $V(oForm.footer_id , '');
              $V(oForm.preface_id, '');
              $V(oForm.ending_id , '');
            }
          }

          Modele.preview_layout();
          {{/if}}
        }

        Main.add(updateType);
      </script>
    {{/me_form_field}}
  </tr>

  <tbody id="components">

  {{if $headers|@count}}
    <tr id="headers">
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field=header_id}}
        <select name="header_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.header_id}}" {{if !$droit}}disabled{{/if}} style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$headers item=headersByOwner key=owner}}
            <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
              {{foreach from=$headersByOwner item=_header}}
                <option value="{{$_header->_id}}" {{if $compte_rendu->header_id == $_header->_id}}selected{{/if}}>{{$_header->nom}}</option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}None{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if $prefaces|@count}}
    <tr id="prefaces">
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field=preface_id}}
        <select name="preface_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.preface_id}}" {{if !$droit}}disabled{{/if}} style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$prefaces item=prefacesByOwner key=owner}}
            <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
              {{foreach from=$prefacesByOwner item=_preface}}
                <option value="{{$_preface->_id}}" {{if $compte_rendu->preface_id == $_preface->_id}}selected{{/if}}>{{$_preface->nom}}</option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}None{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if $endings|@count}}
    <tr id="endings">
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="ending_id"}}
        <select name="ending_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.ending_id}}" {{if !$droit}}disabled{{/if}} style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$endings item=endingsByOwner key=owner}}
            <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
              {{foreach from=$endingsByOwner item=_ending}}
                <option value="{{$_ending->_id}}" {{if $compte_rendu->ending_id == $_ending->_id}}selected{{/if}}>{{$_ending->nom}}</option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}None{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if $footers|@count}}
    <tr id="footers">
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="footer_id"}}
        <select name="footer_id" onchange="Thumb.old();" class="{{$compte_rendu->_props.footer_id}}" {{if !$droit}}disabled{{/if}} style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$footers item=footersByOwner key=owner}}
            <optgroup label="{{tr}}CCompteRendu._owner.{{$owner}}{{/tr}}">
              {{foreach from=$footersByOwner item=_footer}}
                <option value="{{$_footer->_id}}" {{if $compte_rendu->footer_id == $_footer->_id}}selected{{/if}}>{{$_footer->nom}}</option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}None{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
  {{/if}}
  </tbody>

  <tr>
    {{me_form_field nb_cells=2  mb_object=$compte_rendu mb_field="object_class"}}
      <select name="object_class" class="{{$compte_rendu->_props.object_class}}"
              onchange="loadCategory(); reloadHeadersFooters(); toggleAlertCreation(this.value);" style="width: 15em;">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      </select>
    {{/me_form_field}}
  </tr>

  <tr id="alert_creation_area"
    {{if !$compte_rendu->_id || !in_array($compte_rendu->object_class, array("CConsultation", "CConsultAnesth"))}}style="display: none;"{{/if}}>
    {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="alert_creation"}}
      {{mb_field object=$compte_rendu field="alert_creation"}}
    {{/me_form_bool}}
  </tr>

  <tr>
    {{assign var=class_notnull value="notNull"}}
      {{if $compte_rendu->type !== "body"}}
          {{assign var=class_notnull value=""}}
      {{/if}}

    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="file_category_id"}}
      <select name="file_category_id" class="{{$compte_rendu->_props.file_category_id}} {{$class_notnull}}" style="width: 15em;" onchange="Modele.categoryNullOrNotNull(this);">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      </select>
    {{/me_form_field}}
  </tr>

  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="language"}}
      {{mb_field object=$compte_rendu field="language"}}
    {{/me_form_field}}
  </tr>

  <tr>
    {{assign var=warning_dompdf value=""}}
    {{if $compte_rendu->_is_dompdf}}
      {{assign var=warning_dompdf value="warning"}}
    {{/if}}

    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="factory" class=$warning_dompdf}}
      {{mb_field object=$compte_rendu field="factory"}}
    {{/me_form_field}}
  </tr>

  {{if "dmp"|module_active}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="type_doc_dmp"}}
        {{mb_field object=$compte_rendu field="type_doc_dmp" emptyLabel="Choose" style="width: 15em;"}}
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if "sisra"|module_active}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="type_doc_sisra"}}
        {{mb_field object=$compte_rendu field="type_doc_sisra" emptyLabel="Choose" style="width: 15em;"}}
      {{/me_form_field}}
    </tr>
  {{/if}}

  {{if $compte_rendu->type == "body" || !$compte_rendu->_id}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="purge_field"}}
        {{mb_field object=$compte_rendu field="purge_field"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="nb_print"}}
        {{mb_field object=$compte_rendu field="nb_print" form=editFrm increment=true}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="fast_edit"}}
        {{mb_field object=$compte_rendu field="fast_edit"}}
      {{/me_form_bool}}
    </tr>

    {{if $pdf_and_thumbs}}
      <tr>
        {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="fast_edit_pdf"}}
          {{mb_field object=$compte_rendu field="fast_edit_pdf"}}
        {{/me_form_bool}}
      </tr>
    {{/if}}

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="purgeable"}}
        {{mb_field object=$compte_rendu field="purgeable"}}
      {{/me_form_bool}}
    </tr>

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="signature_mandatory"}}
        {{mb_field object=$compte_rendu field="signature_mandatory"}}
      {{/me_form_bool}}
    </tr>

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="send"}}
        {{mb_field object=$compte_rendu field="send"}}
      {{/me_form_bool}}
    </tr>
  {{/if}}

  <tr>
    {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="actif"}}
      {{mb_field object=$compte_rendu field="actif"}}
    {{/me_form_bool}}
  </tr>
  <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="masquage_praticien"}}
      {{mb_field object=$compte_rendu field="masquage_praticien"}}
      {{/me_form_bool}}
  </tr>
  <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="masquage_patient"}}
      {{mb_field object=$compte_rendu field="masquage_patient"}}
      {{/me_form_bool}}
  </tr>
  <tr>
      {{me_form_bool nb_cells=2 mb_object=$compte_rendu mb_field="masquage_representants_legaux"}}
      {{mb_field object=$compte_rendu field="masquage_representants_legaux"}}
      {{/me_form_bool}}
  </tr>
</table>
