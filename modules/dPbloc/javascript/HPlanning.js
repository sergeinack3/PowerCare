/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

HPlanning = {
  interval: null,

  display: function(form, reset_period) {
    var url = new Url('bloc', 'ajax_horizontal_planning');
    url.addParam('date', $V(form.elements['date']));
    url.addParam('blocs_ids[]', $V(form.elements['blocs_ids']));
    var rooms_ids = $$('.salle_selector:checked').invoke('get', 'salle_id').join('|');
    url.addParam('salles_ids', rooms_ids);
    url.addParam('window_width', HPlanning.getWindowWidth(rooms_ids.split('|').length));

    if ($('timeline_container') && !reset_period) {
      url.addParam('selected_period', $$('table.horizontal_planning.selected')[0].get('period_id'));
    }

    url.requestUpdate('timeline_body');
  },

  previousPeriod: function(period) {
    var new_period = period - 1;
    $('period_' + period + '_table').removeClassName('selected');
    $('period_' + new_period + '_table').addClassName('selected');

    $$('.period_' + period).invoke('hide');
    $$('.period_' + new_period).invoke('show');
  },

  nextPeriod: function(period) {
    var new_period = period + 1;
    $('period_' + period + '_table').removeClassName('selected');
    $('period_' + new_period + '_table').addClassName('selected');

    $$('.period_' + period).invoke('hide');
    $$('.period_' + new_period).invoke('show');
  },

  getWindowWidth: function(count_rooms) {
    var container = HPlanning.getLayout();
    /* Getting the available display height (without the Mb menu and the filters of the timeline */
    var avlHeight = window.innerHeight - container.cumulativeOffset().top - 110;

    /* Taking into account the width of the left menu, if any */
    var container_position = container.cumulativeOffset().left + container.getLayout().get('padding-left');
    var width = window.innerWidth - 120 - container_position;
    /* If the height of the rooms to display is higher than the available height, we substract the width of the scrollbar */
    if (count_rooms * 85 > avlHeight) {
      width = width - 20;
    }

    return width;
  },

  setContainerWidth: function() {
    var layout = HPlanning.getLayout();
    /* Taking into account the width of the left menu, if any */
    var layout_position = layout.cumulativeOffset().left + layout.getLayout().get('padding-left');
    var width_compenser = 150;
    var width = window.innerWidth - width_compenser - layout_position;

    var container = $('timeline_container');
    container.setStyle({width: width + 'px'});
  },

  getLayout: function() {
    var timeline_layout = $('timeline_layout');
    var layout = timeline_layout.up('td');
    return typeof(layout) === "undefined" ? timeline_layout.up() : layout;
  },

  openFilters: function() {
    Modal.open($('timeline_salle_filters'), {title: "Filtres d'affichage", showClose: true});
  },

  refreshSelectedBlocs: function(form) {
    var blocs = $V(form.elements['blocs_ids']);
    $$('label.salle_display').each(function(element) {
      if (blocs.indexOf(element.get('bloc_id')) != -1) {
        element.down('input').checked = true;
        element.show();
        element.up().show();
      }
      else {
        element.hide();
        element.down('input').checked = false;
      }
    });

    $$('span.bloc_display').each(function(element) {
      if (blocs.indexOf(element.get('bloc_id')) != -1) {
        element.show();
      }
      else {
        element.hide();
      }
    });

    if (blocs.length == 0) {
      $('bloc_none').show();
    }
    else {
      $('bloc_none').hide();
    }
  },

  refreshDate: function(form, refresh) {
    var months = Control.DatePicker.Language['fr'].months;
    var days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']
    var date = new Date($V(form.elements['date']));
    $('date_placeholder').innerHTML = days[date.getDay()] + ' ' + date.getDate() + ' ' + months[date.getMonth()].toLowerCase() + ' ' + date.getFullYear();

    if (refresh) {
      form.onsubmit();
    }
  },

  editBlocage: function(blocage_id, salle_id) {
    var url = new Url('bloc', 'ajax_edit_blocage');

    if (!blocage_id) {
      blocage_id = '';
      url.addParam('salle_id', salle_id);
      url.addParam('date', $V(getForm('timeline_filters').elements['date']));
    }

    url.addParam('blocage_id', blocage_id);
    url.requestModal(600, 400, {
      onClose: HPlanning.display.curry(getForm('timeline_filters'))
    });
  },

  /* Mouse over and mouse out event */

  setEventFor: function(operation_guid) {
    $(operation_guid).observe('mouseover', HPlanning.onOperationHover.curry(operation_guid));
    $(operation_guid).observe('mouseout', HPlanning.onOperationOut.curry(operation_guid));
    if ($('preop_' + operation_guid)) {
      $('preop_' + operation_guid).observe('mouseover', HPlanning.onOperationHover.curry(operation_guid));
      $('preop_' + operation_guid).observe('mouseout', HPlanning.onOperationOut.curry(operation_guid));
    }
    if ($('postop_' + operation_guid)) {
      $('postop_' + operation_guid).observe('mouseover', HPlanning.onOperationHover.curry(operation_guid));
      $('postop_' + operation_guid).observe('mouseout', HPlanning.onOperationOut.curry(operation_guid));
    }
  },

  onOperationHover: function(operation_guid) {
    var operation = $(operation_guid);
    if ($('preop_' + operation_guid)) {
      $('preop_' + operation_guid).addClassName('onhover');
    }
    if ($('postop_' + operation_guid)) {
      var postop = $('postop_' + operation_guid);
      postop.writeAttribute('data-position', parseInt(postop.getStyle('left').replace('px', '')));
      var left = parseInt(operation.getStyle('left').replace('px', ''));
      var size = 450;
      if (!operation.hasClassName('undersized')) {
        size = parseInt(operation.getStyle('width').replace('px', ''));
      }
      left = left + size;
      postop.setStyle({left: left + 'px'});
      postop.addClassName('onhover');
    }
    operation.addClassName('onhover');
    $('infos-' + operation_guid).show();
  },

  onOperationOut: function(operation_guid) {
    if ($('preop_' + operation_guid)) {
      $('preop_' + operation_guid).removeClassName('onhover');
    }
    if ($('postop_' + operation_guid)) {
      var postop = $('postop_' + operation_guid);
      postop.setStyle({left: postop.get('position') + 'px'});
      postop.writeAttribute('data-position', '');
      postop.removeClassName('onhover');
    }
    $(operation_guid).removeClassName('onhover');
    $('infos-' + operation_guid).hide();
  },

  /* Drag an drop functions */

  allowDrop: function(event) {
    event.preventDefault();
  },

  onDrag: function(event) {
    event.dataTransfer.setData("text", event.target.id);
  },

  onDrop: function(event) {
    event.preventDefault();
    var element = $(event.dataTransfer.getData('text'));
    var operation_id = element.get('operation_id');
    var salle_id = event.target.up('tr').get('salle_id');

    HPlanning.displayChangeOperationRoom(operation_id, salle_id, element);
  },

  searchPatientByNDA: function(auto_entree_bloc, sejour_id, operation_id) {
    new Url('salleOp', 'vw_code_barre_nda')
      .addParam('modal', 1)
      .addParam('auto_entree_bloc', auto_entree_bloc)
      .addParam('sejour_id', sejour_id)
      .addParam('operation_id', operation_id)
      .requestModal(
        600,
        320,
        {
          onClose: HPlanning.display.curry(getForm('timeline_filters'))
        }
      );
  },

  /** Operations timings **/

  openOperationTimings: function(operation_id) {
    var url = new Url('salleOp', 'httpreq_vw_timing');
    url.addParam('operation_id', operation_id);
    url.addParam('submitTiming', 'HPlanning.submitTiming');
    url.addParam('operation_header', 1);
    url.addParam('modal', 1);
    url.requestModal(1200, null, {
      onClose: HPlanning.display.curry(getForm('timeline_filters'))
    });
  },

  submitTiming: function(oForm) {
    onSubmitFormAjax(oForm, function() {
      HPlanning.reloadTiming($V(oForm.operation_id));
    });
  },

  reloadTiming: function(operation_id) {
    var url = new Url('salleOp', 'httpreq_vw_timing');
    url.addParam('operation_id', operation_id);
    url.addParam('submitTiming', 'HPlanning.submitTiming');
    url.addParam('operation_header', 1);
    url.requestUpdate('timing');
  },

  displayChangeOperationRoom: function(operation_id, salle_id, element) {
    var form = getForm('changeOperationRoom');
    $V(form.elements['operation_id'], operation_id);
    $V(form.elements['salle_id'], salle_id);

    if (element.hasClassName('hors_plage')) {
      form.elements['time_operation'].enable();
      $V(form.elements['time_operation'], element.get('time_operation'));
      $V(form.elements['time_operation_da'], element.get('time_operation').substring(0, 5));
      $('changeOperationRoom-hors_plage').show();
      $('changeOperationRoom-msg').hide();
    }
    else {
      form.elements['time_operation'].disable();
      $V(form.elements['time_operation'], '');
      $V(form.elements['time_operation_da'], '');
      $('changeOperationRoom-hors_plage').hide();
      $('changeOperationRoom-msg').down('div').update($T('COperation-msg-confirm_room_change', $('CSalle-' + salle_id).get('name')));
      $('changeOperationRoom-msg').show();
    }

    var title_operation = $('COperation-' + operation_id + '-patient').textContent;
    if ($('COperation-' + operation_id + '-libelle').textContent != '') {
      title_operation = title_operation + ' - ' + $('COperation-' + operation_id + '-libelle').textContent
    }

    $('changeOperationRoom-operation').update(title_operation);

    Modal.open('changeOperationRoom', {title: $T('COperation-title-room_change'), width: '430px', height: '100px'});
  },

  changeOperationRoom: function() {
    var form = getForm('changeOperationRoom');

    if ($('changeOperationRoom-hors_plage').visible()) {
      var url = new Url('bloc', 'ajax_check_planning_conflict');
      url.addParam('salle_id', $V(form.elements['salle_id']));
      url.addParam('operation_id', $V(form.elements['operation_id']));
      url.addParam('time', $V(form.elements['time_operation']));
      url.requestJSON(function(data) {
        if (data.conflicts) {
          var msg = 'COperation-error-timing_conflict';
          if (data.conflicts > 1) {
            msg = msg + '|pl';
          }
          var element = DOM.div({}, $T(msg));
          var list = DOM.ul();
          data.operations.each(function(operation) {
            list.insert(DOM.li({}, operation));
          });
          element.insert(list);
          Modal.confirm(element, {
            onOK: HPlanning.submitFormChangeOperationRoom.bind(HPlanning, form),
            onKO: Control.Modal.close.curry()
          });
        }
        else {
          HPlanning.submitFormChangeOperationRoom(form);
        }
      });
    }
    else {
      HPlanning.submitFormChangeOperationRoom(form);
    }
  },

  submitFormChangeOperationRoom: function(form) {
    onSubmitFormAjax(form, HPlanning.display.curry(getForm('timeline_filters')));
    Control.Modal.close();
  },

  presentationMode: function() {
    var form = getForm('timeline_filters');
    var url = new Url('bloc', 'vw_horizontal_planning');
    url.addParam('date', $V(form.elements['date']));
    url.addParam('blocs_ids[]', $V(form.elements['blocs_ids']));
    url.addParam('display', 'fullscreen');
    url.popup("100%", "100%", 'Mode présentation');
  },

  print: function() {
    $('timeline_body').print();
  },

  setAutoRefreshInterval: function(interval) {
    this.interval = interval;
  },

  toggleAutoRefresh: function(button) {
    if (button) {
      button.toggleClassName('play');
      button.toggleClassName('pause');
    }

    if (!(window.autoRefreshTimelineSalle) && HPlanning.interval && HPlanning.interval > 0) {
      window.autoRefreshTimelineSalle = setInterval(
        HPlanning.display.curry(getForm('timeline_filters')),
        HPlanning.interval
      );
    }
    else {
      clearTimeout(window.autoRefreshTimelineSalle);
    }
  },

  legend: function() {
    Modal.open($('horizontal_planning_legend'), {title: "Légende", showClose: true});
  },

  selectView: function() {
    Modal.open(
      $('select_planning_view'),
      {
        title: $T('pref-view_planning_bloc'),
        showClose: true,
        width: 300
      }
    );
  }
};
