{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function reloadPatient(form) {
    var url_patient = new Url("hospi", "httpreq_pathologies");
    url_patient.addParam("affichage_patho", "{{$affichage_patho}}");
    url_patient.addParam("sejour_id", form.sejour_id.value);
    url_patient.requestUpdate('sejour-' + form.sejour_id.value);
  }

  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <td colspan="4" style="text-align: right" class="me-color-black-high-emphasis">
      Type d'affichage
      <form name="selAffichage" action="?m=hospi&tab=vw_idx_pathologies" method="post">
        <select name="affichage_patho" onchange="submit()">
          <option value="tous" {{if $affichage_patho == "tous"}}selected{{/if}}>
            {{tr}}All{{/tr}}
          </option>
          <option value="non_complet" {{if $affichage_patho=="non_complet"}}selected{{/if}}>
            Non complétés
          </option>
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <th colspan="4" class="me-color-black-high-emphasis"">
      <a href="?m={{$m}}&tab={{$tab}}&date={{$yesterday}}" style="float: left;"><<<</a>
      <a href="?m={{$m}}&tab={{$tab}}&date={{$tomorow}}" style="float: right;">>>></a>
      {{$date|date_format:$conf.longdate}}
      <form name="changeDate" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
    </th>
  </tr>
  <tr>
    {{foreach from=$groupSejourNonAffectes key=group_name item=sejourNonAffectes}}
      <td style="vertical-align: top; width: {{math equation=100/x x=$groupSejourNonAffectes|@count}}%;">
        <table class="tbl">
          <tr>
            <th class="title">
              {{tr}}CSejour.groupe.{{$group_name}}{{/tr}} ({{$sejourNonAffectes|@count}})
            </th>
          </tr>
        </table>
        <table class="tbl">
          {{foreach from=$sejourNonAffectes item=curr_sejour}}
            <tbody id="sejour-{{$curr_sejour->sejour_id}}">
            {{mb_include module=hospi template=inc_pathologies}}
            </tbody>
          {{/foreach}}
        </table>
      </td>
    {{/foreach}}
  </tr>
</table>