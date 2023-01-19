{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $exclude_id != $_catalogue->_id}}
  <option value="{{$_catalogue->_id}}"
    {{if $_catalogue->_id == $selected_id}}selected{{/if}}
    style="padding-left: {{$_catalogue->_level * 2}}em;">
    {{tr}}CExamen-catalogue-{{$_catalogue->_level}}{{/tr}} : {{$_catalogue}}
  </option>

  {{foreach from=$_catalogue->_ref_catalogues_labo item=_catalogue}}
    {{mb_include module=labo template=options_catalogues}}
  {{/foreach}}
{{/if}}
