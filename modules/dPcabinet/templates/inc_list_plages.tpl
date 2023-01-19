{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=plage_selector ajax=$ajax}}

{{mb_default var=period value=week}}
{{mb_default var=multipleMode value=0}}
{{mb_default var=offline value=0}}
{{mb_default var=as_place value=0}}

{{if !$offline}}
  <script>
    searchPlages = function(table, keywords) {
      table.select("tr").each(function(elt) {
        elt.setStyle({display: "table-row"});
      });

      if (!keywords) {
        return;
      }

      table.select(".libelle_plage").each(function(elt) {
        elt.up("tr").hide();
      });

      keywords = keywords.split(" ");
      table.select(".libelle_plage").each(function(e) {
        keywords.each(function(keyword) {
          if (e.getText().like(keyword)) {
            e.up("tr").setStyle({display: "table-row"});
          }
        });
      });
    };

    Main.add(function() {
      Calendar.regField(getForm("FilterPlage_{{$refDate}}_{{$chir_id}}").date, null, {noView: true});
    });
  </script>

  <form name="FilterPlage_{{$refDate}}_{{$chir_id}}" action="?" method="get">
    <table class="form me-no-border-radius-bottom me-margin-bottom-0">
      <tr>
        <td class="button narrow">
          {{if $as_place}}
            <select name="chir_id" onchange="PlageConsultSelector.changePlageChir($V(this), '{{$refDate}}', {{$multipleMode}}); return false;">
              {{foreach from=$list_prat item=_prat}}
                <option value="{{$_prat->_id}}" {{if $chir_id == $_prat->_id}}selected{{/if}}>{{$_prat}}</option>
              {{/foreach}}
            </select>
            <br/>
          {{/if}}
          <a href="#1" onclick="{{if $as_place}}PlageConsultSelector.changePlageChir('{{$chir_id}}', '{{$pdate}}', {{$multipleMode}}); return false;{{else}}PlageConsultSelector.updatePlage({{$multipleMode}}, '{{$pdate}}');{{/if}}">&lt;&lt;&lt;</a>
          <strong>
            {{if $period == "day"  }}{{$refDate|date_format:" %A %d %B %Y"}}{{/if}}
            {{if $period == "week" || $period == "4weeks"}}{{$refDate|date_format:" semaine du %d %B %Y (%U)"}}{{/if}}
            {{if $period == "month"}}{{$refDate|date_format:" %B %Y"}}{{/if}}
          </strong>
          <input type="hidden" name="date" class="date" value="{{$date}}" onchange="{{if $as_place}}PlageConsultSelector.changePlageChir('{{$chir_id}}', $V(this), {{$multipleMode}});{{else}}PlageConsultSelector.updatePlage({{$multipleMode}}, $V(this) );{{/if}}" />
          <a href="#1" onclick="{{if $as_place}}PlageConsultSelector.changePlageChir('{{$chir_id}}', '{{$ndate}}', {{$multipleMode}}); return false;{{else}}PlageConsultSelector.updatePlage({{$multipleMode}}, '{{$ndate}}');{{/if}}">&gt;&gt;&gt;</a>
        </td>
      </tr>
    </table>
  </form>
{{/if}}

<table class="tbl me-margin-top-0 me-no-border-radius-top" id="listPlages_{{$period}}_{{$refDate}}">
  <tr>
    <th id="inc_list_plages_date_th" style="width: 7em;">{{mb_title class=CPlageconsult field=date}}</th>
    <th>{{mb_title class=CPlageconsult field=chir_id}}</th>
    <th>
      {{if !$offline}}
          {{me_form_field field_class="me-form-icon search"}}
            <input type="text" placeholder="{{tr}}CPlageconsult-libelle{{/tr}}" class="compact search me-placeholder" style="float: left;" size="10" onkeyup="searchPlages(this.up('table'), this.value)" />
          {{/me_form_field}}
      {{/if}}
    </th>
    <th>{{tr}}CPlageConsult-nb_patients{{/tr}}</th>
    <th>{{tr}}CPlageConsult-disponibles{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$listPlage item=_plage}}
    <tr class="plage {{if $_plage->_id == $plageconsult_id && !$multipleMode}}selected{{/if}}" id="plage-{{$_plage->_id}}">
      <td {{if in_array($_plage->date, $bank_holidays)}}style="background: #fc0"{{/if}} class="text">
        {{mb_include template=inc_plage_etat multiple=$multipleMode offline=$offline}}
      </td>
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage->_ref_chir}}
      </td>
      <td class="text">
        <div style="background-color:#{{$_plage->color}};display:inline;">&nbsp;&nbsp;</div>
        {{if $online}}
          <span style="float: right;">
            {{mb_include module=system template=inc_object_notes object=$_plage}}
          </span>
        {{/if}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_plage->_guid}}');" class="libelle_plage">{{$_plage->libelle}}</span>
      </td>
      <td style="text-align: center;" {{if !$_plage->_nb_patients}}class="hatching"{{/if}}>
        {{$_plage->_nb_patients}}
      </td>
      <td style="text-align: center;"  class="{{if $_plage->date < $today}}hatching{{/if}}">
        {{if !$_plage->_nb_free_freq}}<strong style="color:red">{{$_plage->_nb_free_freq}}</strong>{{else}}{{$_plage->_nb_free_freq|floor}}{{/if}} / {{$_plage->_total}}
      </td>
      <td>
        {{if $_plage->_consult_by_categorie|@count}}
          {{foreach from=$_plage->_consult_by_categorie item=curr_categorie}}
            {{$curr_categorie.nb}}
            <img alt="{{$curr_categorie.nom_categorie}}" title="{{$curr_categorie.nom_categorie}}" src="modules/dPcabinet/images/categories/{{$curr_categorie.nom_icone|basename}}"  style="vertical-align: middle;" />
          {{/foreach}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="{{if $multipleMode}}6{{else}}5{{/if}}" class="empty">{{tr}}CPlageconsult.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>