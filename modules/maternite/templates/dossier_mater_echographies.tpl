{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}

<script>
    {{if $grossesse->multiple && $count_children > 1}}
    Main.add(function () {
      Control.Tabs.create('echo_child', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
    });
    {{/if}}
</script>

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="main">
  <tr>
    <td colspan="2" class="button">
      <button type="button" class="thumbnails"
              onclick="DossierMater.graphMosMode('{{$grossesse->_id}}' , 0);">{{tr}}CDossierPerinat.mosaique_view{{/tr}}</button>
      <button type="button" class="add not-printable" onclick="DossierMater.addEchographie(null, '{{$grossesse->_id}}');">
          {{tr}}Add{{/tr}} {{tr}}CSurvEchoGrossesse.one{{/tr}}
      </button>
      <button type="button" class="close not-printable" id="close_dossier_perinat" onclick="Control.Modal.close();">
        Fermer
      </button>
    </td>
  </tr>
</table>
<table class="tbl">
  {{if $list_children_new|@count}}
    <tr>
    {{foreach from=$list_children_new key=date_echo item=_echo_fields name=dates_echo}}
      {{assign var=first_value_date value=$smarty.foreach.dates_echo.first}}
      {{assign var=last_value_date  value=$smarty.foreach.dates_echo.last}}
      {{assign var=first_id_echo    value=$_echo_fields.echos|@first}}

        {{if $first_value_date}}
          <td class="narrow">
            <input type="checkbox" class="mosaique-all-checkbox" value=""
                   onclick="DossierMater.graphMosAllCheckbox(this)"
                   title="{{tr}}CDossierPerinat.select_all_mosaique_view{{/tr}}"/>
          </td>
          <td style="width: 15em;"></td>
        {{/if}}

        <th style="width: 15em;" colspan="{{$_echo_fields.counter}}">
          <button type="button" class="edit notext not-printable me-float-left"
                  onclick="DossierMater.addEchographie('{{$first_id_echo->_id}}', '{{$grossesse->_id}}', '{{$date_echo}}');">
              {{tr}}Edit{{/tr}}
          </button>
            {{$date_echo|date_format:$conf.date}}
          <br/>
            {{$_echo_fields.sa}} SA &ndash; {{$_echo_fields.type_echo}}
        </th>

        {{if $last_value_date}}
          <td></td>
        {{/if}}
    {{/foreach}}
    </tr>
    <tr>
      {{foreach from=$list_children_new key=date_echo item=_echo_fields name=dates_echo}}
        {{assign var=first_value_date value=$smarty.foreach.dates_echo.first}}
        {{assign var=last_value_date value=$smarty.foreach.dates_echo.last}}

        {{if $first_value_date}}
          <td></td>
          <td></td>
        {{/if}}

        {{foreach from=$list_children_new.$date_echo.echos key=key_field item=_echographie}}
          <th class="section">
              {{tr}}CPatient.civilite.enf-long{{/tr}} {{$_echographie->num_enfant}}
          </th>
        {{/foreach}}

        {{if $last_value_date}}
          <td></td>
        {{/if}}
      {{/foreach}}
    </tr>

    {{counter start=0 skip=1 assign="compteur"}}
    {{foreach from=$list_cat key=key_field item=_echo_values name=dates_echo}}
      {{assign var=categorie value=$list_cat.$key_field}}
      {{assign var=echo_first value=$surv_echo|@first}}
      <tr>
        <td class="narrow">
          {{if $categorie.button}}
            <input type="checkbox" class="mosaique-checkbox mosaique-checkbox-{{$compteur}}"
                   value="{{$key_field}}" title="{{tr}}CDossierPerinat.select_mosaique_view{{/tr}}"
                   onclick="DossierMater.graphMosCheckbox(this, '{{$key_field}}', {{$compteur}})"/>
            <button type="button" class="stats not-printable" title="{{tr}}CGrossesse-back-echographies{{/tr}}"
                    onclick="DossierMater.showModalGraph('{{$grossesse->_id}}', '{{$key_field}}', '{{$echo_first->num_enfant}}');"></button>
          {{/if}}
        </td>

        <td class="narrow" style="text-align: right;">{{mb_label class=CSurvEchoGrossesse field=$key_field}}</td>
        {{foreach from=$categorie.datas item=_value}}
          <td class="text">
            {{if $_value}}
              {{$_value}} {{$categorie.text}}
            {{/if}}
          </td>
        {{/foreach}}
        <td></td>
      </tr>
      {{counter}}
    {{/foreach}}
  {{else}}
    <tr>
      <td>
        <table class="tbl">
          <tr>
            <td class="narrow">
              <input type="checkbox" class="mosaique-all-checkbox" value=""
                     onclick="DossierMater.graphMosAllCheckbox(this)" title="{{tr}}CDossierPerinat.select_all_mosaique_view{{/tr}}" />
            </td>
            <td style="width: 15em;"></td>
              {{foreach from=$surv_echo item=echo}}
                <th style="width: 15em;">
                  <button type="button" class="edit notext not-printable me-float-left"
                          onclick="DossierMater.addEchographie('{{$echo->_id}}', '{{$grossesse->_id}}', '{{$echo->date}}');">
                      {{tr}}Edit{{/tr}}
                  </button>
                    {{mb_value object=$echo field=date}}
                  <br />
                    {{mb_value object=$echo field=_sa}} SA &ndash; {{mb_value object=$echo field=type_echo}}
                </th>
              {{/foreach}}
            <td></td>
          </tr>

            {{foreach from=$list_cat item=_cat key=_lib_cat name=cat_loop}}
              <tr>
                <td class="narrow">
                    {{if $_cat.button}}
                      <input type="checkbox" class="mosaique-checkbox mosaique-checkbox-{{$smarty.foreach.cat_loop.iteration}}"
                             value="{{$_lib_cat}}" title="{{tr}}CDossierPerinat.select_mosaique_view{{/tr}}"
                             onclick="DossierMater.graphMosCheckbox(this, '{{$_lib_cat}}', {{$smarty.foreach.cat_loop.iteration}})" />
                      <button type="button" class="stats notext not-printable" title="{{tr}}CGrossesse-back-echographies{{/tr}}"
                              onclick="DossierMater.showModalGraph('{{$grossesse->_id}}', '{{$_lib_cat}}');"></button>
                    {{/if}}
                </td>
                <td style="text-align: right;">{{mb_label class=CSurvEchoGrossesse field=$_lib_cat}}</td>
                  {{foreach from=$surv_echo item=echo}}
                    <td class="{{$_cat.class}}">{{mb_value object=$echo field=$_lib_cat}} {{if $echo->$_lib_cat}}{{$_cat.text}}{{/if}}</td>
                  {{/foreach}}
                <td></td>
              </tr>
            {{/foreach}}
        </table>
      </td>
    </tr>
  {{/if}}
</table>

