{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=configured value=false}}
{{assign var=dsnConfig value=0}}
{{if array_key_exists("elastic", $conf)}}
    {{if $dsn|array_key_exists:$conf.elastic}}
        {{assign var=dsnConfig value=$conf.elastic.$dsn}}
    {{/if}}

    {{if $dsnConfig && $dsnConfig.elastic_host}}
        {{assign var=configured value=true}}
    {{/if}}
{{/if}}
<script>
  Main.add(function () {
    var configured = {{$configured|@json}};
    var container = $("{{$dsn_uid}}").up('tr');
    container.down('.dsn-is-configured').setVisible(configured);
    container.down('.dsn-is-empty').setVisible(!configured);
  })
</script>

{{if $dsnConfig && $dsnConfig.elastic_host}}
    {{assign var=_althosts value='/\s*,\s*/'|preg_split:$dsnConfig.elastic_host}}

    {{if array_key_exists('nocache',$dsnConfig)}}
        {{assign var=_nocache value=$dsnConfig.nocache}}
    {{else}}
        {{assign var=_nocache value=false}}
    {{/if}}
  <code>
    <span class="fa-stack">
      <i class="fa fa-bolt fa-stack-2x" style="color: #787878"></i>
      {{if $_nocache}}<i class="fa fa-ban fa-stack-2x" style="color: #c17b75"
                         title="{{tr}}config-db-nocache-checked{{/tr}}"></i>{{/if}}
    </span>
    <span class="dsn dsn-dbtype" title="{{tr}}config-db-dbtype-desc{{/tr}}">elastic</span>://
    <span class="dsn dsn-dbuser" title="{{tr}}config-db-dbuser{{/tr}}">{{$dsnConfig.elastic_user}}</span> @
    <span class="dsn dsn-dbhost" title="{{tr}}config-db-dbhost-desc{{/tr}}">
      {{if $_althosts|@count > 1}}
        [
          {{foreach from=$_althosts item=_althost name=althosts}}
            <span class="dsn-host">{{$_althost}}</span>
              {{if !$smarty.foreach.althosts.last}},{{/if}}
          {{/foreach}}
        ]
      {{else}}
          {{$dsnConfig.elastic_host}}
      {{/if}}
    </span>:
    <span class="dsn dsn-dbuser" title="{{tr}}config-db-dbuser{{/tr}}">{{if $dsnConfig.elastic_port !== ""}}{{$dsnConfig.elastic_port}}{{else}}9200{{/if}}</span> /
    <span class="dsn dsn-dbname" title="{{tr}}config-db-dbname-desc{{/tr}}">{{$dsnConfig.elastic_index}}</span>

      {{if !$dsnConfig.elastic_pass}}
        <span class="dsn dsn-dbpass">[{{tr}}config-dsn-no_password{{/tr}}]</span>
      {{/if}}
  </code>
{{else}}
  <span class="dsn-empty">{{tr}}CSQLDataSource-msg-DSN empty{{/tr}}</span>
{{/if}}
