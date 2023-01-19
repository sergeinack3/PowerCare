{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1 style="cursor: pointer; text-align: center;">
  <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
  {{tr}}common-Total{{/tr}} {{tr}}date.from{{/tr}} {{$debut|date_format:$conf.date}}
  {{tr}}date.to{{/tr}} {{$fin|date_format:$conf.date}}
</h1>

<table class="tbl">
  <tr>
    <th rowspan="3" colspan="2" style="width: 20%;">
      {{tr}}CFacture-praticien_id{{/tr}}
    </th>
    {{foreach from=$object_classes item=classes key=classe}}
      <th colspan="{{if $classe == "Cabinet"}}2{{else}}6{{/if}}" class="title">
        {{$classe}}
      </th>
    {{/foreach}}
    <th colspan="2" rowspan="3" style="width: 20%;">
      {{tr}}common-Total|pl{{/tr}}
    </th>
  </tr>
  <tr>
    {{foreach from=$object_classes item=type key=nom_type}}
      {{foreach from=$type item=classe}}
        <th colspan="2">
          {{tr}}{{$classe}}{{/tr}}
        </th>
      {{/foreach}}
    {{/foreach}}
  </tr>
  <tr>
    {{foreach from=$object_classes item=type}}
      {{foreach from=$type item=classe}}
        {{foreach from=$tab_actes item=acte key=nom}}
        <th>
          {{$nom|upper}}
        </th>
        {{/foreach}}
      {{/foreach}}
    {{/foreach}}
  </tr>
  {{foreach from=$cotation item=_cotation key=_chir_id}}
    <tr>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prats.$_chir_id}}
      </td>
      <td class="narrow">
        {{if $conf.ref_pays == 1}}
          {{tr}}CFactureEtablissement-_secteur1-court{{/tr}}<br />
          {{tr}}CFactureEtablissement-_secteur2-court{{/tr}}
        {{/if}}
      </td>
      {{foreach from=$_cotation item=type}}
        {{foreach from=$type item=_total_by_class}}
          {{foreach from=$_total_by_class item=_total}}
            <td style="text-align: right;">
              <div>{{$_total.sect1|currency}}</div>
              {{if $conf.ref_pays == 1}}
                <div>{{$_total.sect2|currency}}</div>
              {{/if}}
            </td>
          {{/foreach}}
        {{/foreach}}
      {{/foreach}}
      <td style="text-align: right;">
        <strong>{{$total_by_prat.$_chir_id|currency}}</strong>
      </td>
      <td style="text-align: right;">
        {{math equation=(x/y)*100 x=$total_by_prat.$_chir_id y=$total assign=percent_prat}}
        <strong>
          {{$percent_prat|round:2}}%
        </strong>
      </td>
    </tr>
  {{/foreach}}
  <tbody class="hoverable">
    <tr>
      <td  rowspan="2"  colspan="2" style="text-align: right;">
        <strong>{{tr}}Total{{/tr}}</strong>
      </td>
      {{foreach from=$total_by_class item=type}}
        {{foreach from=$type item=_total_by_code}}
          {{foreach from=$_total_by_code item=_total}}
            <td style="text-align: right;">
              <strong>{{$_total|currency}}</strong>
            </td>
          {{/foreach}}
        {{/foreach}}
      {{/foreach}}
      <td rowspan="2" style="text-align: right;">
        <strong>{{$total|currency}}</strong>
      </td>
      <td rowspan="2" style="text-align: right;">
        <strong>100%</strong>
      </td>
    </tr>
    <tr>
      {{foreach from=$total_by_class item=type}}
        {{foreach from=$type item=_total_by_code}}
          <td colspan="2" style="text-align: center;">
            {{if $total}}
              {{math equation=x+y x=$_total_by_code.ccam y=$_total_by_code.ngap assign=sub_total}}
              {{math equation=(x/y)*100 x=$sub_total y=$total assign=percent_total}}
              <strong>
                {{$sub_total|currency}}
                {{if $percent_total}}
                  ({{$percent_total|round:2}}%)
                {{/if}}
              </strong>
            {{/if}}
          </td>
        {{/foreach}}
      {{/foreach}}
    </tr>
  </tbody>
</table>
