{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=HistoryViewer}}

<script>
  Main.add(function(){
    var classTree = $("class-tree");
    var tree = new TreeView(classTree.down("ul"));
    tree.collapseAll();

    ViewPort.SetAvlHeight(classTree, 1);
    ViewPort.SetAvlHeight($("history-line"), 1);

    document.on('click', 'input.history', function(e) {
      var element = e.element();
      var path = element.value;

      if (element.checked) {
        if (!HistoryViewer.loadedPaths[path]) {
          HistoryViewer.loadedPaths[path] = true;
          HistoryViewer.loadHistory(path, '{{$object->_class}}', '{{$object->_id}}');
          return;
        }

        $$("tr[data-path='"+path+"']").invoke("show");
      }

      if (!element.checked) {
        $$("tr[data-path='"+path+"']").invoke("hide");
      }
    });

    HistoryViewer.loadHistory(null, '{{$object->_class}}', '{{$object->_id}}');
  });
</script>

<style>
  .treeview .backref > label {
    color: #009a24;
  }
  .treeview .fwdref > label {
    color: #00179a;
  }

  #history-logs .changes {
    padding: 0 !important;
  }

  #history-logs .changes table,
  #history-logs .changes th,
  #history-logs .changes td {
    border: 0;
  }

  #history-logs .changes td.fieldname {
    font-weight: bold;
    width: 180px;
    text-align: right;
  }

  #history-logs .changes td.before {
    width: 200px;
  }

  #history-logs .changes tr:nth-child(odd) td {
    background: rgba(217, 217, 217, 0.3);
  }

  #history-logs .changes tr:nth-child(even) td {
    background: rgba(169, 169, 169, 0.3);
  }

  #history-logs tr.type-create .type-cell {
    background: #cbecca;
  }

  #history-logs tr.type-store .type-cell {
    background: #d2d7ec;
  }

  #history-logs tr.type-merge .type-cell {
    background: #ecdacb;
  }

  #history-logs tr.type-delete .type-cell {
    background: #ecc3be;
  }

  #history-logs > tr > td {
    border-bottom: 1px solid #999;
  }
</style>

<h3>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')">{{$object}}</span>
</h3>

<table class="main layout">
  <tr>
    <td style="width: 300px;">
      <div id="class-tree">
        {{mb_include module=system template=inc_full_history_tree tree=$tree deepness=0 path=null}}
      </div>
    </td>
    <td>
      <div id="history-line">
        <table class="main tbl">
          <tr>
            <th class="narrow"></th>
            <th>{{mb_title class=CUserLog field=date}}</th>
            <th>{{mb_title class=CUserLog field=object_class}}</th>
            <th>{{mb_title class=CUserLog field=object_id}}</th>
            <th>{{mb_title class=CUserLog field=user_id}}</th>
            <th></th>
          </tr>
          <tbody id="history-logs"></tbody>
        </table>
      </div>
    </td>
  </tr>
</table>