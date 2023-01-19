/**
 * @package Mediboard\Addictologie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
TimelineImplement = {
    pregnancy_id : 0,
    /**
     * Refresh timeline (using menu filter)
     * @param menus
     * @param pregnancy_id
     */
  refreshResume: function (menus, pregnancy_id) {
    new Url('maternite', 'ajax_timeline_pregnancy')
      .addParam('menu_filter', JSON.stringify(menus), true)
      .addParam('pregnancy_id', pregnancy_id ? pregnancy_id : TimelineImplement.pregnancy_id)
      .addParam('refresh', 1)
      .requestUpdate('pregnancy_main_timeline')
  },

  /**
   * Selects a practitioner that will filter the timeline
   *
   * @param pregnancy_id
   * @param menu_filter - selected menus
   * @param filter_user_id - user chosen to filter the timeline
   */
  selectPractitioner: function(pregnancy_id, menu_filter, filter_user_id) {
    new Url('maternite', 'ajax_timeline_pregnancy')
      .addParam('pregnancy_id', pregnancy_id)
      .addParam('menus_filter', menu_filter)
      .addParam('practitioner_filter', filter_user_id)
      .addParam('refresh', 1)
      .requestUpdate('pregnancy_main_timeline');
  },

  /**
   * Prints the pregnancy file
   *
   * @param {int} anesth_file - anesthesia file id
   */
  printFichePregnancy: function (anesth_file) {
    new Url("cabinet", "print_fiche")
      .addParam("dossier_anesth_id", anesth_file)
      .addParam("print", true)
      .popup(700, 500, "printFiche");
  }
};
