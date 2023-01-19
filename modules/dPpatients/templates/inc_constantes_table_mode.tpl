{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=patients script=courbe_reference_graph ajax=true}}

{{assign var=unique_id value=''|uniqid}}

<script type="text/javascript">
  calculImcVst = function () {
    // Function added because there is a callback on this function when the size or the weight is set
  };

  checkGraph = function () {
    var checkboxes = $$('input[name="_displayGraph"][data-uid="{{$unique_id}}"]:checked');

    if (checkboxes.length >= 5) {
      checkboxes = $$('input[name="_displayGraph"][data-uid="{{$unique_id}}"]:not(:checked)').each(function (elt) {
        elt.disable();
      });
    } else {
      checkboxes = $$('input[name="_displayGraph"][data-uid="{{$unique_id}}"]:not(:checked)').each(function (elt) {
        elt.enable();
      });
    }
  };

  displayGraph = function () {
    var checkboxes = $$('input[name="_displayGraph"][data-uid="{{$unique_id}}"]:checked');
    var selection = [];
    checkboxes.each(function (checkbox) {
      selection.push(checkbox.getAttribute('data-constant'));
    });

    if (selection.length == 0) {
      alert($T('CConstantesMedicales-You must at least select a constant !'));
      return;
    }

    var url = new Url('patients', 'ajax_custom_constants_graph');
    url.addParam('patient_id', '{{$patient_id}}');
    url.addParam('constants', JSON.stringify(selection));
    url.requestModal();
  };

  displayAllConstantes = function (patient_id) {
    var url = new Url('patients', 'ajax_display_constantes');
    url.addParam('patient_id', patient_id);
    url.requestModal();
  };

  editConstant = function (constant_id, patient_id, list_releves) {
    var url = new Url('patients', 'ajax_edit_constantes');
    url.addParam('constant_id', constant_id);
    url.requestModal(280, null, {
      onClose: function () {
        if (list_releves) {
          Control.Modal.close();
          displayAllConstantes(patient_id);
        }
      }
    });
  };

  addComment = function (form) {
    var comments = [];
    if ($V(form._constant_comments)) {
      comments = JSON.parse($V(form._constant_comments));
    }

    var comment = {
      'constant': $V(form._constant_comment),
      'comment':  $V(form._comment)
    };
    comments.push(comment);
    $V(form._constant_comments, JSON.stringify(comments));
    $V(form._constant_comment, '');
    $V(form._comment, '');
    Control.Modal.close();
  };

  editComment = function (constant, constant_id, comment_id, value) {
    if (!constant_id) {
      var form = getForm('edit-constantes-medicales{{$unique_id}}');
      $V(form._constant_comment, constant);

      var comments = [];
      if ($V(form._constant_comments)) {
        comments = JSON.parse($V(form._constant_comments));
      }

      comments.each(function (item) {
        if (item.constant && item.constant == constant) {
          $V(form._comment, item.comment);
        }
      });

      Modal.open('add-comment{{$unique_id}}', {
        showClose: true,
        width:     500,
        height:    300
      });

      var validate_comment_cte = $('validate_comment_cte');
      validate_comment_cte.disabled = '';
      if (value == '') {
        validate_comment_cte.disabled = 'disabled';
      }
      $('list_comments{{$unique_id}}').innerHTML = '';
      var urlCte = new Url('patients', 'vw_last_comments_cte');
      urlCte.addParam('constant', constant);
      urlCte.addParam('context_class', $V(form.context_class));
      urlCte.addParam('context_id', $V(form.context_id));
      urlCte.requestUpdate('list_comments{{$unique_id}}');
    } else {
      var url = new Url('patients', 'ajax_edit_constant_comment');
      url.addParam('constant', constant);
      url.addParam('constant_id', constant_id);
      url.addParam('unique_id', '{{$unique_id}}');
      url.requestModal(500, 300);
    }
  };

  editConstantComment = function () {
    Modal.open('edit-CConstantesMedicales-comment{{$unique_id}}', {
      showClose: true,
      width:     500,
      height:    300,
      title:     $T('CConstantesMedicales-title-add_comment')
    });
  };

  Main.add(function () {
    var form = getForm('edit-constantes-medicales{{$unique_id}}');
    var url = new Url('patients', 'ajax_do_autocomplete_constants');
    url.autoComplete(form._search_constants, '_constants_autocomplete', {
      minChars:      2,
      dropdown:      true,
      updateElement: function (selected) {
        var constant = selected.getAttribute('data-constant');
        var row = $$('tr[data-constant="' + constant + '"][data-uid="{{$unique_id}}"]');
        if (row.length != 0) {
          row = row[0];
          var table = row.up();
          /* We remove the row, and add to the table to display the node at the bottom of the table */
          row = table.removeChild(row);
          row = table.appendChild(row);
          row.show();
        }
      }
    });

    {{if !$constantes->datetime}}
    var formCst = getForm('edit-constantes-medicales{{$unique_id}}');
    formCst.datetime.value = "now";
    formCst.datetime_da.value = $T('Now');
    {{/if}}
  });

  refreshConstants = function () {
    {{if $modal}}
    var parent = getForm('edit-constantes-medicales{{$unique_id}}').up();
    var url = new Url('patients', 'ajax_constantes_table_mode');
    url.addParam('patient_id', '{{$patient_id}}');
    url.addParam('modal', '1');
    url.requestUpdate(parent.id);
    {{else}}
    loadConstants();
    {{/if}}
  }
