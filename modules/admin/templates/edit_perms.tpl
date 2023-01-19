{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=class_indexer ajax=true}}
{{mb_script module="system" script="object_selector"}}

<script>
  cancelObject = function () {
    var oForm = document.editPermObj;
    oForm.object_id.value = "";
    oForm._object_view.value = "";
    oForm.autocomplete_input.value = "";
    oForm.object_class.value = "";
  };

  LoadListExistingRights = function () {
    var url = new Url("admin", "ajax_list_perms");
    url.requestUpdate('result_list_perms');
  };

  Main.add(function () {
    Control.Tabs.create('tab_permissions', true);

    const form = getForm('editPermObj');

    ClassIndexer.autocomplete(form.autocomplete_input, form.object_class);

    LoadListExistingRights();
  });
</script>

<table class="main me-no-align">
  <tr>
    <th class="title me-line-height-12" colspan="2">
      {{if $user->template}}
        {{tr}}CUser-User profile{{/tr}} '{{$user}}'
      {{else}}
        {{tr}}CUser-template.user{{/tr}} '{{$user}}'
        &mdash; {{tr}}CUser-based on{{/tr}}
        {{if $profile->_id}}
          {{tr}}CUser-the profile{{/tr}}
          <a class="button edit" href="?m=admin&tab=vw_edit_users&user_id={{$profile->_id}}&tab_name=edit_perms">
            {{$profile->user_username}}
          </a>
          <br />
          ({{$profile->_user_type_view}})
        {{else}}
          {{tr}}CUser-no profile{{/tr}}
        {{/if}}
      {{/if}}
    </th>
  </tr>
  <tr>
    <td>
      <table class="form me-no-align me-margin-0 me-padding-0 me-no-box-shadow">
        <tr>
          <td class="separator expand me-padding-0" onclick="MbObject.toggleColumn(this, $(this).next())"></td>
          <td class="me-padding-0">
          <table class="form me-no-box-shadow me-margin-0 me-padding-0">
            {{*Module*}}
            <td class="halfPane me-padding-0">
              <fieldset>
                <legend>{{tr}}modules{{/tr}}</legend>

                <form name="EditCPermModule" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_key object=$permModule}}

                  <input type="hidden" name="m" value="admin" />
                  <input type="hidden" name="dosql" value="do_perm_module_aed" />
                  <input type="hidden" name="callback" value="LoadListExistingRights"/>
                  <input type="hidden" name="user_id" value="{{$user->user_id}}" />
                  <input type="hidden" name="perm_module_id" value="" />
                  <input type="hidden" name="element_id" />

                  <input type="hidden" name="@token" value="{{$token_perm_module}}" />

                  <table class="main form me-small-form me-no-box-shadow me-margin-top-0">
                    <tr>
                      <th>{{tr}}CPermObject-object_id{{/tr}}</th>
                      <td>
                        <select name="mod_id">
                          {{if !$isAdminPermSet}}
                            <option value="">{{tr}}CPermObject-General right|pl{{/tr}}</option>
                          {{/if}}
                          {{foreach from=$modulesInstalled_trad key=module_id item=module_name}}
                            <option value="{{$module_id}}">
                              {{$module_name|smarty:nodefaults}}
                            </option>
                          {{/foreach}}
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$permModule field=permission}}</th>
                      <td>
                        {{mb_field object=$permModule field=permission}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$permModule field=view}}</th>
                      <td>
                        {{mb_field object=$permModule field=view}}
                      </td>
                    </tr>
                    <tr>
                      <th></th>
                      <td>
                        <button class="new" type="submit">{{tr}}Add{{/tr}}</button>
                      </td>
                    </tr>
                  </table>
                </form>
              </fieldset>
            </td>

            {{*Object*}}
            <td class="halfPane me-padding-0">
              <fieldset>
                <legend>{{tr}}CMbObject|pl{{/tr}}</legend>

                <form name="editPermObj" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_key object=$permObject}}

                  <input type="hidden" name="m" value="admin" />
                  <input type="hidden" name="dosql" value="do_perm_object_aed" />
                  <input type="hidden" name="callback" value="LoadListExistingRights"/>
                  <input type="hidden" name="user_id" value="{{$user->user_id}}" />
                  <input type="hidden" name="perm_object_id" value="" />
                    {{mb_field object=$permObject field=object_class hidden=true canNull=true onchange=form.onsubmit()}}

                  <input type="hidden" name="@token" value="{{$token_perm_object}}" />

                  <table class="main form me-small-form me-no-box-shadow me-margin-top-0">
                    <tr>
                      <th>{{tr}}CPermObject-object_class{{/tr}}</th>
                      <td>
                        <input type="text" name="autocomplete_input" size="40">
                      </td>
                    </tr>
                    <tr>
                      <th>Objet particulier</th>
                      <td class="button readonly" style="text-align: left;">
                        <input type="text" name="_object_view" value="" readonly="readonly" />
                        <input type="hidden" name="object_id" value="" />
                        <button type="button" class="search me-tertiary" onclick="ObjectSelector.init()">
                          {{tr}}common-action-Search object{{/tr}}
                        </button>
                        <button type="button" class="cancel me-tertiary me-dark" onclick="cancelObject()">
                          {{tr}}CPermObject-action-No object{{/tr}}
                        </button>
                        <script type="text/javascript">
                          ObjectSelector.init = function(){
                            this.sForm     = "editPermObj";
                            this.sId       = "object_id";
                            this.sView     = "_object_view";
                            this.sClass    = "object_class";
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
                        <button class="new" type="submit">{{tr}}Add{{/tr}}</button>
                      </td>
                    </tr>
                  </table>
                </form>
              </fieldset>
            </td>
          </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<div id="result_list_perms"></div>
