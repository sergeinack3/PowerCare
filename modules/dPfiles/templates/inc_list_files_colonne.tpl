{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=alerts_anormal_result value=false}}
{{mb_default var=alerts_new_result value=false}}

<script>
  Main.add(function() {
    {{if isset($category_id|smarty:nodefaults)}}
      // Mise à jour des compteurs des documents
      var countDocItemTotal = {{$nbItems}};

      {{assign var=count_docitems value=0}}
      {{foreach from=$list item=_item}}
        {{if !$_item->annule}}
          {{math equation=x+1 x=$count_docitems assign=count_docitems}}
        {{/if}}
      {{/foreach}}

      var countDocItemCat = {{$count_docitems}};
      var button = $("docItem_{{$object->_guid}}");

      Control.Tabs.setTabCount("Category-{{$category_id}}", countDocItemCat);

      if (button) {
        button.update(countDocItemTotal);
        if (!countDocItemTotal) {
          button.addClassName("right-disabled");
          button.removeClassName("right");
        }
        else {
          button.removeClassName("right-disabled");
          button.addClassName("right");
        }
      }
    {{/if}}

    {{if !$app->touch_device}}
    // Draggable
    $$(".droppable").each(function(li) {
      Droppables.add(li, {
        onDrop: function(from, to, event) {
          Event.stop(event);
          var destGuid = to.get("guid");
          var fromGuid = from.get("targetFrom");
          var idFile   = from.get("id");
          var classFile   = from.get("class");
          var url = new Url("files","controllers/do_move_file");
          url.addParam("object_id", idFile);
          url.addParam("object_class", classFile);
          url.addParam("destination_guid", destGuid);
          url.requestUpdate("systemMsg", function() {
            $("docItem_"+destGuid).onclick();   //update destination
            $("docItem_"+fromGuid).onclick();   //update provenance
          });
        },
        accept: 'draggable',
        hoverclass:'dropover'
      });
    });

    $$(".draggable").each(function(a) {
      new Draggable(a, {
        onEnd: function(element, event) {
          Event.stop(event);
        },
        ghosting: true});
    });
    {{/if}}
  });
</script>

{{foreach from=$list item=_doc_item}}
  <div style="float: left; width: 240px; position: relative; {{if $_doc_item->annule}}display: none;{{/if}}"
       class="me-file-card {{if $_doc_item->annule}}file_cancelled{{/if}}">
    <table class="tbl me-no-hover">
      <tbody class="hoverable">
        <tr class="{{if $_doc_item->annule}}hatching{{/if}}">
          <td rowspan="2" style="width: 70px; height: 112px; text-align: center">
            {{assign var="elementId" value=$_doc_item->_id}}
            {{if $_doc_item->_class=="CCompteRendu"}}
              {{if $app->user_prefs.pdf_and_thumbs}}
                {{assign var=document_id value=$_doc_item->_id}}
                {{assign var=document_class value=CCompteRendu}}
              {{else}}
                {{assign var=document_class value=medifile}}
                {{assign var=document_id value=''}}
              {{/if}}
            {{else}}
              {{assign var=document_id value=$elementId}}
              {{assign var=document_class value=CFile}}
            {{/if}}

            <span {{if !$app->touch_device}}ondblclick{{else}}onclick{{/if}}="popFile('{{$object->_class}}', '{{$object->_id}}', '{{$_doc_item->_class}}', '{{$elementId}}', '0');">
              {{assign var=_class value=thumbnail}}
              {{if !$app->touch_device}}
                {{assign var=_class value="$_class draggable"}}
              {{/if}}
              {{thumbnail profile=medium document_id=$document_id document_class=$document_class class=$_class
                style="max-width:64px; max-height:92px" data_id=$elementId data_class=$_doc_item->_class
                data_targetForm="`$_doc_item->object_class`-`$_doc_item->object_id`"}}
            </span>
          </td>

          <!-- Tooltip -->
          <td class="text me-file-card-title" style="height: 35px; overflow: auto">
              {{mb_include module=files template="inc_file_synchro" docItem=$_doc_item}}
              {{mb_include module=files template="inc_file_send" docItem=$_doc_item}}

              {{if $_doc_item|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu' && $_doc_item->_is_locked}}
              {{me_img src="lock.png" icon="lock" class="me-primary" onmouseover="ObjectTooltip.createEx(this, '`$_doc_item->_guid`', 'locker')"}}
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_doc_item->_guid}}');">
              {{$_doc_item->_view|truncate:60}}
              {{if $_doc_item->private}}
                &mdash; <em>{{tr}}CCompteRendu-private{{/tr}}</em>
              {{/if}}
            </span>
            {{if ($alerts_anormal_result || $alerts_new_result) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
              <br>
              <span style="float: right">
                {{assign var=doc_id value=$_doc_item->_id}}
                {{if array_key_exists($doc_id, $alerts_anormal_result)}}
                  <span id="OxLaboAlert_{{$doc_id}}">
                    {{mb_include module=oxLaboClient template=vw_alerts object=$object object_id=$object->_id object_class=$object->_class response_id=$doc_id response_type='file' nb_alerts=$alerts_anormal_result.$doc_id.total alerts=$alerts_anormal_result.$doc_id}}
                  </span>
                {{/if}}
                {{if array_key_exists($doc_id, $alerts_new_result)}}
                  <span id="OxLaboNewAlert_{{$doc_id}}">
                  {{mb_include module=oxLaboClient template=vw_alerts object=$object object_id=$object->_id object_class=$object->_class response_id=$doc_id response_type='file' nb_alerts=$alerts_new_result.$doc_id|@count alerts=$alerts_new_result.$doc_id alert_new_result=true}}
                </span>
                {{/if}}
              </span>
            {{/if}}
          </td>
        </tr>
        <tr class="{{if $_doc_item->annule}}hatching{{/if}}">
          <!-- Toolbar -->
          <td class="button me-text-align-right" style="height: 1px;">
            {{mb_include module=files template=inc_file_toolbar notext=notext}}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
{{foreachelse}}
  <div class="empty">{{tr}}CMbObject-back-documents.empty{{/tr}}</div>
{{/foreach}}
