{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_mb_module->_files_missing}}
  <td colspan="4" class="cancelled">
    Module '{{$_mb_module->mod_name}}' missing
  </td>
{{else}}
  {{assign var=module_name value=$_mb_module->mod_name}}
  <td class="narrow">
    {{mb_module_icon mod_name=$module_name mod_category=$_mb_module->mod_category}}
  </td>
  <td>
    <label title="{{tr}}module-{{$_mb_module->mod_name}}-long{{/tr}}">
      {{if $installed}}
      <a href="?m={{$_mb_module->mod_name}}">
        {{/if}}
        <strong>{{tr}}module-{{$_mb_module->mod_name}}-court{{/tr}}</strong>
        {{if $installed}}
      </a>
      {{/if}}
    </label>
  </td>
  <td class="narrow">{{mb_include module=system template=inc_object_history object=$_mb_module}}</td>
  <td>{{mb_value object=$_mb_module field=mod_type}}</td>
{{/if}}

<!-- Actions -->
<td>
  {{if $_mb_module->_too_new}}
    <div class="warning">
      {{tr}}Module-_too_new-msg{{/tr}} ({{$_mb_module->_latest}})
    </div>
  {{elseif $_mb_module->_need_php_update}}
    <div class="warning" title="{{tr var1=$php_version }}Module-_need_php_update-msg-detail{{/tr}}">
      {{tr var1=$_mb_module->_mod_requires_php}}Module-_need_php_update-msg{{/tr}}
    </div>
  {{elseif $_mb_module->_upgradable && $can->admin}}
    <table class="layout">
      <tr>
        <td style="padding: 0;">
          <form name="formUpdateModule-{{$module_id}}" method="post" class="upgrade" data-id="{{$module_id}}"
                data-dependencies="{{$_mb_module->_dependencies_not_verified}}"
            {{if $_mb_module->mod_type != "core"}} onsubmit="return Module.updateOne(this)" {{/if}}>
            <input type="hidden" name="dosql" value="do_manage_module" />
            <input type="hidden" name="m" value="system" />
            {{if $_mb_module->mod_type != "core"}}
              <input type="hidden" name="ajax" value="1" />
            {{/if}}
            <input type="hidden" name="mod_id" value="{{$module_id}}" />
            <input type="hidden" name="cmd" value="upgrade" />
            <button class="change compact upgrade oneclick"
                    type="submit" {{* onclick="return confirm('{{tr}}CModule-confirm-upgrade{{/tr}}')" *}}>
              {{tr}}Upgrade{{/tr}} {{$_mb_module->_latest}}
            </button>
          </form>
        </td>
        <td>
          {{if $_mb_module->_update_messages && $_mb_module->_update_messages|@count}}
            <div class="warning" onmouseover="ObjectTooltip.createDOM(this, 'tooltip-message-module-{{$_mb_module->_id}}')">&nbsp;
            </div>
          {{/if}}
          <div id="tooltip-message-module-{{$_mb_module->_id}}" style="display:none;">
            <div class="big-warning">
              {{foreach from=$_mb_module->_update_messages key=_version item=_message}}
                <strong>{{$_version}}</strong>
                : {{$_message}}
                <br />
              {{/foreach}}
            </div>
          </div>
        </td>
      </tr>
    </table>
  {{elseif $_mb_module->_upgradable}}
    {{tr}}Out of date{{/tr}} : {{$_mb_module->_latest}}
  {{elseif $_mb_module->mod_type != "core" && $can->admin}}
    <form name="formDeleteModule-{{$_mb_module->mod_name}}" method="post">
      <input type="hidden" name="dosql" value="do_manage_module" />
      <input type="hidden" name="m" value="system" />
      <input type="hidden" name="cmd" value="remove" />
      <input type="hidden" name="mod_id" value="{{$module_id}}" />

      <button class="cancel compact me-tertiary" type="submit" disabled="true" onclick="return confirm('{{tr}}CModule-confirm-deletion{{/tr}}');">
        {{tr}}Remove{{/tr}}
      </button>
    </form>
  {{else}}
    <div class="info">
      {{tr}}Up to date{{/tr}}
    </div>
  {{/if}}
</td>

