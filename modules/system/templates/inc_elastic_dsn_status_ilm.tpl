{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<div class="card_container_column">
    {{if $ilm.exists}}
        {{assign var="ilm_name" value=$ilm.status|@key}}
        {{assign var="ilm" value=$ilm.status.$ilm_name}}
      <div class="card">
        <div class="card_title">
          <p>{{$ilm_name}}</p>
        </div>
        <div class="card_body">
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}Last|f{{/tr}} {{tr}}common-Changing{{/tr}}</h4>
            <p>{{$ilm.modified_date}}</p>
          </div>
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}Version{{/tr}}</h4>
            <p>{{$ilm.version}}</p>
          </div>
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticIndexManager-Used by{{/tr}}</h4>
            <div style="display: flex; flex-wrap: wrap;">
                {{foreach from=$ilm.in_use_by.indices item=_index}}
                  <span class="tag_task">{{$_index}}</span>
                {{/foreach}}
            </div>
          </div>
          {{if $ilm.in_use_by.indices|@count === 0}}
          <button class="btn-error" type="button" style="color: red; border-color: red;"
                  onclick="ElasticDSN.deleteILM('{{$dsn}}', '{{$module}}', this.up())">
            <i class="fa fa-database"></i>
              {{tr}}ElasticIndexManager-action-Delete ILM{{/tr}}
          </button>
          {{/if}}
        </div>
      </div>
        {{if $ilm.policy.phases|@count}}
          <div class="card">
            <div class="card_title">
              <p>{{tr}}ElasticIndexManager-msg-Elasticsearch ILM phases{{/tr}}</p>
            </div>
            <div class="card_body">
                <div class="card_container_row">
                    {{foreach from=$ilm.policy.phases key=_phase_name item=_phase}}
                        {{mb_include module=system template=inc_elastic_dsn_status_ilm_phase phase=$_phase phase_name=$_phase_name inline=true}}
                    {{/foreach}}
                </div>
            </div>
          </div>
        {{/if}}
    {{else}}
      <p>{{tr}}ElasticIndexManager-msg-No ILM{{/tr}}</p>
      <button class="fa fa-database" type="button"
              onclick="ElasticDSN.createILM('{{$dsn}}', '{{$module}}', this.up())">
          {{tr}}ElasticIndexManager-action-Create ILM{{/tr}}
      </button>
    {{/if}}
</div>
