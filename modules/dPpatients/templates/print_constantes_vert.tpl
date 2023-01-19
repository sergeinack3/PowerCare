{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uniq_ditto}}
{{assign var="one_date" value="1"}}
{{assign var=cste_grid value=$constantes_medicales_grid}}
{{assign var=list_constantes value='Ox\Mediboard\Patients\CConstantesMedicales'|static:list_constantes}}
{{mb_default var=full_size value=0}}
{{mb_default var=view value='view_constantes'}}
{{mb_default var=fixed_header value=0}}
{{mb_default var=print value=0}}
{{mb_default var=ref_unit_glycemie value=""}}

{{if $fixed_header}}
    <script type="text/javascript">
        fixConstantsTableVertHeader = function () {
            var header = $('constant_grid_vert_header');
            var table = $('constant_grid_vert');
            var container = $('constantes-medicales-graphs');
            var body = $('constant_grid_vert_container');
            var header_rows = header.down().childElements();

            table.down().childElements().each(function (row, index) {
                if (row.down()) {
                    var line_height = row.down().measure('height');
                    header_rows[index].down().setStyle({height: line_height + 'px'});
                }
            });

            if (table.getWidth() + header.getWidth() > container.getWidth()) {
                var width = container.getWidth() - header.getWidth() - 25;

                body.setStyle({height: (table.getHeight() + 20) + 'px', width: width + 'px'});
            }

            if (body.getHeight() > header.up().getHeight()) {
                header.up().setStyle({height: body.getHeight() + 'px'});
            }
        }
    </script>
    {{* Affichage avec headers fixes *}}
    <div style="display: inline-block; top: 0px;">
        <table id="constant_grid_vert_header" class="tbl constantes"
               style="width: auto; border-right: none; box-sizing: border-box;">
            <tr>
                <th style="height: 24px;" class="me-dossier-soin-cell-compenser"></th>
            </tr>
            {{assign var=noticed_constant value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"_noticed_constant"}}
            {{foreach from=$cste_grid.names item=_cste_name}}
                <tr>
                    <th style="text-align: left; height: 15px;">
                        {{if $_cste_name|in_array:$noticed_constant}}
                            <span onmouseover="ObjectTooltip.createDOM(this, '{{$_cste_name}}-notice');"
                                  style="float: right; color: #2946c9; border-bottom: none;">
                <i class="fa fa-lg fa-info-circle"></i>
              </span>
                            <div id="{{$_cste_name}}-notice" class="me-color-black-high-emphasis"
                                 style="display: none;">
                                {{tr}}CConstantesMedicales-notice-{{$_cste_name}}{{/tr}}
                            </div>
                        {{/if}}
                        {{if array_key_exists("cumul_for", $list_constantes.$_cste_name)}}
                            {{tr}}CConstantesMedicales-_{{$list_constantes.$_cste_name.cumul_for}}_cumul{{/tr}}
                        {{else}}
                            {{tr}}CConstantesMedicales-{{$_cste_name}}-court{{/tr}}
                        {{/if}}

                        {{if $list_constantes.$_cste_name.unit}}
                            {{if $_cste_name == "glycemie" && $ref_unit_glycemie}}
                                ({{$ref_unit_glycemie}})
                            {{else}}
                                ({{$list_constantes.$_cste_name.unit}})
                            {{/if}}
                        {{/if}}
                    </th>
                </tr>
            {{/foreach}}
        </table>
    </div>
    <div id="constant_grid_vert_container" style="overflow-x: auto; overflow-y: hidden; display: inline-block;">
        <table id="constant_grid_vert" class="tbl constantes" style="border-left: none;">
            <tr class="header">
                {{foreach from=$cste_grid.grid item=_constante key=_datetime}}
                    <th class="text"
                        style="width: 58px; text-align: center; vertical-align: top; font-size: 1em; cursor: pointer;">
          <span{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}
                  {{if $_constante.author && !$print}}onmouseover="ObjectTooltip.createDOM(this, 'CConstantesMedicales-{{$_constante.object.id}}-author');"{{/if}}>
          {{$_datetime|substr:0:18|date_format:$conf.datetime}}
          </span>
                        {{if $_constante.author}}
                            <span id="CConstantesMedicales-{{$_constante.object.id}}-author" style="display: none;">
              {{$_constante.creation_date}} - {{$_constante.author}}
            </span>
                        {{/if}}
                        {{if isset($patient|smarty:nodefaults) && $view == 'display_all_constantes_patient'}}
                            <button type="button" class="edit notext"
                                    onclick="editConstant('{{$_constante.object.id}}', '{{$patient->_id}}', 1);"
                                    title="{{tr}}Edit{{/tr}}"></button>
                        {{/if}}
                        {{if $_constante.comment}}
                            {{if $app->user_prefs.constantes_show_comments_tooltip}}
                                <i class="me-icon comment me-primary" title="{{$_constante.comment}}"></i>
                            {{else}}
                                <div class="me-constante-comment"
                                     style="min-width: 120px; font-weight: normal; background: #eee; background: rgba(255,255,255,0.6); white-space: normal; text-align: left; padding: 2px; border: 1px solid #ddd;">
                                    {{$_constante.comment}}
                                </div>
                            {{/if}}
                        {{/if}}
                    </th>
                {{/foreach}}
            </tr>
            {{foreach from=$cste_grid.names item=_cste_name}}
                <tr>
                    {{foreach from=$cste_grid.grid item=_constante key=_datetime}}
                        {{assign var=_value value=null}}

                        {{if array_key_exists($_cste_name,$_constante.values)}}
                            {{assign var=_value value=$_constante.values.$_cste_name}}
                        {{/if}}

                        {{if is_array($_value)}}
                            {{if $_value.value === null}}
                                <td
                                  style="height: 15px; border-left: 1px solid #999; {{if $_value.color}}background-color: {{$_value.color}};{{/if}} cursor: pointer;" {{if $_value.span > 0}} colspan="{{$_value.span}}" {{/if}} title="{{$_datetime|substr:0:18|date_format:$conf.datetime}}" {{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}></td>
                            {{else}}
                                <td
                                  style="height: 15px; text-align: center; border-top: 1px solid {{if $_value.pair == "odd"}} #36c {{else}} #3c9 {{/if}}; border-left: 1px solid #999; {{if $_value.color}}background-color: {{$_value.color}};{{/if}} cursor: pointer;"
                                        {{if $_value.span > 0}} colspan="{{$_value.span}}" {{/if}} title="{{$_datetime|substr:0:18|date_format:$conf.datetime}}" {{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
                                    <strong>{{$_value.value}}</strong> -
                                    <small>{{$_value.day}}</small>
                                </td>
                            {{/if}}
                        {{elseif $_value != "__empty__"}}
                            <td class="text"
                                style="height: 15px; text-align: center; cursor: pointer;" title="{{$_datetime|substr:0:18|date_format:$conf.datetime}}" {{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
                                {{if $_value !== ""}}
                                    {{$_value}}
                                    {{if array_key_exists('comments', $_constante) && array_key_exists($_cste_name, $_constante.comments)}}
                                        <i class="me-icon comment me-primary"
                                           title="{{$_constante.comments.$_cste_name}}"></i>
                                    {{/if}}
                                    {{if array_key_exists('alerts', $_constante) && array_key_exists($_cste_name, $_constante.alerts)}}
                                        {{assign var=alert_uid value=''|uniqid}}
                                        <i class="fa fa-exclamation-circle" style="color: firebrick; cursor: help;"
                                           onmouseover="ObjectTooltip.createDOM(this, 'alert_{{$alert_uid}}');"></i>
                                        <div id="alert_{{$alert_uid}}" style="display: none;">
                                            {{$_constante.alerts.$_cste_name|smarty:nodefaults}}
                                        </div>
                                    {{/if}}
                                {{else}}
                                    &nbsp;
                                {{/if}}
                            </td>
                        {{/if}}
                    {{/foreach}}
                </tr>
            {{/foreach}}
        </table>
    </div>
{{else}}
    {{* Affichage normal *}}
    <table class="tbl me-no-max-width constantes" style="{{if !$full_size}}width: 1%; {{/if}}font-size: inherit;">
        <tr>
            <th></th>
            {{foreach from=$cste_grid.grid item=_constante key=_datetime}}
                <th class="text"
                    style="text-align: center; vertical-align: top; font-size: 0.9em; cursor: pointer;{{if !$print}} max-width: 80px;{{/if}}">
                <span{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
                    {{$_datetime|substr:0:18|date_format:$conf.datetime}}

                    {{if $_constante.author}}
                        <span id="CConstantesMedicales-{{$_constante.object.id}}-author"
                              style="{{if !$print}}display: none;{{else}}display: block;{{/if}}">
                        {{if !$print}}{{$_constante.creation_date}} - {{/if}}{{$_constante.author}}
                            </span>
                    {{/if}}
                    {{if $_constante.comment}}
                        {{if $app->user_prefs.constantes_show_comments_tooltip && !$print}}
                            <i class="me-icon comment me-primary" title="{{$_constante.comment}}"></i>
                        {{else}}
                            <div class="me-constante-comment"
                                 style="font-weight: normal; background: #eee; background: rgba(255,255,255,0.6); white-space: normal; text-align: left; padding: 2px; border: 1px solid #ddd;{{if !$print}} width: 80px; box-sizing: border-box;{{else}}min-width: 120px;{{/if}}">
                                {{$_constante.comment}}
                            </div>
                        {{/if}}
                    {{/if}}
                </span>
                    {{if isset($patient|smarty:nodefaults) && $view == 'display_all_constantes_patient'}}
                        <button type="button" class="edit notext not-printable"
                                onclick="editConstant('{{$_constante.object.id}}', '{{$patient->_id}}', 1);"
                                title="{{tr}}Edit{{/tr}}"></button>
                    {{/if}}
                </th>
            {{/foreach}}
        </tr>

        {{assign var=noticed_constant value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"_noticed_constant"}}
        {{foreach from=$cste_grid.names item=_cste_name}}
            <tr>
                <th style="text-align: right;">
                    {{if array_key_exists("cumul_for", $list_constantes.$_cste_name)}}
                        {{tr}}CConstantesMedicales-_{{$list_constantes.$_cste_name.cumul_for}}_cumul{{/tr}}
                    {{else}}
                        {{tr}}CConstantesMedicales-{{$_cste_name}}-court{{/tr}}
                    {{/if}}

                    {{if $list_constantes.$_cste_name.unit}}
                        {{if $_cste_name == "glycemie" && $ref_unit_glycemie}}
                            ({{$ref_unit_glycemie}})
                        {{else}}
                            ({{$list_constantes.$_cste_name.unit}})
                        {{/if}}
                    {{/if}}
                    {{if $_cste_name|in_array:$noticed_constant && !$print}}
                        <span onmouseover="ObjectTooltip.createDOM(this, '{{$_cste_name}}-notice');"
                              style="float: right; color: #2946c9; border-bottom: none;">
              <i class="fa fa-lg fa-info-circle"></i>
            </span>
                        <div id="{{$_cste_name}}-notice" class="me-color-black-high-emphasis" style="display: none;">
                            {{tr}}CConstantesMedicales-notice-{{$_cste_name}}{{/tr}}
                        </div>
                    {{/if}}
                </th>

                {{foreach from=$cste_grid.grid item=_constante key=_datetime}}
                    {{assign var=_value value=null}}

                    {{if array_key_exists($_cste_name,$_constante.values)}}
                        {{assign var=_value value=$_constante.values.$_cste_name}}
                    {{/if}}

                    {{if is_array($_value)}}
                        {{if $_value.value === null}}
                            <td
                              style="border-left: 1px solid #999; {{if $_value.color}}background-color: {{$_value.color}};{{/if}} cursor: pointer;" {{if $_value.span > 0}} colspan="{{$_value.span}}" {{/if}}{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}></td>
                        {{else}}
                            <td
                              style="text-align: center; border-top: 1px solid {{if $_value.pair == "odd"}} #36c {{else}} #3c9 {{/if}}; border-left: 1px solid #999; {{if $_value.color}}background-color: {{$_value.color}};{{/if}} cursor: pointer;"
                                    {{if $_value.span > 0}} colspan="{{$_value.span}}" {{/if}}{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
                                <strong>{{$_value.value}}</strong> -
                                <small>{{$_value.day}}</small>
                            </td>
                        {{/if}}
                    {{elseif $_value != "__empty__"}}
                        <td class="text"
                            style="text-align: center; cursor: pointer;"{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
                            {{if $_value !== ""}}
                                {{$_value}}
                                {{if array_key_exists('comments', $_constante) && array_key_exists($_cste_name, $_constante.comments)}}
                                    {{if !$print}}
                                        <i class="me-icon comment me-primary"
                                           title="{{$_constante.comments.$_cste_name}}"></i>
                                    {{else}}
                                        <p>
                                            {{$_constante.comments.$_cste_name}}
                                        </p>
                                    {{/if}}
                                {{/if}}
                                {{if array_key_exists('alerts', $_constante) && array_key_exists($_cste_name, $_constante.alerts) && !$print}}
                                    {{assign var=alert_uid value=''|uniqid}}
                                    <i class="fa fa-exclamation-circle" style="color: firebrick; cursor: help;"
                                       onmouseover="ObjectTooltip.createDOM(this, 'alert_{{$alert_uid}}');"></i>
                                    <div id="alert_{{$alert_uid}}" style="display: none;">
                                        {{$_constante.alerts.$_cste_name|smarty:nodefaults}}
                                    </div>
                                {{/if}}
                            {{else}}
                                &nbsp;
                            {{/if}}
                        </td>
                    {{/if}}
                {{/foreach}}
            </tr>
        {{/foreach}}
    </table>
{{/if}}
