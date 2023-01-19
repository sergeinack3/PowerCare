{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; {{tr}}CFilesCategory.select{{/tr}}</option>
      {{foreach from='Ox\Mediboard\Files\CFilesCategory::getFileCategories'|static_call:null item=_model}}
        <option value="{{$_model->_id}}" {{if $_model->_id == $value}}selected{{/if}}>{{$_model}}</option>
      {{/foreach}}
  </select>
{{else}}
    {{if $value}}
      {{$value}}
    {{/if}}
{{/if}}
