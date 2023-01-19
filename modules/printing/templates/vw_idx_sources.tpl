{{*
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function editSource(id, klass) {
    var action = "ajax_edit_source_";
    if (!klass) {
      return;
    }
    switch (klass) {
      case "CSourceLPR":
        action += "lpr";
        break;
      case "CSourceSMB":
        action += "smb";
    }
    var url = new Url("printing", action);
    url.addParam("source_id", id);
    url.addParam("class", klass);
    url.requestUpdate("edit_source");
  }

  function refreshList() {
    var url = new Url("printing", "ajax_list_sources");
    url.requestUpdate("list_sources");
  }

  function testPrint(klass, id) {
    var url = new Url("printing", "ajax_test_print");
    url.addParam("id", id);
    url.addParam("class", klass);
    url.requestUpdate("result_print");
  }

  Main.add(function() {
    refreshList();
    editSource('{{$source_id}}', '{{$class}}');
  });
</script>

<table class="main me-align-auto me-margin-top-4">
  <tr>
    <td colspan="2">
      <select id="type_source" onchange="removeSelected(); editSource(0, this.value);">
        <option value="">
          &mdash; {{tr}}CSourceLPR.choose_type{{/tr}}
        </option>
        <option value="CSourceLPR">
          {{tr}}CSourceLPR{{/tr}}
        </option>
        <option value="CSourceSMB">
          {{tr}}CSourceSMB{{/tr}}
        </option>
      </select>
    </td>
  </tr>
  <tr>
    <td id="list_sources" style="width: 45%;"></td>
    <!-- Création / Modification de la source -->
    <td id="edit_source"></td>
  </tr>
</table>