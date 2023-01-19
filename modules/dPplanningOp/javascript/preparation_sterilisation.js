/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Gestion de la préparation des stérilisation avec un export CSV et une impression
 *
 * @type {{refreshList: (function(): Boolean), print: PreparationSterilisation.print, form: null, exportCSV: PreparationSterilisation.exportCSV}}
 */
PreparationSterilisation = {
  form: null,

  /**
   * Refresh list of dms
   *
   * @returns {Boolean}
   */
  refreshList: function() {
    return onSubmitFormAjax(
      this.form,
      null,
      'dms_sterilisation'
    );
  },

  /**
   * Exports the list in CSV format
   */
  exportCSV: function() {
    this.form.target = '_blank';
    $V(this.form.suppressHeaders, '1');
    $V(this.form.csv, '1');

    this.form.submit();

    this.form.target = null;
    $V(this.form.suppressHeaders, '0');
    $V(this.form.csv, '0');
  },

  /**
   * Prints the list
   */
  print: function() {
    this.form.up('div.content').print();
  },

  /**
   * Update period of dates
   */
  updatePeriod: function() {
    console.log('ici');
    var date_min, date_max;
    switch ($V(this.form._prepa_period)) {
      default:
      case 'all_day':
        date_min = new Date();
        date_min.setHours(0, 0, 0);
        $V(this.form._prepa_dt_min, date_min.toDATETIME());
        $V(this.form._prepa_dt_min_da, date_min.toLocaleDateTime());

        date_max = new Date();
        date_min.setHours(23, 59, 59);
        $V(this.form._prepa_dt_max, date_min.toDATETIME());
        $V(this.form._prepa_dt_max_da, date_min.toLocaleDateTime());

        break;

      case 'morning':
        date_min = new Date();
        date_min.setHours(0, 0, 0);
        $V(this.form._prepa_dt_min, date_min.toDATETIME());
        $V(this.form._prepa_dt_min_da, date_min.toLocaleDateTime());

        date_max = new Date();
        date_min.setHours(12, 0, 0);
        $V(this.form._prepa_dt_max, date_min.toDATETIME());
        $V(this.form._prepa_dt_max_da, date_min.toLocaleDateTime());

        break;

      case 'afternoon':
        date_min = new Date();
        date_min.setHours(12, 0, 0);
        $V(this.form._prepa_dt_min, date_min.toDATETIME());
        $V(this.form._prepa_dt_min_da, date_min.toLocaleDateTime());

        date_max = new Date();
        date_min.setHours(23, 59, 59);
        $V(this.form._prepa_dt_max, date_min.toDATETIME());
        $V(this.form._prepa_dt_max_da, date_min.toLocaleDateTime());
    }
  }
};