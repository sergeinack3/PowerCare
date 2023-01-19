{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=see_active_field value=true}}

{{if "oxCabinet"|module_active && ($app->user_prefs.UISTYLE === "tamm") && "oxCabinet CMediusers hide_active_field"|gconf}}
   {{assign var=see_active_field value=false}}
{{/if}}

{{assign var=can_null_deb_activite value=false}}

{{if $object->_id && !$object->deb_activite}}
    {{assign var=can_null_deb_activite value=true}}
{{/if}}

<tr>
    <th style="width:30%;">{{mb_label object=$object field="_user_username"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$object field="_user_username"}}
        {{else}}
            {{mb_value object=$object field="_user_username"}}
            {{mb_field object=$object field="_user_username" hidden=true}}
        {{/if}}
    </td>
</tr>

{{if !$readOnlyLDAP}}
    <tr>
        <th>{{mb_label object=$object field="_user_password"}}</th>
        <td>
            <input type="password" name="_user_password"
                   class="{{$object->_props._user_password}}{{if !$object->user_id}} notNull{{/if}}"
                   onkeyup="checkFormElement(this); checkMailAndPassword(this);" value=""/>

            {{mb_include module=admin template=inc_get_random_password}}

            <span id="mediuser__user_password_message"></span>
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$object field="_user_password2"}}</th>
        <td>
            <input type="password" name="_user_password2" value="" onkeyup="checkMailAndPassword(this);"
                   class="{{$object->_props._user_password2}}{{if !$object->user_id}} notNull{{/if}}"
            />
        </td>
    </tr>
{{/if}}

<tr>
    <th>{{mb_label object=$object field="_user_sexe"}}</th>
    <td>
        {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="_user_sexe"}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="color"}}</th>
    <td>{{mb_field object=$object field="color" form="mediuser"}}</td>
</tr>

{{if $see_active_field}}
  <tr>
      <th>{{mb_label object=$object field="actif"}}</th>
      <td>
          {{if !$readOnlyLDAP}}
              {{mb_field object=$object field="actif"}}
          {{else}}
              {{mb_value object=$object field="actif"}}
              {{mb_field object=$object field="actif" hidden=true}}
          {{/if}}
      </td>
  </tr>
{{/if}}

