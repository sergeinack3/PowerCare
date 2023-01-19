{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  searchCotations = function(form) {
    var url = new Url('pmsi', 'ajax_cotations_stats');
    url.addFormData(form);
    url.requestUpdate('stats');
    return false;
  };

  exportStats = function(form) {
    $V(form.export, 1);
    $V(form.limit, 0);
    $V(form.suppressHeaders, 1);

    form.submit();

    $V(form.export, 0);
    $V(form.limit, 20);
    $V(form.suppressHeaders, '');
  };

  toggleValueCheckbox = function(checkbox, element) {
    $V(element, checkbox.checked ? 1 : 0);
  };

  changePage = function(start) {
    var form = getForm('filterCotations');
    if (form) {
      $V(form.start, start);
      searchCotations(form);
    }
  };

  showDetailsFor = function(chir_id, period) {
    var period_elt = $(period);
    var form = getForm('filterCotations');
    var url = new Url('pmsi', 'ajax_cotation_operations_details');
    url.addParam('chir_id', chir_id);
    url.addParam('sejour_type', $V(form.sejour_type));
    url.addParam('begin_date', period_elt.readAttribute('data-begin_date'));
    url.addParam('end_date', period_elt.readAttribute('data-end_date'));
    url.addParam('period', period);
    url.requestModal();
  };

  exportActs = function(operation_guid) {
    var guids = [];
    if (operation_guid) {
      guids.push(operation_guid);
    }
    else {
      $$('input.select_operations').each(function(input) {
        guids.push(input.readAttribute('data-guid'));
      });
    }

    if (guids.length) {
      var url = new Url('pmsi', 'export_multiple_actes_pmsi');
      url.addParam('object_guids', Object.toJSON(guids));
      url.requestUpdate('systemMsg', {
        method:        'post',
        getParameters: {m: 'pmsi', a: 'export_multiple_actes_pmsi'}
      });
    }
  };
</script>

<div id="filters">
  {{mb_include module=pmsi template=cotations/inc_filters}}
</div>

<div id="stats">
  {{mb_include module=pmsi template=cotations/inc_statistics}}
</div>