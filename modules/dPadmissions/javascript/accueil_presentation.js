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
  /** Donnée-bandeau courante */
  currentBandeauData: 0,
  /** Données à afficher dans le bandeau de données */
  bandeauData: [],
  /** Gestion du son d'alerte */
  soundAlert: null,
  soundEnabled: true,
  /**
   * Rafraichissement automatique de la liste des patients
   *  Lance le défilement de la barre de chargement
   *  Lance le défilement automatique des pages
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
   *  Lance automatiquement le défilement automatique des éléments du bandeau
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
   * Défilement automatique des pages du tableau des patients
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
   * Défilement automatique des éléments du bandeau d'informations
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
   * Ajoute un élément (tableau ou chaîne de caractères) aux données à
   *  afficher dans le bandeau
   *
   * @param element mixed chaîne de caractères ou tableau de chaîne de caractère à ajouter
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
   * Prépare la requête pour la récupération des données
   *  Retourne l'Url préparée
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
   * Défilement de la barre de chargement
   *
   * @returns {AccueilPresentation}
   */
  defilerBarLoader: function() {
    $('bar_loader').setStyle({animationDuration: this.rafraichissementPages + 's'})
      .addClassName('animated');
    return this;
  },
  /**
   * Désactivation de la barre de chargement (utile pour synchroniser la barre avec les chargements)
   *
   * @returns {AccueilPresentation}
   */
  disableBarLoader: function() {
    $('bar_loader').removeClassName('animated');
    return this;
  },
};
