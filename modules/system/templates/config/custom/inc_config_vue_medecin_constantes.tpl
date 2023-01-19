{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{assign var=components value="|"|explode:$_prop.components}}
  <select name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisir une constante</option>
    {{foreach from=$components item=_component}}
      <option value="{{$_component}}" {{if $_component == $value}}selected{{/if}}>{{tr}}CConstantesMedicales-{{$_component}}{{/tr}}</option>
    {{/foreach}}
  </select>
{{else}}
  {{tr}}CConstantesMedicales-{{$value}}{{/tr}}
{{/if}}