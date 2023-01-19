{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<div class="card_container_column">
  <div class="card">
    <div class="card_title">
      <p>{{$cluster.cluster_name}}</p>
    </div>

    <div class="card_body">
      <div class="card_field">
        <h4 class="card_field_title">{{tr}}config-{{$section}}-metadata-elected{{/tr}}</h4>
        <p>{{$status.server.elected}}</p>
      </div>

      <div class="card_field">
        <h4 class="card_field_title">{{tr}}ElasticIndexManager-Online{{/tr}}</h4>
        <p>{{tr}}{{if $cluster.online}}Yes{{else}}No{{/if}}{{/tr}}</p>
      </div>

      <div class="card_field">
        <h4 class="card_field_title">{{tr}}ElasticIndexManager-Elasticsearch version{{/tr}}</h4>
        <p>{{$cluster.elasticsearch_version}}</p>
      </div>

      <div class="card_field">
        <h4 class="card_field_title">{{tr}}ElasticIndexManager-Lucene version{{/tr}}</h4>
        <p>{{$cluster.lucene_version}}</p>
      </div>
    </div>
  </div>
</div>
