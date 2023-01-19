{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=sejour_type value=''}}
{{mb_default var=field_type value=''}}
{{mb_default var=user_profile value=false}}
{{mb_default var=container_id value='search-cim-results'}}
{{mb_default var=tags value=null}}

{{mb_ternary var=form_name test=$user_profile value="searchCIM-CMediusers-$user_profile" other='searchCIM'}}

<form name="{{$form_name}}" method="get" action="?" onsubmit="CIM.search(this, '{{$object_class}}', '{{$object_id}}', '{{$container_id}}');">
  <input type="hidden" name="sejour_type" value="{{$sejour_type}}">
  <input type="hidden" name="field_type" value="{{$field_type}}">
  <input type="hidden" name="ged" value="{{$ged}}">
  {{if $user_profile}}
    <input type="hidden" name="user_id" value="{{$user_profile}}">
  {{/if}}


  <table class="form" style="margin-top: 5px;">
    <tr>
      <th>
        <label for="{{$form_name}}_code" title="{{tr}}CCodeCIM10-search-code-desc{{/tr}}">
          {{tr}}CCodeCIM10-search-code{{/tr}}
        </label>
      </th>
      <td>
        <input type="text" name="code" value="" id="{{$form_name}}_code">
      </td>
      <th>
        <label for="{{$form_name}}_chapter" title="{{tr}}CCodeCIM10-search-chapter-desc{{/tr}}">
          {{tr}}CCodeCIM10-search-chapter{{/tr}}
        </label>
      </th>
      <td>
        <select name="chapter" id="{{$form_name}}_chapter" onchange="CIM.refreshCategories(this);" style="width: 200px;">
          <option value="">
            &mdash; {{tr}}common-Search by chapter|pl{{/tr}}
          </option>
          {{assign var=field value='Ox\Mediboard\Cim10\CCodeCIM10::getIdField'|static_call:null}}
          {{foreach from=$chapters item=chapter}}
            <option value="{{$chapter->$field}}" data-code="{{$chapter->code}}">
              {{$chapter->libelle|smarty:nodefaults}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="{{$form_name}}_keywords" title="{{tr}}common-One or more keywords, separated by spaces-desc{{/tr}}">
          {{tr}}common-Keywords{{/tr}}
        </label>
      </th>
      <td>
        <input type="text" name="keywords" value="" id="{{$form_name}}_keywords">
      </td>
      <th>
        <label for="{{$form_name}}_category" title="{{tr}}CCodeCIM10-search-category-desc{{/tr}}">
          {{tr}}CCodeCIM10-search-category{{/tr}}
        </label>
      </th>
      <td id="{{$form_name}}-categories-placeholder">
        {{mb_include module=cim10 template=cim/inc_filter_category}}
      </td>
    </tr>
    {{if $user_profile}}
      <tr>
        <th>
          <label for="{{$form_name}}_tag_id" title="{{tr}}CCodeCIM10-search-tag-desc{{/tr}}">
            {{tr}}CCodeCIM10-search-tag{{/tr}}
          </label>
        </th>
        <td>
          <select name="tag_id" id="{{$form_name}}_tag_id" class="taglist">
            <option value=""> &mdash; {{tr}}Select{{/tr}} </option>
            {{mb_include module=ccam template=inc_favoris_tag_select depth=0 show_empty=true tag_tree=$tags}}
          </select>
        </td>
        <td colspan="2"></td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="4">
        <button type="button" class="search" onclick="this.form.onsubmit();">
          {{tr}}Search{{/tr}}
        </button>
      </td>
    </tr>
    {{if $user_profile}}
      <tr>
        <th class="title" colspan="4" style="position: sticky;">
          {{tr}}CFavoriCIM10|pl{{/tr}}
        </th>
      </tr>
    {{else}}
      <tr>
        <th class="title" colspan="4">
          {{tr}}Results{{/tr}}
        </th>
      </tr>
    {{/if}}
  </table>
</form>
