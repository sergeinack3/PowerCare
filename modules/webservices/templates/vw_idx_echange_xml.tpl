{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

sendMessage = function(module, echange_xml_id, echange_xml_classname){
  var url = new Url(module, "ajax_send_message");
  url.addParam("echange_xml_id", echange_xml_id);
  url.addParam("echange_xml_classname", echange_xml_classname);
  url.requestUpdate("systemMsg", { onComplete:function() { 
     refreshEchange(echange_xml_id, echange_xml_classname) }});
}

reprocessing = function(echange_xml_id, echange_xml_classname){
  var url = new Url("webservices", "ajax_reprocessing_message_xml");
  url.addParam("echange_xml_id", echange_xml_id);
  url.addParam("echange_xml_classname", echange_xml_classname);
  url.requestUpdate("systemMsg", { onComplete:function() { 
     refreshEchange(echange_xml_id, echange_xml_classname) }});
}

refreshEchange = function(echange_xml_id, echange_xml_classname){
  var url = new Url("webservices", "ajax_refresh_message_xml");
  url.addParam("echange_xml_id", echange_xml_id);
  url.addParam("echange_xml_classname", echange_xml_classname);
  url.requestUpdate("echange_"+echange_xml_id);
}

viewEchange = function(echange_xml_id) {
  var url = new Url(App.m, App.tab);
  url.addParam("echange_xml_id", echange_xml_id);
  url.requestModal(800, 500);
}

function changePage(page) {
  $V(getForm('filterEchange').page,page);
}

</script>

<table class="main">
  {{if !$echange_xml->_id}}
  <!-- Filters -->
  <tr>
    <td style="text-align: center;">
      <form action="?" name="filterEchange" method="get" onsubmit="return Url.update(this, 'listEchangesXML')">
        <input type="hidden" name="m" value="webservices"/>
        <input type="hidden" name="a" value="ajax_refresh_echanges_xml"/>
        <input type="hidden" name="types[]" />
        <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()" />
        <input type="hidden" name="echange_xml_class" value="{{$echange_xml->_class}}" />
        
        <table class="form">
          <tr>
            <th class="category" colspan="4">Choix de la date d'échange</th>
          </tr>
          <tr>
            <th style="width:50%">{{mb_label object=$echange_xml field="_date_min"}}</th>
            <td class="narrow">{{mb_field object=$echange_xml field="_date_min" form="filterEchange" register=true onchange="\$V(this.form.page, 0)"}} </td>
            <th class="narrow">{{mb_label object=$echange_xml field="_date_max"}}</th>
            <td style="width:50%">{{mb_field object=$echange_xml field="_date_max" form="filterEchange" register=true onchange="\$V(this.form.page, 0)"}} </td>
          </tr>
          
          <tr>
            <th class="category" colspan="4">{{tr}}filter-criteria{{/tr}}</th>
          </tr>
          
          <tr>
            <th colspan="2">
              {{mb_label object=$echange_xml field="object_id"}}
            </th>
            <td colspan="2">
              {{mb_field object=$echange_xml field="object_id"}}
            </td>
          </tr>
          
          {{mb_include module=$echange_xml->_ref_module->mod_name template="`$echange_xml->_class`_filter_inc"}}
          
          <tr>
            <td colspan="4" style="text-align: center">
              {{foreach from=$types key=type item=value}}
                <label>
                  <input onclick="$V(this.form.page, 0)" type="checkbox" name="types[{{$type}}]" 
                    {{if array_key_exists($type, $selected_types)}}checked="checked"{{/if}} />
                  {{tr}}{{$echange_xml->_class}}-type-{{$type}}{{/tr}}
                </label>
              {{/foreach}}
            </td>
          </tr>

          <tr>
            <td colspan="4" style="text-align: center">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  
  <tr>
    <td class="halfPane" rowspan="3" id="listEchangesXML">
    </td>
  </tr>
  {{else}}
    {{mb_include template=inc_echange_xml_details}}
  {{/if}}
</table>