{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="files" script="file" ajax=true}}

{{mb_include module=system template=inc_pagination total=$total_passages current=$page change_page='CExtractPassages.changePage' jumper='10' step=25}}

<form name="search-extract_passages_id" action="" method="get"
      onsubmit="return CExtractPassages.doesExtractPassagesExist($V($('extract_passages_id')));" style="float: right; clear: both;">
  <div class="me-margin-bottom-8">
    <input type="search" id="extract_passages_id" name="extract_passages_id" required placeholder="{{tr}}CExtractPassages-extract_passages_id{{/tr}}" />
    <button type="submit" class="lookup notext">{{tr}}search_extract_passages_id-button{{/tr}}</button>
  </div>
</form>

<table class="tbl">
  <tr>
    <th>{{mb_title object=$extractPassages field="extract_passages_id"}}</th>
    <th>{{tr}}Actions{{/tr}}</th>
    <th>{{mb_title object=$extractPassages field="type"}}</th>
    <th>{{mb_title object=$extractPassages field="rpu_sender"}}</th>
    <th>{{mb_title object=$extractPassages field="date_extract"}}</th>
    <th>{{mb_title object=$extractPassages field="debut_selection"}}</th>
    <th>{{mb_title object=$extractPassages field="fin_selection"}}</th>
    <th>{{mb_title object=$extractPassages field="_nb_rpus"}}</th>
    <th>{{mb_title object=$extractPassages field="date_echange"}}</th>
    <th>{{mb_title object=$extractPassages field="nb_tentatives"}}</th>
    <th>{{mb_title object=$extractPassages field="message_valide"}}</th>
    <th>{{tr}}CFile{{/tr}}</th>
  </tr>

  {{foreach from=$listPassages item=_passage}}
    <tr>
      <td class="narrow">
        <a href="#1" onclick="CExtractPassages.popupEchangeViewer('{{$_passage->_id}}')" class="button search">
          {{$_passage->_id|str_pad:6:'0':$smarty.const.STR_PAD_LEFT}}
        </a>

        {{if $can->admin}}
        <button type="button" class="edit notext"
                onclick="CExtractPassages.editPassage('{{$_passage->_id}}')">
          {{tr}}Edit{{/tr}}
        </button>
        {{/if}}

        <a target="blank" href="?m=urgences&raw=download_echange&extract_passages_id={{$_passage->_id}}"
           class="button fas fa-download notext" title="{{tr}}Download{{/tr}}"></a>
        {{if $can->admin}}
          <form name="Purge-{{$_passage->_guid}}" action="?m={{$m}}&tab=vw_extract_passages" method="post"
                onsubmit="return confirmCreation(this)">
            <input type="hidden" name="dosql" value="do_extract_passages_aed" />
            <input type="hidden" name="m" value="dPurgences" />
            <input type="hidden" name="tab" value="vw_extract_passages" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="_purge" value="0" />
            <input type="hidden" name="extract_passages_id" value="{{$_passage->_id}}" />

            <script>
              confirmPurge{{$_passage->_id}} = function(form) {
                if (confirm("ATTENTION : Vous êtes sur le point de purger l'extraction d'un passage !")) {
                  form._purge.value = "1";
                  confirmDeletion(form,  {
                    typeName:'l\'extraction de passage',
                    objName:'{{$_passage->_view|smarty:nodefaults|JSAttribute}}'
                  } );
                }
              }
            </script>
            <button {{if !$_passage->message_valide}}disabled{{/if}} type="button" class="cancel notext"
                    onclick="confirmPurge{{$_passage->_id}}(this.form);">
              {{tr}}Purge{{/tr}}
            </button>
          </form>
        {{/if}}
      </td>
      <td class="narrow compact">
        <button {{if !$_passage->message_valide}}disabled{{/if}} class="lock notext" type="button" id="encrypt_rpu"
                onclick="CExtractPassages.encrypt({{$_passage->_id}})" title="{{tr}}Encrypt{{/tr}}">{{tr}}Encrypt{{/tr}}</button>
        <button {{if !$_passage->message_valide}}disabled{{/if}} type="button" class="send notext"
                onclick="CExtractPassages.sendPassage('{{$_passage->_id}}', '{{$_passage->type}}')">{{tr}}Transmit{{/tr}}</button>
        <div id="result_send_passage-{{$_passage->_id}}"></div>
      </td>

      <td class="narrow">
        {{mb_value object=$_passage field="type"}}
      </td>
      <td class="narrow">
        {{tr}}{{mb_value object=$_passage field="rpu_sender"}}{{/tr}}
      </td>
      <td class="narrow">
        <label title='{{mb_value object=$_passage field="date_extract"}}'>
          {{mb_value object=$_passage field="date_extract" format=relative}}
        </label>
      </td>
      <td class="narrow">
        {{mb_value object=$_passage field="debut_selection"}}
      </td>
      <td class="narrow">
        {{mb_value object=$_passage field="fin_selection"}}
      </td>
      <td class="narrow {{if $_passage->type != "rpu"}}arretee{{/if}}">
        {{if $_passage->type == "rpu"}}
          {{mb_value object=$_passage field="_nb_rpus"}}
        {{/if}}
      </td>
      <td class="narrow">
        <label title='{{mb_value object=$_passage field="date_echange"}}'>
          {{mb_value object=$_passage field="date_echange" format=relative}}
        </label>
      </td>
      <td class="narrow {{if $_passage->nb_tentatives > 5}}warning{{/if}}">
        {{mb_value object=$_passage field="nb_tentatives"}}
      </td>
      <td class="narrow {{if !$_passage->message_valide}}error{{/if}}">
        {{mb_value object=$_passage field="message_valide"}}
      </td>
      <td id="file_passage_{{$_passage->_id}}" class="narrow">
        {{mb_include template=inc_extract_file}}
      </td>

    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="18" class="empty">{{tr}}CExtractPassages.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
