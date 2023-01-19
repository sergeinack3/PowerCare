{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$is_last}}
  {{foreach from='Ox\Mediboard\Stock\CProductCategory::getList'|static_call:null item=_cat}}
    {{if $_cat->_id == $value}}
      <span onmouseover="ObjectTooltip.createEx(this,'{{$_cat->_guid}}')">
        {{$_cat}}
      </span>
    {{/if}}
  {{/foreach}}
{{else}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez une catégorie</option>

    {{foreach from='Ox\Mediboard\Stock\CProductCategory::getList'|static_call:null item=_cat}}
      <option value="{{$_cat->_id}}" {{if $_cat->_id == $value}}selected{{/if}}>{{$_cat}}</option>
    {{/foreach}}
  </select>
{{/if}}