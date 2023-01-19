/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Composant affichant un indicateur de pourcentage
 */
InputPercentCircle = Class.create(
  {
    /** Pourcentage courant */
    percent: null,
    /** Taille du composant (ne doit pas contenir d'unité) */
    size: null,
    /** Conteneur dans lequel est stocké le composant */
    container: null,
    /**
     * Constructeur de l'indicateur
     *
     * @param {HTMLDivElement} container      Conteneur dans lequel afficher le composant
     * @param {int}            size           Taille du composant
     * @param {float}          initialPercent Pourcentage initial
     */
    initialize: function (container, size, initialPercent) {
      this.size = size;
      this.container = $(container);
      this.percent = initialPercent ? initialPercent : 0;
      this.createCircle();
    },
    /**
     * Création de la DOM du composant
     *
     * @returns {InputPercentCircle}
     */
    createCircle: function() {
      if (!this.container) {
        return this;
      }
      this.container.update(
        DOM.div(
          {
            class: 'input-percent-circle',
            style: 'width: ' + (this.size) + 'px; height: ' + (this.size) + 'px;'
          },
          '<svg>' +
          '  <circle class="input-percent-circle-background"' +
          '    stroke-dasharray="' + (Math.floor(Math.PI * 2 * this.getCircleR())) + '"></circle>' +
          '  <circle class="input-percent-circle-displayed"' +
          '    stroke-dasharray="' + (Math.floor(Math.PI * 2 * this.getCircleR())) + '"></circle>' +
          '</svg>'
        ).insert(
          DOM.div(
            {
              class: 'input-percent-circle-value',
              style: 'font-size: ' + (this.size / 4) + 'px;'
            }
          )
        )
      );
      this.setCircle.bind(this).delay();
    },
    /**
     * Initialisation des éléments de cercle
     *
     * @returns {InputPercentCircle}
     */
    setCircle: function() {
      this.circle = this.container.down('.input-percent-circle').down('.input-percent-circle-displayed');
      this.background = this.container.down('.input-percent-circle').down('.input-percent-circle-background');
      return this.setCircleSize()
        .setCircleSize(this.size, this.background)
        .setCirclePercent(1, this.background)
        .setPercent(this.percent);
    },
    /**
     * Initialisation de la taille d'un cercle du composant
     *
     * @param {int}             size   Taille du cercle
     * @param {HTMLHtmlElement} circle Cercle à initialiser
     *
     * @returns {InputPercentCircle}
     */
    setCircleSize: function(size, circle) {
      size = size ? size : this.size;
      circle = circle ? circle : this.circle;
      circle.setAttribute('cx', (this.size / 2));
      circle.setAttribute('cy', (this.size / 2));
      circle.setAttribute('r', this.getCircleR());
      return this;
    },

    /**
     * Récupération du rayon de cercle du composant
     *
     * @returns {number}
     */
    getCircleR: function() {
      return (this.size / 2) - 10;
    },

    /**
     * Fixe le pourcentage appliqué au cercle du composant
     *
     * @param {float}           percent Pourcentage à appliquer
     * @param {HTMLHTMLElement} circle  Cercle concerné
     *
     * @returns {InputPercentCircle}
     */
    setCirclePercent: function(percent, circle) {
      percent = percent ? percent : this.percent;
      percent = percent < 0.05 ? 0.05 : percent;
      circle = circle ? circle : this.circle;
      var dashoffset = Math.floor((Math.PI * 2 * this.getCircleR()) * (1 - percent));
      circle.setAttribute('stroke-dashoffset', dashoffset);
      return this;
    },

    /**
     * Fixe le pourcentage du composant
     *
     * @param {float} percent Pourcentage à appliquer
     *
     * @returns {InputPercentCircle}
     */
    setPercent: function(percent) {
      if (percent < 0 || percent > 1) {
        return this;
      }
      this.percent = percent;
      return this.setCirclePercent()
        .updateCircleLevel()
        .updateValue();
    },

    /**
     * Mise à jour de la valeur affichée dans le composant
     *
     * @returns {InputPercentCircle}
     */
    updateValue: function() {
      this.container.down('.input-percent-circle-value')
        .update(Math.floor(this.percent * 100));
      return this;
    },

    /**
     * Mise à jour de la class appliquée aux cercles (influe sur la couleur du cercle et de la valeur)
     *
     * @returns {InputPercentCircle}
     */
    updateCircleLevel: function() {
      var levelClass = Math.floor(this.percent * 4);
      if (this.percent === 1) {
        levelClass = '3';
      }
      else if (this.percent === 0) {
        levelClass = 'null';
      }
      for (var i = 0; i < 4; i++) {
        this.container.down('.input-percent-circle')
          .removeClassName('input-percent-circle-level-' + i);
      }
      this.container.down('.input-percent-circle')
        .addClassName('input-percent-circle-level-' + levelClass);
      return this;
    }
  }
);
