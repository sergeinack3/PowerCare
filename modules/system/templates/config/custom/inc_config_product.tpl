{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{assign var=products value='Ox\Erp\COXProduct::getAll'|static_call:null}}

  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez un produit</option>

    {{foreach from=$products item=_product}}
      <option value="{{$_product->_id}}" {{if $_product->_id == $value}}selected{{/if}}>{{$_product}}</option>
    {{/foreach}}
  </select>
{{else}}
  {{if $value}}
    <span onmouseover="ObjectTooltip.createEx(this, 'COXProduct-{{$value}}');">
      COXProduct-{{$value}}
    </span>
  {{/if}}
{{/if}}
