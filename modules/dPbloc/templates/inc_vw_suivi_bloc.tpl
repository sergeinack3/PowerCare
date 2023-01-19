{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  div.suivi {
    height:60px;
    width:120px;
    float: left;
    margin-top: 3px;
    margin-bottom: 1px;
    padding: 1px;
    white-space:normal;
    overflow-y: auto;
  }
  div.suivi_evolve {
    width:100px;
    height:12px;
  }
  .suivi_case {
    height: 10px;
    float: left;
    border: 1px solid #ccc;
  }
  .suivi_case.brancard, .suivi_case.interv{
    width: 23%;
  }
  .suivi_case.sspi{
    width: 31%;
  }
  .suivi_green {
    background-color: green;
  }
  .suivi_white, #tooltip-legende-suivi td {
    background-color: white;
  }
  #table_sspi_suivi td, #table_programme_suivi td {
    background-color: white;
  }
</style>

{{if $type_view == "all" || $type_view == "interv"}}
  <table class="main tbl" id="table_programme_suivi">
    <tr>
      <th rowspan="2" class="category narrow">{{tr}}CSalle{{/tr}}</th>
      <th colspan="2" class="category">{{tr}}Programme{{/tr}}</th>
      {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
        <th rowspan="2" class="category narrow">{{tr}}CBrancardage{{/tr}}</th>
      {{/if}}
      <th rowspan="2" class="category narrow">En salle</th>
    </tr>
    <tr>
      <th class="section">Planifié</th>
      <th class="section">Hors plage</th>
    </tr>
    {{foreach from=$op_salles item=_ops_salle key=_salle_id}}
      {{assign var=_bloc_id value=$salles.$_salle_id->bloc_id}}
      <tr>
        <td>{{if !is_array($blocs_ids) || !$blocs_ids|@count}}{{$blocs.$_bloc_id->nom}} - {{/if}}{{$salles.$_salle_id->nom}}</td>
        <td>
          {{foreach from=$_ops_salle.op item=operation}}
            {{mb_include module=bloc template=vw_patient_suivi_bloc}}
          {{/foreach}}
        </td>
        <td>
          {{foreach from=$_ops_salle.op_hp item=operation}}
            {{mb_include module=bloc template=vw_patient_suivi_bloc}}
          {{/foreach}}
        </td>
        {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
          <td>
            {{foreach from=$_ops_salle.brancardage item=operation}}
              {{mb_include module=bloc template=vw_patient_suivi_bloc type_cell="brancardage"}}
            {{/foreach}}
          </td>
        {{/if}}
        <td>
          {{foreach from=$_ops_salle.interv item=operation}}
            {{mb_include module=bloc template=vw_patient_suivi_bloc type_cell="interv"}}
          {{/foreach}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

{{if $type_view == "all" || $type_view == "sspi"}}
  <table class="main tbl" id="table_sspi_suivi">
    <tr>
      <th class="category narrow">Attente</th>
      <th class="category">{{tr}}SSPI.Reveil{{/tr}}</th>
    </tr>
    {{if (!isset($sspis.attente|smarty:nodefaults) || !$sspis.attente|@count) && (!isset($sspis.sspi|smarty:nodefaults) || !$sspis.sspi|@count)}}
      <tr>
        <td colspan="2" class="empty">Aucun patient en SSPI</td>
      </tr>
    {{else}}
      <tr>
        <td>
          {{foreach from=$sspis.attente item=_attente_salle key=salle_id}}
            {{assign var=_bloc_id value=$salles.$salle_id->bloc_id}}
            <div style="float: left;">
              <strong style="margin: 3px;">{{if !is_array($blocs_ids) || !$blocs_ids|@count}}{{$blocs.$_bloc_id->nom}} - {{/if}}{{$salles.$salle_id->nom}}</strong><br/>
              {{foreach from=$_attente_salle item=operation}}
                {{mb_include module=bloc template=vw_patient_suivi_bloc type_cell="sspi"}}
              {{/foreach}}
            </div>
          {{/foreach}}
        </td>
        <td style="padding:0;vertical-align: top;" >
          <table class="main tbl">
            <tr>
              {{math equation="100 / x" x=$sspis.sspi|@count assign=pourcentage format="%.1f"}}
              {{foreach from=$sspis.sspi item=_attente_salle key=salle_id}}
                {{assign var=_bloc_id value=$salles.$salle_id->bloc_id}}
                <th class="section" style="width: {{$pourcentage}}%;">{{if !is_array($blocs_ids) || !$blocs_ids|@count}}{{$blocs.$_bloc_id->nom}} - {{/if}}{{$salles.$salle_id->nom}}</th>
              {{/foreach}}
            </tr>
            <tr>
              {{foreach from=$sspis.sspi item=_attente_salle key=salle_id}}
                <td>
                  {{assign var=_bloc_id value=$salles.$salle_id->bloc_id}}
                  {{foreach from=$_attente_salle item=operation}}
                    {{mb_include module=bloc template=vw_patient_suivi_bloc type_cell="sspi"}}
                  {{/foreach}}
                </td>
              {{/foreach}}
            </tr>
          </table>
        </td>
      </tr>
    {{/if}}
  </table>
{{/if}}