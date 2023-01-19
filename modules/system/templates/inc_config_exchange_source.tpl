{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="sourcename" value=$source->name}}

{{if $source->_incompatible}}
<div class="small-error" {{if !$can->admin}}style="display:none;"{{/if}}>
  {{tr}}CExchangeSource-_incompatible{{/tr}} <strong>({{tr}}config-instance_role-{{$conf.instance_role}}{{/tr}})</strong>
</div>
{{/if}}

{{if !$sourcename}} 
  <div class="small-info">
    {{tr}}CExchangeSource-no-name{{/tr}}
  </div>
{{else}}
  <div id="exchange_source-{{$sourcename}}">
    <div class="small-info" {{if !$can->admin}}style="display:none;"{{/if}}>{{tr}}CExchangeSource-only_one_active{{/tr}}</div>

    <script type="text/javascript">
      refreshExchangeSource = function(exchange_source_name, type){
        var url = new Url("system", "ajax_refresh_exchange_source");
        url.addParam("exchange_source_name", exchange_source_name);
        url.addParam("type", type);
        url.requestUpdate('exchange_source-'+exchange_source_name);
      }
    </script>
    <table class="main">
      <tr>
        <td style="vertical-align: top;{{if $source->_allowed_instances}} width: 20%" {{/if}}>
          {{if $source->_allowed_instances}}
            <script type="text/javascript">
              Main.add(function () {
                Control.Tabs.create('tabs-exchange-source-{{$sourcename}}', true);
              });
            </script>

            <ul id="tabs-exchange-source-{{$sourcename}}" class="control_tabs_vertical">
              {{foreach from=$source->_allowed_instances item=_source_allowed}}
                <li><a href="#{{$_source_allowed->_class}}-{{$sourcename}}" class="{{if $_source_allowed->_id}}{{if $_source_allowed->active}}special{{else}}wrong{{/if}}{{else}}empty{{/if}}">{{tr}}{{$_source_allowed->_class}}{{/tr}}</a></li>
               {{/foreach}}
            </ul>
          {{/if}}
        </td>
        <td style="vertical-align: top;">
          <div id="CSourceFTP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Interop\Ftp\CSourceFTP')}}block{{else}}none{{/if}};">
            {{if !"ftp"|module_active}}
              {{mb_include module=system template=module_missing mod=ftp}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=ftp class="CSourceFTP"}}
            {{/if}}
          </div>

          <div id="CSourceSFTP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Interop\Ftp\CSourceSFTP')}}block{{else}}none{{/if}};">
            {{if !"ftp"|module_active}}
              {{mb_include module=system template=module_missing mod=ftp}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=ftp class="CSourceSFTP"}}
            {{/if}}
          </div>

          <div id="CSourceSOAP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Interop\Webservices\CSourceSOAP')}}block{{else}}none{{/if}};">
            {{if !"webservices"|module_active}}
              {{mb_include module=system template=module_missing mod=webservices}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=webservices class="CSourceSOAP"}}
            {{/if}}
          </div>

          <div id="CSourceSMTP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Mediboard\System\CSourceSMTP')}}block{{else}}none{{/if}};">
            {{mb_include module=system template=CExchangeSource_inc_config mod=system class="CSourceSMTP"}}
          </div>

          <div id="CSourceFileSystem-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Mediboard\System\CSourceFileSystem')}}block{{else}}none{{/if}};">
            {{mb_include module=system template=CExchangeSource_inc_config mod=system class="CSourceFileSystem"}}
          </div>

          <div id="CSourceMLLP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Interop\Hl7\CSourceMLLP')}}block{{else}}none{{/if}};">
            {{if !"hl7"|module_active}}
              {{mb_include module=system template=module_missing mod=hl7}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=hl7 class="CSourceMLLP"}}
            {{/if}}
          </div>

          <div id="CSourceHTTP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Mediboard\System\CSourceHTTP')}}block{{else}}none{{/if}};">
            {{mb_include module=system template=CExchangeSource_inc_config mod=system class="CSourceHTTP"}}
          </div>

          <div id="CSourceDicom-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Interop\Dicom\CSourceDicom')}}block{{else}}none{{/if}};">
            {{if !"dicom"|module_active}}
              {{mb_include module=system template=module_missing mod=dicom}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=dicom class="CSourceDicom"}}
            {{/if}}
          </div>

          <div id="CSourcePOP-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Mediboard\System\CSourcePOP')}}block{{else}}none{{/if}};">
            {{if !"messagerie"|module_active}}
              {{mb_include module=system template=module_missing mod=messagerie}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=system class="CSourcePOP"}}
            {{/if}}
          </div>

          <div id="CSyslogSource-{{$sourcename}}" class="source" style="display:{{if !$source->_allowed_instances && ($source|instanceof:'Ox\Mediboard\System\CSyslogSource')}}block{{else}}none{{/if}};">
            {{if !"eai"|module_active}}
              {{mb_include module=system template=module_missing mod=eai}}
            {{else}}
              {{mb_include module=system template=CExchangeSource_inc_config mod=system class="CSyslogSource"}}
            {{/if}}
          </div>
        </td>
      </tr>
    </table>
  </div>
{{/if}}