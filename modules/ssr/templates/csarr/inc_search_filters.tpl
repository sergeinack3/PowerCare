{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=hide_selector value=0}}

<form name="searchCsARR" method="get" action="?" onsubmit="CsARR.search(this, '{{$object_class}}', '{{$object_id}}', '{{$hide_selector}}');">
  <table class="form" style="margin-top: 5px;">
    <tr>
      <th>
        <label for="searchCsARR_code" title="{{tr}}CActiviteCsARR-code-desc{{/tr}}">
          {{tr}}CActiviteCsARR-code{{/tr}}
        </label>
      </th>
      <td>
        <input type="text" name="code" value="" id="searchCsARR_code">
      </td>
      <th>
        <label for="searchCsARR_hierarchy_1">
          {{tr}}CActiviteCsARR-search-hierarchy_1{{/tr}}
        </label>
      </th>
      <td>
        {{mb_include module=ssr template=csarr/inc_filter_hierarchy level=1}}
      </td>
    </tr>
    <tr>
      <th>
        <label for="searchCsARR_keywords" title="{{tr}}common-One or more keywords, separated by spaces-desc{{/tr}}">
          {{tr}}common-Keywords{{/tr}}
        </label>
      </th>
      <td>
        <input type="text" name="keywords" value="" id="searchCsARR_keywords">
      </td>
      <th>
        <label for="searchCsARR_hierarchy_2">
          {{tr}}CActiviteCsARR-search-hierarchy_2{{/tr}}
        </label>
      </th>
      <td id="searchCsARR-hierarchy_2-placeholder">
        {{mb_include module=ssr template=csarr/inc_filter_hierarchy level=2}}
      </td>
    </tr>
    <tr>
      <td colspan="2"></td>
      <th>
        <label for="searchCsARR_hierarchy_3">
          {{tr}}CActiviteCsARR-search-hierarchy_3{{/tr}}
        </label>
      </th>
      <td id="searchCsARR-hierarchy_3-placeholder">
        {{mb_include module=ssr template=csarr/inc_filter_hierarchy level=3}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button type="button" class="search" onclick="this.form.onsubmit();">
          {{tr}}Search{{/tr}}
        </button>
      </td>
    </tr>
    <tr>
      <th class="title" colspan="4">
        {{tr}}Results{{/tr}}
      </th>
    </tr>
  </table>
</form>