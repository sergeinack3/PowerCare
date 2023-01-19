{{*
 * @package Mediboard\system
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="cache"}}

{{math assign=colspan equation='x+1' x=$servers_ip|@count}}

<script>
  Main.add(function () {
    $("cache").fixedTableHeaders();
  });
</script>

<div id="cache" class="me-padding-0">
  <table id="CacheClearTab" class="tbl main">
    <thead>
    <tr>
      <th id="CacheClearTabKeyStone" class="text no-server me-text-align-center" style="width: 20%;" rowspan="2">{{tr}}CMonitorServer.target{{/tr}}</th>
      <th class="text all-servers me-text-align-center" rowspan="2">{{tr}}CMonitorServer.all{{/tr}}</th>
      <th class="text current-instance me-text-align-center" rowspan="2">{{tr}}CMonitorInstance.this{{/tr}}
        ({{$dshm_infos.name}} {{$dshm_infos.version}})
      </th>
      <th class="text target-server me-text-align-center" colspan="{{$colspan}}">{{tr}}CMonitorInstance.current{{/tr}}</th>
    </tr>
    <tr>
      <th class="text current-server me-text-align-center">{{tr}}system-msg-Localhost{{/tr}} ({{$actual_ip}})</th>
        {{if $servers_ip|@count > 0}}
            {{foreach from=$servers_ip item=server_ip}}
              <th class="text target-server me-text-align-center">{{tr}}system-msg-Remote{{/tr}} ({{$server_ip}})</th>
            {{/foreach}}
        {{/if}}
    </tr>
    </thead>
    <tbody>
    {{foreach from=$cache_keys key=cache_key item=cache_value}}
      <tr class="cache" id="cache-{{$cache_key}}">
        <th class="text no-server" style="text-align:left; padding-left: 10px;">
            {{if $cache_key === 'all'}}
            <i class="fas fa-trash"></i>
            {{elseif $cache_key === 'css'}}
              <i class="fab fa-css3"></i>
            {{elseif $cache_key === 'js' || $cache_key === 'storybook'}}
              <i class="fab fa-js"></i>
            {{elseif $cache_key === 'config'}}
              <i class="fas fa-wrench"></i>
            {{elseif $cache_key === 'locales'}}
              <i class="fas fa-globe"></i>
            {{elseif $cache_key === 'logs'}}
              <i class="fas fa-clipboard-list"></i>
            {{elseif $cache_key === 'templates'}}
              <i class="fas fa-file"></i>
            {{elseif $cache_key === 'devtools'}}
              <i class="fas fa-toolbox"></i>
            {{elseif $cache_key === 'children'}}
              <i class="fas fa-sitemap"></i>
            {{elseif $cache_key === 'core'}}
              <i class="fas fa-cogs"></i>
            {{elseif $cache_key === 'routing' }}
              <i class="fa fa-route"></i>
            {{elseif $cache_key === 'modules'}}
              <i class="fa fa-cubes"></i>
            {{/if}}

            {{if $cache_key === 'all' }}
              <span style="text-transform: uppercase">{{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}</span>
          {{else}}
            {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}
          {{/if}}
        </th>
            <td class="all-servers me-text-align-center">
              <button class="singleclick fill {{if $cache_key === 'all'}}cancel{{else}}trash{{/if}} notext"
                      onclick="CacheManager.openModalConfirm('{{$cache_key}}', 'all', 'all');"
                      title="{{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}">
              <span class="sr-only">
                {{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}
              </span>
              </button>
            </td>
        <td class="current-instance me-text-align-center">
            {{if $cache_key === 'all'}}
              <button class="singleclick fill cancel notext"
                      onclick="CacheManager.openModalConfirm('{{$cache_key}}', 'all', 'dshm');"
                      title="{{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}">
            <span class="sr-only">
              {{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}
            </span>
              </button>
            {{/if}}
        </td>
        <td class="current-server me-text-align-center">
          <button class="singleclick fill {{if $cache_key === 'all'}}cancel{{else}}trash{{/if}} notext"
                  onclick="CacheManager.openModalConfirm('{{$cache_key}}', 'local', 'shm');"
                  title="{{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}">
            <span class="sr-only">
              {{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}
            </span>
          </button>
        </td>
          {{foreach from=$servers_ip item=server_ip}}
            <td class="target-server me-text-align-center">
              <button class="singleclick fill {{if $cache_key === 'all'}}cancel{{else}}trash{{/if}} notext"
                      onclick="CacheManager.openModalConfirm('{{$cache_key}}', '{{$server_ip}}', 'shm');"
                      title="{{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}">
                <span class="sr-only">
                  {{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_values.{{$cache_key}}{{/tr}}
                </span>
              </button>
            </td>
          {{/foreach}}
      </tr>
        {{* Below clear modules cache, display modules list *}}
        {{if $cache_key === 'modules'}}
            {{foreach from=$modules_cache key=module_name item=module_cache}}
              <tr class="cache-module" id="cache-module-{{$module_name}}">
                <th class="text no-server section" style="text-align:left; padding-left: 10px;">
                  <i class="fa fa-cube"></i>
                  <span style="text-transform: initial;">{{tr}}module-{{$module_name}}-court{{/tr}}</span>
                </th>
                  <td class="all-servers me-text-align-center">
                    <button class="singleclick fill trash notext"
                            onclick="CacheManager.openModalConfirm('{{$module_cache.class|JSAttribute}}', 'all', 'all', '{{tr}}module-{{$module_name}}-court{{/tr}}');"
                            title="{{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_module{{/tr}} {{$module_name}}">
                    <span class="sr-only">
                      {{tr}}Clear{{/tr}} : {{tr}}CacheManager-cache_module{{/tr}} {{$module_name}}
                    </span>
                    </button>
                  </td>
                <td class="current-instance me-text-align-center">
                    {{if !empty($module_cache.distr)}}
                      <button class="singleclick fill trash notext"
                              onclick="CacheManager.openModalConfirm('{{$module_cache.class|JSAttribute}}', 'all', 'dshm', '{{tr}}module-{{$module_name}}-court{{/tr}}', '{{"|"|implode:$module_cache.distr}}');"
                              onmouseover="ObjectTooltip.createDOM(this, 'current-instance-{{$module_name}}-distr');"
                      >
                      </button>
                      <div id="current-instance-{{$module_name}}-distr" style="display: none;">
                        <table class="tbl">
                          <tr>
                            <th>Clés</th>
                          </tr>
                            {{foreach from=$module_cache.distr item=_cache_key}}
                              <tr>
                                <td>{{$_cache_key}}</td>
                              </tr>
                            {{/foreach}}
                        </table>
                      </div>
                    {{/if}}
                </td>
                <td class="current-server me-text-align-center">
                    {{if !empty($module_cache.outer)}}
                      <button class="singleclick fill trash notext"
                              onclick="CacheManager.openModalConfirm('{{$module_cache.class|JSAttribute}}', 'local', 'shm', '{{tr}}module-{{$module_name}}-court{{/tr}}', '{{"|"|implode:$module_cache.outer}}');"
                              onmouseover="ObjectTooltip.createDOM(this, 'current-server-{{$module_name}}-outer');"
                      >
                      </button>
                      <div id="current-server-{{$module_name}}-outer" style="display: none;">
                        <table class="tbl">
                          <tr>
                            <th>Clés</th>
                          </tr>
                            {{foreach from=$module_cache.outer item=_cache_key}}
                              <tr>
                                <td>{{$_cache_key}}</td>
                              </tr>
                            {{/foreach}}
                        </table>
                      </div>
                    {{/if}}
                </td>
                  {{foreach from=$servers_ip key=server_key item=server_ip}}
                    <td class="target-server me-text-align-center">
                        {{if !empty($module_cache.outer)}}
                          <button class="singleclick fill {{if $cache_key === 'all'}}cancel{{else}}trash{{/if}} notext"
                                  onclick="CacheManager.openModalConfirm('{{$module_cache.class|JSAttribute}}', '{{$server_ip}}', 'shm', '{{tr}}module-{{$module_name}}-court{{/tr}}', '{{"|"|implode:$module_cache.outer}}');"
                                  onmouseover="ObjectTooltip.createDOM(this, 'server-{{$server_key}}-{{$module_name}}-outer');"
                          >
                          </button>
                          <div id="server-{{$server_key}}-{{$module_name}}-outer" style="display: none;">
                            <table class="tbl">
                              <tr>
                                <th>Clés</th>
                              </tr>
                                {{foreach from=$module_cache.outer item=_cache_key}}
                                  <tr>
                                    <td>{{$_cache_key}}</td>
                                  </tr>
                                {{/foreach}}
                            </table>
                          </div>
                        {{/if}}
                    </td>
                  {{/foreach}}
              </tr>
            {{/foreach}}
        {{/if}}
    {{/foreach}}
    </tbody>
  </table>
