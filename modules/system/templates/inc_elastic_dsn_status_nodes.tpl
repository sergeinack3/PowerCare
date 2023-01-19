{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="card_container_row" style="padding: 0 40px">
    {{foreach from=$nodes key=_name item=_node}}
      <div class="card" style="flex: 1;">
        <div class="card_title">
          <p>{{$_name}}</p>
        </div>

        <div class="card_body">
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}CMbFieldSpec.type.ipAddress{{/tr}}</h4>
            <p>{{$_node.ip}}</p>
          </div>

          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-Elasticsearch version{{/tr}}</h4>
            <p>{{$_node.version}}</p>
          </div>

          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-Java version{{/tr}}</h4>
            <p>{{$_node.java_version}}</p>
          </div>

          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-msg-Node started on{{/tr}}</h4>
            <p>{{$_node.date_start}}</p>
          </div>

          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-msg-Memory used{{/tr}}</h4>
            <p>{{$_node.memory}} {{tr}}common-Byte|pl{{/tr}}</p>
          </div>

          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-Roles{{/tr}}</h4>
            <div style="text-align: start">
                {{foreach from=$_node.roles item=_role}}
                  <span class="tag_task">{{$_role}}</span>
                {{/foreach}}
            </div>
          </div>
        </div>
      </div>
    {{/foreach}}
</div>
