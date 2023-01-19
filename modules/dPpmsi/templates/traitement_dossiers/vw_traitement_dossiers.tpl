{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="traitementDossiers" ajax=true}}

<script>
  Main.add(function() {
    new Url("pmsi", "ajax_traitement_dossiers_month")
      .addFormData(getForm('selType'))
      .periodicalUpdate('allDossiers', {frequency: 120});

    new Url("pmsi", "ajax_traitement_dossiers_lines")
      .addFormData(getForm('selType'))
      .periodicalUpdate('listDossiers', {frequency: 120});
  });
</script>

<table class="main">
  <tr>
    <td>
      <a href="#legend" onclick="traitementDossiers.showLegend();" class="button search">{{tr}}common-Legend{{/tr}}</a>
    </td>
    <td style="float: right">
      <form action="?" name="selType" method="get">
        <input type="hidden" name="date" value="{{$date}}" />
        <input type="hidden" name="tri_recept" value="{{$tri_recept}}" />
        <input type="hidden" name="tri_complet" value="{{$tri_complet}}" />
        <input type="hidden" name="order_col" value="{{$order_col}}" />
        <input type="hidden" name="order_way" value="{{$order_way}}" />
        <input type="hidden" name="filterFunction" value="{{$filterFunction}}" />
        <select name="period" onchange="traitementDossiers.reloadListDossiers(this.form);">
          <option value=""      {{if !$period          }}selected{{/if}}>&mdash; {{tr}}common-All the day{{/tr}}</option>
          <option value="matin" {{if $period == "matin"}}selected{{/if}}>{{tr}}Matin{{/tr}}</option>
          <option value="soir"  {{if $period == "soir" }}selected{{/if}}>{{tr}}Soir{{/tr}}</option>
        </select>
        {{mb_field object=$sejour field="_type_admission" emptyLabel="CSejour.all" onchange="traitementDossiers.reloadAllTraitementDossiers(this.form)"}}
        <select name="service_id" onchange="traitementDossiers.reloadAllTraitementDossiers(this.form);" {{if $sejour->service_id|@count > 1}}size="5" multiple="true"{{/if}}>
          <option value="">&mdash; {{tr}}CService.all{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if in_array($_service->_id, $sejour->service_id)}}selected{{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
        <input type="checkbox" onclick="traitementDossiers.toggleMultipleServices(this)" {{if $sejour->service_id|@count > 1}}checked{{/if}}/>

        <select name="prat_id" onchange="traitementDossiers.reloadAllTraitementDossiers(this.form);">
          <option value="">&mdash; {{tr}}common-Practitioner.all{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$sejour->praticien_id}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <td id="allDossiers" style="width: 250px">
    </td>
    <td id="listDossiers" style="width: 100%">
    </td>
  </tr>
</table>