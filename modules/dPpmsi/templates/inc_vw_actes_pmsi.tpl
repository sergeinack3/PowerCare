{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-liste-actes', true);
    tabs.setActiveTab('{{$guid_max_nb_actes}}');
    {{foreach from=$sejour->_ref_operations item=_op}}
      PMSI.loadExportActes('{{$_op->_id}}', 'COperation');
    {{/foreach}}
    PMSI.loadExportActes('{{$sejour->_id}}', 'CSejour', 0, "{{$m}}" );
    PMSI.loadDiagsDossier('{{$sejour->_id}}');

    {{if $sejour->type != "ssr"}}
      PMSI.loadDiagsPMSI('{{$sejour->_id}}');
    {{/if}}
  });
</script>

<table class="main layout">
  <tr>
    <td style="white-space:nowrap;" class="narrow">
      <ul id="tabs-liste-actes" class="control_tabs_vertical">
        {{* Séjour *}}
        <li>
          <a href="#{{$sejour->_guid}}" class="{{if $sejour->_count_actes == 0}}empty{{/if}} {{if $sejour->annule}}cancelled{{/if}}"
            >{{tr}}CSejour{{/tr}} (<span id="count_actes_{{$sejour->_guid}}">{{$sejour->_count_actes}}</span>)
            <br/>
            <span>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}</span>
          </a>
        </li>
        {{* Interventions *}}
        {{foreach from=$sejour->_ref_operations item=_op}}
          <li>
            <a href="#{{$_op->_guid}}" class="{{if $_op->_count_actes == 0}}empty{{/if}} {{if $_op->annulee}}cancelled{{/if}}"
              >{{tr var1=$_op->_datetime|date_format:$conf.date}}COperation-Intervention of %s{{/tr}} (<span id="count_actes_{{$_op->_guid}}">{{$_op->_count_actes}}</span>)
              <br/>
              <span>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_praticien}}</span>
            </a>
          </li>
        {{/foreach}}
        {{* Consultations liées au séjour *}}
        {{foreach from=$sejour->_ref_consultations item=_consult}}
          <li>
            <a href="#{{$_consult->_guid}}" class="{{if $_consult->_count_actes == 0}}empty{{/if}} {{if $_consult->annule}}cancelled{{/if}}"
              >{{tr var1=$_consult->_ref_plageconsult->date|date_format:$conf.date}}dPcabinet-Consultation of %s{{/tr}}
                (<span id="count_actes_{{$_consult->_guid}}">{{$_consult->_count_actes}}</span>)
              <br/>
              <span>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_praticien}}</span>
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{* Séjour *}}
      <div id="{{$sejour->_guid}}" style="display: none;">
          {{assign var=acte_key value=$sejour->_guid}}
        {{mb_include module=pmsi template=inc_vw_actes_pmsi_sejour acte_ngap=$actes_ngap.$acte_key}}
      </div>
      {{* Interventions *}}
      {{foreach from=$sejour->_ref_operations item=_op}}
        {{assign var=acte_key value=$_op->_guid}}
        <div id="{{$_op->_guid}}" style="display: none;">
          {{mb_include module=pmsi template=inc_vw_actes_pmsi_interv operation=$_op acte_ngap=$actes_ngap.$acte_key}}
        </div>
      {{/foreach}}
      {{* Consultations liées au séjour *}}
      {{foreach from=$sejour->_ref_consultations item=_consult}}
          {{assign var=acte_key value=$_consult->_guid}}
        <div id="{{$_consult->_guid}}" style="display: none;">
          {{mb_include module=pmsi template=inc_header_actes subject=$_consult}}
          {{mb_include module=pmsi template=inc_codage_actes subject=$_consult acte_ngap=$actes_ngap.$acte_key}}
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
