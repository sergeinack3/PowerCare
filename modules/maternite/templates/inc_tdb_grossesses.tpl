{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Tdb.views.filterByText('grossesses_tab');
  });
</script>

<table class="tbl" id="grossesses_tab">
  <tbody id="tbody_grossesses_tab">
  {{foreach from=$grossesses item=_grossesse}}
    <tr>
      <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_guid}}')">
            {{$_grossesse->terme_prevu|date_format:$conf.date}}
          </span>
      </td>
      <td>
          <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_ref_parturiente->_guid}}')">
            {{$_grossesse->_ref_parturiente}}
          </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_grossesse->_ref_parturiente}}
      </td>
      <td>
        {{$_grossesse->_semaine_grossesse}} SA +{{$_grossesse->_reste_semaine_grossesse}} j
      </td>
      <td class="text">
        {{$_grossesse->_nb_ref_sejours}} {{tr}}CSejour{{/tr}}(s)
        / {{$_grossesse->_ref_consultations|@count}} {{tr}}CConsultation{{/tr}}(s)
        {{if $_grossesse->_ref_consultations|@count && $_grossesse->_ref_consultations_anesth|@count}}
          dont {{$_grossesse->_ref_consultations_anesth|@count}} {{tr}}CConsultAnesth{{/tr}}
        {{/if}}
      </td>
      <td class="button">
        <button class="search notext" onclick="Tdb.showPrenancyDashboard('{{$_grossesse->_id}}', 1);">{{tr}}CGrossesse.edit{{/tr}}</button>
        {{*<button class="consultation_create notext" onclick="Tdb.editConsult(null,'{{$_grossesse->_id}}', '{{$_grossesse->_ref_parturiente->_id}}');">{{tr}}CConsultation-title-create{{/tr}}</button>
        <button class="sejour_create notext" onclick="Tdb.editSejour(null, '{{$_grossesse->_id}}','{{$_grossesse->_ref_parturiente->_id}}');">{{tr}}CSejour-title-create{{/tr}}</button>*}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">{{tr}}CGrossesse.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="10">
      <button type="button" class="change notext me-tertiary" onclick="Tdb.views.listGrossesses(false);" style="float: right;">
        {{tr}}Refresh{{/tr}}
      </button>
      <button class="grossesse_create notext" onclick="Tdb.editGrossesse(0);" style="float: left;">
        {{tr}}CGrossesse-title-create{{/tr}}
      </button>
      <button class="search notext me-tertiary" onclick="Tdb.searchGrossesse();" style="float: left;">
        {{tr}}Rechercher{{/tr}}
      </button>
      <a onclick="zoomViewport(this);">{{if !$grossesses|@count}}Aucun{{else}}{{$grossesses|@count}}{{/if}}
        terme{{if $grossesses|@count > 1}}s{{/if}} prévu{{if $grossesses|@count > 1}}s{{/if}} entre
        le {{$date_min|date_format:$conf.date}} et le {{$date_max|date_format:$conf.date}}</a>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CGrossesse field=terme_prevu}}</th>
    <th>{{mb_title class=CGrossesse field=parturiente_id}}</th>
    <th>{{mb_title class=CGrossesse field=_semaine_grossesse}}</th>
    <th>Séjours / consults</th>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
  </tr>
  </thead>
</table>
