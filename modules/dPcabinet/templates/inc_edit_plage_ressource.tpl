{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_ternary var=max_repeat test=$plage->_count_duplicated_plages value=$plage->_count_duplicated_plages other=100}}

<form name="editPlage" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_plageressourcecab_multi_aed" />
  {{mb_key object=$plage}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$plage}}

    <tr>
      <td>
        <fieldset>
          <legend>
            {{tr}}CPlageRessourceCab.infos{{/tr}}
          </legend>

          <table class="form me-no-box-shadow">
            <tr>
              <th>{{mb_label object=$plage field=ressource_cab_id}}</th>
              <td>
                <select name="ressource_cab_id">
                  {{foreach from=$ressources item=_ressource}}
                  <option value="{{$_ressource->_id}}" {{if $_ressource->_id === $plage->ressource_cab_id}}selected{{/if}}>{{$_ressource}}</option>
                  {{/foreach}}
                </select>
              </td>
              <th>{{mb_label object=$plage field=libelle}}</th>
              <td>{{mb_field object=$plage field=libelle}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$plage field=date}}</th>
              <td>
                <select name="date" class="{{$plage->_props.date}}" style="width: 15em;">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$list_days item=curr_day}}
                    <option value="{{$curr_day}}"
                      {{if ($curr_day == $plage->date) || (!$plage->_id && $curr_day == $date)}}selected{{/if}}
                      {{if array_key_exists($curr_day, $holidays) && !$app->user_prefs.allow_plage_holiday}}disabled{{/if}}
                    >
                      {{$curr_day|date_format:"%A"}} {{if array_key_exists($curr_day, $holidays)}}({{tr}}common-holiday{{/tr}}){{/if}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
              <th>{{mb_label object=$plage field=color}}</th>
              <td>{{mb_field object=$plage field=color form=editPlage}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$plage field=debut}}</th>
              <td colspan="3">{{mb_field object=$plage field=debut form=editPlage}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$plage field=fin}}</th>
              <td colspan="3">{{mb_field object=$plage field=fin form=editPlage}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$plage field=_freq}}</th>
              <td colspan="3">
                <select name="_freq" onchange="updateFreq(this);">
                  <option value="05" {{if $plage->_freq == "05"}}selected{{/if}}>05</option>
                  <option value="10" {{if $plage->_freq == "10"}}selected{{/if}}>10</option>
                  <option value="15" {{if $plage->_freq == "15" || !$plage->_id}}selected{{/if}}>15</option>
                  <option value="20" {{if $plage->_freq == "20"}}selected{{/if}}>20</option>
                  <option value="30" {{if $plage->_freq == "30"}}selected{{/if}}>30</option>
                  <option value="45" {{if $plage->_freq == "45"}}selected{{/if}}>45</option>
                  <option value="60" {{if $plage->_freq == "60"}}selected{{/if}}>60</option>
                </select> min
              </td>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <fieldset>
          <legend>{{tr}}CPlageRessourceCab.repetition{{/tr}}</legend>

          <table class="form me-no-box-shadow">
            <tr>
              <th>{{mb_label object=$plage field=_repeat}}</th>
              <td>
                <input type="text" size="2" name="_repeat" value="1"
                       onchange="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';"
                       onKeyUp="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';" />
                {{if $plage->_count_duplicated_plages}}
                  (max. modifiables: {{$max_repeat+1}})
                {{/if}}
              </td>
              <td rowspan="3" class="text">
                {{if $plage->_count_duplicated_plages}}
                  <div class="small-info">
                    {{tr}}CPlageRessourceCab._count_duplicated_plages{{/tr}}
                  </div>
                {{/if}}
              </td>
            </tr>
            {{if $plage->_id && $plage->_count_duplicated_plages}}
              <tr>
                <th>{{tr}}CPlageRessourceCab.similar_plage{{/tr}}</th>
                <td>
                  {{$max_repeat}}
                </td>
              </tr>
            {{/if}}
            <tr>
              <th>{{mb_label object=$plage field=_type_repeat}}</th>
              <td>{{mb_field object=$plage field=_type_repeat style="width: 15em;" typeEnum=select disabled=disabled}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$plage}}
  </table>
</form>