{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
{{foreach from=$databases key=_dsn item=_db}}
  <li data-dsn="{{$_dsn}}">
    <span class="compact" style="float: right;">({{$_db.tables|@count}} tables)</span>
    <span>{{$_dsn}}</span>

    {{if $_db.errors&1}}
      <span class="error" title="Fichier de description non pr�sent">Desc.</span>
    {{/if}}

    {{if $_db.errors&2}}
      <span class="error" title="Aucune table d�crite">Tables</span>
    {{/if}}

    {{if $_db.errors&4}}
      <span class="error" title="Datasource mal configur�">DS</span>
    {{/if}}
  </li>
{{/foreach}}
</ul>