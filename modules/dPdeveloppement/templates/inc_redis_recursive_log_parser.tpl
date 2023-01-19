{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=deepness value=1}}

{{foreach from=$tbl key=_key_name item=_functions}}
  {{if $deepness == 1}}
    {{assign var=total value=$_functions.Total}}
  {{/if}}
  <tr {{if $deepness > 1}}style="display: none"{{/if}}>
    <td class="text">
      {{if $_functions.children}}
          <span style="margin-left: {{$idx}}px; text-decoration: underline; cursor: pointer;" onclick="hideShowElem(this);"
                data-checked="0" data-deepness="{{$deepness}}" data-key="{{$_key_name}}">
            {{$_key_name}}
          </span>
      {{else}}
        <span style="margin-left: {{$idx}}px" data-checked="0" data-deepness="{{$deepness}}" data-key="{{$_key_name}}">
          <button class="search notext" type="button" onclick="displayKeyDetails(this, '{{$_key_name}}');">Voir l'utilisation</button>
          {{$_key_name}}
        </span>
      {{/if}}
    </td>
    {{foreach from=$calls item=_call_name}}
      <td align="right" {{if $_call_name == 'Total'}}style="font-weight: bold" {{else}}colspan="2"{{/if}}>
        {{if array_key_exists($_call_name, $_functions)}}
          {{if $hits_per_sec}}
            {{if $duration && $duration !== 0}}
              {{math assign=value_per_sec equation="x/y" x=$_functions.$_call_name y=$duration}}
              {{if $size_mode}}
                {{'Ox\Core\CMbString::toDecaBinary'|static_call:$value_per_sec}}
              {{else}}
                {{$value_per_sec|number_format:2:',':' '}}
              {{/if}}
            {{/if}}
          {{else}}
            {{if $size_mode}}
              {{'Ox\Core\CMbString::toDecaBinary'|static_call:$_functions.$_call_name}}
            {{else}}
              {{$_functions.$_call_name|integer}}
            {{/if}}

          {{/if}}

          {{if $_call_name !== 'Total' && $_functions.Total > 0}}
            {{math assign=total_pct equation="(x/y)*100" x=$_functions.$_call_name y=$_functions.Total}}

            {{if $total_pct lt 30}}
              {{assign var=backgroundClass value="normal"}}
            {{elseif $total_pct lt 60}}
              {{assign var=backgroundClass value="empty"}}
            {{elseif $total_pct lt 100}}
              {{assign var=backgroundClass value="booked"}}
            {{elseif $total_pct eq 100}}
              {{assign var=backgroundClass value="full"}}
            {{else}}
              {{assign var=backgroundClass value='_cell_overbooked'}}
            {{/if}}

            <div class="progressBar" style="width: 50px; display: inline-block;">
              <div class="bar {{$backgroundClass}}" style="width: {{$total_pct}}%; text-align: center;"></div>
              <div class="text" style="text-align: center;">
                {{$total_pct|number_format:2:',':' '}}%
              </div>
            </div>
          {{/if}}

        {{/if}}
      </td>

      {{if $_call_name == 'Total'}}
        <td align="right" class="compact" class="narrow">
          {{if $total && $total !== 0}}
            {{math assign=total_pct equation="(x/y)*100" x=$_functions.$_call_name y=$total}}
            {{$total_pct|number_format:2:',':' '}}%
          {{/if}}
        </td>
      {{/if}}

    {{/foreach}}
  </tr>

  {{assign var=new_idx value=$idx+20}}
  {{mb_include module=dPdeveloppement template=inc_redis_recursive_log_parser tbl=$_functions.children idx=$new_idx deepness=$deepness+1}}
{{/foreach}}