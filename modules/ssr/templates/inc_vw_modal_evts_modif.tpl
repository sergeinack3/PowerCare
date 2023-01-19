{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=csarr register=true}}

<script>
  // Parcours de toutes les checkbox, 2 cas possibles:
  // - checkbox decoché: on supprime le code
  // - checkbox coché: on rajoute le code
  submitCdarrs = function(){
    var form = getForm('editCodes');
    $V(form.added_codes, '');
    $V(form.remed_codes, '');

    var qtes = form.down('table').select('input.quantity');

    // Parcours des checkbox
    if (qtes.length == 0) {
      $$('.list-codes input[type="checkbox"]').each(function(checkbox) {
        var added_codes = new TokenField(form.added_codes);
        var remed_codes = new TokenField(form.remed_codes);
        (checkbox.checked ? added_codes : remed_codes).add(checkbox.value);
      });
    }
    else {
      //Parcours des quantités
      qtes.each(function(input) {
        var input_acte = input.up('span').down('input');
        var added_codes = new TokenField(form.added_codes);
        var remed_codes = new TokenField(form.remed_codes);
        var type_presta = 'presta_ssr';
        if (input_acte.type == "hidden") {
          added_codes.add(input_acte.value+'-'+input.value+'-'+type_presta);
        }
        else {
          (input_acte.checked ? added_codes : remed_codes).add(input_acte.value+'-'+input.value+'-'+type_presta);
        }
      });
    }

    return onSubmitFormAjax(form, function() {
      {{if !$refresh_validation}}
        refreshPlanningsSSR();
      {{/if}}
      Control.Modal.close();
      {{if $refresh_validation}}
        ModalValidation.refresh();
      {{/if}}
    });
  };

  updateFieldCodeModal = function(selected) {
    var code_selected = selected;
    if (!Object.isString(selected)) {
      code_selected = selected.childElements()[0].textContent;
    }
    var container = $$('span.other_csarr')[0];
    container.insert({ bottom:
      DOM.span({},
        DOM.input({
          type: 'hidden',
          id: 'editCodes__codes['+code_selected+']',
          name:'_codes['+code_selected+']',
          value: code_selected
        }),
        DOM.button({
          className: "cancel notext",
          type: "button",
          onclick: "this.up('span').remove()"
        }),
        DOM.label({}, code_selected)
      )
    });
  };

  updateFieldCodeModalPresta = function(selected, type_presta) {
    var code_selected = selected.childElements()[0].textContent;
    var uniqid = Math.ceil(Math.random()*1000000);
    var elt_to_insert = selected.up(3).down('.other_'+type_presta);
    elt_to_insert.insert({ bottom:
        DOM.span({},
          DOM.input({
            type: 'hidden',
            id: 'editCodes__'+type_presta+'['+code_selected+'-'+uniqid+']',
            name:'_'+type_presta+'['+code_selected+'-'+uniqid+']',
            value: code_selected
          }),
          DOM.button({
            className: "cancel notext",
            type: "button",
            onclick: "this.up('span').remove()"
          }),
          DOM.label({}, code_selected),
          DOM.span({id: '_quantity_' + code_selected + uniqid} , '(x '),
          DOM.input({
            type:      'number',
            min:       '1',
            id:       'editCodes__' + type_presta + '_quantity[' + code_selected+'-'+uniqid + ']',
            name:      '_' + type_presta + '_quantity[' + code_selected+'-'+uniqid + ']',
            value:     '1',
            className: 'checkbox-other-' + type_presta + '_quantity quantity '+type_presta,
            style:     'width: 32px;'
          }),
          DOM.span({} , ')')
        )
    });
  };

  addSsrEventCode = function(button, type) {
    button.up('span').hide();
    updateFieldCodeModalPresta(button, type);
    var fieldset = button.up('fieldset');
    var hide = true;
    fieldset.select('span').each(
      function(element) {
        if (!hide) {
          return true;
        }
        else if (element.visible()) {
          hide = false;
        }
      }
    );
    if (hide) {
      fieldset.hide();
    }
  };

  Main.add(function() {
    var select_value = "value";
    var method = "post";
    {{foreach from=$types item=_type}}
      {{if $_type == 'prestas'}}
        var url = new Url("ssr", "ajax_presta_ssr_autocomplete");
        method = "get";
      {{else}}
        var url = new Url("ssr", "httpreq_do_{{$_type}}_autocomplete");
      {{/if}}

      url.autoComplete(getForm("editCodes").code_{{$_type}}, "other_{{$_type}}_auto_complete", {
        dropdown: true,
        minChars: 2,
        select: select_value,
        method: method,
        updateElement: function(selected) {
          {{if $_type == 'prestas'}}
            updateFieldCodeModalPresta(selected, '{{$_type}}');
          {{else}}
            updateFieldCodeModal(selected);
          {{/if}}
        }
      } );
    {{/foreach}}
  });
