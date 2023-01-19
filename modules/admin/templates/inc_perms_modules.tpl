{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  PermConfirmDeletion = function (form, module) {
    Modal.confirm($T('CPermModule-action-Delete this module: %s ?', module),
      {onOK: function () {
        $V(form.del, 1);

        return onSubmitFormAjax(form, {onComplete: function () {
          Control.Modal.close();
          LoadListExistingRights();
        }});
      }});

    return false;
  }
</script>

<td style="width: 20%;">
  {{mb_value object=$_perm field=permission}} / {{mb_value object=$_perm field=view}}
  <div style="float: right">
    {{mb_include module=system template=inc_object_history object=$_perm}}
  </div>
  {{if $owner == "user"}}
    <div style="float: right; margin: 0 1em;" class="me-margin-0">
      <button class="edit notext" type="button"
        onclick="Modal.open('save_perm-{{$_perm->_guid}}', {width: 500, showClose: true} );">{{tr}}Edit{{/tr}}</button>
      <div id="save_perm-{{$_perm->_guid}}" style="display: none;">
        <form name="Edit-{{$_perm->_guid}}" action="?m={{$m}}" method="post"
              onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); LoadListExistingRights();} );">
          {{mb_key object=$_perm}}

          <input type="hidden" name="m" value="admin" />
          <input type="hidden" name="dosql" value="do_perm_module_aed" />
          <input type="hidden" name="del" value="0" />

          <input type="hidden" name="@token" value="{{$token_perm_module_item}}" />

          <table class="form">
            <div style="width: 8em;">
              <tr>
                <td>
                  <table class="form">
                    <tr>
                      <th class="title" colspan="3">
                        {{if $_perm->_ref_db_module->mod_name}}
                          {{tr}}module-{{$_perm->_ref_db_module->mod_name}}-court{{/tr}}
                        {{else}}
                          {{tr}}module-All modules-court{{/tr}}
                        {{/if}}
                      </th>
                    </tr>
                    <tr>
                      <th>
                        {{mb_label object=$_perm field=permission}}
                      </th>
                      <th>
                        {{mb_label object=$_perm field=view}}
                      </th>
                      <th></th>
                    </tr>
                    <tr>
                      <td>
                        {{if $owner == "user"}}
                          {{mb_field object=$_perm field=permission}}
                        {{else}}
                          <span style="padding: 0 6px;">
                            {{mb_value object=$_perm field=permission}}
                           </span>
                        {{/if}}
                      </td>
                      <td>
                        {{if $owner == "user"}}
                          {{mb_field object=$_perm field=view}}
                        {{else}}
                          <span style="padding: 0 6px;">
                            {{mb_value object=$_perm field=view}}
                          </span>
                        {{/if}}
                      </td>
                      {{if $owner == "user"}}
                        {{if !$modname}}
                          {{assign var=modname value="All modules"}}
                        {{/if}}
                        <td rowspan="2" class="narrow">
                    <span style="margin: 0 1em;">
                     <button class="modify notext" type="submit">{{tr}}common-action-Save{{/tr}}</button>
                      <button class="trash notext" type="button"
                              onclick="PermConfirmDeletion(this.form, '{{$modname}}');">{{tr}}common-action-Delete{{/tr}}</button>
                    </span>
                        </td>
                      {{/if}}
                    </tr>
                  </table>
                </td>
              </tr>
            </div>
          </table>
        </form>
      </div>
    {{/if}}
  </div>
</td>
