{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hermetic_mode value='Ox\Mediboard\System\Forms\CExClass::inHermeticMode'|static_call:false}}

{{if $object->_id}}
  <button type="button" class="new" onclick="MbObject.edit('{{$object->_class}}-0')">
    {{tr}}{{$object->_class}}-title-create{{/tr}}
  </button>
{{/if}}

{{mb_script module=forms script=ex_class_editor ajax=true}}

<script type="text/javascript">
  switchType = function (select) {
    $$(".switch-type").each(function (type) {
      type.hide().disableInputs();
    });

    $("switch-type-" + $V(select)).show().enableInputs();

    var form = getForm("edit-{{$object->_guid}}");
    ExConceptSpec.edit(form);
  };

  selectList = function (input) {
    if (!$V(input.form.elements.name)) {
      $V(input.form.elements.name, input.form.ex_list_id_autocomplete_view.value);
    }

    ExConceptSpec.edit(input.form);
  };

  clearList = function (input) {
    var form = input.form;

    $V(form.elements.ex_list_id, '', true);
    $V(form.elements.ex_list_id_autocomplete_view, '');
  };

  updateEnumToSetSpec = function (form) {
    Modal.confirm(
      $T('CEXConcept-confirm-Do you really want to update this object? This operation is irreversible.'),
      {
        onOK: function () {
          var prop = $V(form.elements.prop);

          new_prop = prop.replace(/^enum/, 'set').replace(/typeEnum\|(radio|select)/, 'typeEnum|checkbox');

          if (new_prop) {
            $V(form.elements.prop, new_prop);
            form.onsubmit();
          }
        }
      }
    );
  };

  Main.add(function () {
    var form = getForm("edit-{{$object->_guid}}");
    ExConceptSpec.edit(form);
    Control.Tabs.create("ex-concept-tabs", true);
    switchType(form._concept_type);

    {{if $conf.forms.CExConcept.native_field && !$object->_id}}
    var url = new Url("forms", "ajax_autocomplete_native_fields");
    url.autoComplete(form.elements._native_field_view, null, {
      minChars:           2,
      method:             "get",
      dropdown:           true,
      //width: "550px",
      afterUpdateElement: function (field, selected) {
        var elements = field.form.elements;
        $V(elements.native_field, selected.get("value"));
        $V(elements._native_field_view, selected.down(".view").getText().replace(/\s+/g, ' ').strip());

        if ($V(elements.name) == "") {
          $V(elements.name, selected.get("title"));
        }

        var spec_type = selected.get("prop").split(/ /g)[0];

        if (spec_type === 'enum') {
          var list = selected.get("prop").split(/ list\|([^\s]*)\s?/g)[1];

          if (list) {
            list = list.split(/\|/g);

            var msg = [
              printf($T("CExConcept-msg-In order to integrate MB enum type host field %s, a coded list must be present:", selected.get('title')))
            ];

            list.forEach(function(item) {
              var translated_item = $T(selected.get('field').replace(/-/g, '.') + '.' + item);

              msg.push(
                printf('%s : %s', item, translated_item)
              );
            });

            $('switch-msg').update(DOM.div({className: 'small-info'}, msg.join('<br />'))).show();
          }

          $V(elements._concept_type, 'list');
        }
        else {
          $V(elements._concept_type, "custom");
          $('switch-msg').update('').hide();
        }

        $V(elements._spec_type, spec_type);
      }
    });
    {{/if}}

      {{if !$object->_id}}
        var list_autocomplete = new Url('forms', 'ajax_autocomplete_ex_list');
        list_autocomplete.addParam("input_field", form.ex_list_id_autocomplete_view.name);
        list_autocomplete.autoComplete(form.ex_list_id_autocomplete_view, null, {
          minChars:      2,
          dropdown:      true,
          method:        'get',
          callback:      function (input, queryString) {
            var group_id = $V(form.elements.group_id);

            // Standard mode
            if (group_id === undefined) {
              return queryString;
            }

            return queryString + '&group_id=' + group_id;
          },
          updateElement: function (selected) {
            var list_id = selected.get('id');

            if (list_id) {
              $V(form.elements.ex_list_id_autocomplete_view, selected.down('span').getText().strip());

              $V(form.elements.ex_list_id, list_id, true);
            }
          }
        });
      {{/if}}
  });
