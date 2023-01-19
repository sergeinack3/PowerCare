{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7 script=mllp ajax=true}}

{{mb_default var=callback value=""}}

{{assign var=statistics value=$source->_ref_last_statistics}}

<table class="main">
  <tr>
    <td>
      <form name="edtSourceMLLP-{{$source->name}}" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, { onComplete : (function() {
            {{if $callback}}{{$callback}}{{/if}}
        
              if (this.up('.modal')) {
              Control.Modal.close();
              } else {
              ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$source->_wanted_type}}');
              }}).bind(this)})">
        
        <input type="hidden" name="m" value="hl7"/>
        <input type="hidden" name="dosql" value="do_source_mll_aed"/>
        <input type="hidden" name="source_mllp_id" value="{{$source->_id}}"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="name" value="{{$source->name}}"/>
        
        <fieldset>
          <legend>
              {{tr}}CSourceMLLP{{/tr}}
              {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
          </legend>
          
          <table class="form">
              {{mb_include module=system template=CExchangeSource_inc}}
            
            <tr>
              <th>{{mb_label object=$source field="client_name"}}</th>
              <td>{{mb_field object=$source field="client_name" typeEnum="radio"}}</td>
            </tr>
            
            <tr>
              <th>{{mb_label object=$source field="port"}}</th>
              <td>{{mb_field object=$source field="port"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field="timeout_socket"}}</th>
              <td>{{mb_field object=$source field="timeout_socket" register=true increment=true
                  form="edtSourceMLLP-`$source->name`" size=3 step=1 min=0}}
              </td>
            </tr>
            
            <tr>
              <th>{{mb_label object=$source field="timeout_period_stream"}}</th>
              <td>{{mb_field object=$source field="timeout_period_stream" register=true increment=true
                  form="edtSourceMLLP-`$source->name`" size=3 step=1 min=0}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field="set_blocking"}}</th>
              <td>{{mb_field object=$source field="set_blocking"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field="ssl_enabled"}}</th>
              <td>{{mb_field object=$source field="ssl_enabled"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field="ssl_certificate"}}</th>
              <td>{{mb_field object=$source field="ssl_certificate" size="50"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field="ssl_passphrase"}}</th>
                {{assign var=placeholder value="Pas de phrase de passe"}}
                {{if $source->ssl_passphrase}}
                    {{assign var=placeholder value="Phrase de passe enregistrée"}}
                {{/if}}
              <td>{{mb_field object=$source field="ssl_passphrase" placeholder=$placeholder size="50"}}</td>
            </tr>

              {{*          <legend>*}}
              {{*              {{tr}}CSourceFTP-legend-Resilience options{{/tr}}*}}
              {{*              {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}*}}
              {{*          </legend>*}}
              {{*          <table class="form">*}}
            <tr>
              <th></th>
              <td>
                <div class="small-info">
                  Explication de la stratégie par défaut '1|5 5|60 10|120 20|'</br></br>
                  1er appel en erreur la source est bloquée 5 secondes avant un nouvel appel</br>
                  5ème appel en erreur à la suite, la source est bloquée 60 secondes avant un nouvel appel</br>
                  10ème appel en erreur à la suite, la source est bloquée 120 secondes avant un nouvel appel</br>
                  20ème appel en erreur à la suite, la source est bloquée et doit être débloqué manuellement</br>
                </div>
              </td>
            </tr>
            <tr>
              <th></th>
              <td>
                <div class="small-warning">
                  Attention la dernier valeur saisie dans la stratégie doit correspondre au maximum d'appels en erreur à
                  la suite, autorisé avant de bloquer la source
                </div>
              </td>
            </tr>
            <tr>
              <th style="width: 120px">{{mb_label object=$source field="retry_strategy"}}</th>
              <td>{{mb_field object=$source  field="retry_strategy" canNull=false disabled='disabled'}}
                <i class="fas fa-lock" onclick="FTP.toggleDisabled('retry_strategy','{{$source->name}}');"></i>
              </td>
            </tr>
            
            
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
                  {{else}}
                    <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
                  {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
        
        <fieldset>
          <legend>{{tr}}utilities-source-mllp{{/tr}}</legend>
          
          <table class="main form">
            <!-- Test connexion MLLP -->
            <tr>
              <td class="button">
                <button type="button" class="search" onclick="MLLP.connexion('{{$source->name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                    {{tr}}utilities-source-mllp-connexion{{/tr}}
                </button>
                
                <button type="button" class="search" onclick="MLLP.send('{{$source->name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                    {{tr}}utilities-source-mllp-send{{/tr}}
                </button>

                <button type="button" class="lookup" onclick="ExchangeSource.unlock('{{$source->name}}', '{{$source->client_name}}');"
                        {{if !$source->_id}}disabled{{/if}}>
                    {{tr}}CSourceMLLP-unlock{{/tr}}
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
