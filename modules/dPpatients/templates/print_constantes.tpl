{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=offline value=0}}
{{mb_default var=empty_lines value=0}}
{{mb_default var=fixed_header value=0}}
{{mb_default var=print value=0}}

{{unique_id var=uniq_ditto}}
{{assign var=cste_grid value=$constantes_medicales_grid}}
{{mb_default var=view value='view_constantes'}}

{{if $fixed_header && $cste_grid.grid|@count}}
  <script type="text/javascript">
    fixConstantsTableHeaders = function () {
      var header = $('constant_grid_header');
      var table = $('constant_grid');
      header.setStyle({width: table.getWidth() + 'px'});
      var headers = $$('#constant_grid_header tr')[0].childElements();

      $$('#constant_grid tr')[0].childElements().each(function (td, index) {
        var th = headers[index];
        th.setStyle({width: td.measure('width') + 'px'});
      });

      var container = $('constantes-medicales-graphs');
      var body = $('constant_grid_container');

      if (table.getHeight() + header.getHeight() > container.getHeight()) {
        var height = container.getHeight() - table.positionedOffset().top;
        if (table.getWidth() > container.getWidth()) {
          height = height - 30;
        }

        body.setStyle({height: height + 'px', width: (table.getWidth() + 20) + 'px'});
      }
    };
  </script>
{{/if}}

