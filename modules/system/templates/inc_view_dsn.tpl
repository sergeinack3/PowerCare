{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=configured value=false}}
{{assign var=dsnConfig value=0}}

{{if $dsn|array_key_exists:$conf.db}}
  {{assign var=dsnConfig value=$conf.db.$dsn}}
{{/if}}

{{if $dsnConfig && $dsnConfig.dbtype && $dsnConfig.dbhost}}
  {{assign var=configured value=true}}
{{/if}}

<script>
  Main.add(function(){
    var configured = {{$configured|@json}};
    var container = $("{{$dsn_uid}}").up('tr');
    container.down('.dsn-is-configured').setVisible(configured);
    container.down('.dsn-is-empty').setVisible(!configured);
  })
</script>

{{if $dsnConfig && $dsnConfig.dbtype && $dsnConfig.dbhost}}
  {{assign var=_althosts value='/\s*,\s*/'|preg_split:$dsnConfig.dbhost}}

  {{if array_key_exists('nocache',$dsnConfig)}}
    {{assign var=_nocache value=$dsnConfig.nocache}}
  {{else}}
    {{assign var=_nocache value=false}}
  {{/if}}
  <code>
    <span class="fa-stack">
      <i class="fa fa-bolt fa-stack-2x" style="color: #787878"></i>
      {{if $_nocache}}<i class="fa fa-ban fa-stack-2x" style="color: #c17b75" title="{{tr}}config-db-nocache-checked{{/tr}}"></i>{{/if}}
    </span>
    <span class="dsn dsn-dbtype" title="{{tr}}config-db-dbtype-desc{{/tr}}">{{$dsnConfig.dbtype}}</span>://
    <span class="dsn dsn-dbuser" title="{{tr}}config-db-dbuser{{/tr}}">{{$dsnConfig.dbuser}}</span> @
    <span class="dsn dsn-dbhost" title="{{tr}}config-db-dbhost-desc{{/tr}}">
      {{if $_althosts|@count > 1}}
        [
        {{foreach from=$_althosts item=_althost name=althosts}}
          <span class="dsn-host">{{$_althost}}</span>
          {{if !$smarty.foreach.althosts.last}},{{/if}}
        {{/foreach}}
        ]
      {{else}}
        {{$dsnConfig.dbhost}}
      {{/if}}
    </span> /
    <span class="dsn dsn-dbname" title="{{tr}}config-db-dbname-desc{{/tr}}">{{$dsnConfig.dbname}}</span>

    {{if !$dsnConfig.dbpass}}
      <span class="dsn dsn-dbpass">[{{tr}}config-dsn-no_password{{/tr}}]</span>
    {{/if}}
  </code>
{{else}}
  <span class="dsn-empty">{{tr}}CSQLDataSource-msg-DSN empty{{/tr}}</span>
{{/if}}
