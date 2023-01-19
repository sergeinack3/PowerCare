{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Import des tables -->
<script type="text/javascript">

var Action = {
  module: "hprim21",
  
  read: function () {
    var url = new Url(this.module, "httpreq_read_hprim_files");
    url.requestUpdate("read_hprim_files");
  },

  link: function () {
    var url = new Url(this.module, "httpreq_link_hprim_objects");
    url.requestUpdate("link_hprim_objects");
  }
}

</script>

<table class="tbl">
  <tr>
    <th class="category" style="width:15%">{{tr}}Action{{/tr}}</th>
    <th class="category">{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button type="button" class="new" onclick="Action.read()">
        {{tr}}read_hprim_files{{/tr}}
      </button>
    </td>
    <td id="read_hprim_files"></td>
  </tr>
</table>

<table class="tbl">
  <tr>
    <th class="category" style="width:15%">{{tr}}Action{{/tr}}</th>
    <th class="category">{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button type="button" class="new" onclick="Action.link()">
        {{tr}}link_hprim_objects{{/tr}}      
      </button>
    </td>
    <td id="link_hprim_objects"></td>
  </tr>
</table>