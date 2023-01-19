{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="pref[{{$var}}]" class="text" size="1">
  {{if $user_id != "default"}} 
    <option value="">&mdash; {{tr}}Ditto{{/tr}}</option>
  {{/if}}

  {{foreach from=$modules item=_module}}
    {{assign var=mod_name value=$_module->mod_name}}
    <option value="{{$mod_name}}" {{if $mod_name == $pref.user}} selected="selected" {{/if}} style="font-weight: bold;">
      {{tr}}module-{{$mod_name}}-court{{/tr}}
    </option>
    
    {{foreach from=$_module->_tabs item=_tab_type}}
      {{foreach from=$_tab_type key=_tab item=_tab_link}}
        <option value="{{$mod_name}}-{{$_tab}}" {{if "$mod_name-$_tab" == $pref.user}} selected="selected" {{/if}}>
          &nbsp; |&ndash; {{tr}}mod-{{$_module->mod_name}}-tab-{{$_tab}}{{/tr}}
        </option>
      {{/foreach}}
    {{/foreach}}

  {{/foreach}}
</select>


