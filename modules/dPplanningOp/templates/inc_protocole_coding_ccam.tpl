{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="title" style="border-bottom: none; border-spacing: 0px;">
    <div style="float: left">
      {{mb_script module="dPccam" script="code_ccam" ajax=$ajax}}
      {{mb_script module="planningOp" script="ccam_selector" ajax=$ajax}}

      {{mb_include module=salleOp template=js_codage_ccam do_subject_aed="" object=$subject}}

      <script>
        CCAMSelector.init = function() {
          this.sForm = "addActes-{{$subject->_guid}}";
          this.sClass = "_class";
          this.sChir = "_chir";
          {{if ($subject->_class=="COperation")}}
          this.sAnesth = "_anesth";
          {{/if}}
          {{if $subject->_class == 'CSejour'}}
          this.sDate = '{{$subject->sortie}}';
          {{else}}
          this.sDate = '{{$subject->_datetime}}';
          {{/if}}
          this.sView = "_new_code_ccam";
          this.pop();
        };


        Main.add(function() {
          ProtocoleDHE.codes.subjectId = '{{$subject->_id}}';
          ProtocoleDHE.codes.role = '{{$role}}';
          ProtocoleDHE.codes.objectClass = '{{$object_class}}';
          {{if $codages|@count != 1}}
          Control.Tabs.create('codages-tab', true);
          {{/if}}

          var form = getForm("addActes-{{$subject->_guid}}");
          var url = new Url("ccam", "autocompleteCcamCodes");
          {{if $subject->_class == 'CSejour'}}
          url.addParam("date", '{{$subject->sortie}}');
          {{else}}
          url.addParam("date", '{{$subject->_datetime}}');
          {{/if}}
          url.addParam('user_id', '{{$subject->_praticien_id}}');
          url.autoComplete(form._codes_ccam, "_ccam_autocomplete_{{$subject->_guid}}", {
            minChars: 1,
            dropdown: true,
            width: "250px",
            updateElement: function(selected) {
              CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
            }
          });
          CCAMField{{$subject->_class}}{{$subject->_id}} = new TokenField(form.elements["codes_ccam"], {
            onChange : function() {
              return onSubmitFormAjax(form, function(){ ProtocoleDHE.codes.refreshCoding(); });
            },
            sProps : "notNull code ccam"
          });
        });
      </script>

      <form name="addActes-{{$subject->_guid}}" method="post" onsubmit="return false">
        {{mb_class object=$subject}}
        {{mb_key object=$subject}}

        <input type="hidden" name="_class" value="{{$subject->_class}}" />
        <input type="hidden" name="_chir" value="{{$subject->_praticien_id}}" />

        {{mb_field object=$subject field="codes_ccam" hidden=true}}
        <input type="hidden" name="_new_code_ccam" value="" onchange="CCAMField{{$subject->_class}}{{$subject->_id}}.add(this.value, true);"/>

        <button id="didac_actes_ccam_tr_modificateurs" class="search" type="button" onclick="CCAMSelector.init()">
          {{tr}}Search{{/tr}}
        </button>
        <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()" style="width: 12em" value="" class="autocomplete" placeholder="Ajoutez un acte" />
        <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
             class="autocomplete" id="_ccam_autocomplete_{{$subject->_guid}}"></div>
      </form>
    </div>

    Codage CCAM du protocole
  </th>
</tr>
<tr>
  <th class="title" style="border-top: none; border-spacing: 0px;">
    {{foreach from=$subject->_ext_codes_ccam item=_code}}
      <span id="action-{{$_code->code}}" class="circled" style="background-color: #eeffee; color: black; font-weight: normal; font-size: 0.8em;">
         {{$_code->code}}

        {{if count($_code->assos) > 0}}
          {{unique_id var=uid_autocomplete_comp}}
          <form name="addAssoCode{{$uid_autocomplete_comp}}" method="get" onsubmit="return false;">
              <input type="text" size="8em" name="keywords" value="{{$_code->assos|@count}} cmp./sup." onclick="$V(this, '');"/>
            </form>
          <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
               class="autocomplete" id="_ccam_add_comp_autocomplete_{{$_code->code}}">
            </div>
          <script>
              Main.add(function() {
                var form = getForm("addAssoCode{{$uid_autocomplete_comp}}");
                var url = new Url("dPccam", "autocompleteAssociatedCcamCodes");
                url.addParam("code", "{{$_code->code}}");
                url.autoComplete(form.keywords, '_ccam_add_comp_autocomplete_{{$_code->code}}', {
                  minChars: 2,
                  dropdown: true,
                  width: "250px",
                  updateElement: function(selected) {
                    CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                  }
                });
              });
            </script>
        {{/if}}

          <button type="button" class="trash notext" onclick="CCAMField{{$subject->_class}}{{$subject->_id}}.remove('{{$_code->code}}', true)">
            {{tr}}Delete{{/tr}}
          </button>
      </span>
    {{/foreach}}
  </th>
</tr>
<tr>
  <td>
    {{if $codages|@count != 1}}
      {{assign var=total value=0}}
      <ul id="codages-tab" class="control_tabs">
        {{foreach from=$codages item=_codage}}
          {{math assign=total equation="x+y" x=$total y=$_codage->_total}}
          <li>
            <a href="#codage-{{$_codage->_id}}">
              {{tr}}CCodageCCAM.activite_anesth.{{$_codage->activite_anesth}}{{/tr}}
            </a>
          </li>
        {{/foreach}}
        <li>
          Total activités : {{$total|number_format:2:',':' '}} {{$conf.currency_symbol|html_entity_decode}}
        </li>
      </ul>

      {{foreach from=$codages item=_codage}}
        <div id="codage-{{$_codage->_id}}" style="display: none;">
          {{mb_include module=planningOp template=inc_protocole_coding_edit codage=$_codage}}
        </div>
      {{/foreach}}
    {{else}}
      {{mb_include module=planningOp template=inc_protocole_coding_edit codage=$codages|@first}}
    {{/if}}
  </td>
</tr>