</script>

<form name="edit-{{$object->_guid}}" data-object_guid="{{$object->_guid}}" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$object}}
  {{mb_key object=$object}}

  <input type="hidden" name="callback" value="MbObject.editCallback" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header css_class="text" colspan=2}}

    {{if $object->_id}}
      {{mb_include module=system template=inc_tag_binder colspan=2}}
    {{/if}}

    <tr>
      <th class="narrow">{{mb_label object=$object field=name}}</th>
      <td>{{mb_field object=$object field=name size=40}}</td>
    </tr>

    <tr>
      <th>
          {{mb_label object=$object field=group_id}}
      </th>

      <td>
          {{if $hermetic_mode}}
              {{if $object->_id}}
                  {{mb_value object=$object field=group_id tooltip=true form="edit-`$object->_guid`"}}
              {{else}}
                <div style="display: inline-block;">
                  <select name="group_id" style="width: 20em;" onchange="clearList(this);">
                      {{if $hermetic_mode && $app->_ref_user->isAdmin()}}
                        <option value=""> &ndash; Tous </option>
                      {{/if}}

                      {{foreach from=$object->_groups item=_group}}
                        <option value="{{$_group->_id}}" {{if $g == $_group->_id}} selected="selected" {{/if}}>{{$_group}}</option>
                      {{/foreach}}
                  </select>
                </div>

                <div style="display: inline-block;">
                  <div class="small-warning" style="white-space: nowrap;">Cette opération est irréversible.</div>
                </div>
              {{/if}}
          {{/if}}
      </td>
    </tr>

    {{if $conf.forms.CExConcept.native_field && (!$object->_id || $object->_id && $object->native_field)}}
      <tr>
        <th>
          {{mb_label object=$object field=native_field}}
        </th>
        <td>
          {{mb_field object=$object field=native_field hidden=true}}
          <input type="text" name="_native_field_view" value="{{$object->_native_field_view}}"
                 size="60" {{if $object->_id}} disabled {{/if}} />
          <button class="cancel notext" type="button"
                  onclick="$V(this.form._native_field_view,'');$V(this.form.native_field,''); $('switch-msg').update('').hide();">
            {{tr}}Empty{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}

    <tr>
      <th>
        {{assign var=concept_type value="custom"}}

        {{if $object->ex_list_id}}
          {{assign var=concept_type value="list"}}
        {{/if}}

        {{if !$object->_id}}
          <select onchange="switchType(this)" name="_concept_type">
            <option value="list" {{if $concept_type == "list"}} selected {{/if}}>{{tr}}CExConcept-ex_list_id{{/tr}}</option>
            <option value="custom" {{if $concept_type == "custom"}} selected {{/if}}>Type</option>
          </select>
        {{else}}
          <label>
            {{if $concept_type == "list"}}{{tr}}CExConcept-ex_list_id{{/tr}}{{/if}}
            {{if $concept_type == "custom"}}Type{{/if}}
          </label>
        {{/if}}
      </th>

      <td>
        <div id="switch-msg" style="display: none;"></div>

        {{* LIST *}}
        <div class="switch-type" id="switch-type-list" {{if $concept_type != "list"}} style="display: none;" {{/if}}>
          {{if !$object->_id}}
            {{assign var=_prop value=$object->_props.ex_list_id}}
            {{mb_label object=$object field=ex_list_id style="display: none;"}}
            {{mb_field object=$object field=ex_list_id prop="$_prop notNull" form="edit-`$object->_guid`" onchange="selectList(this)" hidden=true}}

            <input type="text" name="ex_list_id_autocomplete_view" value="" />
            <button class="new" onclick="ExList.createInModal()" type="button">{{tr}}CExList-title-create{{/tr}}</button>
            <label>
              <input type="checkbox" name="_multiple" value="1" onclick="ExConceptSpec.edit(this.form)" /> Choix multiple
            </label>
          {{else}}
            {{mb_value object=$object field=ex_list_id}}
            {{mb_field object=$object field=ex_list_id hidden=true}}
          {{/if}}
        </div>

        {{* CUSTOM *}}
        <div class="switch-type" id="switch-type-custom" {{if $concept_type != "custom"}} style="display: none;" {{/if}}>
          {{assign var=spec_type value=$object->_concept_spec->getSpecType()}}

          {{if !$object->_id}}
            <select name="_spec_type" onchange="ExConceptSpec.edit(this.form)">
              {{foreach from='Ox\Mediboard\System\Forms\CExClassField::getTypes'|static_call:null key=_key item=_class}}
                {{if !$conf.forms.CExConcept.force_list || ($_key != "enum" && $_key != "set")}}
                  <option value="{{$_key}}" {{if $_key == $spec_type && !$object->ex_list_id}}selected="selected"{{/if}}>
                    {{tr}}CMbFieldSpec.type.{{$_key}}{{/tr}}
                  </option>
                {{/if}}
              {{/foreach}}
            </select>
          {{else}}
            <input type="hidden" name="_spec_type" value="{{$spec_type}}" />
            {{if !$object->ex_list_id}}
              {{tr}}CMbFieldSpec.type.{{$spec_type}}{{/tr}}
            {{/if}}
          {{/if}}
        </div>
      </td>
    </tr>

    <tr>
      <th></th>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $object->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$object->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>

          {{assign var=_props value=' '|@explode:$object->prop}}
          {{assign var=_prop value=$_props|@first}}

          {{if $_prop == 'enum' && $object->_concept_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && !$object->_concept_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec'}}
            <button type="button" class="far fa-check-square" onclick="updateEnumToSetSpec(this.form);">
              {{tr}}CEXConcept-action-Transform to multiple choice|pl{{/tr}}
            </button>
          {{/if}}
        {{/if}}
      </td>
    </tr>

    <tr {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none;"{{/if}}>
      <th></th>
      <td>
        {{mb_field object=$object field=prop readonly=true size=80}}
      </td>
    </tr>
  </table>
</form>

<ul id="ex-concept-tabs" class="control_tabs me-align-auto">
  <li>
    <a href="#ExConcept-spec-editor">Paramètres</a>
  </li>
  {{if $object->_id}}
    <li>
      <a href="#ex-back-class_fields" {{if $object->_back.class_fields|@count == 0}} class="empty" {{/if}}>
        {{tr}}CExConcept-back-class_fields{{/tr}}
        <small>({{$object->_back.class_fields|@count}})</small>
      </a>
    </li>
  {{/if}}
</ul>

<div id="ExConcept-spec-editor" style="display: none;" class="me-align-auto me-padding-0"></div>

{{if $object->_id}}
  <div id="ex-back-class_fields" style="display: none;" class="me-align-auto me-padding-0">
    <table class="main tbl me-no-align me-no-box-shadow">
      <tr>
        <th>{{tr}}CExClass{{/tr}}</th>
        <th>{{tr}}CExClassFieldGroup{{/tr}}</th>
        <th>{{tr}}CExClassField{{/tr}}</th>
      </tr>

      {{foreach from=$object->_back.class_fields item=_field}}
        <tr>
          <td>
            {{mb_value object=$_field->_ref_ex_group->_ref_ex_class field=name}}
          </td>
          <td>
            {{mb_value object=$_field->_ref_ex_group field=name}}
          </td>
          <td>
            {{mb_value object=$_field field=_locale}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td class="empty" colspan="3">{{tr}}CExClassField.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
{{/if}}
