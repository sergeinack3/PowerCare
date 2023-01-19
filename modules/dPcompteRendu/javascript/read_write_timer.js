/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ReadWriteTimer = {
  start:           null,
  step:            null,
  compte_rendu_id: null,
  creation:        null,
  unique_id:       null,
  modele_id:       null,

  init: (compte_rendu_id, modele_id, creation) => {
    ReadWriteTimer.start = new Date();
    ReadWriteTimer.compte_rendu_id = compte_rendu_id;
    ReadWriteTimer.modele_id = modele_id;
    ReadWriteTimer.creation = creation;
    ReadWriteTimer.unique_id = window.unique_id_refresh;
  },

  storeSave: (compte_rendu_id, callback) => {
    if (!compte_rendu_id && !ReadWriteTimer.compte_rendu_id) {
      if (callback) {
        callback();
      }
      return;
    }

    if (!ReadWriteTimer.compte_rendu_id) {
      ReadWriteTimer.compte_rendu_id = compte_rendu_id;
    }

    ReadWriteTimer.saveTime(
      () => {
        if (callback) {
          callback();
        }

        ReadWriteTimer.step = new Date();
      }
    );
  },

  saveTime: (callback) => {
    new Url()
      .addParam('@class', 'CCompteRendu')
      .addParam('compte_rendu_id', ReadWriteTimer.compte_rendu_id)
      .addParam('_add_duree_ecriture', ReadWriteTimer.getTime())
      .addParam('duree_lecture', 0)
      .requestUpdate(
        'systemMsg',
        {
          method: 'POST',
          onComplete: () => {
            if (callback) {
              callback();
            }
          }
        }
      );
  },

  getTime: () => {
    let now = new Date();
    let from = ReadWriteTimer.step ? ReadWriteTimer.step : ReadWriteTimer.start;

    return Math.round((now.getTime() - from.getTime()) / 1000);
  }
};
