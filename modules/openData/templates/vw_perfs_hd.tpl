{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=use_ga value=false}}
{{mb_default var=finance value=false}}

<table class="main tbl">
  <tr>
    {{foreach from=$labels item=_label}}
      {{if !$use_ga || ($use_ga && strpos($_label, '_libelle') === false)}}
        <th class="text" colspan="{{if $use_ga && strpos($_label, 'group_') === 0}}3{{else}}2{{/if}}">
          {{mb_title class=$class field=$_label}}
          {{if array_key_exists($_label, $pages)}}
            <br/>
            <button class="help notext" type="button" onclick="openFieldDetails('{{$pages.$_label}}');">
              {{tr}}mod-openData-hospiDiag-display-infos{{/tr}}
            </button>
          {{/if}}
        </th>
      {{/if}}
    {{/foreach}}
  </tr>

  {{assign var=idx value=1}}
  {{foreach from=$fields item=_fields}}
    {{assign var=empty value=false}}
    {{if array_key_exists('empty', $_fields)}}
      {{assign var=empty value=true}}
    {{/if}}
    <tr>
      {{foreach from=$_fields key=_name item=_field}}
        {{if (!$use_ga || ($use_ga && strpos($_name, '_libelle') === false)) && $_name !== 'empty'}}

          {{if $use_ga && strpos($_name, 'group_') === 0}}
            <td class="text {{if $empty}}empty{{/if}}">
              {{assign var=field_name value="`$_name`_libelle"}}
              {{$_fields.$field_name}}
            </td>
          {{/if}}

          <td align="right" {{if $empty}}class="empty"{{/if}} {{if !$_field|is_numeric || $_name == 'annee'}}colspan="2"{{/if}}>
            {{if $_field == 'N. Calc.'}}
              {{tr}}mod-openData-hd-data-no-calc{{/tr}}
            {{elseif $_field == 'N. Conc.'}}
              {{tr}}mod-openData-hd-data-no-conc{{/tr}}
            {{else}}

              {{if $_name !== 'annee' && $finance && $_name !== $ignore_field}}
                {{$_field|currency}}
              {{elseif $_name !== 'annee' && $_field|is_numeric}}
                {{if $_field|is_float}}
                  {{$_field|number_format:2:',':' '}}
                {{else}}
                  {{$_field|number_format:0:',':' '}}
                {{/if}}

              {{else}}
                {{$_field}}
              {{/if}}
            {{/if}}
          </td>

          {{if $_field|is_numeric && $_name != 'annee'}}
            <td class="narrow">
              {{if array_key_exists($idx, $fields) && array_key_exists($_name, $fields.$idx) && $fields.$idx.$_name && $_field !== ''}}
                {{math assign=result equation="((x/y)-1)*100" x=$_field y=$fields.$idx.$_name}}

                {{if $finance}}
                  <span class="hospi_diag
                    {{if $result >= 0 || ($result <= 0 && $_field < 0) || ($result <= 0 && $_field > 0 && $fields.$idx.$_name < 0)}}
                      increase
                    {{else}}
                      decrease
                    {{/if}}
                  ">
                    ({{if $result >= 0}}+{{/if}}{{$result|number_format:0}}%)
                  </span>
                {{else}}
                  <span class="hospi_diag {{if $result >= 0}}increase{{else}}decrease{{/if}}">
                    ({{if $result >= 0}}+{{/if}}{{$result|number_format:0}}%)
                  </span>
                {{/if}}

              {{/if}}
            </td>
          {{/if}}

        {{/if}}
      {{/foreach}}
      {{if !array_key_exists('empty', $_fields)}}
        {{assign var=idx value=$idx+1}}
      {{/if}}
    </tr>
  {{/foreach}}
</table>