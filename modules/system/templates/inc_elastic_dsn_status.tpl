{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .card_container_column {
    display: flex;
    align-items: stretch;
    flex-flow: column;
    gap: 16px;
    padding: 0 40px;
  }

  .card_container_row {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px
  }

  .card {
    border: 1px #999999 solid;
    border-radius: 5px;
  }

  .card_title {
    background-color: #EEEEEE;
    border-bottom: 1px #999999 solid;
    font-weight: bold;
    font-size: 1.5em;
    padding: 8px;
  }

  .card_body {
    display: flex;
    flex-flow: column;
    padding: 8px;
  }

  .card_field {
    display: flex;
    gap: 8px;
  }

  .card_field_title {
    width: 128px;
    margin: 0;
    flex-shrink: 0;
    display: inline;
  }

  .card_field_title + * {
    flex: 1;
  }

  .btn-error {
    color: firebrick !important;
    border-color: #f44336 !important;
    margin-top: 16px !important;
  }
</style>

{{assign var="has_index" value="index"|array_key_exists:$status}}
{{assign var="has_ilm" value="ilm"|array_key_exists:$status}}

{{if !"errors"|array_key_exists:$status}}
    {{if $status|@count}}
      <script>
        Main.add(function () {
          Control.Tabs.create('tabs-info', true);
        });
      </script>
      <div style="display: flex; flex-flow: column; gap: 8px">
        <div style="width: 100%">
          <ul class="control_tabs" id="tabs-info" style="padding-top: 0">
            <li>
              <a href="#cluster">
                  {{if $status.server.online}}
                    <i class="fa fa-check"></i>
                  {{else}}
                    <i class="fa fa-times"></i>
                  {{/if}}
                  {{tr}}ElasticIndexManager-Cluster status{{/tr}}
              </a>
            </li>
            <li>
              <a href="#nodes">
                  {{tr}}ElasticIndexManager-Node status{{/tr}}
              </a>
            </li>
              {{if $has_index}}
                <li>
                  <a href="#index">
                      {{if $status.index.exists}}
                        <i class="fa fa-check"></i>
                      {{else}}
                        <i class="fa fa-times"></i>
                      {{/if}}
                      {{tr}}Indexes{{/tr}}
                  </a>
                </li>
              {{/if}}
              {{if $has_ilm}}
                  {{if $status.ilm.has}}
                    <li><a href="#ilm">
                            {{if $status.ilm.exists}}
                              <i class="fa fa-check"></i>
                            {{else}}
                              <i class="fa fa-times"></i>
                            {{/if}}
                            {{tr}}ElasticIndexManager-Index lifecycle management{{/tr}}
                      </a>
                    </li>
                  {{/if}}
              {{/if}}
          </ul>
        </div>

        <div style="width: 100%">
          <div style="display: none" id="cluster">
              {{mb_include module=system template=inc_elastic_dsn_status_cluster cluster=$status.server inline=true}}
          </div>

          <div style="display: none" id="nodes">
              {{mb_include module=system template=inc_elastic_dsn_status_nodes nodes=$status.nodes inline=true}}
          </div>
            {{if $has_index}}
              <div style="display: none" id="index">
                  {{mb_include module=system template=inc_elastic_dsn_status_index index=$status.index _template=$status.template inline=true}}
              </div>
            {{/if}}
            {{if $has_ilm}}
                {{if $status.ilm.has}}
                  <div style="display: none" id="ilm">
                      {{mb_include module=system template=inc_elastic_dsn_status_ilm ilm=$status.ilm inline=true}}
                  </div>
                {{/if}}
            {{/if}}
        </div>
      </div>
        {{if $has_index}}
            {{if !$status.index.exists || !$status.template.exists || ($status.ilm.has && !$status.ilm.exists)}}
              <div
                style="display: flex; align-items: center; justify-content: space-between; flex-flow: column; gap: 8px; padding-top: 8px">
                <button class="fa fa-database" type="button" style="width: 80%"
                        onclick="ElasticDSN.init('{{$dsn}}', '{{$module}}', this.up())">
                    {{tr}}ElasticObjectManager-action-Init{{/tr}}
                </button>
              </div>
            {{/if}}
        {{/if}}
    {{else}}
      <p class="empty">{{tr}}config-{{$section}}-no_metadata{{/tr}}</p>
    {{/if}}
{{else}}
    {{foreach from=$hosts item=_host}}
        {{mb_include module=system template=inc_test_elastic_dsn_failure dsn=$dsn host=$_host errors=$status.errors}}
    {{/foreach}}
{{/if}}
