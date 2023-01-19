{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=onchange value="Value.synchronize(this, 'editSejour');"}}
{{mb_default var=circled value=true}}

{{assign var=ald_mandatory value="dPplanningOp CSejour ald_mandatory"|gconf}}

<tr>
    <th></th>
    <td colspan="3" class="text me-ws-nowrap">
        <script>
            setAldC2S = function (curr_form, field) {
                var pat_form = getForm('patAldForm');
                var sejour_form = getForm('editSejour');
                var op_easy_form = getForm('editOpEasy');
                var forms = [];

                $V(pat_form.patient_id, $V(sejour_form.patient_id));

                if (curr_form.name !== 'editSejour') {
                    forms.push(sejour_form);
                }

                if (curr_form.name !== 'editOpEasy' && op_easy_form) {
                    forms.push(op_easy_form);
                }

                var input = DOM.input({type: 'hidden', name: field});

                pat_form.insert(input);

                switch (field) {
                    default:
                    case 'ald':
                        $V(input, $V(curr_form._ald_pat));

                        forms.each(function (_form) {
                            {{if $ald_mandatory}}
                            $A(_form._ald_pat).each(function (_input) {
                                if (_input.value === $V(curr_form._ald_pat)) {
                                    _input.checked = true;
                                }
                            });
                            {{else}}
                            $V(_form._ald_pat, $V(curr_form._ald_pat) === '1' ? 1 : 0, false);
                            _form.___ald_pat.checked = $V(curr_form._ald_pat) === '1';
                            {{/if}}
                        });

                        // Ajout du formulaire initial pour l'activation / désactivation de la case séjour concerné par l'ald
                        if (curr_form.name === 'editSejour') {
                            forms.push(sejour_form);
                        } else {
                            forms.push(op_easy_form);
                        }

                        if ($V(curr_form._ald_pat) == '1') {
                            forms.each(function (_form) {
                                {{if $ald_mandatory}}
                                _form.ald[0].disabled = '';
                                _form.ald[0].addClassName('notNull');
                                _form.ald[1].disabled = '';
                                _form.ald[1].addClassName('notNull');

                                _form.ald[0].up('span').down('label').addClassName('notNull');

                                _form.removeClassName('prepared');
                                prepareForm(_form);

                                {{else}}
                                _form.__ald.disabled = '';
                                {{/if}}
                            });
                        } else {
                            forms.each(function (_form) {
                                {{if $ald_mandatory}}
                                _form.ald[0].checked = false;
                                _form.ald[1].checked = false;
                                _form.ald[0].disabled = 'disabled';
                                _form.ald[1].disabled = 'disabled';
                                _form.ald[0].removeClassName('notNull');
                                _form.ald[1].removeClassName('notNull');

                                _form.ald[0].up('span').down('label').removeClassName('notNull').removeClassName('notNullOK');
                                _form.removeClassName('prepared');
                                prepareForm(_form);
                                {{else}}
                                _form.__ald.checked = false;
                                $V(_form.ald, 0, false);
                                _form.__ald.disabled = 'disabled';
                                {{/if}}
                            });
                        }

                        break;
                    case 'c2s':
                    case 'cmu':
                    case 'acs':
                        $V(input, $V(curr_form.elements['_' + field + '_pat']));

                        forms.each(function (_form) {
                            $V(_form.elements['_' + field + '_pat'], $V(curr_form.elements['__' + field + '_pat']) ? 1 : 0);
                            $V(_form.elements['__' + field + '_pat'], $V(curr_form.elements['__' + field + '_pat']) ? 1 : 0);
                        });
                }

                return onSubmitFormAjax(pat_form, function () {
                    input.remove();
                });
            };
        </script>

        <!-- Patient sous C2S -->
        <span class="me-margin-right-4 {{if $circled}} circled{{/if}}">
     <label title="Patient bénéficiant de la Complémentaire Santé Solidaire (C2S)">
       Patient bénéficiant de la C2S
       {{if $patient && $patient->_id}}
           {{mb_field object=$sejour field=_c2s_pat typeEnum=checkbox onchange="setAldC2S(this.form, 'c2s');" default=$patient->c2s}}
       {{else}}
           <input name="__c2s_pat" type="checkbox" disabled/>
           <input name="_c2s_pat" value="0" type="hidden"/>
       {{/if}}
     </label>
   </span>

        <!-- Patient sous ALD -->
            <span class="me-margin-right-4 {{if $circled}}circled{{/if}}">
       {{assign var=type_enum_ald_pat value="checkbox"}}
                {{if $ald_mandatory && $patient && $patient->_id}}
                    {{assign var=type_enum_ald_pat value="radio"}}
                {{/if}}

                {{mb_label object=$sejour field=_ald_pat typeEnum=$type_enum_ald_pat}}

                {{if $patient && $patient->_id}}
                    {{mb_field object=$sejour field=_ald_pat typeEnum=$type_enum_ald_pat onchange="setAldC2S(this.form, 'ald');" default=$patient->ald}}
                {{else}}
                    <input name="__ald_pat" type="checkbox" disabled/>
                    <input name="_ald_pat" value="0" type="hidden"/>
                {{/if}}
            </span>
            <!-- Séjour concerné par ALD -->
            <span class="me-margin-right-4 {{if $circled}}circled{{/if}}">
       {{assign var=class_sejour_ald value=""}}

                {{if $patient && $patient->ald && $ald_mandatory}}
                    {{assign var=class_sejour_ald value="notNull"}}
                {{/if}}

       <label for="ald" class="{{$class_sejour_ald}}"
              title="{{tr}}CSejour-ald-desc{{/tr}}">{{tr}}CSejour-ald{{/tr}}</label>

       {{assign var=ald_sejour_disabled value=true}}
                {{if $patient && $patient->ald}}
                    {{assign var=ald_sejour_disabled value=false}}
                {{/if}}

                {{if $ald_mandatory && $patient && $patient->_id}}
                    <label>
           <input type="radio" name="ald" value="1"
                  {{if $ald_sejour_disabled}}disabled{{/if}} class="bool {{$class_sejour_ald}}"
                  {{if $sejour->ald === "1"}}checked{{/if}}
                  onchange="Value.synchronize(this, 'editSejour', false);"/>
           {{tr}}Yes{{/tr}}
         </label>
                    <label>
           <input type="radio" name="ald" value="0"
                  {{if $ald_sejour_disabled}}disabled{{/if}} class="bool {{$class_sejour_ald}}"
                  {{if $sejour->ald === "0"}}checked{{/if}}
                  onchange="Value.synchronize(this, 'editSejour', false);"/>
           {{tr}}No{{/tr}}
         </label>
                {{else}}
                    {{mb_field object=$sejour field=ald typeEnum=checkbox disabled=$ald_sejour_disabled onchange=$onchange|smarty:nodefaults}}
                {{/if}}
     </span>

        <!-- Patient sous ACS -->
        <span class="me-margin-right-4 {{if $circled}}circled{{/if}}">
      <label title="{{tr}}CSejour-acs-msg-Patient receiving support for a complementary health{{/tr}}">
        {{tr}}CSejour-acs{{/tr}}
          {{if $patient && $patient->_id}}
              {{mb_field object=$sejour field=_acs_pat typeEnum=checkbox onchange="setAldC2S(this.form, 'acs');" default=$patient->acs}}
          {{else}}
              <input name="__acs_pat" onclick="setAldC2S(this.form, 'acs');" type="checkbox" disabled/>
              <input name="_acs_pat" value="0" type="hidden"/>
          {{/if}}
      </label>
    </span>
    </td>
</tr>
