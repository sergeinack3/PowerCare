{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=exchange_data_format ajax=true}}

<script type="text/javascript">
  orderColonne = function (order_col, order_way) {
    var form = getForm("filterExchange");
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    form.onsubmit();
  };

  filterColonne = function (input, type) {
    var table = $("exchanges-list");
    table.select(".table-row").invoke("show");

    var term = $V(input);
    if (!term) {
      return;
    }

    table.select(".exchange-" + type).each(function (e) {
      if (!e.getText().like(term)) {
        e.up(".table-row").hide();
      }
    });
  };

  togglePrint = function (elt) {
    var main_input = document.getElementById("main_exchange_checkbox");
    // change current elt
    if (elt.id !== main_input.id) {
      elt.checked = !elt.checked
      if (elt.checked && main_input.getAttribute('data-selection') === 'none') {
        main_input.setAttribute('data-selection', 'partial')
      }
      checkSelection()
      updateCheckbox(elt, main_input)
      return;
    }

    // change all elements
    var all_chekbox = $("exchanges-list").select("input[name=exchange_checkbox]")
    all_chekbox.push(main_input)
    all_chekbox.forEach(function(input) {
      input.checked = !main_input.checked
      updateCheckbox(input, main_input)
    })
  };

  updateCheckbox = function(input, main_input) {
    var select_all  = ['fas', 'fa-check-square', 'fa-2x', 'me-color-primary'];
    var select_current  = ['far', 'fa-check-square', 'fa-2x', 'me-color-primary'];
    var select_partial  = ['far', 'fa-minus-square', 'fa-2x', 'me-color-primary'];
    var select_none  = ['far', 'fa-square', 'fa-2x', 'me-color-grey'];
    var main_selection = main_input.getAttribute('data-selection')
    var element = input.nextElementSibling;

    var main_element = main_input.nextElementSibling
    main_element.className = ""
    if (main_selection === 'all') {
      main_element.classList.add(...select_all)
    } else if (main_selection === 'current') {
      main_element.classList.add(...select_current)
    } else if (main_selection === 'partial') {
      main_element.classList.add(...select_partial)
    } else {
      main_element.classList.add(...select_none)
    }

    element.className = ""
    if (input.checked) {
      if (main_selection === 'all') {
        element.classList.add(...select_all)
      } else {
        element.classList.add(...select_current)
      }
    } else {
      element.classList.add(...select_none)
    }
  }

  selectCurrentPage = function (main_input) {
    if (main_input.checked) {
      main_input.setAttribute('data-selection', 'none')
    } else {
      main_input.setAttribute('data-selection', 'current')
    }

    togglePrint(main_input)
  }

  selectAllPages = function (main_input) {
    if (main_input.getAttribute('data-selection') === 'partial' || main_input.getAttribute('data-selection') === "current") {
      main_input.checked = 0;
    }

    if (main_input.checked) {
      main_input.setAttribute('data-selection', 'none')
    } else {
      main_input.setAttribute('data-selection', 'all')
    }
    togglePrint(main_input)
  }

  checkSelection = function () {
    var main_input = document.getElementById("main_exchange_checkbox");
    var main_selection = main_input.getAttribute('data-selection');
    var all_chekbox = $("exchanges-list").select("input[name=exchange_checkbox]");

    var selection = "none";
    var count_inputs_checked = 0;
    all_chekbox.forEach(function(input) {
      if (input.checked) {
        selection = 'partial';
        count_inputs_checked += 1;
      }
    })

    if (main_selection !== "all" && count_inputs_checked === all_chekbox.length) {
      selection = "current"
    } else if (main_selection === "all" && count_inputs_checked < all_chekbox.length) {
      all_chekbox.forEach(function(input) {
        input.checked = false
        updateCheckbox(input, main_input)
      })
      selection = "none"
    }

    main_input.setAttribute('data-selection', selection)
  }

  actionForElementsSelected = function (action) {
    var main_input = document.getElementById("main_exchange_checkbox");
    var main_selection = main_input.getAttribute('data-selection');
    var url = new Url("eai", "ajax_action_exchange");
    var form = getForm("filterExchange");
    var element_guids = [];
    url.addParam('action', action)

    if (main_selection === "all") {
      // for all elements
      url.addFormData(form)
    } else {
      // form all selected elements
      $("exchanges-list").select("input[name=exchange_checkbox]:checked").each(function (elt) {
        var tbody = elt.up('tbody');
        element_guids.push(tbody.get("exchange"));
      });

      url.addParam('exchange_guids[]', element_guids)
    }

    // refresh element
    var oncomplete = null
    if (element_guids.length > 0 && element_guids.length <= 5) {
      oncomplete = function() {
        element_guids.forEach(function(guid) {
          ExchangeDataFormat.refreshExchange(guid)
        })
      }
    }

    // refresh all
    var refresh_all = (main_selection === 'all' && action !== "export") || (element_guids.length > 5)
    if (refresh_all) {
      oncomplete = function() {
        ExchangeDataFormat.refreshExchangesList(form)
      }
    }

    var options = null
    if (oncomplete) {
      options = { onComplete: oncomplete}
    }

    if (action === "export") {
      url.pop();
    } else {
      url.requestUpdate("systemMsg", options);
    }
  }
