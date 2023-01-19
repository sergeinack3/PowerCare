{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Redis = {
    electMaster: function (id) {
      var url = new Url("system", "do_elect_redis_master", "dosql");
      url.addParam("redis_server_id", id);
      url.requestUpdate(SystemMessage.id, {
        method: "post", onComplete: Redis.listServers
      });
    },
    listServers: function () {
      var url = new Url("system", "ajax_list_redis_servers");
      url.requestUpdate("redis-servers");
    },

    loadServerInfo: function (id) {
      var url = new Url("system", "ajax_redis_server_info");
      url.addParam("redis_server_id", id);
      url.requestUpdate("redis-data");
    },

    edit: function (id) {
      var url = new Url("system", "ajax_redis_server_edit");
      url.addParam("redis_server_id", id);
      url.requestModal(400, 400, {onClose: Redis.listServers});
    },

    makeServersFromConfig: function () {
      var url = new Url("system", "do_make_redis_servers", "dosql");
      url.requestUpdate(SystemMessage.id,
        {
          method:     "post",
          onComplete: Redis.listServers
        }
      );
    }
  };

  Main.add(function(){
    Redis.listServers();

    setInterval(Redis.listServers, 60000);
  });
</script>

<table class="main layout">
  <tr>
    <td style="width: 30%;">
      <button class="new" onclick="Redis.edit(0)">{{tr}}CRedisServer-title-create{{/tr}}</button>
      <button class="change notext" onclick="Redis.listServers()">{{tr}}Refresh{{/tr}}</button>
      <div id="redis-servers"></div>
    </td>
    <td id="redis-data"></td>
  </tr>
</table>