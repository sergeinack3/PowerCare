{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    showAccessLogs = function () {
      new Url('system', 'view_access_logs')
        .requestModal('90%', '90%');
    };

    showLongRequestsLogs = function () {
      new Url('system', 'view_long_request_logs')
        .requestModal('90%', '90%');
    };
    
    showUserAgents = function () {
      new Url('system', 'vw_user_agents')
        .requestModal('90%', '90%');
    };

    showCluster = function () {
      new Url('system', 'vw_cluster')
        .requestModal();
    };

    showQueryDigests = function () {
      new Url('system', 'vw_query_digests')
        .requestModal();
    };

    viewAggregationBoard = function () {
      var url = new Url('system', 'ajax_vw_aggregation_board');
      url.requestModal('90%', '90%');
    };
    
    crazyLogs = function () {
      var url = new Url('system', 'vw_crazy_logs');
      url.requestModal('90%', '90%');
    };
    
    purgeCrazyLogs = function (class_name) {
      var url = new Url('system', 'crazy_logs');
      url.addParam("mode", "purge");
      url.addParam("class", class_name);
      url.requestUpdate("crazy_" + class_name);
    };

    viewObjectIndexer = function () {
      new Url('system', 'vw_object_indexer')
        .requestModal('90%', '90%')
    };

    showConfigDashboard = function () {
      new Url('system', 'vw_config_dashboard').requestModal('90%', '90%');
    };
  });
</script>

<table class="main layout tbl me-no-align me-no-box-shadow me-no-bg">
  <tr>
    <td class="halfPane me-no-border" rowspan="2">
      <fieldset>
        <legend>{{tr}}CAccessLog{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="stats me-tertiary" type="button" onclick="showAccessLogs()">{{tr}}CAccessLog{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-view_access_logs-desc{{/tr}}
            </td>
          </tr>
          <tr>
            <td>
              <button class="search me-tertiary" type="button" onclick="crazyLogs()">{{tr}}mod-system-tab-crazy_datasource_logs{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-crazy_datasource_logs-desc{{/tr}}
            </td>
          </tr>
          <tr>
            <td>
              <button class="search me-tertiary" type="button" onclick="viewAggregationBoard()">{{tr}}Aggregation{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_aggregation_board-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    
    <td class="me-no-border">
      <fieldset>
        <legend>{{tr}}mod-system-tab-vw_long_request_logs{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="search me-tertiary" type="button"
                      onclick="showLongRequestsLogs()">{{tr}}mod-system-tab-vw_long_request_logs{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_long_request_logs-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  
  <tr>
    <td class="me-no-border">
      <fieldset>
        <legend>{{tr}}mod-system-tab-vw_user_agents{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="stats me-tertiary" type="button" onclick="showUserAgents()">{{tr}}mod-system-tab-vw_user_agents{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_user_agents-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>

  <tr>
    <td class="me-no-border">
      <fieldset>
        <legend>{{tr}}mod-system-cluster{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="search me-tertiary" type="button" onclick="showCluster()">{{tr}}mod-system-tab-vw_cluster{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_cluster-desc{{/tr}}
            </td>
          </tr>
          <tr>
            <td>
              <button class="search me-tertiary" type="button" onclick="showQueryDigests()">{{tr}}mod-system-tab-vw_query_digests{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_query_digests-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>

    <td class="me-no-border">
      <fieldset>
        <legend>{{tr}}mod-system-tab-vw_object_indexer{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="search me-tertiary" type="button" onclick="viewObjectIndexer()">{{tr}}mod-system-tab-vw_object_indexer{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_object_indexer-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>

  <tr>
    <td class="me-no-border">
      <fieldset>
        <legend>{{tr}}mod-system-tab-vw_config_dashboard{{/tr}}</legend>
        <table class="main layout">
          <tr>
            <td>
              <button class="search me-tertiary" type="button"
                      onclick="showConfigDashboard()">{{tr}}mod-system-tab-vw_config_dashboard{{/tr}}</button>
            </td>
            <td class="text compact">
              {{tr}}mod-system-tab-vw_config_dashboard-desc{{/tr}}
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>

</table>
