{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=error value=0}}

<script>
  toggleListCustom = function (form) {
    if (form._concept_type) {
      var concept_type = $V(form._concept_type);
      var enableList = (concept_type == "concept");

      var input = form.concept_id_autocomplete_view;
      var select = form._spec_type;

      if (input) {
        input.up(".dropdown").down(".dropdown-trigger").setVisibility(enableList);
        input.disabled = input.readOnly = !enableList;
      }

      if (enableList) {
        //$V(select, "none");
        $V(form.prop, "");
      }
      else {
        $V(input, "");
        $V(form.concept_id, "");
      }

      select.disabled = select.readOnly = !!$V(form.ex_class_field_id) || enableList;
    }

    ExFieldSpec.edit(form);
  };

  selectConcept = function (input) {
    ExFieldSpec.edit(input.form);

    if (!$V(input.form._locale)) {
      $V(input.form._locale, input.form._concept_autocomplete.value);
    }
  };

  warnNotNullPredicate = function () {
    var warning = $("notNull-predicate-warning");
    var form = getForm("editField");

    warning.setVisible($V(form.prop).indexOf(" notNull") > -1 && $V(form.predicate_id));
  };

  Main.add(function () {
    var form = getForm("editField");
    var field_id = $V(form.ex_class_field_id);

    toggleListCustom.defer(form);
    form.elements._locale.select();

    {{assign var=_can_formula_arithmetic value=false}}
    {{assign var=_can_formula_concat value=false}}

    {{if $ex_field->_id}}
      {{assign var=_spec_type value=$ex_field->_spec_object->getSpecType()}}
      {{assign var=_can_formula_arithmetic value='Ox\Mediboard\System\Forms\CExClassField::formulaCanArithmetic'|static_call:$_spec_type}}
      {{assign var=_can_formula_concat value='Ox\Mediboard\System\Forms\CExClassField::formulaCanConcat'|static_call:$_spec_type}}

      {{if $_can_formula_arithmetic || $_can_formula_concat}}
        ExFormula.edit(field_id, ($('fieldFormulaEditor').isVisible()) ? function () {
          ExFormula.toggleInsertButtons(true, "{{$_can_formula_arithmetic|ternary:'arithmetic':'concat'}}", '{{$ex_field->_id}}');
        } : null);
      {{/if}}
    {{/if}}

    Control.Tabs.create("ExClassField-param", true, {
      afterChange: function (newContainer) {
        ExFormula.toggleInsertButtons(newContainer.id == "fieldFormulaEditor", "{{$_can_formula_arithmetic|ternary:'arithmetic':'concat'}}", '{{$ex_field->_id}}');
      }
    });

    // highlight current field
    $$("tr.ex-class-field.selected").invoke("removeClassName", "selected");

    var selected = $$("tr.ex-class-field[data-ex_class_field_id='{{$ex_field->_id}}']");

    if (selected.length) {
      selected[0].addClassName("selected");
    }

    var url = new Url("forms", "ajax_autocomplete_ex_class_field_predicate");
    url.autoComplete(form.elements.predicate_id_autocomplete_view, null, {
      minChars:           2,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var id = selected.get("id");

        if (!id) {
          $V(field.form.predicate_id, "");
          $V(field.form.elements.predicate_id_autocomplete_view, "");
          return;
        }

        $V(field.form.predicate_id, id);

        if (id) {
          showField(id, selected.down('.name').getText());
        }

        if ($V(field.form.elements.predicate_id_autocomplete_view) == "") {
          $V(field.form.elements.predicate_id_autocomplete_view, selected.down('.view').getText());
        }
      },
      callback: function (input, queryString) {
        return queryString + "&ex_class_id={{$ex_class->_id}}&ex_class_field_id={{$ex_field->_id}}";
      }
    });

    {{if !$ex_field->_id}}
      var concept_autocomplete = new Url('forms', 'ajax_autocomplete_ex_concept');
      concept_autocomplete.addParam("input_field", form._concept_autocomplete.name);
      concept_autocomplete.addParam("group_id", '{{$ex_class->group_id}}');
      concept_autocomplete.autoComplete(form._concept_autocomplete, null, {
        minChars: 2,
        dropdown: true,
        method:   'get',
        updateElement: function (selected) {
          var concept_id = selected.get('id');
          if (concept_id) {
            $V(form.elements._concept_autocomplete, selected.down('span').getText().strip());

            // Order is important here: we need to valuate text field BEFORE concept ID field (onchange event triggered)
            $V(form.elements.concept_id, concept_id, true);
          }
        }
      });
    {{/if}}

    warnNotNullPredicate();
  });
