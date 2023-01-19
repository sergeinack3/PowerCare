/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AccueilPresentation = {
  /** Timings */
  rafraichissementPages: 60,
  tempsDefilementPages: 60,
  rafraichissementBandeau: 60,
  tempsDefilementBandeau: 60,
  /** Timeouts */
  timeoutPage: null,
  timeoutBandeau: null,
  /** Page courante */
  currentPage: 0,
  /** Donn�e-bandeau courante */
  currentBandeauData: 0,
  /** Donn�es � afficher dans le bandeau de donn�es */
  bandeauData: [],
  /** Gestion du son d'alerte */
  soundAlert: null,
  soundEnabled: true,
  /**
   * Rafraichissement automatique de la liste des patients
   *  Lance le d�filement de la barre de chargement
   *  Lance le d�filement automatique des pages
   *
   * @returns {AccueilPresentation}
   */
  periodicalUpdatePages: function() {
    this.prepareUrl()
      .periodicalUpdate('admission_presentation_lines', {
        onCreate: function() {
          this.disableBarLoader()
            .currentPage = 0;
          window.clearTimeout(this.timeoutPage);
        }.bind(this),
        onSuccess: function() {
          this.defilerBarLoader()
            .periodicalSwipPages();
        }.bind(this),
        frequency: this.rafraichissementPages
      });
    return this;
  },
  /**
   * Rafraichissement automatique du bandeau d'informations
   *  Lance automatiquement le d�filement automatique des �l�ments du bandeau
   *
   * @returns {AccueilPresentation}
   */
  periodicalUpdateBandeau: function() {
    this.prepareUrl()
      .addParam('mode_bandeau', true)
      .periodicalUpdate('bandeau_container_data', {
        onCreate: function() {
          window.clearTimeout(this.timeoutBandeau);
        }.bind(this),
        evalJSON: 'force',
        onSuccess: function(data) {
          this.bandeauData = [];

          if (data.responseJSON.length > 0) {
            if (this.soundEnabled) {
              if (!this.soundAlert) {
                this.soundAlert = $('admission_presentation_alert');
              }
              this.soundAlert.play();
            }
            this.addBandeauElement(data.responseJSON);
            this.periodicalSwipBandeau();
          }
        }.bind(this),
        frequency: this.rafraichissementBandeau
      });
    return this;
  },
  /**
   * D�filement automatique des pages du tableau des patients
   *
   * @returns {AccueilPresentation}
   */
  periodicalSwipPages: function() {
    if ($('sejour_page_' + this.currentPage)) {
      $('sejour_page_' + this.currentPage).hide();
    }

    this.currentPage++;
    if ($('sejour_page_' + this.currentPage)) {
      $('sejour_page_' + this.currentPage).show();
      var vignettesList = $('accueil_presentation_vignettes').select('div');
      if (typeof(vignettesList[this.currentPage]) !== 'undefined') {
        vignettesList.invoke('removeClassName', 'selectionnee');
        vignettesList[this.currentPage].addClassName("selectionnee");
      }
    }
    else {
      this.currentPage = -1;
      if ($('sejour_page_0')) {
        return this.periodicalSwipPages();
      }
    }
    this.timeoutPage = setTimeout(function() {
      this.periodicalSwipPages();
    }.bind(this), this.tempsDefilementPages*1000);
    return this;
  },
  /**
   * D�filement automatique des �l�ments du bandeau d'informations
   *
   * @returns {AccueilPresentation}
   */
  periodicalSwipBandeau: function() {
    if (this.bandeauData.length > 0) {
      var bandeauContainer = $('bandeau_container');
      bandeauContainer.select('div').invoke("remove");
      bandeauContainer.insert('<div><span>' + this.bandeauData[this.currentBandeauData] + '</span></div>');
      bandeauContainer.select('div').each(function(element) {
        element.setStyle({
          animationDuration: this.tempsDefilementBandeau+'s',
          animationIterationCount: 1,
        }).addClassName('defile');
      }.bind(this));
      this.currentBandeauData = (this.currentBandeauData < (this.bandeauData.length - 1)) ? this.currentBandeauData + 1 : 0;
    }

    this.timeoutBandeau = setTimeout(function() {
      this.periodicalSwipBandeau();
    }.bind(this), this.tempsDefilementBandeau*1000);
    return this;
  },
  /**
   * Ajoute un �l�ment (tableau ou cha�ne de caract�res) aux donn�es �
   *  afficher dans le bandeau
   *
   * @param element mixed cha�ne de caract�res ou tableau de cha�ne de caract�re � ajouter
   *
   * @returns {AccueilPresentation}
   */
  addBandeauElement: function(element) {
    if (typeof(element) === 'object' && Array.isArray(element)) {
      element.each(function(_element) {
        this.addBandeauElement(_element);
      }.bind(this));
    }
    else if (typeof(element) === 'string') {
      this.bandeauData.push(element);
    }

    return this;
  },
  /**
   * Pr�pare la requ�te pour la r�cup�ration des donn�es
   *  Retourne l'Url pr�par�e
   *
   * @returns Url
   */
  prepareUrl: function() {
    var form = getForm('admission_presentation_filters');
    return new Url('admissions', 'accueil_presentation_lines')
      .addFormData(form)
      .addParam('type_pec[]', JSON.parse($V(form.type_pec)), true);
  },
  /**
   * D�filement de la barre de chargement
   *
   * @returns {AccueilPresentation}
   */
  defilerBarLoader: function() {
    $('bar_loader').setStyle({animationDuration: this.rafraichissementPages + 's'})
      .addClassName('animated');
    return this;
  },
  /**
   * D�sactivation de la barre de chargement (utile pour synchroniser la barre avec les chargements)
   *
   * @returns {AccueilPresentation}
   */
  disableBarLoader: function() {
    $('bar_loader').removeClassName('animated');
    return this;
  },
};
