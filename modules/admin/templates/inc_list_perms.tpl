{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=modname value=""}}

<script>
  showObjectsClass = function (mod_name, user_id) {
    var url = new Url('admin', "vw_objects_class");
    url.addParam('mod_name', mod_name);
    url.addParam('user_id', user_id);
    url.requestModal("40%", null);
  };

  enableFormElements = function (element, name) {
    if (!element.checked) {
      $$("tr." + name).each(function (tr) {
        tr.hide();
      });
    } else {
      $$("tr." + name).each(function (tr) {
        tr.show();
      });
    }

    localStorageCreate();
  };

  localStorageCreate = function () {
    var checkboxes = $$('input.check');
    var checkboxList = [];

    checkboxes.each(function (elt) {
      checkboxList.push(elt.checked);
    });

    store.set("tabcheck", checkboxList);
  };

  localStorageGet = function () {
    var values = store.get("tabcheck");

    if (values[0] == false) {
      $("module").checked = values[0];
      $$("tr.module").each(function (tr) {
        tr.hide();
      });
    }

    if (values[1] == false) {
      $("type_objet").checked = values[1];
      $$("tr.type_objet").each(function (tr) {
        tr.hide();
      });
    }

    if (values[2] == false) {
      $("objet").checked = values[2];
      $$("tr.objet").each(function (tr) {
        tr.hide();
      });
    }
  };

  Main.add(function () {
    $("listPermsDiv").fixedTableHeaders();
    localStorageGet();
  });
</script>

