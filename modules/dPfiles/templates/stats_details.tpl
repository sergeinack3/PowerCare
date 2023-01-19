{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshStats = function() {
    $$('.details').invoke('setVisible', $('show-details').checked);
    $('body-by-class').setVisible($('by-what-class').checked);
    $('body-by-category').setVisible($('by-what-category').checked);
  }
</script>

<table class="tbl">
  <tr>
    <th colspan="10" class="title text">
      Statistiques pour le propriétaire : {{if $owner}}{{$owner}}{{else}}{{tr}}All{{/tr}}{{/if}}
      {{if $doc->_id}}
        <br /> {{$doc->_view}}
      {{/if}}
      {{if $date_min || $date_max}}
      <br/ >{{tr}}Period{{/tr}}
      {{mb_include module=system template=inc_interval_datetime from=$date_min to=$date_max}}
      {{/if}}
      {{if $factory}}
        &mdash; {{tr}}CCompteRendu-factory{{/tr}} : {{tr}}CCompteRendu.factory.{{$factory}}{{/tr}}
      {{/if}}
    </th>
  </tr>
  <tr>
    <td colspan="10">
      <label>
        <input type="radio" name="by-what" value="class" id="by-what-class" onclick="refreshStats();"  checked />
        {{tr}}CFile-object_class{{/tr}}
      </label>
      <label>
        <input type="radio" name="by-what" value="category" id="by-what-category" onclick="refreshStats();" />
        {{tr}}CFile-file_category_id{{/tr}}
      </label>
      <label style="float: right;">
        <input type="checkbox" id="show-details" onclick="refreshStats();" />
        {{tr}}Show details{{/tr}}
      </label>
    </td>
  </tr>

  <!-- Stats by class -->
  <tbody id="body-by-class">
  <tr>
    <th colspan="2">{{tr}}CFile-_count{{/tr}}</th>
    <th colspan="2">{{tr}}CFile-_total_weight{{/tr}}</th>
    <th>{{tr}}CFile-_average_weight{{/tr}}</th>
    {{if $is_doc}}
      <th>{{tr}}CCompteRendu-duree_lecture_average{{/tr}}</th>
      <th>{{tr}}CCompteRendu-duree_ecriture_average{{/tr}}</th>
    {{/if}}
    <th>
      {{tr}}CFile-object_class{{/tr}}
    </th>
    {{if !$date_min && !$date_max}}
    <th class="narrow"></th>
    {{/if}}
  </tr>
  {{foreach from=$class_totals key=_class item=_total}}
  <tr>
    <td style="text-align: right;">{{$_total.count|integer}}</td>
    <td style="text-align: right;">{{$_total.count_percent|percent}}</td>
    <td style="text-align: right;">{{$_total.weight|decabinary}}</td>
    <td style="text-align: right;">{{$_total.weight_percent|percent}}</td>
    <td style="text-align: right;">{{$_total.weight_average|decabinary}}</td>
    {{if $is_doc}}
      <td class="me-text-align-center">{{$_total.docs_read_time}}</td>
      <td class="me-text-align-center">{{$_total.docs_write_time}}</td>
    {{/if}}
    <td class="text">
      {{tr}}{{$_class}}{{/tr}}
    </td>
    {{if !$date_min && !$date_max}}
    <td>
      <button
        class="stats notext" type="button"
        onclick="Details.statPeriodicalOwner('{{$doc->_class}}', '{{$doc->_id}}', '{{$owner_guid}}', '', '{{$_class}}', null, '{{$factory}}');"
      >
        {{tr}}Periodical details{{/tr}}
      </button>
    </td>
    {{/if}}
  </tr>

  {{foreach from=$user_details item=_details}}
    {{if $_details.object_class == $_class}}
      <tr class="details opacity-50" style="display: none;">
        <td style="text-align: right;">{{$_details.count|integer}}</td>
        <td style="text-align: right;">{{$_details.count_percent|percent}}</td>
        <td style="text-align: right;">{{$_details.weight|decabinary}}</td>
        <td style="text-align: right;">{{$_details.weight_percent|percent}}</td>
        <td style="text-align: right;">{{$_details.weight_average|decabinary}}</td>
        {{if $is_doc}}
          <td class="me-text-align-center">{{$_details.docs_read_time}}</td>
          <td class="me-text-align-center">{{$_details.docs_write_time}}</td>
        {{/if}}
        {{assign var=category_id value=$_details.category_id}}
        <td class="text">
          &raquo;
          {{if !$category_id}}
          <em>{{tr}}CFilesCategory.none{{/tr}}</em>
          {{else}}
          {{$categories.$category_id}}
          {{/if}}
        </td>
        {{if !$date_min && !$date_max}}
        <td>
          <button
            class="stats notext" type="button"
            onclick="Details.statPeriodicalOwner('{{$doc->_class}}', '{{$doc->_id}}', '{{$owner_guid}}', '{{$category_id}}', '{{$_class}}', null, '{{$factory}}');"
          >
            {{tr}}Periodical details{{/tr}}
          </button>
        </td>
        {{/if}}
      </tr>
    {{/if}}
  {{/foreach}}

  {{/foreach}}
  </tbody>

  <!-- Stats by category -->
  <tbody id="body-by-category" style="display: none;">
  <tr>
    <th colspan="2">{{tr}}CFile-_count{{/tr}}</th>
    <th colspan="2">{{tr}}CFile-_total_weight{{/tr}}</th>
    <th>{{tr}}CFile-_average_weight{{/tr}}</th>
    {{if $is_doc}}
      <th>{{tr}}CCompteRendu-duree_lecture_average{{/tr}}</th>
      <th>{{tr}}CCompteRendu-duree_ecriture_average{{/tr}}</th>
    {{/if}}
    <th>{{tr}}CFile-file_category_id{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$category_totals key=_category_id item=_total}}
  <tr>
    <td style="text-align: right;">{{$_total.count|integer}}</td>
    <td style="text-align: right;">{{$_total.count_percent|percent}}</td>
    <td style="text-align: right;">{{$_total.weight|decabinary}}</td>
    <td style="text-align: right;">{{$_total.weight_percent|percent}}</td>
    <td style="text-align: right;">{{$_total.weight_average|decabinary}}</td>
    {{if $is_doc}}
      <td class="me-text-align-center">{{$_total.docs_read_time}}</td>
      <td class="me-text-align-center">{{$_total.docs_write_time}}</td>
    {{/if}}
    <td class="text">
      {{if !$_category_id}}
      <em>{{tr}}CFilesCategory.none{{/tr}}</em>
      {{else}}
      {{$categories.$_category_id}}
      {{/if}}
    </td>
    <td>
      <button
        class="stats notext" type="button"
        onclick="Details.statPeriodicalOwner('{{$doc->_class}}', '{{$doc->_id}}', '{{$owner_guid}}', '{{$_category_id}}', null, null, '{{$factory}}');"
      >
        {{tr}}Periodical details{{/tr}}
      </button>
    </td>
  </tr>

  {{foreach from=$user_details item=_details}}
  {{if $_details.category_id == $_category_id}}
  <tr class="details opacity-50" style="display: none;">
    <td style="text-align: right;">{{$_details.count|integer}}</td>
    <td style="text-align: right;">{{$_details.count_percent|percent}}</td>
    <td style="text-align: right;">{{$_details.weight|decabinary}}</td>
    <td style="text-align: right;">{{$_details.weight_percent|percent}}</td>
    <td style="text-align: right;">{{$_details.weight_average|decabinary}}</td>
    {{if $is_doc}}
      <td  class="me-text-align-center">{{$_details.docs_read_time}}</td>
      <td class="me-text-align-center">{{$_details.docs_write_time}}</td>
    {{/if}}
    <td class="text">&raquo;{{tr}}{{$_details.object_class}}{{/tr}}</td>
    <td>
      <button
        class="stats notext" type="button"
        onclick="Details.statPeriodicalOwner('{{$doc->_class}}', '{{$doc->_id}}', '{{$owner_guid}}', '{{$_category_id}}', '{{$_details.object_class}}', null, '{{$factory}}');"
      >
        {{tr}}Periodical details{{/tr}}
      </button>
    </td>
  </tr>
  {{/if}}
  {{/foreach}}

  {{/foreach}}
  </tbody>

</table>