<table{{if $fixed_header}} id="constant_grid_header"{{/if}} class="tbl me-no-max-width constantes"
                                                            style="width: 1%; font-size: inherit;{{if $fixed_header}} border-bottom: medium none #ddd; box-sizing: border-box;{{/if}}">
  {{if $offline && isset($sejour|smarty:nodefaults)}}
    <thead>
    <tr>
      <th class="title"></th>
      <th class="title" colspan="{{$cste_grid.names|@count}}">
        {{$sejour->_view}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
    </tr>
    <tr>
      <th style="page-break-inside: avoid; min-width: 88px"></th>
      {{assign var=list_constantes value='Ox\Mediboard\Patients\CConstantesMedicales'|static:list_constantes}}
      {{assign var=noticed_constant value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"_noticed_constant"}}
      {{foreach from=$cste_grid.names item=_cste_name}}
        <th class="narrow" style="vertical-align: bottom; font-weight: normal; min-width: 13px; text-align: center">
          {{if $_cste_name|in_array:$noticed_constant}}
            <span onmouseover="ObjectTooltip.createDOM(this, '{{$_cste_name}}-notice');"
                  style="float: right; color: #2946c9; border-bottom: none;">
                <i class="fa fa-lg fa-info-circle"></i>
              </span>
            <div id="{{$_cste_name}}-notice" class="me-color-black-high-emphasis" style="display: none;">
              {{tr}}CConstantesMedicales-notice-{{$_cste_name}}{{/tr}}
            </div>
          {{/if}}
          {{vertical}}
          {{if array_key_exists("cumul_for", $list_constantes.$_cste_name)}}
            {{tr}}CConstantesMedicales-_{{$list_constantes.$_cste_name.cumul_for}}_cumul{{/tr}}
          {{else}}
            {{tr}}CConstantesMedicales-{{$_cste_name}}-court{{/tr}}
          {{/if}}

          {{if $list_constantes.$_cste_name.unit}} ({{$list_constantes.$_cste_name.unit}}){{/if}}
          {{/vertical}}
        </th>
      {{/foreach}}
    </tr>
    </thead>
  {{/if}}

  {{if $cste_grid.grid|@count}}
  {{assign var=list_constantes value='Ox\Mediboard\Patients\CConstantesMedicales'|static:list_constantes}}
  {{assign var=noticed_constant value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"_noticed_constant"}}
  {{if !$offline}}
    <tr>
      <th class="narrow" style="page-break-inside: avoid;"></th>
      {{foreach from=$cste_grid.names item=_cste_name}}
        <th class="narrow" style="vertical-align: bottom; font-weight: normal; position: relative; text-align: center">
          {{if $_cste_name|in_array:$noticed_constant}}
            <div onmouseover="ObjectTooltip.createDOM(this, '{{$_cste_name}}-notice');"
                 style="position: absolute; width: 100%; text-align: center; top: 0px; left: 0px; color: #2946c9; border-bottom: none;">
              <i class="fa fa-lg fa-info-circle"></i>
            </div>
            {{* Empty box for preventing the text to overlap the icon *}}
            <div style="width: 15px; height: 15px;"></div>
            <div id="{{$_cste_name}}-notice" class="me-color-black-high-emphasis" style="display: none;">
              {{tr}}CConstantesMedicales-notice-{{$_cste_name}}{{/tr}}
            </div>
          {{/if}}
          {{vertical}}
          {{if array_key_exists("cumul_for", $list_constantes.$_cste_name)}}
            {{tr}}CConstantesMedicales-_{{$list_constantes.$_cste_name.cumul_for}}_cumul{{/tr}}
          {{else}}
            {{tr}}CConstantesMedicales-{{$_cste_name}}-court{{/tr}}
          {{/if}}

          {{if $list_constantes.$_cste_name.unit}} ({{$list_constantes.$_cste_name.unit}}){{/if}}
          {{/vertical}}
        </th>
      {{/foreach}}
    </tr>
  {{/if}}

  {{if $fixed_header}}
</table>
<div id="constant_grid_container" style="overflow-y: auto;">
  <table id="constant_grid" class="tbl constantes" style="width: 1%; font-size: inherit; border-top: none;">
    {{/if}}
    
    {{foreach from=$cste_grid.grid item=_constante key=_datetime}}
      {{assign var=_datetime value=$_datetime|substr:0:18}}
      <tr
        class="comment-line"{{if $view == 'view_constantes'}} onclick="editConstants('{{$_constante.object.id}}', '{{$_constante.object.context}}');"{{/if}}>
        <th style="text-align: left; cursor: pointer;">
          <span{{if $_constante.author && !$print}} onmouseover="ObjectTooltip.createDOM(this, 'CConstantesMedicales-{{$_constante.object.id}}-author');"{{/if}}>
          {{$_datetime|date_format:$conf.datetime}}
          </span>
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
                style="min-width: 120px; font-weight: normal; background: #eee; background: rgba(255,255,255,0.6); white-space: normal; text-align: left; padding: 2px; border: 1px solid #ddd;">
                {{$_constante.comment}}
              </div>
            {{/if}}
          {{/if}}
        </th>
        {{foreach from=$cste_grid.names item=_cste_name}}
          {{assign var=_value value=null}}
          
          {{if array_key_exists($_cste_name,$_constante.values)}}
            {{assign var=_value value=$_constante.values.$_cste_name}}
          {{/if}}

          {{if is_array($_value)}}
            {{if $_value.value === null}}
              <td
                style="min-width: 15px;{{if $_value.color}} background-color: {{$_value.color}};{{/if}} cursor: pointer;" {{if $_value.span > 0}} rowspan="{{$_value.span}}" {{/if}}></td>
            {{else}}
              <td
                style="text-align: center; min-width: 15px; font-size: 0.9em; border-left: 2px solid {{if $_value.pair == "odd"}} #36c {{else}} #3c9 {{/if}}; border-top: 1px solid #999; {{if $_value.color}}background-color: {{$_value.color}};{{/if}} cursor: pointer;"
                {{if $_value.span > 0}} rowspan="{{$_value.span}}" {{/if}}>
                <strong>{{$_value.value}}</strong> <br />
                <small>{{$_value.day}}</small>
              </td>
            {{/if}}
          {{elseif $_value != "__empty__"}}
            <td class="text" style="text-align: center; font-size: 0.9em; min-width: 15px; cursor: pointer;">
              {{if $_value !== ""}}
                {{$_value}}
                {{if array_key_exists('comments', $_constante) && array_key_exists($_cste_name, $_constante.comments)}}
                  {{if !$print}}
                    <i class="me-icon comment me-primary" title="{{$_constante.comments.$_cste_name}}"></i>
                  {{else}}
                    <p>
                      {{$_constante.comments.$_cste_name}}
                    </p>
                  {{/if}}
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

    {{if $empty_lines}}
      {{foreach from=1|range:$empty_lines item=i}}
        <tr>
          <td style="height: 30px;"></td>
          {{foreach from=$cste_grid.names item=_cste_name}}
            <td></td>
          {{/foreach}}
        </tr>
      {{/foreach}}
    {{/if}}
    {{else}}
    <tr>
      <td></td>
      <td class="empty">{{tr}}CConstantesMedicales.none{{/tr}}</td>
    </tr>
    {{/if}}
  </table>
  {{if $cste_grid.grid|@count && $fixed_header}}
</div>
{{/if}}