<div id="listPermsDiv">
  <table class="tbl me-no-align">
    <tbody>
    {{***Module***}}
    {{foreach from=$modules key=key_module item=_module}}
      {{assign var=obj_class_name value=""}}
      {{assign var=_module_id value=$_module->_id}}

      {{if !$_module_id}}
        {{assign var=_module_id value=$key_module}}
      {{/if}}

      {{assign var=_permsModule value=$permsModule.$_module_id}}
      {{assign var=modname value=$_module->mod_name}}
      <tr class="module">
        <td class="text">
          {{if $_module->_id && $key_module != "all"}}
            <button class="search" style="float: right;"
                    onclick="showObjectsClass('{{$modname}}', '{{$user->user_id}}');">
              {{tr}}CPermObject-object_class|pl{{/tr}}
            </button>
            <i class="fa fa-folder" aria-hidden="true">  {{tr}}CPermModule-mod_id{{/tr}} -</i>
            <strong>{{tr}}module-{{$modname}}-court{{/tr}}</strong>
            <div class="compact">{{tr}}module-{{$modname}}-long{{/tr}}</div>
          {{else}}
            <i class="fa fa-folder" aria-hidden="true"> </i>
            <strong>{{tr}}CModule.all{{/tr}}</strong>
          {{/if}}
        </td>

        {{if isset($_permsModule.module|smarty:nodefaults)}}
          {{if isset($_permsModule.module.profil|smarty:nodefaults)}}
            {{assign var=owner value=profil}}
            {{assign var=_perm value=$_permsModule.module.profil}}
            {{mb_include module=admin template=inc_perms_modules}}
          {{else}}
            <td>
              &ndash;
              <div style="float: right;">
                {{mb_include module=system template=inc_object_history}}
              </div>
            </td>
          {{/if}}
          {{if isset($_permsModule.module.user|smarty:nodefaults)}}
            {{assign var=_perm value=$_permsModule.module.user}}
            {{assign var=owner value=user}}
            {{mb_include module=admin template=inc_perms_modules}}
          {{else}}
            <td>
              &ndash;
              <div style="float: right;">
                {{mb_include module=system template=inc_object_history}}
              </div>
              <div style="float: right; margin: 0 1em;">
                <button class="add notext"
                        onclick="Modal.open('save_module_new-{{$_module->_id}}', {width: 500, showClose: true} );">{{tr}}Add{{/tr}}</button>
                <div id="save_module_new-{{$_module->_id}}" style="display: none;">
                  NOUVEAU
                  <form name="EditCPermModule-{{$_module->_id}}" action="?" method="post"
                        onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); LoadListExistingRights(); } );">
                    {{mb_key object=$permModule}}

                    <input type="hidden" name="m" value="admin" />
                    <input type="hidden" name="dosql" value="do_perm_module_aed" />
                    <input type="hidden" name="user_id" value="{{$user->user_id}}" />
                    <input type="hidden" name="perm_module_id" value="" />
                    <input type="hidden" name="mod_id" value="{{$_module->_id}}" />
                    <input type="hidden" name="element_id" />

                    <input type="hidden" name="@token" value="{{$token_perm_module_item}}" />

                    <table class="tbl">
                      <div>
                        <tr>
                          <th class="title" colspan="2">{{tr}}CPermModule-mod_id{{/tr}}
                            - {{tr}}module-{{$_module->mod_name}}-court{{/tr}}</th>
                        </tr>
                        <tr>
                          <th>{{mb_label object=$permModule field=permission}}</th>
                          <th>{{mb_label object=$permModule field=view}}</th>
                        </tr>
                        <tr class="button">
                          <td>
                            {{mb_field object=$permModule field=permission}}
                          </td>
                          <td>
                            {{mb_field object=$permModule field=view}}
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="button">
                            <button class="new" type="button" onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
                          </td>
                        </tr>
                      </div>
                    </table>
                  </form>
                </div>
              </div>
            </td>
          {{/if}}
        {{else}}
          <td>
            &ndash;
            <div style="float: right;">
              {{mb_include module=system template=inc_object_history}}
            </div>
          </td>
          <td>
            {{if !isset($_permsModule.module|smarty:nodefaults)}}
              &ndash;
              <div style="float: right;">
                {{mb_include module=system template=inc_object_history}}
              </div>
              <div style="float: right; margin: 0 1em;">
                <button class="add notext"
                        onclick="Modal.open('save_module_new-{{$_module->_id}}', {width: 500, showClose: true} );">{{tr}}Add{{/tr}}</button>
                <div id="save_module_new-{{$_module->_id}}" style="display: none;">
                  <form name="EditCPermModule-{{$_module->_id}}" action="?" method="post"
                        onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); LoadListExistingRights(); } );">
                    {{mb_key object=$permModule}}

                    <input type="hidden" name="m" value="admin" />
                    <input type="hidden" name="dosql" value="do_perm_module_aed" />
                    <input type="hidden" name="user_id" value="{{$user->user_id}}" />
                    <input type="hidden" name="perm_module_id" value="" />
                    <input type="hidden" name="mod_id" value="{{$_module->_id}}" />
                    <input type="hidden" name="element_id" />

                    <input type="hidden" name="@token" value="{{$token_perm_module_item}}" />

                    <table class="tbl">
                      <div>
                        <tr>
                          <th class="title" colspan="2">{{tr}}CPermModule-mod_id{{/tr}}
                            - {{tr}}module-{{$_module->mod_name}}-court{{/tr}}</th>
                        </tr>
                        <tr>
                          <th>{{mb_label object=$permModule field=permission}}</th>
                          <th>{{mb_label object=$permModule field=view}}</th>
                        </tr>
                        <tr class="button">
                          <td>
                            {{mb_field object=$permModule field=permission}}
                          </td>
                          <td>
                            {{mb_field object=$permModule field=view}}
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="button">
                            <button class="new" type="button" onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
                          </td>
                        </tr>
                      </div>
                    </table>
                  </form>
                </div>
              </div>
            {{/if}}
          </td>
        {{/if}}
      </tr>
      {{if isset($_permsModule.object|smarty:nodefaults)}}
        {{foreach from=$_permsModule.object item=_perms_by_owner}}
          {{if isset($_perms_by_owner.profil|smarty:nodefaults)}}
            {{assign var=_perm value=$_perms_by_owner.profil}}
          {{else}}
            {{assign var=_perm value=$_perms_by_owner.user}}
          {{/if}}
          <tr class="type_objet">
            {{if $obj_class_name != $_perm->object_class}}
              <td class="text">
                <div>
                  {{if $obj_class_name != $_perm->object_class}}
                    {{assign var=obj_class_name value=$_perm->object_class}}

                    &boxur;
                    <i class="fa fa-users" aria-hidden="true">  {{tr}}CPermObject-object_class{{/tr}} -</i>
                    <strong>{{tr}}{{$_perm->object_class}}{{/tr}}</strong>
                  {{/if}}
                </div>
              </td>
              {{if isset($_perms_by_owner.profil|smarty:nodefaults) && !$_perm->object_id}}
                {{assign var=_perm value=$_perms_by_owner.profil}}
                {{assign var=owner value=profil}}
                {{mb_include module=admin template=inc_perms_objects}}
              {{else}}
                <td>
                  &ndash;
                  <div style="float: right;">
                    {{mb_include module=system template=inc_object_history}}
                  </div>
                </td>
              {{/if}}
              {{if isset($_perms_by_owner.user|smarty:nodefaults) && !$_perm->object_id}}
                {{assign var=_perm value=$_perms_by_owner.user}}
                {{assign var=owner value=user}}
                {{mb_include module=admin template=inc_perms_objects}}
              {{else}}
                <td>
                  &ndash;
                  <div style="float: right;">
                    {{mb_include module=system template=inc_object_history}}
                  </div>
                  <div style="float: right; margin: 0 1em;">
                    <button class="add notext"
                            onclick="Modal.open('save_object_new-{{$_perm->object_class}}', {width: 500, showClose: true} );">{{tr}}Add{{/tr}}</button>
                    <div id="save_object_new-{{$_perm->object_class}}" style="display: none;">
                      {{mb_script module="system" script="object_selector"}}

                      {{unique_id var=uid}}

                      <form name="editPermObject{{$_perm->_id}}_{{$uid}}" method="post"
                            onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); LoadListExistingRights(); });">
                        {{mb_key object=$_perm}}

                        <input type="hidden" name="m" value="admin" />
                        <input type="hidden" name="dosql" value="do_perm_object_aed" />
                        <input type="hidden" name="user_id" value="{{$user->user_id}}" />
                        <input type="hidden" name="perm_object_id" value="" />

                        <input type="hidden" name="@token" value="{{$token_perm_object_item}}" />

                        <table class="form">
                          <tr>
                            <th class="title" colspan="2">{{tr}}CPermObject-object_class{{/tr}}
                              - {{tr}}{{$_perm->object_class}}{{/tr}}</th>
                          </tr>
                          <tr>
                            <th>{{tr}}CPermObject-Particular object{{/tr}}</th>
                            <td class="button readonly" style="text-align: left;">
                              <input type="text" name="_object_view" value="" readonly="readonly" />
                              <input type="hidden" name="object_id" value="" />
                              <input type="hidden" name="object_class" value="{{$_perm->object_class}}" />
                              <button type="button" class="search" onclick="ObjectSelector.init{{$_perm->_id}}();">
                                {{tr}}common-action-Search object{{/tr}}
                              </button>
                              <script>
                                ObjectSelector.init{{$_perm->_id}} = function () {
                                  this.sForm = "editPermObject{{$_perm->_id}}_{{$uid}}";
                                  this.sId = "object_id";
                                  this.sView = "_object_view";
                                  this.sClass = "object_class";
                                  this.onlyclass = "false";
                                  this.pop();
                                }
                              </script>
                            </td>
                          </tr>
                          <tr>
                            <th>{{mb_label object=$permModule field=permission}}</th>
                            <td>
                              {{mb_field object=$permObject field=permission}}
                            </td>
                          </tr>
                          <tr>
                            <th></th>
                            <td>
                              <button class="new" type="button" onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
                            </td>
                          </tr>
                        </table>
                      </form>
                    </div>
                  </div>
                </td>
              {{/if}}
            {{/if}}
          </tr>
          {{if $_perm->object_id}}
            <tr class="objet">
            <td class="text">
              <div
                style="{{if $_perm->object_id && $obj_class_name && $obj_class_name != $_perm->object_class}}margin-left: 1em;{{/if}}">

                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_perm->_ref_db_object->_guid}}')" style="margin-left: 12px;">
                      &boxur;
                      <i class="fa fa-user" aria-hidden="true">  {{tr}}CPermObject-object_id{{/tr}} -</i>
                      {{$_perm->_ref_db_object->_view}}
                    </span>
              </div>
            </td>
          {{/if}}
          {{if $_perm->object_id}}
            {{*profil*}}
            {{if isset($_perms_by_owner.profil|smarty:nodefaults)}}
              {{assign var=_perm value=$_perms_by_owner.profil}}
              {{assign var=owner value=profil}}
              {{mb_include module=admin template=inc_perms_objects}}
            {{else}}
              <td>
                &ndash;
                <div style="float: right;">
                  {{mb_include module=system template=inc_object_history}}
                </div>
              </td>
            {{/if}}
            {{if isset($_perms_by_owner.user|smarty:nodefaults)}}
              {{assign var=_perm value=$_perms_by_owner.user}}
              {{assign var=owner value=user}}
              {{mb_include module=admin template=inc_perms_objects}}
            {{else}}
              <td>
                &ndash;
                <div style="float: right;">
                  {{mb_include module=system template=inc_object_history}}
                </div>
              </td>
            {{/if}}
          {{/if}}
          </tr>
        {{/foreach}}
      {{/if}}
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CPermModule.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    </tbody>

    <thead>
    <tr>
      <th class="title me-padding-top-2 me-padding-bottom-2" colspan="3">
        {{tr}}CUser-Existing right|pl{{/tr}}
        <span style="float: right;">
          {{tr}}modules{{/tr}}<input type="checkbox" id="module" class="check" name="module"
                                     onclick="enableFormElements(this, 'module');" checked />
          {{tr}}CPermObject-object_class|pl{{/tr}}<input type="checkbox" id="type_objet" class="check" name="type_objet"
                                                         onclick="enableFormElements(this, 'type_objet');" checked />
          {{tr}}CMbObject|pl{{/tr}}<input type="checkbox" class="check" id="objet" name="objet"
                                          onclick="enableFormElements(this, 'objet');" checked />
        </span>
      </th>
    </tr>
    <tr>
      <th width="60%">{{tr}}CUser-Context{{/tr}}</th>
      <th>{{tr}}CUser-template{{/tr}}</th>
      <th>{{tr}}CUser-template.user{{/tr}}</th>
    </tr>
    </thead>
  </table>
</div>