</script>
{{assign var=mod_name value=$exchange->_ref_module->mod_name}}


{{mb_include module=system template=inc_pagination total=$total_exchanges current=$page change_page='ExchangeDataFormat.changePage' jumper='10' step=25}}

<table class="layout" style="width: 100%">
  <tr>
    <td style="text-align: right">
      <form name="search-exchange_id" action="" method="get"
            onsubmit="return ExchangeDataFormat.doesExchangeExist('{{$exchange->_class}}', $V($('exchange_id')));">
        <input type="search" id="exchange_id" name="exchange_id" required placeholder="{{tr}}CExchangeDataFormat-exchange_id{{/tr}}" size="25"/>
        <button type="submit" class="lookup notext">{{tr}}search_exchange_by_id-button{{/tr}}</button>
      </form>
    </td>
  </tr>
</table>

<table class="tbl" id="exchanges-list">
  <tr>
    <th class="narrow" colspan="2">
      <input id="main_exchange_checkbox" type="checkbox" style="display: none" data-selection="none"/>
      <span class="far fa-square fa-2x me-color-grey"
            style="cursor: pointer; vertical-align: middle;"
            onclick="selectCurrentPage(document.getElementById('main_exchange_checkbox'));">
      </span>

      {{me_button label="ExchangeDataFormat-action-select current page"
      onclick="selectCurrentPage(document.getElementById('main_exchange_checkbox'));"}}
      {{me_button label="ExchangeDataFormat-action-select all page" label_suf="($total_exchanges)"
      onclick="selectAllPages(document.getElementById('main_exchange_checkbox'));"}}

      {{me_dropdown_button button_label="" button_icon="fas fa-caret-down" button_class="notext me-tertiary fa-lg"  attr='style="margin:0;padding:0;"'
      container_class="me-dropdown-button"}}
    </th>
    <th class="narrow">
      <span>
        {{me_button label="ExchangeDataFormat-action-Reprocess selection" icon="fas fa-sync"
        onclick="actionForElementsSelected('reprocess');" attr="style='color: blue !important;'"}}
        {{me_button label="ExchangeDataFormat-action-Send selection" icon="fa fa-share"
        onclick="actionForElementsSelected('send');" attr="style='color: green !important;'"}}
        {{me_button label="ExchangeDataFormat-action-delete selection" icon="cancel"
        onclick="actionForElementsSelected('delete');" attr="style='color: red !important;'"}}
        {{if $exchange|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2'}}
          {{me_button label="ExchangeDataFormat-action-Export selection in CSV"
          icon="hslip singleclick" onclick="actionForElementsSelected('export');" attr="style='color: darkorange !important;'"}}
        {{/if}}


        {{me_dropdown_button button_label=Options button_icon=opt button_class="notext me-tertiary"
        container_class="me-dropdown-button"}}
      </span>
    </th>
    <th class="narrow"></th>
    <th>{{tr}}eai-Message{{/tr}}</th>
    <th>{{mb_title object=$exchange field="object_id"}}</th>
    <th>{{mb_title object=$exchange field="id_permanent"}}</th>
    <th>{{mb_colonne class=$exchange->_class field="date_production" order_col=$order_col order_way=$order_way function=orderColonne}}</th>
    <th>
      {{mb_title object=$exchange field="sender_id"}}
      <input type="search" onkeyup="filterColonne(this, 'sender')" size="6" />
    </th>
    <th>
      {{mb_title object=$exchange field="receiver_id"}}
      <input type="search" onkeyup="filterColonne(this, 'receiver')" size="6" />
    </th>
    <th>{{mb_title object=$exchange field="type"}}</th>
    <th>{{mb_title object=$exchange field="sous_type"}}</th>
    <th>{{mb_colonne class=$exchange->_class field="send_datetime" order_col=$order_col order_way=$order_way function=orderColonne}}</th>
    <th>{{mb_title object=$exchange field="statut_acquittement"}}</th>
    <th>{{mb_title object=$exchange field="message_valide"}}</th>
    <th>{{mb_title object=$exchange field="acquittement_valide"}}</th>
  </tr>
  {{foreach from=$exchanges item=_exchange}}
    <tbody id="exchange_{{$_exchange->_guid}}" class="table-row" data-exchange="{{$_exchange->_guid}}">
      {{mb_include template=inc_exchange object=$_exchange}}
    </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="21" class="empty">
      {{tr}}{{$exchange->_class}}.none{{/tr}}
    </td>
  </tr>
{{/foreach}}
</table>