<!-- SD / Config. / Vers. / Actif / Visible / Dépendances -->
{{if $installed}}
  <td>
    {{if count($_mb_module->_dsns)}}
      {{foreach from=$_mb_module->_dsns item=dsns_by_status key=status}}
        {{foreach from=$dsns_by_status item=dsn}}
          <div class="
      {{if $status == 'uptodate'}}info{{/if}}
      {{if $status == 'obsolete'}}warning{{/if}}
      {{if $status == 'unavailable'}}error{{/if}}" title="{{$dsn.1}}">
            {{$dsn.0}}
          </div>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
  </td>

  <td class="me-text-align-center">
      {{if $_mb_module->_requirements}}
          {{assign var=requirement_title value=$_mb_module->_requirements}}
          {{assign var=requirement_title_error value=""}}
          {{if $_mb_module->_requirements_failed > 0}}
              {{assign var=nb_errors value=$_mb_module->_requirements_failed}}
              {{assign var=requirement_title_error value="($nb_errors errors)"}}
          {{/if}}
          {{assign var=requirement_title value="$requirement_title requirements $requirement_title_error"}}
        <button class="compact notext me-color-care"
                title="{{$requirement_title}}"
                {{if $_mb_module->_requirements_failed}}style="color: firebrick !important;"{{/if}}
                 onclick="new Url('system', 'vw_requirements').addParam('mod_name', '{{$_mb_module->mod_name}}').requestModal('100%', '100%')">
          <i class="fas fa-{{if $_mb_module->_requirements_failed}}times error{{else}}check{{/if}}"></i>
        </button>
      {{/if}}
  </td>
  <td>
    <!-- Configure -->
    {{if $_mb_module->_configable}}
      <a class="button search action" href="?m={{$_mb_module->mod_name}}&tab=configure">
        {{tr}}Configure{{/tr}}
      </a>
    {{/if}}
  </td>
  <td>
    <!-- Version -->
    <div {{if $_mb_module->_too_new}} class="warning" {{/if}}>
      {{mb_value object=$_mb_module field=mod_version}}
    </div>
  </td>
  <td style="text-align: center;" class="narrow">
    <!-- Actif -->
    {{if $can->edit}}
      <form name="formActifModule-{{$module_id}}" method="post"
            onsubmit="return onSubmitFormAjax(this, Module.refresh.curry('{{$module_id}}'))">
        <input type="hidden" name="dosql" value="do_manage_module" />
        <input type="hidden" name="m" value="system" />
        <input type="hidden" name="ajax" value="1" />
        <input type="hidden" name="mod_id" value="{{$module_id}}" />
        <input type="hidden" name="cmd" value="toggle" />

        <input type="checkbox" {{if $can->edit && $_mb_module->mod_type != "core"}}onclick="this.form.onsubmit();"{{/if}}
          {{if $_mb_module->mod_active}}checked="checked"{{/if}}
          {{if $_mb_module->mod_type=="core"}}disabled="disabled"{{/if}} />
      </form>
    {{else}}
      {{mb_value object=$_mb_module field=mod_active}}
    {{/if}}
  </td>
  <td style="text-align: center;" class="narrow">
    <!-- Visible -->
    {{if $can->edit}}
      <form name="formVisibleModule-{{$module_id}}" method="post"
            onsubmit="return onSubmitFormAjax(this, Module.refresh.curry('{{$module_id}}'))">
        <input type="hidden" name="dosql" value="do_manage_module" />
        <input type="hidden" name="m" value="system" />
        <input type="hidden" name="ajax" value="1" />
        <input type="hidden" name="mod_id" value="{{$module_id}}" />
        <input type="hidden" name="cmd" value="toggleMenu" />

        <input type="checkbox" {{if $can->edit && $_mb_module->mod_active}}onclick="this.form.onsubmit();"{{/if}}
          {{if $_mb_module->mod_ui_active}}checked="checked"{{/if}}
          {{if !$_mb_module->mod_active}}disabled="disabled"{{/if}} />
      </form>
    {{else}}
      {{mb_value object=$_mb_module field=mod_ui_active}}
    {{/if}}
  </td>
  <td class="text">
    <!-- Dépendances -->
    {{foreach from=$_mb_module->_dependencies key=num_version item=version}}
      {{foreach from=$version item=dependency name=dependencies}}
        {{if $_mb_module->mod_version <= $num_version}}
          {{if isset($dependency->verified|smarty:nodefaults) && $dependency->verified}}
            {{assign var=dependency_class value='me-succes'}}
            {{assign var=dependency_color value='#050'}}
          {{else}}
            {{assign var=dependency_class value='me-error'}}
            {{assign var=dependency_color value='#500'}}
          {{/if}}
          <label class="{{$dependency_class }}" style="color: {{$dependency_color}}" title="{{$dependency->module}}">
            {{tr}}module-{{$dependency->module}}-court{{/tr}} ({{$dependency->revision}})
            {{if !$smarty.foreach.dependencies.last}},{{/if}}
          </label>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  </td>
{{/if}}
