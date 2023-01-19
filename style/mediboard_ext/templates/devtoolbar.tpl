{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    var json = {{$devs|@json}};
    App.loadJS("includes/javascript/devtoolbar", function () {
      Devtoolbar.init(json);
    });
  });
</script>

<div id="divDevToolBar" class="{{if $devToolBarSmall == 1}} small {{/if}}">
  <div>
    <!-- TITLE -->
    <div id="titleOX"></div>

    <!-- SELECT XHR -->
    <div id="countRequest">
      <span>1</span>
    </div>
    <select id="selectRequest">
      <optgroup label="Document">
        <option title="{{ $devs.time }} - {{ $devs.request}}" id="{{ $devs.request_uid }}">
          #1 {{ $devs.title }}
        </option>
      </optgroup>
      <optgroup label="XHR" id="groupAjax">
      </optgroup>
    </select>

    <!-- LOADER -->
    <div id="loaderDevToolBar">
      <i class="fas fa-spinner fa-pulse"></i>
    </div>
  </div>

  <ul id="ulDevToolBar">
    <!-- REQUEST/RESPONSE -->
    <li class="turquoise">
      <i class="fas fa-fw fa-map-signs"></i>
      <span title="Headers, get, post ...">Request / Reponse</span>
      <ul id="request"></ul>
    </li>

    <!-- SERVEUR -->
    <li class="blue">
      <i class="fas fa-fw fa-server"></i>
      <span id="network" title="Server"></span>
      <ul id="ulServer"></ul>
    </li>

    <!-- TIME -->
    <li class="blue">
      <i class="fas fa-fw fa-stopwatch"></i>
      <span id="time" title="Temps de génération">sec</span>
      <ul id="ulTime"></ul>
    </li>

    <!-- SIZE -->
    <li class="noHover blue">
      <i class="fas fa-fw fa-weight"></i>
      <span id="size" title="Taille de la page"></span>
    </li>

    <!-- MEMORY -->
    <li class="noHover blue">
      <i class="fas fa-fw fa-memory"></i>
      <span id="memory" title="Mémoire PHP"></span>
    </li>

    <!-- DATA SOURCE -->
    <li class="blue">
      <i class="fas fa-fw fa-database"></i>
      <span id="datasourceCount" title="SGBD, Redis ..."></span>
      <ul id="datasource"></ul>
      <span id="enslaved">ENSLAVED</span>
    </li>

    <!-- OBJETS -->
    <li class="blue">
      <i class="fas fa-fw fa-archive"></i>
      <span title="Objets chargés / cachables">
        <span id="objetsCount"></span> / <span id="cachableCount"></span>
      </span>
      <ul id="objet"></ul>
    </li>

    <!-- INCLUDES -->
    <li class="blue">
      <i class="fas fa-fw fa-puzzle-piece"></i>
      <span id="includesCount" title="Included files"></span>
      <ul id="includes"></ul>
    </li>

    <!-- CACHE -->
    <li class="blue">
      <i class="fas fa-fw fa-hdd"></i>
      <span id="cacheCount" title="Cache"></span>
      <ul id="cache"></ul>
    </li>

    <hr>

    <!-- QUERY_TRACE -->
    <li class="white" id="liQueryTrace">
      <i class="fas fa-fw"></i>
      <span title="Activer/Désactiver le query_trace">Query trace</span>
      <span class="devtoolbar_count" id="countQueryTrace"></span>
      <ul id="ulQueryTrace"></ul>
    </li>

    <!-- QUERY_REPORT -->
    <li class="white" id="liQueryReport">
      <i class="fas fa-fw fa-toggle-off"></i>
      <span title="Activer/Désactiver le query_report">Query report</span>
      <span class="devtoolbar_count" id="countQueryReport"></span>
      <ul id="ulQueryReport"></ul>
    </li>

    <!-- SMARTY -->
    <li class="white" id="liSmarty">
      <i class="fas fa-fw fa-toggle-off"></i>
      <span title="Activer/Désactiver les infos templates">Templates</span>
      <span class="devtoolbar_count" id="tplCount"></span>
      <ul id="ulTpl"></ul>
    </li>

    <!-- FORMS -->
    <li class="white" id="liForm" data-active="0">
      <i class="fas fa-fw fa-toggle-off"></i>
      <span title="Inspecter">Form inspect</span>
    </li>

    <hr />

    <!-- ERROR -->
    <li class="white" id="liError">
      <i class="fab fa-fw fa-lg fa-php"></i>
      <span title="Throwable php">PHP</span>
      <span class="devtoolbar_count" id="errorCount"></span>
      <ul id="error"></ul>
    </li>

    <!-- ERROR JS -->
    <li class="white" id="liErrorJs">
      <i class="fab fa-fw fa-lg fa-js-square"></i>
      <span title="Erreur javascript">JS</span>
      <span class="devtoolbar_count" id="errorJsCount"></span>
      <ul id="errorJs"></ul>
    </li>

    <!-- TRADUCTION -->
    {{if $conf.locale_warn}}
      <li class="white" onclick="Localize.showForm()" title="{{tr}}system-msg-unlocalized_warning{{/tr}}">
        <i class="fas fa-fw fa-lg fa-globe"></i>
        <span title="Traductions">Locales </span>
        <span class="devtoolbar_count" id="i10n-alert"></span>
      </li>
    {{/if}}

    <!-- DUMP -->
    <li class="white">
      <i class="fas fa-fw fa-lg fa-eye"></i>
      <span title="CApp::dump">Dump</span>
      <span class="devtoolbar_count" id="countDump"></span>
      <ul id="dump"></ul>
    </li>

    <!-- LOG -->
    <li class="white">
      <i class="fas fa-fw fa-lg fa-flag"></i>
      <span title="CApp::log">Log</span>
      <span class="devtoolbar_count" id="countLog"></span>
      <ul id="log"></ul>
    </li>

    <hr>

    <!-- PROFILER -->
    <li class="white" id="liProfiler">
      <i class="fas fa-fw fa-stethoscope"></i>
      <span title="Profiler">Profilage</span>
    </li>

    <!-- RELOAD -->
    <li class="white" id="liReload">
      <i class="fas fa-fw fa-sync"></i>
      <span title="Reload query">Recharger</span>
    </li>

    <!-- CLEAR CACHE -->
    <li class="white" id="liClearCache">
      <i class="fas fa-fw fa-trash"></i>
      <span title="{{tr}}Clear{{/tr}}">Vider les caches</span>
    </li>

    <li class="white" id="liFavorise">
      <i class="fas fa-fw fa-star"></i>
      <span title="{{tr}}Details{{/tr}}">Favoris</span>
      <ul id="ulFavoris">
        <li>
          <span class="span_bookmark"><i class="fas fa-fw fa-bookmark"></i> Journaux</span>
          <ul style="margin:5px;line-height: 15px;">
            <li><a href="?m=dPdeveloppement&tab=view_logs">Journaux système</a></li>
            <li><a href="?m=system&tab=view_access_logs">Journaux d'accès</a></li>
            <li><a href="?m=system&tab=view_history">Journaux utilisateurs</a></li>
          </ul>
        </li>
        <li>
          <span class="span_bookmark"><i class="fas fa-fw fa-bookmark"></i> Cache</span>
          <ul style="margin:5px;line-height: 15px;">
            <li><a href="?m=system&tab=vw_cache">View cache</a></li>
            <li><a href="?m=system&tab=latest_cache_hits">Lastest cache hits</a></li>
          </ul>
        </li>
        <li>
          <span class="span_bookmark"><i class="fas fa-fw fa-bookmark"></i> SGBD</span>
          <ul style="margin:5px;line-height: 15px;">
            <li><a href="?m=system&tab=view_long_request_logs">Long request</a></li>
            <li><a href="?m=system&tab=viewDatasources">Data sources</a></li>
            <li><a href="?m=dPdeveloppement&tab=view_data_model">Data model</a></li>
            <li><a href="?m=dPdeveloppement&tab=vw_db_std">Std base explore</a></li>
          </ul>
        </li>
        <li>
          <span class="span_bookmark"><i class="fas fa-fw fa-bookmark"></i> TMP</span>
          <ul style="margin:5px;line-height: 15px;">
            <li><a target="_blank" href="./tmp/dump.html" >Dump</a></li>
            <li><a target="_blank" href="./tmp/mediboard.log">Log</a></li>
          </ul>
        </li>
      </ul>
    </li>

    <!-- DOWNLOAD -->
    <li class="white" id="liExport">
      <i class="fas fa-fw fa-download"></i>
      <span title="{{tr}}Export{{/tr}}">{{tr}}Download{{/tr}}</span>
    </li>

    <hr />

    <!-- RESIZE -->
    <li class="white" id="liResize">
      <i class="fas fa-fw {{if $devToolBarSmall == 1}} fa-expand {{else}} fa-compress {{/if}}"></i>
      <span title="Agrandir/Réduire la toolbar">

      </span>
    </li>

    <!-- CLOSE -->
    <li class="white" id="liClose">
      <i class="fas fa-fw fa-times"></i>
      <span title="{{tr}}Close{{/tr}}">Fermer</span>
    </li>
  </ul>
</div>
