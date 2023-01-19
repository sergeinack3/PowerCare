{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback value=""}}
{{assign var=statistics value=$source->_ref_last_statistics}}
<script>
  FileSystem = {
    connexion: function (exchange_source_name) {
      var url = new Url("system", "ajaxConnexionFileSystem");
      url.addParam("exchange_source_name", exchange_source_name);
      url.addParam("type_action", "connexion");
      url.requestModal(500, 400);
    },

    sendFile: function (exchange_source_name) {
      var url = new Url("system", "ajaxSendFileFileSystem");
      url.addParam("exchange_source_name", exchange_source_name);
      url.addParam("type_action", "sendFile");
      url.requestModal(500, 400);
    },

    getFiles: function (exchange_source_name) {
      var url = new Url("system", "ajaxGetFileFileSystem");
      url.addParam("exchange_source_name", exchange_source_name);
      url.addParam("type_action", "getFiles");
      url.requestModal("70%", "50%");
    },

    delFile: function (source_guid, path, exchange_source_name) {
      var url = new Url("system", "ajax_delete_file_system");
      url.addParam("source_guid", source_guid);
      url.addParam("path", path);
      url.requestUpdate("systemMsg", {
        onComplete: function () {
          Control.Modal.close();
          FileSystem.getFiles(exchange_source_name);
        }
      });
    },

    toggleDisabled: function (input_name, source_name) {
      console.log(source_name);
      var form = getForm("editSourceFileSystem-" + source_name);
      var input = form.elements[input_name];
      console.log(input);
      input.disabled ? input.disabled = '' : input.disabled = 'disabled';
    }
  }

</script>

<table class="main"> 
  <tr>
    <td>
      <form name="editSourceFileSystem-{{$source->name}}" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, { onComplete : (function() {
              {{if $callback}}{{$callback}}{{/if}}
              
              if (this.up('.modal')) {
                Control.Modal.close();
              } else {
                ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$source->_wanted_type}}');
              }}).bind(this)})">

        <input type="hidden" name="m" value="system" />
        <input type="hidden" name="dosql" value="do_source_file_system_aed" />
        <input type="hidden" name="source_file_system_id" value="{{$source->_id}}" />
        <input type="hidden" name="del" value="0" />

        <fieldset>
          <legend>
            {{tr}}CSourceFileSystem{{/tr}}
            {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
          </legend>

          <table class="form">
            {{mb_include module=system template=CExchangeSource_inc}}

            <tr>
              <th>{{mb_label object=$source field="client_name"}}</th>
              <td>{{mb_field object=$source field="client_name" typeEnum="radio"}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field="fileprefix"}}</th>
              <td>{{mb_field object=$source field="fileprefix"}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field="fileextension"}}</th>
              <td>{{mb_field object=$source field="fileextension"}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field="fileextension_write_end"}}</th>
              <td>{{mb_field object=$source field="fileextension_write_end"}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field="ack_prefix"}}</th>
              <td>{{mb_field object=$source field="ack_prefix"}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field="sort_files_by"}}</th>
              <td>{{mb_field object=$source field="sort_files_by" typeEnum="radio"}}</td>
            </tr>
          </table>
        </fieldset>

        <fieldset>
          <legend>Options de Résilience</legend>
          <table class="main form">
            <tr>
              <th style="width: 120px">{{mb_label object=$source field="retry_strategy"}}</th>
              <td>{{mb_field object=$source  field="retry_strategy" canNull=false disabled='disabled'}}
                <i class="fas fa-lock" onclick="FileSystem.toggleDisabled('retry_strategy','{{$source->name}}');"></i>
              </td>
            </tr>
            <div class="small-info">
              Explication de la stratégie par défaut '1|5 5|60 10|120 20|'</br></br>
              1er appel en erreur la source est bloquée 5 secondes avant un nouvel appel</br>
              5ème appel en erreur à la suite, la source est bloquée 60 secondes avant un nouvel appel</br>
              10ème appel en erreur à la suite, la source est bloquée 120 secondes avant un nouvel appel</br>
              20ème appel en erreur à la suite, la source est bloquée et doit être débloqué manuellement</br>
            </div>
            <div class="small-warning">
              Attention la dernier valeur saisie dans la stratégie doit correspondre au maximum d'appels en erreur à la suite, autorisé avant de bloquer la source
            </div>
          </table>
        </fieldset>

        <table class="main form">
          <tr>
            <td class="button" colspan="2">
              {{if $source->_id}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                <button class="trash" type="button" onclick="confirmDeletion(this.form,
                  { ajax: 1, typeName: '', objName: '{{$source->_view}}'},
                  { onComplete: (function() {
                  if (this.up('.modal')) {
                    Control.Modal.close();
                  } else {
                    ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$source->_wanted_type}}');
                  }}).bind(this.form)})">

                  {{tr}}Delete{{/tr}}
                </button>
                <button type="button" class="lookup" onclick="ExchangeSource.unlock('{{$source->name}}', '{{$source->client_name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                    {{tr}}CSourceFileSystem-unlock{{/tr}}
                </button>
              {{else}}
                <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>

        <fieldset>
          <legend>{{tr}}utilities-source-file_system{{/tr}}</legend>

          <table class="main form">
            <tr>
              <td class="button">
                <!-- Test de connexion -->
                <button type="button" class="search" onclick="FileSystem.connexion('{{$source->name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                  {{tr}}utilities-source-file_system-connexion{{/tr}}
                </button>

                <!-- Dépôt d'un fichier -->
                <button type="button" class="search" onclick="FileSystem.sendFile('{{$source->name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                  {{tr}}utilities-source-file_system-sendFile{{/tr}}
                </button>

                <!-- Liste des fichiers -->
                <button type="button" class="search" onclick="FileSystem.getFiles('{{$source->name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                  {{tr}}utilities-source-file_system-getFiles{{/tr}}
                </button>

              </td>
            </tr>
          </table>
        </fieldset>
      </form>
        {{mb_include module=eai template=inc_statistics_source}}
    </td>
  </tr>
</table>