</script>

{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

<form name="editCodes" action="?" method="post">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="validateSSREvents" />
  <input type="hidden" name="token_evts" value="{{$token_evts}}" />
  <input type="hidden" name="added_codes" />
  <input type="hidden" name="remed_codes" />

  {{assign var="count_events" value=$evenements|@count}}
  <table class="main tbl">
    <tr>
      <th colspan="7" class="title">{{tr}}CEvenementSSR-selected{{/tr}}</th>
    </tr>
    <tr>
      <th colspan="2">{{mb_label class="CEvenementSSR" field="debut"}}</th>
      <th class="narrow">{{mb_label class="CEvenementSSR" field="duree"}}</th>
      {{if $use_acte_presta == "csarr"}}
        <th class="narrow">{{tr}}CActeCdARR-code-court{{/tr}}</th>
        <th class="narrow">{{tr}}CActeCsARR-code-court{{/tr}}</th>
      {{/if}}

      {{if $use_acte_presta == "presta"}}
        <th class="narrow">{{tr}}CPrestaSSR{{/tr}}</th>
      {{/if}}
    </tr>
    {{foreach from=$evenements item=_evenement}}
      <tr>
        <td class="narrow">{{$_evenement->debut|date_format:"%A"}}</td>
        <td>{{mb_value object=$_evenement field="debut"}}</td>
        <td style="text-align: right;">{{mb_value object=$_evenement field="duree"}} min</td>

        {{if $use_acte_presta == "csarr"}}
          <td style="text-align: center;">{{$_evenement->_ref_actes_cdarr|@count|nozero}}</td>
          <td style="text-align: center;">{{$_evenement->_ref_actes_csarr|@count|nozero}}</td>
        {{/if}}

        {{if $use_acte_presta == "presta"}}
          <td style="text-align: center;">{{$_evenement->_refs_prestas_ssr|@count|nozero}}</td>
        {{/if}}
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CEvenementSSR-no_selected{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{foreach from=$actes key=_type item=_actes}}
      <tr>
        <th colspan="7">{{tr}}CEvenementSSR-back-actes_{{$_type}}{{/tr}}</th>
      </tr>
      <tr class="list-codes">
        <td colspan="7" class="text">
          <!--
            strong: les actes présents sur tous les événements (checked)
            opacity: les actes présents sur certains événements
          -->
          {{foreach from=$_actes key=_code item=_acte}}
             <span style="whitespace: nowrap; display: inline-block;">
              {{if array_key_exists($_code, $count_actes.$_type)}}
                {{if $count_actes.$_type.$_code == $count_events}}
                  <input name="{{$_type}}[{{$_code}}]" type="checkbox" checked="checked" value="{{$_code}}" />
                  {{$_code}}
                {{else}}
                  <!-- Activation de la checkbox -->
                  <input name="{{$_type}}[{{$_code}}]" type="checkbox" checked="checked" value="{{$_code}}"/>
                  {{$_code}}
                {{/if}}
              {{else}}
                <input name="{{$_type}}[{{$_code}}]" type="checkbox" value="{{$_code}}" /> {{$_code}}
              {{/if}}
              {{if $_type == "prestas"}}
               (<span title="{{tr}}CActePrestationSSR-Amount of code to add{{/tr}}">x</span>
               <input type="text" id="prestas_ssr_quantity_{{$_code}}" class="quantity {{$_type}}"
                      name="prestas_ssr_quantity[{{$_code}}]"
                      value="{{if isset($count_actes.$_type.$_code|smarty:nodefaults)}}{{$count_actes.$_type.$_code}}{{/if}}"
                      style="width: 17px;" />)

                  <script>
                    Main.add(function () {
                      $('prestas_ssr_quantity_{{$_code}}').addSpinner({min: 1});
                    });
                  </script>
              {{/if}}
             </span>
          {{foreachelse}}
            <div class="empty">{{tr}}CEvenementSSR-back-actes_{{$_type}}.empty{{/tr}}</div>
          {{/foreach}}
        </td>
      </tr>

      {{if $_type != "cdarr"}}
        <tr>
          <td colspan="7" class="text">
            <input type="text" name="code_{{$_type}}" class="autocomplete" canNull=true size="6" />
            {{if $_type == 'csarr'}}
              <button type="button" class="search notext" onclick="CsARR.viewSearch(updateFieldCodeModal.curry());">
                 {{tr}}CActiviteCsARR-action-search{{/tr}}
               </button>
            {{/if}}
            <div style="display: none;" class="autocomplete" id="other_{{$_type}}_auto_complete"></div>
            <span class="other_{{$_type}}"></span>
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
    <tr>
      <td colspan="7" class="button">
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
         <button type="button" class="submit" onclick="submitCdarrs();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
