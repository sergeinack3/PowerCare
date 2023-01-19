/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProtocoleOp = window.ProtocoleOp || {
  dmi_active:                false,
  operation_id:              null,
  chir_id:                   null,
  bdm:                       null,
  current_libelle_protocole: null,
  mode:                      null,
  readonly:                  false,

  edit: function (protocole_operatoire_id) {
    Form.onSubmitComplete = Object.isUndefined(protocole_operatoire_id) ? ProtocoleOp.onSubmitComplete : Prototype.emptyFunction;

    new Url('planningOp', 'ajax_edit_protocole_op')
      .addParam('protocole_operatoire_id', protocole_operatoire_id)
      .requestModal('80%', '80%', {onClose: ProtocoleOp.refreshList});
  },

  editMateriel: function (materiel_operatoire_id, protocole_operatoire_id) {
    new Url('planningOp', 'editMaterielOp')
      .addParam('materiel_operatoire_id', materiel_operatoire_id)
      .addParam('protocole_operatoire_id', protocole_operatoire_id)
      .requestModal('500px', null, {onClose: ProtocoleOp.refreshListMateriels.curry(protocole_operatoire_id)});
  },

  duplicateProt: function (form) {
    Form.onSubmitComplete = ProtocoleOp.onSubmitComplete;
    form.down('input[name="@class"]').remove();
    form.insert(DOM.input({type: 'hidden', name: 'm', value: 'planningOp'}));
    form.insert(DOM.input({type: 'hidden', name: 'dosql', value: 'do_duplicate_protocole_op'}));
    form.onsubmit();
  },

  onSubmitComplete: function (guid, properties) {
    var id = guid.split("-")[1];
    ProtocoleOp.edit(id);
  },

  onSubmit: function (form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  refreshList: function (order_col, order_way) {
    new Url('planningOp', 'ajax_list_protocoles_op')
      .addFormData(getForm('filterProtocolesOp'))
      .addParam('order_way', order_way)
      .addParam('order_col', order_col)
      .requestUpdate('protocoles_op_area');
  },
    /**
     * Pagination des protocoles opératoires
     *
     */
    changePage: function (page, order) {
        new Url('planningOp', 'ajax_list_protocoles_op')
            .addFormData(getForm('filterProtocolesOp'))
            .addParam('order_way', order)
            .addParam('page', page)
            .requestUpdate('protocoles_op_area');
    },

  refreshListMateriels: function (protocole_operatoire_id) {
    new Url('planningOp', 'ajax_list_materiels_op')
      .addParam('protocole_operatoire_id', protocole_operatoire_id)
      .requestUpdate('materiel_operatoire_area');
  },

  makeAutocompletes: function (form) {
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', '1')
      .addParam('input_field', 'chir_id_view')
      .autoComplete(
        form.chir_id_view,
        null,
        {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(form.chir_id, id);
          }
        }
      );

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('edit', '1')
      .addParam('input_field', 'function_id_view')
      .addParam('view_field', 'text')
      .autoComplete(
        form.function_id_view,
        null,
        {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(form.function_id, id);
          }
        }
      );

    new Url('etablissement', 'ajax_groups_autocomplete')
      .addParam('edit', '1')
      .addParam('input_field', 'group_id_view')
      .addParam('view_field', 'text')
      .autoComplete(
        form.group_id_view,
        null,
        {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(form.group_id, id);
          }
        }
      );
  },

  makeAutocompletesProduit: function (form, supprime) {
    if (ProtocoleOp.mode === 'validation') {
      return;
    }

    if (Object.isUndefined(supprime)) {
      supprime = 0;
    }

    if (ProtocoleOp.dmi_active) {
      new Url('dmi', 'ajax_autocomplete_dmis')
        .addParam('dm', 1)
        .autoComplete(
          form._product_keywords,
          null,
          {
            minChars:           0,
            width:              '500px',
            method:             'get',
            select:             'view',
            dropdown:           true,
            afterUpdateElement: function (field, selected) {
              let tr = $('complPanier');
              if (tr && selected.dataset.type_usage === 'sterilisable') {
                tr.addClassName('hidden')
              } else if (tr) {
                tr.removeClassName('hidden')
              }
              $V(form.dm_id, selected.dataset.id);
            }
          }
        );
    }

    if (form.produit) {
      new Url('medicament', 'httpreq_do_medicament_autocomplete')
        .addParam('search_by_cis', 0)
        .addParam('supprime', supprime)
        .autoComplete(
          form.produit,
          null,
          {
            minChars:      3,
            width:         '500px',
            updateElement: function (selected) {
              var code = selected.down('.code').getText();
              var libelle = selected.down('.ucd-view').getText();
              $V(form.code_cip, code);
              $V(form.bdm, ProtocoleOp.bdm);
              $V(form.produit, libelle);
            }
          }
        );
    }
  },

  makeAutocompleteProtocole: function (form) {
    if (ProtocoleOp.mode === 'validation' || ProtocoleOp.mode === 'consommation') {
      return;
    }

    if (form._protocole_op_libelle) {
      new Url('planningOp', 'ajax_protocole_op_autocomplete')
        .addParam('view_field', '_protocole_op_libelle')
        .addParam('only_validated', 1)
        .autoComplete(
          form._protocole_op_libelle,
          null,
          {
            minChars:           0,
            dropdown:           true,
            method:             'get',
            width:              '500px',
            callback:           function (input, queryString) {
              return queryString + '&chir_id=' + ProtocoleOp.chir_id
            },
            afterUpdateElement: function (field, selected) {
              $V(field, '');
              ProtocoleOp.applyProtocoleOp(selected.dataset.protocole_op_id);
            }
          }
        );
    }
  },

  validerPrat: function () {
    var form = getForm('editProtocoleOp');
    $V(form.validation_praticien_id, User.id);
    $V(form.validation_praticien_datetime, 'current');
    form.onsubmit();
  },

  invaliderPrat: function () {
    var form = getForm('editProtocoleOp');
    $V(form.validation_praticien_id, '');
    $V(form.validation_praticien_datetime, '');
    form.onsubmit();
  },

  valideCadreBloc: function () {
    var form = getForm('editProtocoleOp');
    $V(form.validation_cadre_bloc_id, User.id);
    $V(form.validation_cadre_bloc_datetime, 'current');
    form.onsubmit();
  },

  invalideCadreBloc: function () {
    var form = getForm('editProtocoleOp');
    $V(form.validation_cadre_bloc_id, '');
    $V(form.validation_cadre_bloc_datetime, '');
    form.onsubmit();
  },

  addProtocoleOp: function (selected) {
    var form = getForm('toggleProtocoleOp');
    $V(form.protocole_operatoire_id, selected.dataset.protocole_op_id);

    ProtocoleOp.current_libelle_protocole = selected.dataset.libelle;

    onSubmitFormAjax(form);
  },

  addProtocoleOpButton: function (id, obj) {
    if (!id) {
      return;
    }

    $('protocoles_op_area').insert(
      DOM.button(
        {
          type:                               'button',
          className:                          'remove',
          onclick:                            'ProtocoleOp.removeProtocoleOp(this)',
          title:                              $T('delete'),
          'data-protocole_operatoire_dhe_id': id
        },
        ProtocoleOp.current_libelle_protocole
      )
    );
  },

  removeProtocoleOp: function (button) {
    var form = getForm('toggleProtocoleOp');

    $V(form.protocole_operatoire_dhe_id, button.dataset.protocole_operatoire_dhe_id);
    $V(form.del, '1');

    onSubmitFormAjax(
      form,
      function () {
        $V(form.protocole_operatoire_dhe_id, '');
        $V(form.del, '0');
        button.remove();
      }
    );
  },

  replaceProduct: function () {
    new Url('planningOp', 'vw_replace_product')
      .requestModal('700px', '80%');
  },

  checkButton: function (input) {
    if (input.form.name === 'productFrom') {
      return ProtocoleOp.checkVisualize();
    }

    return ProtocoleOp.checkValidate();
  },

  checkVisualize: function () {
    var disabled = true;

    var form_from = getForm('productFrom');

    if (($V(form_from.dm_id) || $V(form_from.code_cip))) {
      disabled = false;
    }

    $('view_replace').writeAttribute('disabled', disabled ? 'disabled' : null);
  },

  checkValidate: function () {
    var disabled = true;

    var form_from = getForm('productFrom');
    var form_to = getForm('productTo');

    if (($V(form_from.dm_id) || $V(form_from.code_cip)) && ($V(form_to.dm_id) || $V(form_to.code_cip))) {
      disabled = false;
    }

    $('validate_replace').writeAttribute('disabled', disabled ? 'disabled' : null);
  },

  validerReplacement: function (mode_operation, callback) {
    if (Object.isUndefined(mode_operation)) {
      mode_operation = 0;
    }

    if (!confirm($T('CProtocoleOperatoire-Confirm replacement product' + (mode_operation ? ' operation' : '')))) {
      return;
    }

    var form = getForm('replaceProduct');
    var form_from = getForm('productFrom');
    var form_to = getForm('productTo');

    $V(form.protocole_op_ids, $$('input.replace_prot:checked').pluck('value').join('-'));
    $V(form.operation_ids, $$('input.replace_op:checked').pluck('value').join('-'));
    $V(form.mode_operation, mode_operation);
    $V(form.dm_id_from, $V(form_from.dm_id));
    $V(form.code_cip_from, $V(form_from.code_cip));
    $V(form.dm_id_to, $V(form_to.dm_id));
    $V(form.code_cip_to, $V(form_to.code_cip));

    return onSubmitFormAjax(form, callback ? callback : ProtocoleOp.seeProtocoles);
  },

  seeProtocoles: function (protocole_op_ids) {
    var form_from = getForm('productFrom');

    new Url('planningOp', 'ajax_search_replace_protocoles')
      .addParam('dm_id', $V(form_from.dm_id))
      .addParam('code_cip', $V(form_from.code_cip))
      .addParam('protocole_op_ids', protocole_op_ids)
      .requestUpdate('replacement_result', function () {
        ProtocoleOp.checkValidate();
      });
  },

  seeOperationsReplacement: function () {
    var form_from = getForm('productFrom');

    new Url('planningOp', 'ajax_list_ops_replacement')
      .addParam('dm_id', $V(form_from.dm_id))
      .addParam('code_cip', $V(form_from.code_cip))
      .requestModal('60%', '80%');
  },

  applyProtocoleOp: function (protocole_operatoire_id) {
    new Url('planningOp', 'ajax_apply_protocole_op')
      .addParam('protocole_operatoire_id', protocole_operatoire_id)
      .addParam('operation_id', ProtocoleOp.operation_id)
      .requestModal('70%', '70%');
  },

  apply: function (protocole_operatoire_id, libelle) {
    onSubmitFormAjax(
      getForm('applyProtocoleOp'),
      function () {
        Control.Modal.close();
        ProtocoleOp.refreshListMaterielsOperation();

        $('list_protocoles_operation').insert(
          DOM.button(
            {
              type:      'button',
              className: 'remove',
              title:     $T('Delete'),
              onclick:   'ProtocoleOp.removeProtocoleOperation(this, \'' + protocole_operatoire_id + '\', \'' + libelle + '\')'
            },
            libelle
          )
        )
      }
    );
  },

  applyProduct: function () {
    var form = getForm('addMaterielOp');

    if ($V(form.code_cip)) {
      $V(form.bdm, ProtocoleOp.bdm);
    }

    return onSubmitFormAjax(
      form,
      function () {
        ProtocoleOp.refreshListMaterielsOperation();

        $V(form.dm_id, '', false);
        $V(form.code_cip, '', false);
        $V(form.bdm, '');
        $V(form._product_keywords, '');
        $V(form.produit, '');
      }
    );
  },

  removeProtocoleOperation: function (button, protocole_operatoire_id, libelle) {
    if (!confirm($T('CProtocoleOperatoire-Confirm remove', libelle))) {
      return;
    }

    var form = getForm('removeProtocole');
    $V(form.protocole_operatoire_id, protocole_operatoire_id);

    onSubmitFormAjax(
      form,
      function () {
        button.remove();
        ProtocoleOp.refreshListMaterielsOperation();
      }
    );
  },

  refreshListMaterielsOperation: function (materiel_operatoire_id) {
    materiel_operatoire_id = parseInt(materiel_operatoire_id);
    new Url('planningOp', 'ajax_list_materiels_operation')
      .addParam('operation_id', ProtocoleOp.operation_id)
      .addParam('materiel_operatoire_id', materiel_operatoire_id ? materiel_operatoire_id : null)
      .addParam('mode', ProtocoleOp.mode)
      .addParam('readonly', ProtocoleOp.readonly)
      .requestUpdate(materiel_operatoire_id ? ('CMaterielOperatoire-' + materiel_operatoire_id) : 'materiels_area');
  },

  editMaterielOperation: function (materiel_operatoire_id, props, refresh_line, del) {
    if (Object.isUndefined(del)) {
      del = 0;
    }

    if (Object.isUndefined(refresh_line)) {
      refresh_line = 0;
    }

    if (del && !confirm($T('CMaterielOperatoire-Confirm deletion', props.libelle))) {
      return;
    }

    var form = getForm('editMateriel');

    Object.keys(props).each(
      function (_key) {
        form.insert(DOM.input({type: 'hidden', name: _key, value: props[_key]}));
      }
    );

    $V(form.materiel_operatoire_id, materiel_operatoire_id);
    $V(form.del, del);

    onSubmitFormAjax(
      form,
      function () {
        if (refresh_line) {
          ProtocoleOp.refreshListMaterielsOperation(materiel_operatoire_id);
        }

        if (del) {
          ProtocoleOp.refreshListMaterielsOperation();
        }
      }
    );

    Object.keys(props).each(
      function (_key) {
        var elt = form.elements[_key];
        if (elt) {
          elt.remove();
        }
      }
    );
  },

  delConsommationMateriel: function (consommation_materiel_id, materiel_operatoire_id) {
    var form = getForm('delConsommation');

    $V(form.consommation_materiel_id, consommation_materiel_id);

    return onSubmitFormAjax(form, function () {
      $V(form.consommation_materiel_id, '');

      ProtocoleOp.refreshListMaterielsOperation(materiel_operatoire_id);
    });
  },

  toggleLockConsommation: function (lock) {
    var form = getForm('toggleLockConsommation');

    $V(form.consommation_user_id, lock ? User.id : '');
    $V(form.consommation_datetime, lock ? 'current' : '');

    return onSubmitFormAjax(form, Control.Modal.refresh);
  },

  createLot: function (product_id) {
    new Url('dmi', 'ajax_create_lot')
      .addParam('product_id', product_id)
      .addParam('callback', 'ProtocoleOp.injectLot')
      .requestModal();
  },

  injectLot: function (lot_id, lot) {
    new Url('dmi', 'ajax_inject_lot')
      .addParam('lot_id', lot_id)
      .requestJSON(function (detail_lot) {

        var view_lot = '[' + detail_lot.code + ']';
        if (detail_lot.lapsing_date) {
          view_lot += ' - ' + detail_lot.lapsing_date;
        }
        view_lot += ' - ' + detail_lot.societe;

        $$('form.consommation_' + lot._product_id).each(function (_form) {
          _form.lot_id.insert(DOM.option({value: lot_id}, view_lot));
          $V(_form.lot_id, lot_id);
        });

        Control.Modal.close();
      });
  },

  print: function (protocole_op_id, all_protocoles) {
    all_protocoles = Object.isUndefined(all_protocoles) ? 0 : all_protocoles;

    var form = getForm('filterProtocolesOp');

    new Url('planningOp', 'print_protocoles_op')
      .addParam('chir_id', all_protocoles ? '' : $V(form.chir_id))
      .addParam('function_id', all_protocoles ? '' : $V(form.function_id))
      .addParam('search_all_protocole_op', $V(form.search_all_protocole_op))
      .addParam('protocole_op_id', protocole_op_id)
      .addParam('dialog', 1)
      .open();
  },

  importCSV: function () {
    var form = getForm('filterProtocolesOp');

    new Url('planningOp', 'vw_import_protocoles_ops')
      .addParam('chir_id', $V(form.chir_id))
      .addParam('function_id', $V(form.function_id))
      .addParam('group_id', $V(form.group_id))
      .popup(800, 500);
  },

  exportCSV: function () {
    var form = getForm('filterProtocolesOp');

    new Url('planningOp', 'ajax_export_protocoles_ops', 'raw')
      .addParam('chir_id', $V(form.chir_id))
      .addParam('function_id', $V(form.function_id))
      .addParam('search_all_protocole_op', $V(form.search_all_protocole_op))
      .open();
  },

  toggleListConsommations: function (button) {
    Element.classNames(button).flip('down', 'up');

    button.up('div').down('div')[button.hasClassName('down') ? 'hide' : 'show']();
  },
  /**
   * Affiche tout les protocoles opératoires de l'établissement en cours, praticiens et fonctions confondues
   *
   * @param form
   */
  showProcotolesGroup:     function (form) {
    $V(form.search_all_protocole_op, form._search_all_protocole_op.checked ? 1 : 0);
    $V(form.chir_id, '', false);
    $V(form.chir_id_view, '', false);
    $V(form.function_id, '', false);
    $V(form.function_id_view, '', false);
    if (form._search_all_protocole_op.checked) {
      ProtocoleOp.refreshList(form.order_col, form.order_way)
    }
  }
};
