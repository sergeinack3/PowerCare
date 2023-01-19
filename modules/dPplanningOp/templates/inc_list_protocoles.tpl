{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=appFine_pack_id value=false}}

<script>
{{if $dialog}}
  if(!aProtocoles) {
    aProtocoles = {};
  }
  
  {{foreach from=$list_protocoles item=_protocole}}
    aProtocoles[{{$_protocole->_id}}] = {
      {{mb_include module=planningOp template=inc_js_protocole protocole=$_protocole nodebug=true}}
    };
  {{/foreach}}
{{else}}
  Main.add(function() {
    Control.Tabs.setTabCount('{{$type}}', '{{$total_protocoles}}');
  });
{{/if}}

</script>

{{if !count($list_protocoles)}}
  <div class="small-info">
  {{tr}}CProtocole.none{{/tr}} n'est disponible, veuillez commencer par 
  créer un protocole afin de l'utiliser pour planifier un séjour
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=inc_pagination total=$total_protocoles current=$page.$type change_page="ProtocoleDHE.changePage$type" step=$step}}

<table class="tbl">
  <tr>
    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf && $appFine_pack_id}}
      <th class="narrow">{{tr}}AppFine{{/tr}}</th>
    {{/if}}
    <th colspan="2">
      {{if $type == "interv"}}
        {{mb_title class=CProtocole field=libelle}}
      {{else}}
        {{mb_title class=CProtocole field=libelle_sejour}}
      {{/if}}
    </th>
    <th>
      {{if $type == "interv"}}
        {{mb_title class=CProtocole field=codes_ccam}}
      {{else}}
        {{mb_title class=CProtocole field=DP}}
      {{/if}}
    </th>
    <th>
      {{mb_title class=CProtocole field=type}}
    </th>
    <th>
      {{mb_title class=CProtocole field=duree_hospi}}
    </th>

    {{if $type == "interv"}}
    <th>
      {{mb_title class=CProtocole field=cote}}
    </th>
    <th>
      {{mb_title class=CProtocole field=temp_operation}}
    </th>
    {{else}}
    <th>
      {{mb_title class=CProtocole field=convalescence}} /
      {{mb_title class=CProtocole field=rques_sejour}}
    </th>
    {{/if}}
    {{foreach from=$tags key=_tag item=value}}
      <th>
        {{$_tag}}
      </th>
    {{/foreach}}
    <th>
      {{mb_title class=CProtocole field=group_id}}
    </th>
    <th>
      {{tr}}CCompteRendu|pl{{/tr}}
    </th>
  </tr>

  {{foreach from=$list_protocoles item=_protocole}}
    <tr
        {{if !"appFineClient"|module_active || !"appFineClient Sync allow_appfine_sync"|gconf || !$appFine_pack_id}}
        onclick="ProtocoleDHE.chooseProtocole({{$_protocole->_id}}); return false;" style="cursor: pointer;"
        {{/if}}
        {{if !$_protocole->actif}}
            class="hatching"
        {{/if}}
    >
      {{if $appFine_pack_id}}
        <td>
          <form name="addPackProtocole{{$_protocole->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.refresh.curry())">
            <input type="hidden" name="m" value="appFineClient" />
            <input type="hidden" name="dosql" value="do_pack_protocole_aed" />
            <input type="hidden" name="pack_id"  value="{{$appFine_pack_id}}"/>
            <input type="hidden" name="protocole_id" value="{{$_protocole->_id}}" />
            <button type="button" onclick="this.form.onsubmit();" class="add notext"
            {{if $_protocole->_pack_already_linked}}title="{{tr}}AppFineClient-msg-Protocole already linked{{/tr}}" disabled{{else}}title="{{tr}}AppFineClient-msg-Add this protocole to pack{{/tr}}" {{/if}}>
            </button>
          </form>
        </td>
      {{/if}}
      <td class="narrow {{$_protocole->_owner}}">
      </td>
      <td class="text">
        {{if $type == "interv"}}
          <strong>{{mb_value object=$_protocole field=libelle}}</strong>
        {{else}}
          <strong>{{mb_value object=$_protocole field=libelle_sejour}}</strong>
        {{/if}}
      </td>

      <td class="text">
        {{if $type == "interv"}}
          {{foreach from=$_protocole->_ext_codes_ccam item=_code}}
          <div class="compact">
            <strong>{{$_code->code}}</strong>
            {{$_code->libelleLong|spancate}}
          </div>
          {{/foreach}}
        {{else}}
          {{assign var=code value=$_protocole->_ext_code_cim}}
          {{if $code->code}}
            <strong>{{$code->code}}</strong>
            {{$code->libelle|spancate}}
          {{/if}}
        {{/if}}
      </td>

      <td>
        {{mb_value object=$_protocole field=type}}
      </td>

      <td {{if !$_protocole->duree_hospi}} class="empty" {{/if}} >
        {{mb_value object=$_protocole field=duree_hospi}} nuit(s)
      </td>

      {{if $type == "interv"}}
      <td {{if !$_protocole->cote}} class="empty" {{/if}} >
        {{mb_value object=$_protocole field=cote}}
      </td>

      <td>
        {{mb_value object=$_protocole field=temp_operation}}
      </td>

      {{else}}
      <td class="text">
        {{if $_protocole->convalescence}}
        <div class="compact">
          <strong>C</strong>: {{$_protocole->convalescence|spancate}}
        </div>
        {{/if}}

        {{if $_protocole->rques_sejour}}
        <div class="compact">
          <strong>R</strong>: {{$_protocole->rques_sejour|spancate}}
        </div>
        {{/if}}
      </td>

      {{/if}}
      {{assign var=id value=$_protocole->_id}}
      {{foreach from=$tags item=_tag}}
        {{if array_key_exists($id, $_tag)}}
          <td>{{$_tag[$id]->id400}}</td>
        {{else}}
          <td class="empty">{{tr}}None{{/tr}}</td>
        {{/if}}
      {{/foreach}}
      <td>
        {{if $_protocole->_ref_function !== null}}
          {{$_protocole->_ref_function->_ref_group}}
        {{else}}
          <span>&dash;&dash;&dash;</span>
        {{/if}}
      </td>
      <td>
        {{if $_protocole->_count_docitems}}
          {{$_protocole->_count_docitems}}
        {{else}}
            &mdash;
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>
