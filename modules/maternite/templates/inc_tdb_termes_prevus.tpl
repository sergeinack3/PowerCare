{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Tdb.views.filterByText('admissions');
  });
</script>

<table class="tbl me-no-align" id="admissions">
  <tbody>
  {{foreach from=$sejours item=_sejour}}
    {{mb_include module=maternite template=inc_line_admission vue_alternative=1}}
    {{foreachelse}}
    <tr>
      <td colspan="11" class="empty">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="11">
      <button type="button" class="change notext me-tertiary me-small" onclick="Tdb.views.listTermesPrevus(false);" style="float: right;">
        {{tr}}Refresh{{/tr}}
      </button>
      <button class="grossesse_create me-add notext me-small me-primary" onclick="Tdb.editGrossesse(0);" style="float: left;">
        {{tr}}CGrossesse-title-create{{/tr}}
      </button>
      <button class="search notext me-tertiary me-small" onclick="Tdb.searchGrossesse();" style="float: left;">
        {{tr}}Rechercher{{/tr}}
      </button>
      {{if !$sejours|@count}}Aucun{{else}}{{$sejours|@count}}{{/if}}
      terme{{if $sejours|@count > 1}}s{{/if}} prévu{{if $sejours|@count > 1}}s{{/if}} entre le {{$date_min|date_format:$conf.date}} et
      le {{$date_max|date_format:$conf.date}}
    </th>
  </tr>

  {{mb_include module=maternite template=inc_header_admissions}}
  </thead>
</table>