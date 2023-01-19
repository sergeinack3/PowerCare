{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{foreach from=$api key=_api item=_fields}}
      var form = getForm('{{$_api}}_form');

      {{foreach from=$_fields.fields key=_field item=_params}}
        {{if $_params.type == 'num'}}
          form.elements.{{$_field}}.addSpinner({min: 0});
        {{/if}}
      {{/foreach}}

    {{if isset($_fields.form|smarty:nodefaults)}}
      {{foreach from=$_fields.form key=_form_field item=_form_params}}
        {{if $_form_params.type == 'num'}}
          form.elements['data[{{$_form_field}}]'].addSpinner({min: 0});
        {{/if}}
      {{/foreach}}
    {{/if}}
    {{/foreach}}
  });
</script>

<table class="main layout">
  <col style="width: 30%;" />

  <tr>
    <td style="vertical-align: top;">
      {{foreach from=$api key=_api item=_fields}}
        <fieldset class="me-margin-10">
          <legend>{{$_api|upper}} &mdash; Méthode : {{$_fields.method|upper}}</legend>

          <div class="me-padding-4">
            <form name="{{$_api}}_form" method="{{$_fields.method}}" onsubmit="return onSubmitFormAjax(this, null, 'vw_api_results');">
              <input type="hidden" name="m" value="mediusers" />
              <input type="hidden" name="raw" value="api_test" />
              <input type="hidden" name="api" value="{{$_api}}" />
              <input type="hidden" name="prettify" value="1" />

              <label>
                {{tr}}common-Username{{/tr}} : <input type="text" name="username" value="" />
              </label>

              <label>
                {{tr}}common-Password{{/tr}} : <input type="password" name="password" value="" />
              </label>

              {{foreach from=$_fields.fields key=_field item=_params}}
                <label title="{{tr}}mediusers-{{$_api}}-{{$_field}}-desc{{/tr}}">
                  {{$_field}}

                  {{if $_params.type == 'num'}}
                    <input type="text" name="{{$_field}}" value="{{$_params.default}}" size="3" />
                  {{elseif $_params.type == 'select'}}
                    <select name="{{$_field}}">
                      {{if $_params.default|@is_null}}
                        <option value="" selected>&mdash; {{tr}}All{{/tr}}</option>
                      {{/if}}

                      {{foreach from=$_params.enum item=_option}}
                        <option value="{{$_option}}"
                                {{if $_params.default == $_option}}selected{{/if}}>{{tr}}mediusers-{{$_api}}-{{$_field}}.{{$_option}}{{/tr}}</option>
                      {{/foreach}}
                    </select>
                  {{elseif $_params.type == 'text'}}
                    <input type="text" name="{{$_field}}" />
                  {{elseif $_params.type == 'password'}}
                    <input type="password" name="{{$_field}}" />
                  {{elseif $_params.type == 'bool'}}
                    <label>
                      <input type="radio" name="{{$_field}}" value="1" /> {{tr}}Yes{{/tr}}
                    </label>
                    <label>
                      <input type="radio" name="{{$_field}}" value="0" checked /> {{tr}}No{{/tr}}
                    </label>
                  {{elseif $_params.type == 'time'}}
                    <input type="time" name="{{$_field}}" />
                  {{elseif $_params.type == 'date'}}
                    <input type="date" name="{{$_field}}" />
                  {{elseif $_params.type == 'datetime'}}
                    <input type="datetime" name="{{$_field}}" />
                  {{/if}}
                </label>
              {{/foreach}}

              <button type="submit" class="tick notext">{{tr}}Submit{{/tr}}</button>

              {{if isset($_fields.form|smarty:nodefaults)}}
                <input type="hidden" name="prettify" value="1" />

                <hr />

                <em>{{tr}}common-msg-Field with * is mandatory.|pl{{/tr}}</em>

                <table class="main form">
                  {{foreach from=$_fields.form key=_field item=_params}}
                    <tr>
                      <th>
                        <label title="{{tr}}mediusers-{{$_api}}-{{$_field}}-desc{{/tr}}">
                          {{if $_params.mandatory}}
                            <strong>{{$_field}}*</strong>
                          {{else}}
                            {{$_field}}
                          {{/if}}
                        </label>
                      </th>

                      <td>
                        {{if $_params.type == 'num'}}
                          <input type="text" name="data[{{$_field}}]" size="3" />
                        {{elseif $_params.type == 'select'}}
                          <select name="data[{{$_field}}]">
                            {{if !$_params.default}}
                              <option value="" selected>&mdash; {{tr}}All{{/tr}}</option>
                            {{/if}}

                            {{foreach from=$_params.enum item=_option}}
                              <option value="{{$_option}}" {{if $_params.default == $_option}}selected{{/if}}>
                                {{tr}}mediusers-{{$_api}}-{{$_field}}.{{$_option}}{{/tr}}
                              </option>
                            {{/foreach}}
                          </select>
                        {{elseif $_params.type == 'text'}}
                          <input type="text" name="data[{{$_field}}]" />
                        {{elseif $_params.type == 'password'}}
                          <input type="password" name="data[{{$_field}}]" />
                        {{elseif $_params.type == 'bool'}}
                          <label>
                            <input type="radio" name="data[{{$_field}}]" value="1" /> {{tr}}Yes{{/tr}}
                          </label>
                          <label>
                            <input type="radio" name="data[{{$_field}}]" value="0" checked /> {{tr}}No{{/tr}}
                          </label>
                        {{elseif $_params.type == 'time'}}
                          <input type="time" name="data[{{$_field}}]" />
                        {{elseif $_params.type == 'date'}}
                          <input type="date" name="data[{{$_field}}]" />
                        {{elseif $_params.type == 'datetime'}}
                          <input type="datetime" name="data[{$_field}}]" />
                        {{/if}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
              {{/if}}
            </form>

            <hr />
            <div class="text compact me-margin-4">{{tr}}mediusers-{{$_api}}-desc{{/tr}}</div>
          </div>
        </fieldset>
      {{/foreach}}
    </td>

    <td id="vw_api_results" style="vertical-align: top;"></td>
  </tr>
</table>