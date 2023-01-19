{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=count_cancelled_services value=0}}
{{mb_default var=count_cancelled_chambres value=0}}
{{mb_default var=count_cancelled_lits value=0}}
{{mb_default var=count_cancelled_elements value=0}}

<script>
  Main.add(function () {
    PairEffect.initGroup("serviceEffect");
    Infrastructure.initShow();
  });
</script>

<table id="list_services" class="main tbl">
  <tr>
    <th colspan="8" class="title">
      <span style="float:left">
        <button type="button" onclick="Infrastructure.addeditService('0')" class="button new me-primary">
          {{tr}}CService-title-create{{/tr}}
        </button>
        <button type="button" onclick="return new Url('dPhospi', 'lits_import_csv').popup(800, 600);"
                class="import">{{tr}}common-import-structure{{/tr}}</button>
        <button type="button" onclick="return new Url('dPhospi', 'vw_export_infra').addParam('only_actifs', $('show_button').get('hide')).requestModal();"
                class="fas fa-external-link-alt">{{tr}}common-export-structure{{/tr}}</button>
      </span>
      <label id="services_title">{{tr}}CService.all{{/tr}}</label>
      <span>
        <button type="button" data-hide="0" class="zoom-out" style="float:right;" id="show_button"
                title="{{$count_cancelled_services}} services, {{$count_cancelled_chambres}} chambres, {{$count_cancelled_lits}} lits"
                onclick="Infrastructure.toggleService('list_services', this, '{{$count_cancelled_elements}}');">
          {{tr}}CService-hide_cancelled{{/tr}}
        </button>
      </span>
    </th>
  </tr>
  {{foreach from=$services item=_service}}
    <tr id="{{$_service->_guid}}-trigger">
      <td colspan="8" class="me-padding-left-16 {{if $_service->cancelled}}service_cancelled cancelled{{/if}}">
        <button type="button" class="edit notext" onclick="Infrastructure.addeditService('{{$_service->_id}}')"></button>
        <span>{{mb_value object=$_service field=nom}}</span>
        <span>
          ({{$_service->_ref_chambres|@count}} chambre(s))
        </span>
        <span class="compact">
          {{if $_service->description}}
            - {{$_service->description|spancate:150}}
          {{/if}}
        </span>
      </td>
    </tr>
    <tbody class="serviceEffect" id="{{$_service->_guid}}" style="display:none;">
    <tr>
      <th class="category" colspan="8">
        <button class="button add" onclick="Infrastructure.addeditChambre('0', {{$_service->_id}})"
                style="float:left;"> {{tr}}CChambre-title-create{{/tr}}</button>
        {{tr}}CChambre-all{{/tr}} du service {{$_service->_view}}</th>
    </tr>
    <tr>
      <th class="section">{{mb_title class=CChambre field=rank}}</th>
      <th class="section">{{mb_title class=CChambre field=nom}}</th>
      <th class="section">{{tr}}CChambre-back-lits{{/tr}}</th>
      <th class="section">{{mb_title class=CChambre field=caracteristiques}}</th>
      <th class="section">{{mb_title class=CChambre field=lits_alpha}}</th>
      <th class="section">{{mb_title class=CChambre field=is_waiting_room}}</th>
      <th class="section">{{mb_title class=CChambre field=is_examination_room}}</th>
      <th class="section">{{mb_title class=CChambre field=is_sas_dechoc}}</th>
    </tr>
    {{foreach from=$_service->_ref_chambres item=_chambre}}
      {{mb_include module=dPhospi template=inc_vw_chambre_line}}
      {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">{{tr}}CChambre.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CChambre.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
