{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{assign var=ufs value='Ox\Mediboard\Hospi\CUniteFonctionnelle::getUFs'|static_call:null}}

  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez une UF</option>

    {{foreach from=$ufs.medicale item=_uf}}
      <option value="{{$_uf->_id}}" {{if $_uf->_id == $value}}selected{{/if}}>{{$_uf}}</option>
    {{/foreach}}
  </select>
{{/if}}