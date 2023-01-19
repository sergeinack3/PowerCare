{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez un mode de traitement</option>
    {{foreach from='Ox\Mediboard\Stock\CProductCategory::getList'|static_call:null item=_category}}
      <option value="{{$_category->_id}}" {{if $_category->_id == $value}}selected{{/if}}>{{$_category->_view}}</option>
    {{/foreach}}
  </select>
{{/if}}