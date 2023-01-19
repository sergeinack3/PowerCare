{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; {{tr}}CProductCategory.select{{/tr}}</option>
    {{foreach from='Ox\Mediboard\Stock\CProductCategory::getList'|static_call:null item=_value}}
      <option value="{{$_value->_id}}" {{if $_value->_id == $value}}selected{{/if}}>{{$_value}}</option>
    {{/foreach}}
  </select>
{{else}}
  {{if $value}}
    {{$value}}
  {{/if}}
{{/if}}
