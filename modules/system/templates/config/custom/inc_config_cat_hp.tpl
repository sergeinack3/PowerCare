{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$is_last}}
  {{mb_return}}
{{/if}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez une cat�gorie de fichiers</option>

    {{foreach from='Ox\Mediboard\Files\CFilesCategory::listCatClass'|static_call:null item=_cat}}
      <option value="{{$_cat->_id}}" {{if $_cat->_id == $value}}selected{{/if}}>{{$_cat}}</option>
    {{/foreach}}
  </select>
{{/if}}