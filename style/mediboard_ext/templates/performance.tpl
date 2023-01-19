{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=long_request value=0}}


<ul id="performance" {{if $long_request}} style="position: initial;" {{/if}}>
    {{if $performance.enslaved}}
      <li class="performance-enslaved">
        <strong>ENSLAVED</strong>
      </li>
    {{/if}}

  <li class="performance-time">
    <strong class="title">Temps de génération</strong>
    <span class="performance-time">{{$performance.genere}} s</span>

      {{assign var=dsTime value=0}}
      {{foreach from=$performance.dataSources key=dsn item=dataSource}}
          {{assign var=dsTime value=$dsTime+$dataSource.time}}
      {{/foreach}}
      {{assign var=dsTime value=$dsTime+$performance.nosqlTime}}

      {{if !$performance.genere}}
          {{assign var=genere value=1}}
      {{else}}
          {{assign var=genere value=$performance.genere}}
      {{/if}}
      {{math equation='(x/y)*100' assign=ratio x=$dsTime y=$genere}}

      {{assign var=ratio value=$ratio|round:2}}

      {{assign var=ratio_transport value=0}}
      {{if 'transportTiers'|array_key_exists:$performance && $performance.transportTiers}}
          {{assign var=transportTime value= $performance.transportTiers.total.time}}
          {{math equation='(x/y)*100' assign=ratio_transport x=$transportTime y=$genere}}
          {{assign var=ratio_transport value=$ratio_transport|round:2}}
      {{/if}}

    <div class="performance-bar"
         title="{{$ratio}} % du temps passé en requêtes aux sources de données (SGBD, Redis..) et {{$ratio_transport}} % en transport tiers (ftp, http, filesystem ...)">
      <div style="width: {{$ratio+$ratio_transport}}%;"></div>
    </div>
    <ul>
        {{foreach from=$performance.dataSources key=dsn item=dataSource}}
          <li>
            <strong>{{$dsn}}</strong>
            <span class="performance-count" title="Nombre de requêtes">{{$dataSource.count}}</span>
            -
            <span class="performance-time" title="Temps de requêtes">{{$dataSource.time*1000|string_format:'%.3f'}} ms</span>
            -
            <span class="performance-time" title="Nombre de fetch">{{$dataSource.timeFetch*1000|string_format:'%.3f'}} ms</span>
          </li>
        {{/foreach}}
        {{if $performance.nosqlCount}}
          <li>
            <strong>NoSQL</strong>
            <span class="performance-count" title="Nombre de requêtes">{{$performance.nosqlCount}}</span>
            -
            <span class="performance-time" title="Temps de requêtes">{{$performance.nosqlTime*1000|string_format:'%.3f'}} ms</span>
          </li>
        {{/if}}
        {{if 'transportTiers'|array_key_exists:$performance && $performance.transportTiers}}
            {{foreach from=$performance.transportTiers.sources key=source item=transport}}
              <li>
                <strong>{{$source}}</strong>
                <span class="performance-count" title="Nombre de transport">{{$transport.count}}</span>
                -
                <span class="performance-time" title="Temps des transports">{{$transport.time*1000|string_format:'%.3f'}} ms</span>
              </li>
            {{/foreach}}
        {{/if}}
    </ul>
  </li>
  
  <li class="performance-memory">
    <strong class="title">Mémoire PHP</strong>
      {{$performance.memoire}}
  </li>
  
  <li class="performance-objects" title="Objets chargés / cachables">
    <strong class="title">Objets chargés / cachables</strong>
    <span class="performance-count">{{$performance.objets}}</span> /
    <span class="performance-count">{{$performance.cachableCount}}</span>
    <ul>
        {{foreach from=$performance.objectCounts key=objectClass item=objectCount}}
          <li>
            <strong>{{$objectClass}}</strong>
            <span class="performance-count">{{$objectCount}}</span>
          </li>
        {{/foreach}}
      <li class="separator"> ---</li>
        {{foreach from=$performance.cachableCounts key=objectClass item=cachableCount}}
          <li>
            <strong>{{$objectClass}}</strong>
            <span class="performance-count">{{$cachableCount}}</span>
          </li>
        {{/foreach}}
    </ul>
  </li>
  
  <li class="performance-autoload" title="Classes chargées / pas encore en cache">
    <strong class="title">Classes chargées / pas encore en cache</strong>
    <span class="performance-count">{{$performance.autoloadCount}}</span>
    <ul>
        {{foreach from=$performance.autoload key=objectClass item=time}}
          <li>
            <strong>{{$objectClass}}</strong>
            <span class="performance-time">{{$time|string_format:"%.3f"}} ms</span>
          </li>
            {{foreachelse}}
          <li class="empty">Aucune classe hors cache</li>
        {{/foreach}}
    </ul>
  </li>
  
  <li class="performance-cache">
    <span class="performance-count">{{$performance.cache.total}}</span>
    <table style="max-height: 600px; overflow: auto;">
      {{foreach from=$performance.cache.totals key=_prefix item=_layers}}
        <tr>
          <td style="text-align: left;">
            <strong>{{$_prefix}}</strong>
          </td>

          {{foreach from=$_layers key=_layer item=_count name=layers}}
            {{if $_count}}
              <td>{{$_count}}</td>
              <td><tt>{{$_layer}}</tt></td>
            {{else}}
              <td colspan="2"></td>
            {{/if}}
          {{/foreach}}
        </tr>
        {{foreachelse}}
        <tr>
          <td class="empty">Aucun cache utilisé</td>
        </tr>
      {{/foreach}}

      {{if !$long_request}}
        <tr>
          <td class="button">
            <button class="search" onclick="new Url('system', 'latest_cache_hits').requestModal(800);">
              {{tr}}Details{{/tr}}
            </button>
          </td>
        </tr>
      {{/if}}
    </table>
  </li>

  <li class="performance-l10n" id="i10n-alert" onclick="Localize.showForm()" title="{{tr}}system-msg-unlocalized_warning{{/tr}}">
    0
  </li>

  <li class="performance-pagesize">
    <strong class="title">Taille de la page</strong>
      {{$performance.size}}
  </li>

  
  <li class="performance-network">
    <strong class="title">Adresse IP</strong>
      {{$performance.ip}}
  </li>

  <li class="export" onclick="window.open('data:text/html;charset=utf-8,'+encodeURIComponent(this.up('ul').innerHTML))"
      title="{{tr}}Export{{/tr}}"></li>

    {{if !$long_request}}
      <li class="close" onclick="this.up('ul').remove()" title="{{tr}}Close{{/tr}}"></li>
    {{/if}}


</ul>