</script>

<form name="editField" method="post" action="?" data-object_guid="{{$ex_field->_guid}}" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_field_aed" />
  <input type="hidden" name="callback" value="ExField.editCallback" />

  <input type="hidden" name="_triggered_data" value="{{$ex_field->_triggered_data|@json|smarty:nodefaults|JSAttribute}}" />

  {{mb_key object=$ex_field}}
  {{mb_field object=$ex_field field=ex_group_id hidden=true}}
  {{mb_field object=$ex_field field=disabled hidden=true}}

  {{foreach from=$ex_field->getPropertyFields() item=_property_field}}
    {{mb_field object=$ex_field field=$_property_field hidden=true}}
  {{/foreach}}

  <table class="main form">

    {{assign var=object value=$ex_field}}
    <tr>
      {{if $object->_id}}
        <th class="title modify text" colspan="4">
          {{mb_include module=system template=inc_object_notes}}
          {{mb_include module=system template=inc_object_idsante400}}
          {{mb_include module=system template=inc_object_history}}
          {{tr}}{{$object->_class}}-title-modify{{/tr}}
          '{{$object}}'
        </th>
      {{else}}
        <th class="title text me-th-new" colspan="4">
          {{tr}}{{$object->_class}}-title-create{{/tr}}
        </th>
      {{/if}}
    </tr>

    <tr>
      {{if $ex_field->_id}}
        <th>
          {{if $ex_field->concept_id}}
            Concept [liste/type]
          {{else}}
            Type
          {{/if}}
        </th>

        <td colspan="3" class="text">
          <strong>
            {{if $ex_field->concept_id}}
              {{mb_value object=$ex_field field=concept_id}}
              {{mb_field object=$ex_field field=concept_id hidden=true}}

              {{if $conf.forms.CExConcept.native_field && $ex_field->_ref_concept && $ex_field->_ref_concept->native_field}}
                <i>(lié au champ Mediboard {{$ex_field->_ref_concept->getNativeFieldView()}})</i>
              {{/if}}
            {{else}}
              {{tr}}CMbFieldSpec.type.{{$spec_type}}{{/tr}}
            {{/if}}
          </strong>
        </td>
      {{else}}
        <th>
          {{if !$conf.forms.CExClassField.force_concept}}
            <label>
              {{tr}}CExClassField-concept_id{{/tr}}
              <input type="radio" onclick="toggleListCustom(this.form)" name="_concept_type" value="concept" checked="checked" />
            </label>
          {{else}}
            <label for="concept_id">{{tr}}CExClassField-concept_id{{/tr}}</label>
          {{/if}}
        </th>

        <td>
          {{assign var=_prop value=$ex_field->_props.concept_id}}

          {{if $conf.forms.CExClassField.force_concept}}
            {{assign var=_prop value="$_prop notNull"}}
          {{/if}}

          {{*{{mb_field object=$ex_field field=concept_id form="editField" autocomplete="true,1,50,true,true"*}}
          {{*onchange="selectConcept(this)" prop=$_prop}}*}}

          {{mb_field object=$ex_field field=concept_id hidden=true prop=$_prop onchange="selectConcept(this)"}}
          <input type="text" name="_concept_autocomplete" class="autocomplete" />
          <button class="new" onclick="ExConcept.createInModal();" type="button">{{tr}}CExConcept-title-create{{/tr}}</button>
        </td>

        {{if $conf.forms.CExClassField.force_concept}}
          <td colspan="2"></td>
        {{else}}
          <th>
            <label>
              Type personnalisé
              <input type="radio" onclick="toggleListCustom(this.form)" name="_concept_type" value="custom" />
            </label>
          </th>

          <td>
            <select name="_spec_type" onchange="ExFieldSpec.edit(this.form)">
              {{foreach from='Ox\Mediboard\System\Forms\CExClassField::getTypes'|static_call:null key=_key item=_class}}
                <option value="{{$_key}}" {{if $_key == $spec_type && !$ex_field->concept_id}}selected="selected"{{/if}}>
                  {{tr}}CMbFieldSpec.type.{{$_key}}{{/tr}}
                </option>
              {{/foreach}}
            </select>
          </td>
        {{/if}}
      {{/if}}
    </tr>

    <tr>
      <th style="width: 8em;">{{mb_label object=$ex_field field=_locale}}</th>
      <td>
        {{if $ex_field->_id}}
          {{mb_field object=$ex_field field=_locale size=50}}
        {{else}}
          {{mb_field object=$ex_field field=_locale size=50}}
        {{/if}}
      </td>

      <th>{{mb_label object=$ex_field field=_locale_court}}</th>
      <td>{{mb_field object=$ex_field field=_locale_court tabIndex="3" size=30}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_field field=_locale_desc}}</th>
      <td>{{mb_field object=$ex_field field=_locale_desc tabIndex="2" size=60}}</td>

      <th><label for="ex_group_id">Groupe</label></th>
      <td>
        <select name="ex_group_id" style="max-width: 20em;">
          {{foreach from=$ex_class->_ref_groups item=_group}}
            <option value="{{$_group->_id}}"
                    {{if $_group->_id == $ex_field->ex_group_id}}selected="selected"{{/if}}>{{$_group}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_field field=report_class}}</th>
      <td>
        {{mb_field object=$ex_field field=report_class typeEnum=select emptyLabel="None" style="max-width: 12em;" onchange="var msg = this.next('.info'); if (msg) msg.setVisible(!!this.value);"}}
        {{if $conf.forms.CExConcept.native_field && $ex_field->_ref_concept && $ex_field->_ref_concept->native_field}}
          <div class="info" style="display: none;">
            Le report de données écrasera les données reprises depuis Mediboard si '{{tr}}CExClassField-update_native_data{{/tr}}' est coché
          </div>

          <div>
            <img src="./images/icons/reported.png" style="background: #a2bad6;" />
            <small>(MB => Form.)</small>
            {{mb_field object=$ex_field field=load_native_data typeEnum=checkbox readonly=$ex_field->_disable_load_native_data}}
            {{mb_label object=$ex_field field=load_native_data}}
          </div>

          <div>
            <img src="./images/icons/reported.png" style="background: #d63e39;" />
            <small>(Form. => MB)</small>
            {{mb_field object=$ex_field field=update_native_data typeEnum=checkbox onchange="if (this.value==1) { Modal.alert(this.next()); this.onchange=null}"}}

            <div style="display: none;">
              Seules les 'Constantes médicales', 'Transmissions médicales', 'Consultations' et 'Séjours' seront pris en compte.<br />
              Attention, une nouvelle saisie sera sauvegardée à chaque enregistrement de formulaire pour les 'Constantes médicales' et 'Transmissions médicales'.
            </div>
            {{mb_label object=$ex_field field=update_native_data}}
          </div>
        {{/if}}
      </td>

      <th></th>
      <td>
        {{if $conf.forms.CExClassField.doc_template_integration}}
          <label title="{{tr}}CExClassField-in_doc_template-desc{{/tr}}">
            {{mb_field object=$ex_field field=in_doc_template typeEnum=checkbox}}
            {{tr}}CExClassField-in_doc_template{{/tr}}
          </label>

        {{/if}}

        <label title="{{tr}}CExClassField-in_completeness-desc{{/tr}}">
          {{mb_field object=$ex_field field=in_completeness typeEnum=checkbox}}
          {{tr}}CExClassField-in_completeness{{/tr}}
        </label>

        {{if $ex_field->canIncrement()}}
          <label title="{{tr}}CExClassField-auto_increment-desc{{/tr}}">
            {{mb_field object=$ex_field field=auto_increment typeEnum=checkbox}}
            {{tr}}CExClassField-auto_increment{{/tr}}
          </label>
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_field field=predicate_id}}</th>
      <td colspan="2">
        <div class="small-warning" id="notNull-predicate-warning" style="display: none;">
          Le champ est à la fois <strong>obligatoire et conditionnel</strong>,
          ceci peut provoquer des problèmes lors de la saisie du formulaire.
        </div>
        <input type="text" name="predicate_id_autocomplete_view" size="60" value="{{$ex_field->_ref_predicate->_view}}"
               placeholder=" -- Toujours afficher -- " />
        {{mb_field object=$ex_field field=predicate_id hidden=true onchange="warnNotNullPredicate(this)"}}
        <button class="new notext" onclick="ExFieldPredicate.create('{{$ex_field->_id}}', '{{$ex_field->_id}}', this.form)"
                type="button">{{tr}}New{{/tr}}</button>
      </td>

      <td>
        {{if $ex_field->_id}}
          <table class="main layout" style="table-layout: fixed; width: 1%;">
            {{mb_include module=dPsante400 template=inc_widget_list_hypertext_links object=$ex_field show_separator=false}}
          </table>
        {{/if}}
      </td>
    </tr>

    {{if $ex_field->_id && $ex_field->_has_available_tags}}
      <tr>
        <th>
          <script>
            Main.add(function () {
              var tags = $('ex_class_field_tags');
              window.fieldTagTokenField = new TokenField(tags, {onChange: function () { getForm("editField").onsubmit(); }});
              ExFieldTag.makeAutocomplete($('ex_field_tags_autocomplete'), window.fieldTagTokenField);
            });
          </script>

          <input type="hidden" name="_store_tag_items" value="1" />

          <input type="hidden" id="ex_class_field_tags" name="_ex_class_field_tags"
                 value="{{'|'|implode:$ex_field->_ex_class_field_tags}}" />

          <div>
            <span>
              <i class="fas fa-tags"></i>
              {{tr}}CExClassFieldTagItem|pl{{/tr}}
            </span>

            <br />

            <span>
              <input type="text" id="ex_field_tags_autocomplete" name="ex_field_tags_autocomplete" value="" />
            </span>
          </div>
        </th>

        <td colspan="3">
          <ul class="tags" id="ex_field_tags_{{$ex_field->_id}}">
            {{foreach from=$ex_field->_ref_ex_class_field_tag_items item=_tag_item}}
              <li class="tag" style="border-color: firebrick;">
                {{$_tag_item->_tag->getName()}}
                <button type="button" class="delete" data-tag="{{$_tag_item->tag}}"
                        onclick="window.fieldTagTokenField.remove(this.get('tag'));"></button>
              </li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/if}}

    {{if $error}}
      <tr>
        <td></td>
        <td colspan="3">
          <div id="regenerate_{{$ex_field->name}}">
            <div class="small-error">
              {{tr}}common-error-Field not exists in base{{/tr}}
              <br/>
              <button class="reboot" type="button" onclick="ExClass.regenerateField('{{$ex_field->name}}');">
                {{tr}}CExClassField.regenerate-field{{/tr}}
              </button>
            </div>
          </div>

        </td>
      </tr>
    {{/if}}

    <tr>
      <td></td>
      <td colspan="3">
        <button style="float: right;" type="button" class="trash opacity-50"
                onclick="confirmDeletion(this.form,{msg:'--- ATTENTION --- \n\nSouhaitez-vous réellement supprimer le champ {{$ex_field->_view|smarty:nodefaults|JSAttribute}} ?\nCette action entraînera la suppression de toutes ses saisies associées, ainsi que son historique.\n\nConfirmez-vous tout de même ', ajax:true,typeName:'',objName:''}, {onComplete: ExClass.edit.curry('{{$ex_class->_id}}')})">
          {{tr}}Delete{{/tr}}
        </button>

        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_field->_id}}
          {{if $ex_field->disabled}}
            <button type="button" class="change"
                    onclick="$V(this.form.elements.disabled, 0); onSubmitFormAjax(this.form, ExClass.edit.curry('{{$ex_class->_id}}'))">
              {{tr}}Enable{{/tr}}
            </button>
          {{else}}
            <button type="button" class="trash"
                    onclick="if(confirm('Voulez-vous désactiver ce champ ?')){
                      $V(this.form.elements.disabled, 1);
                      var field_spec_form = getForm('editFieldSpec');
                      if (field_spec_form) {
                        $V(field_spec_form.elements.notNull, 0);
                      }
                     onSubmitFormAjax(this.form, ExClass.edit.curry('{{$ex_class->_id}}'));
                    }">
              {{tr}}Disable{{/tr}}
            </button>
          {{/if}}
        {{/if}}
      </td>
    </tr>

    <tr {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none"{{/if}}>
      <th></th>
      <td colspan="3">
        {{mb_field object=$ex_field field=prop readonly="readonly" size=50}}
      </td>
    </tr>
  </table>
</form>

<ul class="control_tabs small" id="ExClassField-param">
  <li><a href="#fieldSpecEditor">Propriétés</a></li>

  {{if $ex_field->_id}}
    <li>
      <a href="#fieldProperties" {{if !$ex_field->_ref_properties|@count}} class="empty" {{/if}} >
        {{tr}}CExClassField-back-properties{{/tr}}
        <small>({{$ex_field->_ref_properties|@count}})</small>
      </a>
    </li>

    {{assign var=_spec_type value=$ex_field->_spec_object->getSpecType()}}
    {{assign var=_can_formula value='Ox\Mediboard\System\Forms\CExClassField::formulaCanResult'|static_call:$_spec_type}}

    {{if $_can_formula}}
      <li>
        <a href="#fieldFormulaEditor" {{if !$ex_field->formula}} class="empty" {{/if}}
           style="background-image: url(style/mediboard_ext/images/buttons/formula.png); background-repeat: no-repeat; background-position: 2px 2px; padding-left: 18px;">
          Formule / concaténation
        </a>
      </li>
    {{/if}}

    <li>
      <a href="#fieldPredicates" {{if $ex_field->_ref_predicates|@count == 0}} class="empty" {{/if}}>
        {{tr}}CExClassField-back-predicates{{/tr}}
        <small>({{$ex_field->_ref_predicates|@count}})</small>
      </a>
    </li>
  {{/if}}
</ul>

<div id="fieldSpecEditor" style="white-space: normal; display: none;"></div>

<div id="fieldFormulaEditor" style="display: none;">
  {{if !$ex_field->_id}}
    Enregistrez le champ pour modifier sa formule
  {{/if}}
</div>

<div id="fieldPredicates" style="display: none;">
  <div class="small-info">
    Les prédicats permettent de définir des conditions d'affichage d'autres champs en
    fonction de la valeur du champ actuellement en cours de modification (<strong>{{$ex_field}}</strong>).<br />
    Il est possible, en plus de les créer ici, de les créer lors de la modification de l'autre champ.
  </div>

  {{if $ex_field->_id}}
    <button class="new"
            onclick="ExFieldPredicate.create('{{$ex_field->_id}}')">{{tr}}CExClassFieldPredicate-title-create{{/tr}}</button>

    <table class="main tbl">
      <tr>
        <th class="narrow"></th>
        <th style="width: 50%;">{{mb_title class=CExClassFieldPredicate field=operator}}</th>
        <th>{{mb_title class=CExClassFieldPredicate field=_value}}</th>
      </tr>

      {{foreach from=$ex_field->_ref_predicates item=_predicate}}
        <tr>
          <td>
            <button class="edit notext compact" onclick="ExFieldPredicate.edit({{$_predicate->_id}})">{{tr}}Edit{{/tr}}</button>
          </td>

          <td style="text-align: right;">{{mb_value object=$_predicate field=operator}}</td>

          <td>
            {{if $_predicate->operator != "hasValue" && $_predicate->operator != "hasNoValue"}}
              {{mb_value object=$_predicate field=_value}}
            {{else}}
              <div class="empty">N/A</div>
            {{/if}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="3" class="empty">
            {{tr}}CExClassFieldPredicate.none{{/tr}}
          </td>
        </tr>
      {{/foreach}}
    </table>
  {{/if}}
</div>

<div id="fieldProperties" style="display: none;">
  {{mb_include module=forms template=inc_list_entity_properties object=$ex_field}}
</div>