</script>

<form name="edit-constantes-medicales{{$unique_id}}" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: refreshConstants.curry()});">
  {{mb_class object=$constantes}}
  {{mb_key object=$constantes}}
  {{if $constantes->_id}}
    <input type="hidden" name="_new_constantes_medicales" value="0"/>
  {{else}}
    <input type="hidden" name="_new_constantes_medicales" value="1"/>
  {{/if}}

  {{mb_field object=$constantes field=_unite_ta hidden=1}}
  {{mb_field object=$constantes field=_unite_glycemie hidden=1}}
  {{mb_field object=$constantes field=_unite_cetonemie hidden=1}}
  {{mb_field object=$constantes field=context_class hidden=1}}
  {{mb_field object=$constantes field=context_id hidden=1}}
  {{mb_field object=$constantes field=patient_id hidden=1}}
  <input type="hidden" name="_constant_comments" value=""/>

  <table class="tbl me-small" id="tableConstant-{{$unique_id}}" style="width: 1px;">
    <tr>
      <th rowspan="2" class="category narrow">
        <button class="stats notext" type="button" onclick="displayGraph();">
          {{tr}}CConstantGraph-msg-display{{/tr}}
        </button>
      </th>
      <th rowspan="2" class="category">
        {{tr}}Name{{/tr}}
        <br/>
        {{if $display_search_field}}
          <span class="me-small-fields">
            <input type="text" name="_search_constants" class="autocomplete" placeholder="{{tr}}Search{{/tr}}"/>
          </span>
          <div
            style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
            class="autocomplete" id="_constants_autocomplete"></div>
        {{/if}}
      </th>
      <th rowspan="2" class="category">
        <button type="button" class="comment notext" onclick="editConstantComment();">
          {{tr}}CConstantesMedicales-actions-add_comment{{/tr}}
        </button>
      </th>
      <th colspan="2" class="category" style="border-bottom: none;">
        {{if $patient->_can->edit}}
          <button class="save notext" type="submit" style="float: right;"></button>
        {{/if}}
        <div style="margin-top: 5px;">
          {{tr}}Value{{/tr}}
        </div>
      </th>
      <th rowspan="2" colspan="2" class="category">
        {{tr}}CConstantesMedicales-title-latest_values{{/tr}}
      </th>
      {{if $list_constantes|@count > 0}}
        <th class="category" colspan="{{$list_constantes|@count}}">
          <button type="button" class="list" title="{{tr}}CConstantesMedicales-msg-see_last_values{{/tr}}"
                  onclick="displayAllConstantes('{{$patient_id}}');">
            {{tr}}CConstantesMedicales-msg-see_last_values{{/tr}}
          </button>
        </th>
      {{/if}}
    </tr>
    <tr>
      <th colspan="2" class="category me-small-fields" style="border-top: none;">
        {{mb_field object=$constantes field=datetime form="edit-constantes-medicales"|cat:$unique_id register=true}}
      </th>
      {{if $list_constantes|@count > 0}}
        {{foreach from=$list_constantes item=_constantes}}
          <th class="narrow" class="category">
            {{if $_constantes->comment}}
              <button type="button" class="comment notext me-tertiary me-dark me-btn-small"
                      title="{{$_constantes->comment}}"></button>
            {{/if}}
            {{$_constantes->datetime|date_format:$conf.date}}
            <button type="button" class="edit notext" onclick="editConstant('{{$_constantes->_id}}', 0, 0);"
                    title="{{tr}}Edit{{/tr}}"/>
          </th>
        {{/foreach}}
      {{/if}}
    </tr>

    {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes"}}
    {{assign var=const value=$latest_constantes.0}}
    {{assign var=dates value=$latest_constantes.1}}

    {{foreach from=$constants_ranks key=_type item=_ranks}}
      {{foreach from=$_ranks key=_rank item=_constants}}
        {{foreach from=$_constants item=_constant}}
          {{assign var=_params value=$constants_list.$_constant}}
          {{if !$selection|@count || $_constant|in_array:$selection}}
            <tr class="alternate{{if $_rank == 'hidden' && $const->$_constant == ""}} secondary" style="display: none;{{/if}}"
                data-constant="{{$_constant}}" data-uid="{{$unique_id}}">
              <td class="narrow" style="text-align: center;">
                {{if $_constant[0] != '_'}}
                  <input name="_displayGraph" type="checkbox" data-constant="{{$_constant}}" data-uid="{{$unique_id}}"
                         onclick="checkGraph();"/>
                {{/if}}
              </td>
              <td style="text-align: left;">
                <label for="{{$_constant}}" title="{{tr}}CConstantesMedicales-{{$_constant}}-desc{{/tr}}">
                  {{tr}}CConstantesMedicales-{{$_constant}}{{/tr}}
                </label>
                {{if $constantes->_ref_patient->_annees <= "18"}}
                  {{if $constantes->_ref_patient->_annees <= "5" && $constantes->_ref_patient->_mois <= 60}}
                    {{assign var=select_graph value=0}}
                    {{assign var=constantName value="perimetre_cranien"}}
                  {{else}}
                    {{assign var=select_graph value=1}}
                  {{/if}}
                {{/if}}
                {{if $constantes->_ref_patient->_annees <= "18" && ($_constant == "poids" || $_constant == "taille" || $_constant == "_imc" || $_constant == $constantName)}}
                  {{assign var=constantName value=null}}
                  {{if $_constant == "_imc"}}
                    {{assign var=constantName value="_imc"}}
                  {{/if}}

                  <button type="button" class="stats notext" style="float: right; display: block;"
                          onclick="CourbeReference.showModalGraph('{{$constantes->_ref_patient->_id}}','{{$_constant}}','{{$constantName}}',{{$select_graph}});">
                    {{tr}}CCourbeReference-action-Display reference curve{{/tr}}
                  </button>
                {{/if}}
              </td>
              <td class="narrow">
                <button id="edit_comment_{{$_constant}}{{$unique_id}}" type="button" style="float: right;" class="comment notext"
                        onclick="editComment('{{$_constant}}', '{{$constantes->_id}}', {{if $_constant|array_key_exists:$constantes->_refs_comments}}'{{$comment->_id}}'{{else}}null{{/if}}, $V(this.form.{{$_constant}}));"
                        tabindex="-1">
                  {{tr}}CConstantComment-action-create{{/tr}} {{tr}}CConstantesMedicales-{{$_constant}}{{/tr}}
                </button>
              </td>
              <td style="text-align: center">
                {{assign var=_hidden value=false}}
                {{assign var=_readonly value=null}}
                {{if array_key_exists('formfields', $_params) && !array_key_exists('readonly', $_params)}}
                  {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                    {{assign var=_style value="width:1.7em;"}}
                    {{assign var=_size value=2}}
                    {{if $_params.formfields|@count == 1}}
                      {{assign var=_style value=""}}
                      {{assign var=_size value=3}}
                    {{/if}}

                    {{if !$smarty.foreach._formfield.first}}/{{/if}}
                    {{mb_field object=$constantes field=$_params.formfields.$_key size=$_size style=$_style}}
                  {{/foreach}}
                {{else}}
                  {{if $_constant.0 == "_" && !array_key_exists('edit', $_params)}}
                    {{assign var=_readonly value='readonly'}}

                    {{if array_key_exists('formula', $_params)}}
                      {{assign var=_hidden value=true}}
                    {{/if}}
                  {{/if}}
                  {{if array_key_exists('callback', $_params)}}
                    {{assign var=_callback value=$_params.callback}}
                  {{else}}
                    {{assign var=_callback value=null}}
                  {{/if}}

                  {{mb_field object=$constantes field=$_constant size="3" onchange=$_callback|ternary:"$_callback(this.form)":null readonly=$_readonly hidden=$_hidden}}
                {{/if}}
              </td>
              <td style="text-align: center">
                {{if $_params.unit}}
                  <span>
                    {{$_params.unit}}
                  </span>
                {{/if}}
              </td>
              <td class="narrow" style="text-align: center; font-weight: bold;">
                {{assign var=isnull value=$const->$_constant|is_null}}
                {{if $isnull != '1'}}
                  {{if array_key_exists('formfields', $_params) && !array_key_exists('readonly', $_params)}}
                    {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                      {{if !$smarty.foreach._formfield.first}}/{{/if}}
                      {{mb_value object=$const field=$_params.formfields.$_key}}
                    {{/foreach}}
                  {{else}}
                    {{mb_value object=$const field=$_constant}}
                  {{/if}}

                  {{if $_params.unit}}
                    <span>
                      {{$_params.unit}}
                    </span>
                  {{/if}}
                {{/if}}
              </td>
              <td class="narrow" style="text-align: center; font-weight: bold;">
                {{$dates.$_constant|date_format:$conf.date}}
              </td>
              {{foreach from=$list_constantes item=_constantes}}
                <td class="narrow" style="text-align: center">
                  {{if $_constantes->$_constant != ''}}
                    {{if array_key_exists('formfields', $_params) && !array_key_exists('readonly', $_params)}}
                      {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                        {{if !$smarty.foreach._formfield.first}}/{{/if}}
                        {{mb_value object=$_constantes field=$_params.formfields.$_key}}
                      {{/foreach}}
                    {{else}}
                      {{mb_value object=$_constantes field=$_constant}}
                    {{/if}}

                    {{if $_constantes->$_constant != '' && $_params.unit}}
                      <span>
                        {{$_params.unit}}
                      </span>
                    {{/if}}

                    {{if array_key_exists($_constant, $_constantes->_refs_comments)}}
                      <button type="button" class="comment notext me-tertiary me-dark me-btn-small"
                              title="{{$_constantes->_refs_comments[$_constant]->comment}}"></button>
                    {{/if}}
                  {{/if}}
                </td>
              {{/foreach}}
            </tr>
          {{/if}}
        {{/foreach}}
      {{/foreach}}
    {{/foreach}}
  </table>
  <div id="edit-CConstantesMedicales-comment{{$unique_id}}" style="display: none;">
    <table class="form">
      <tr>
        <th>
          {{mb_label object=$constantes field=comment}}
        </th>
        <td>
          {{mb_field object=$constantes field=comment placeholder="Commentaire" rows=2 form="edit-constantes-medicales$unique_id" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="button" class="tick" onclick="Control.Modal.close();">
            {{tr}}Validate{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </div>
  <div id="add-comment{{$unique_id}}" style="display: none;">
    {{mb_include module=patients template=inc_create_constant_comment}}
  </div>
</form>
