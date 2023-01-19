{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="card_container_column">
  <div class="card">
    <div class="card_title">
      <p>{{$index.name}}</p>
    </div>

    <div class="card_body">
      <div class="card_field">
        <h4 class="card_field_title">{{tr}}ElasticIndexManager-Exists{{/tr}}</h4>
        <p>{{tr}}{{if $index.exists}}Yes{{else}}No{{/if}}{{/tr}}</p>
      </div>
        {{if $index.exists}}
          <button class="btn-error" type="button"
                  onclick="ElasticDSN.deleteIndex('{{$dsn}}', '{{$module}}', this.up())">
              <i class="fa fa-database"></i>
              {{tr}}ElasticObjectManager-action-Delete index{{/tr}}
          </button>
        {{else}}
          <button class="fa fa-database" type="button"
                  onclick="ElasticDSN.createIndex('{{$dsn}}', '{{$module}}', this.up())">
              {{tr}}ElasticObjectManager-action-Create index{{/tr}}
          </button>
        {{/if}}
    </div>
  </div>

  <div class="card">
    <div class="card_title">
      <p>{{$_template.name}}</p>
    </div>

    <div class="card_body">
      <div class="card_field">
        <h4 class="card_field_title">{{tr}}ElasticIndexManager-Exists{{/tr}}</h4>
        <p>{{tr}}{{if $_template.exists}}Yes{{else}}No{{/if}}{{/tr}}</p>
      </div>
        {{if $_template.exists}}
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}common-Option|pl{{/tr}}</h4>
              {{$_template.settings|highlight:"js"}}
          </div>
          <div class="card_field">
            <h4 class="card_field_title">{{tr}}ElasticObject-Mappings{{/tr}}</h4>
              {{$_template.mappings|highlight:"js"}}
          </div>
          <button class="btn-error" type="button"
                  onclick="ElasticDSN.deleteTemplate('{{$dsn}}', '{{$module}}', this.up())">
            <i class="fa fa-database"></i>
              {{tr}}ElasticObjectManager-action-Delete template{{/tr}}
          </button>
        {{else}}
          <button class="fa fa-database" type="button"
                  onclick="ElasticDSN.createTemplate('{{$dsn}}', '{{$module}}', this.up())">
              {{tr}}ElasticObjectManager-action-Create template{{/tr}}
          </button>
        {{/if}}
    </div>
  </div>
  <div class="card_container_row">
      {{foreach from=$index.mappings key=_name item=_mapping}}
        <div class="card" style="flex: 1; ">
          <div class="card_title">
            <p>{{$_name}}</p>
          </div>
          <div class="card_body">
              {{$_mapping.mappings.properties|highlight:"js"}}
          </div>
        </div>
      {{/foreach}}
  </div>
</div>