<tr>
    <th>{{mb_label object=$object field="deb_activite"}}</th>
    <td>{{mb_field object=$object field="deb_activite" canNull=$can_null_deb_activite|smarty:nodefaults form="mediuser" register=true}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="fin_activite"}}</th>
    <td>{{mb_field object=$object field="fin_activite" form="mediuser" register=true}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="activite"}}</th>
    <td>
        {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="activite"}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="use_bris_de_glace"}}</th>
    <td>{{mb_field object=$object field="use_bris_de_glace"}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="remote"}}</th>
    <td>{{mb_field object=$object field="remote"}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field=_force_change_password}}</th>
    <td>{{mb_field object=$object field=_force_change_password}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field=_allow_change_password}}</th>
    <td>{{mb_field object=$object field=_allow_change_password}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="function_id"}}</th>
    <td>
        {{mb_field object=$object field="function_id" hidden=true}}

        {{foreach from=$functions key=group_id item=_functions name=_functions}}
            <select name="function_id-CGroups-{{$group_id}}" id="function-CGroups-{{$group_id}}"
                    style="display : {{if $g == $group_id || !$g && $smarty.foreach._functions.first}}inline{{else}}none{{/if}}; width: 150px;"
                    class="select_functions"
                    onchange="$V(this.form.elements.function_id, $V(this))">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$_functions item=_function}}
                    <option class="mediuser" style="border-color: #{{$_function->color}};" value="{{$_function->_id}}"
                      {{if $_function->_id == $object->function_id}} selected="selected" {{/if}}
                    >
                        {{$_function->text}}
                    </option>
                    {{foreachelse}}
                    <option value="" disabled="disabled">
                        {{tr}}CFunctions.none{{/tr}}
                    </option>
                {{/foreach}}
            </select>
        {{/foreach}}

        {{if $object->_id && $groups|@count > 0 || !$g}}
            <select name="group_id" disabled="disabled"
                    onchange="$$('.select_functions').invoke('hide'); $('function-CGroups-'+this.value).show();
                      $V(this.form.elements.function_id, $V($('function-CGroups-'+this.value))) ">
                {{foreach from=$groups item=_group}}
                    <option value="{{$_group->_id}}"
                      {{if $_group->_id == $g}} selected="selected" {{/if}}>
                        {{$_group->_view}}
                    </option>
                {{/foreach}}
            </select>
            <button type="button" class="unlock notext"
                    onclick="this.form.elements.group_id.disabled = !(this.form.elements.group_id.disabled);"></button>
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_type"}}</th>
    <td>
        <select name="_user_type" style="width: 150px;" class="{{$object->_props._user_type}}"
                onchange="showPratInfo(this.value); loadProfil(this.value)"
          {{if !$is_admin && $object->isAdmin()}} disabled{{/if}}>
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$utypes key=curr_key item=type}}
                {{if $type !== "Patient"}}
                    <option value="{{if $curr_key != 0}}{{$curr_key}}{{/if}}"
                            {{if $curr_key == $object->_user_type}}selected{{/if}}
                      {{if (!$is_admin || !$is_admin_module) && $curr_key == 1}} disabled> {{$type}}
                        ({{tr}}CMbFieldSpec.perm{{/tr}})
                        {{else}} > {{$type}}{{/if}}
                    </option>
                {{/if}}
            {{/foreach}}
        </select>

        {{if !$is_admin && $object->isAdmin()}}
            <div class="small-info">
                {{tr}}CUser-error-You are not allowed to modify an admin user{{/tr}}
            </div>
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_profile_id"}}</th>
    <td>
        <select name="_profile_id" style="width: 150px;"
                {{if !$is_admin && !$is_admin_module && $object->user_id}}disabled{{/if}}>
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$profiles item=_profile}}
                <option
                  value="{{$_profile->user_id}}" {{if $_profile->user_id == $object->_profile_id}} selected="selected" {{/if}}>{{$_profile->user_username}}</option>
            {{/foreach}}
        </select>
        {{if !$is_admin && !$is_admin_module && $object->user_id}}
            <div style="display: inline-block;" class="info">
                {{tr}}CMbFieldSpec.perm{{/tr}}
            </div>
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_last_name"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="_user_last_name"}}
        {{else}}
            {{mb_value object=$object field="_user_last_name"}}
            {{mb_field object=$object field="_user_last_name" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_first_name"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="_user_first_name"}}
        {{else}}
            {{mb_value object=$object field="_user_first_name"}}
            {{mb_field object=$object field="_user_first_name" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="initials"}}</th>
    <td>
        {{mb_field object=$object field="initials"}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_email"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="_user_email" onkeyup='checkMailAndPassword(this);'}}
        {{else}}
            {{mb_value object=$object field="_user_email"}}
            {{mb_field object=$object field="_user_email" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_phone"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_include module=mediusers template=inc_edit_mediuser_field object=$object field="_user_phone"}}
        {{else}}
            {{mb_value object=$object field="_user_phone"}}
            {{mb_field object=$object field="_user_phone" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field=_internal_phone}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$object field=_internal_phone}}
        {{else}}
            {{mb_value object=$object field=_internal_phone}}
            {{mb_field object=$object field=_internal_phone hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field=astreinte}}</th>
    <td>{{mb_field object=$object field=astreinte}}</td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_astreinte"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$object field="_user_astreinte"}}
        {{else}}
            {{mb_value object=$object field="_user_astreinte"}}
            {{mb_field object=$object field="_user_astreinte" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="_user_astreinte_autre"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$object field="_user_astreinte_autre"}}
        {{else}}
            {{mb_value object=$object field="_user_astreinte_autre"}}
            {{mb_field object=$object field="_user_astreinte_autre" hidden=true}}
        {{/if}}
    </td>
</tr>

<tr>
    <th>{{mb_label object=$object field="commentaires"}}</th>
    <td>{{mb_field object=$object field="commentaires"}}</td>
</tr>
