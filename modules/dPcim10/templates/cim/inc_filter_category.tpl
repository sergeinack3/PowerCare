{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=chapter value=false}}
{{mb_default var=field value='Ox\Mediboard\Cim10\CCodeCIM10::getIdField'|static_call:null}}

<select name="category" id="searchCIM_category"{{if !$chapter}} disabled{{/if}} style="width: 200px;">
  <option value="">
    &mdash; {{tr}}CCodeCIM10-search-category-placeholder{{/tr}}
  </option>
  {{if $chapter}}
    {{if $chapter|instanceof:'Ox\Mediboard\Cim10\Oms\CCodeCIM10OMS'}}
      {{foreach from=$chapter->_levelsInf item=category}}
        <option value="{{$category->$field}}">
          {{$category->libelle}}
        </option>
      {{/foreach}}
    {{else}}
      {{foreach from=$chapter->_categories item=category}}
        <option value="{{$category->$field}}">
          {{$category->libelle}}
        </option>
      {{/foreach}}
    {{/if}}
  {{/if}}
</select>