{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ref_module value=bioserveur}}

{{mb_script module=dPfiles script=files}}
{{assign var=_script value=$ref_module|ucfirst}}
{{mb_script module=$ref_module script=$ref_module}}

<script>
  var last_account_id    = "";
  var last_mode_calendar = "unlinked";
  var last_start         = 0;

  /**
   * used to edit account
   */
  edit_account = function(account_id) {
    {{$_script}}.editModal(account_id);
  };

  /**
   * ajax refresh list
   */
  listDocuments = function(account_id, mode, page_start) {
    page_start = page_start ? page_start : 0;
    if (account_id) {
      last_account_id = account_id;
    }
    if (mode) {
      last_mode_calendar = mode;
    }
    if (page_start && page_start != last_start) {
      last_start = page_start;
    }
    if ('{{$_script}}.listDocuments') {
      {{$_script}}.listDocuments(account_id, mode, page_start);
      return false;
    }
    var url = new Url("messagerie", "ajax_list_external_document");
    url.addParam("start", last_start);
    url.addParam("mode", last_mode_calendar);
    url.addParam("account_id", last_account_id);
    url.addParam("class", {{$_script}}._class);
    url.requestUpdate("list_document");
  };

  /**
   * used to do multiple actions on the list
   *
   * @param type
   */
  do_multi_action = function(type) {
    var ids = [];
    $$('#list_document input[class="input_doc"]:checked').each(function(data) {
      ids.push(data.get('object_guid'));
    });

    if (!ids.length) {
      return;
    }

    if (type == "delete") {
      if (!confirm("Êtes vous sur de vouloir supprimer les documents sélectionnés ?")) {
        return;
      }
    }

    if ({{$_script}}.do_multi_action) {
      {{$_script}}.do_multi_action(type, ids, listDocuments);
      return false;
    }

    var url = new Url("messagerie", "do_document_multi_action", "dosql");
    url.addParam("type", type);
    url.addParam("document", ids.length > 0 ? ids.join() : '');
    url.requestUpdate("systemMsg", {method: "post", onComplete:listDocuments});
  };

  /**
   * select all items in the list
   *
   * @param input
   */
  selectAll = function(input) {
    $$('#list_document input[class="input_doc"]').each(function(elt){
      elt.checked = input.checked;
    });
  };

  /**
   * pop the link window
   *
   * @param document_guid
   */
  linkDocument = function(document_guid) {
    if ({{$_script}}.linkDocument) {
      {{$_script}}.linkDocument(document_guid);
      return false;
    }
    var url = new Url("messagerie", "ajax_do_move_file");
    url.addParam("document_guid", document_guid);
    url.requestModal(-40, -40);
    url.modalObject.observe('afterClose', function(){
      listDocuments();
    });
  };

  unlinkDocument = function(document_guid) {
    {{$_script}}.unlinkDocument(document_guid);
  };
</script>

<button type="button" class="add" onclick="edit_account('0');">{{tr}}Add{{/tr}} {{tr}}CBioServeurAccount{{/tr}}</button>

{{if !$users|@count}}
  <p class="empty">{{tr}}CMediusers.none{{/tr}}</p>
{{else}}
  <script>
    Main.add(function() {
      var tabs = Control.Tabs.create("account_list", true);
      tabs.activeLink.onmousedown();
    });
  </script>
  <table class="main">
    <tr>
      <td style="vertical-align: top; width: 15%">
        <ul class="control_tabs_vertical" id="account_list">
          {{foreach from=$users item=_user}}
            <li id="li_tabs_{{$_user->_guid}}">
              <span style="float:left; margin:5px;">
                <button class="edit notext" onclick="edit_account('{{$_user->_id}}')">{{tr}}Edit{{/tr}}</button>
                {{if $ref_module == "bioserveur"}}
                  <button class="change notext" onclick="Bioserveur.updateAccount('{{$_user->_id}}')">Mettre à jour</button>
                {{/if}}
              </span>

              <a href="#list_document"
                 style="font-weight: normal; "  onmousedown="listDocuments('{{$_user->_id}}');">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_user->_guid}}')">{{$_user}}<br/>
                   <small id="count_doc_{{$_user->_guid}}" style="padding:0 3px; font-weight: bold;">
                     {{if $_user->_nb_documents != ""}}
                       ({{$_user->_nb_documents}})
                     {{/if}}
                   </small>
                </span>
              </a>

            </li>
          {{/foreach}}
        </ul>
      </td>
      <td id="list_document" style="width: 85%">
      </td>
    </tr>
  </table>
{{/if}}