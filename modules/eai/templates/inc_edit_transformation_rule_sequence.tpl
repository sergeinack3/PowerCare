{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  EAITransformationRuleSequence.standards_flat = {{$standards_flat|@json}};
  initializeSelect = function(create) {
    EAITransformationRuleSequence.fillSelect("standard"     , null   , '{{$transf_rule_sequence->standard}}'      , create);
    EAITransformationRuleSequence.fillSelect("domain"       , '-desc', '{{$transf_rule_sequence->domain}}'        , create);
    EAITransformationRuleSequence.fillSelect("profil"       , '-desc', '{{$transf_rule_sequence->profil}}'        , create);
    EAITransformationRuleSequence.fillSelect("transaction"  , null   , '{{$transf_rule_sequence->transaction}}'   , create);
    EAITransformationRuleSequence.fillSelect("message_type" , null   , '{{$transf_rule_sequence->message_type}}'  , create);
  };

  Main.add(function () {
    initializeSelect(true);

    {{if $transf_rule_sequence->_id && $transf_rule_sequence->standard && $transf_rule_sequence->profil}}
    EAITransformationRuleSequence.showVersions(
      '{{$transf_rule_sequence->_id}}','{{$transf_rule_sequence->standard}}','{{$transf_rule_sequence->profil}}');

    var flag = false;
    EAITransformationRuleSequence.selects.reverse().each(function(select_name) {
      var select = $("EAITransformationRuleSequence-"+select_name);

      if (!flag && $V(select)) {
        select.options[select.selectedIndex].onclick();
        flag = true;
      }
    });
    EAITransformationRuleSequence.selects.reverse();
    {{/if}}

    {{if $transf_rule_sequence->_id}}
    EAITransformationRuleSequence.refreshListActors('{{$transf_rule_sequence->_id}}');
    {{/if}}
  });
</script>

<form name="edit{{$transf_rule_sequence->_guid}}" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, {
        onComplete: function (){
          EAITransformationRuleSequence.displayDetails('{{$transf_ruleset_id}}','{{$transf_rule_sequence->_id}}');
          Control.Modal.close();
        }
      });">
  {{mb_key object=$transf_rule_sequence}}
  {{mb_class object=$transf_rule_sequence}}
  {{mb_field object=$transf_rule_sequence field=transformation_ruleset_id hidden=true value="$transf_ruleset_id"}}

  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="EAITransformationRuleSet.refreshList" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$transf_rule_sequence}}

    <tr>
      <th style="width: 50%;">{{mb_label object=$transf_rule_sequence field="name"}}</th>
      <td>{{mb_field object=$transf_rule_sequence field="name"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$transf_rule_sequence field="description"}}</th>
      <td>{{mb_field object=$transf_rule_sequence field="description"}}</td>
    </tr>

    <tr>
      <th>{{tr}}CInteropActor-back-actor_transformations{{/tr}}</th>
      <td>
        {{if $transf_rule_sequence->_id}}
          <select name="actor_id" id="pack_id" onchange="EAITransformationRuleSequence.linkActorToSequence(this.value, '{{$transf_rule_sequence->_id}}');">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$receivers item=_receiver}}
              <option value="{{$_receiver->_guid}}">{{$_receiver->nom}}</option>
            {{/foreach}}
            {{foreach from=$senders item=_sender_type}}
                {{foreach from=$_sender_type item=_sender}}
                    {{if $_sender->actif}}
                      <option value="{{$_sender->_guid}}">{{$_sender->nom}}</option>
                    {{/if}}
                {{/foreach}}
            {{/foreach}}
          </select>
        {{else}}
          <div class="small-info">{{tr}}CTransformationRuleSequence-msg-Save to link actor{{/tr}}</div>
        {{/if}}

        <div id="list_actors">
        </div>
      </td>
    </tr>

    {{if $transf_rule_sequence->_id}}
      <tr>
        <th>{{mb_label object=$transf_rule_sequence field="version"}}</th>
        <td id="EAITransformationRuleSequence-version"></td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2">
        <table class="form">
          <tr>
            <td>
              <button type="button" onclick="initializeSelect();" class="cancel notext">{{tr}}Cancel{{/tr}}</button>
              {{mb_label object=$transf_rule_sequence field="standard"}}
            </td>
            <td>{{mb_label object=$transf_rule_sequence field="domain"}}</td>
            <td>{{mb_label object=$transf_rule_sequence field="profil"}}</td>
            <td>{{mb_label object=$transf_rule_sequence field="transaction"}}</td>
            <td>{{mb_label object=$transf_rule_sequence field="message_type"}}</td>
          </tr>

          <tr>
            <!-- NORME !-->
            <td style="width: 20%">
              <select size="10" name="standard" class="EAITransformationRuleSequence-select"
                      id="EAITransformationRuleSequence-standard">
              </select>
            </td>

            <!-- DOMAINE !-->
            <td style="width: 20%">
              <select size="10" name="domain" class="EAITransformationRuleSequence-select" id="EAITransformationRuleSequence-domain">
              </select>
            </td>

            <!-- PROFIL !-->
            <td style="width: 20%">
              <select size="10" name="profil" class="EAITransformationRuleSequence-select" id="EAITransformationRuleSequence-profil">
              </select>
            </td>

            <!-- TRANSACTION !-->
            <td style="width: 20%">
              <select size="10" name="transaction" class="EAITransformationRuleSequence-select"
                      id="EAITransformationRuleSequence-transaction">
              </select>
            </td>

            <!-- MESSAGE !-->
            <td style="width: 20%">
              <select size="10" name="message_type" class="EAITransformationRuleSequence-select"
                      id="EAITransformationRuleSequence-message_type">
              </select>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        {{mb_label object=$transf_rule_sequence field="message_example"}}
        {{mb_field object=$transf_rule_sequence field="message_example"}}
      </td>
    </tr>

    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$transf_rule_sequence
        options="{typeName: '', objName: '`$transf_rule_sequence`'}"
        options_ajax="function(){ EAITransformationRuleSequence.displayDetails(); Control.Modal.close();}"}}
    </tr>
  </table>
</form>
