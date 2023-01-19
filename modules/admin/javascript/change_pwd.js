/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Gestion front du formulaire de modification de mot de passe
 * */
ChangePwd = {
  power: null,
  maxPower: null,
  circle: null,
  /**
   * Initialisation du singleton
   *
   * @param form               Formulaire contenant les champs de saisie
   * @param indicatorContainer Conteneur contenant les indicateurs de saisie
   * @param notContaining      Chaine de caractère qui ne doit pas être contenu dans les mots de passe
   * @param minLength          Longueur minimale
   */
  init: function(form, indicatorContainer, notContaining, minLength) {
    this.form = form;
    this.indicatorContainer = indicatorContainer;
    this.notContaining = notContaining;
    this.minLength = minLength;
    this.initInputCircle()
      .newPwd1OC.bind(this).delay(null, {target:{value:''}});
  },
  /**
   * Redirection vers la page principale de l'utilisateur
   */
  goHome: function() {
    location.replace("?");
  },
  /**
   * Modification du premier champ Mot de passe
   *
   * @returns self
   */
  newPwd1OC: function() {
    return this.control();
  },
  /**
   * Modification du second champ Mot de passe
   *
   * @returns self
   */
  newPwd2OC: function() {
    return this.control();
  },
  newPwdOS: function() {
    this.submitControlDifferentPwd()
      .submitControlSameNewPwd();
  },
  submitControlDifferentPwd: function() {
    if ($V(this.form.old_pwd) !== $V(this.form.new_pwd1)) {
      return true;
    }
  },
  submitControlSameNewPwd: function() {
    if ($V(this.form.new_pwd1) === $V(this.form.new_pwd2)) {
      return true;
    }
  },
  /**
   * Lancement de l'ensemble des contrôles de saisie
   *
   * @returns self
   */
  control: function() {
    this.power = 0;
    this.maxPower = 0;
    var value = $V(this.form.new_pwd1);
    return this.controlNotContaining(value)
      .controlNotNear(value)
      .controlAlphaChars(value)
      .controlCapsChars(value)
      .controlSpecChars(value)
      .controlNumChars(value)
      .controlMinLength(value)
      .controlSameNewPwd()
      .updateCircle();
  },
  /**
   * Vérification de la disponibilité d'un controle en fonction d'une classe
   *
   * @param className Classe de controle
   * @returns {boolean}
   */
  hasControl: function(className) {
    return !!this.indicatorContainer.down('.' + className);
  },
  /**
   * Contrôle de la non-présence du champ à exclure
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlNotContaining: function(value) {
    if (value === '') {
      return this.notOkLabel('not-containing');
    }
    if (value.indexOf(this.notContaining) === -1) {
      return this.okLabel('not-containing');
    }
    return this.notOkLabel('not-containing');
  },
  /**
   * Contrôle de la non-présence élargie du champ à exclure
   * Todo: Should use Levenshtein distance
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlNotNear: function(value) {
    if (value === '') {
      return this.notOkLabel('not-near');
    }
    for (var i = 1; i <= this.notContaining.length; i++) {
      var subNotContainer = this.notContaining.substr(0, (i-1)) + this.notContaining.substr(i);
      if (value.indexOf(subNotContainer) > -1) {
        return this.notOkLabel('not-near');
      }
    }

    if (value.toLowerCase().indexOf(this.notContaining.toLowerCase()) > -1) {
      return this.notOkLabel('not-near');
    }
    return this.okLabel('not-near');
  },
  /**
   * Contrôle de la présence de caractères Alphabétique
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlAlphaChars: function(value) {
    if (!this.hasControl('alpha-chars')) {
      return this;
    }
    if (value !== '' && value.match(/[A-z]/)) {
      return this.okLabel('alpha-chars');
    }
    return this.notOkLabel('alpha-chars');
  },
  /**
   * Contrôle de la présence de caractère Numérique
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlNumChars: function(value) {
    if (!this.hasControl('num-chars')) {
      return this;
    }
    if (value !== '' && value.match(/[0-9]/)) {
      return this.okLabel('num-chars');
    }
    return this.notOkLabel('num-chars');
  },
  /**
   * Contrôle de la présence de caractère majuscule
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlCapsChars: function(value) {
    if (!this.hasControl('caps-chars')) {
      return this;
    }
    if (value !== '' && value.match(/[A-Z]/)) {
      return this.okLabel('caps-chars');
    }
    return this.notOkLabel('caps-chars');
  },
  /**
   * Contrôle de la présence de caractère spéciaux
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlSpecChars: function(value) {
    if (!this.hasControl('spec-chars')) {
      return this;
    }
    if (value !== '' && value.match(/[ `!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/)) {
      return this.okLabel('spec-chars');
    }
    return this.notOkLabel('spec-chars');
  },
  /**
   * Contrôle de la longueur
   *
   * @param value Valeur à tester
   *
   * @returns self
   */
  controlMinLength: function(value) {
    if (value.length >= this.minLength) {
      return this.okLabel('min-length');
    }
    return this.notOkLabel('min-length');
  },
  /**
   * Contrôle de la correspondance des deux champs
   *
   * @returns self
   */
  controlSameNewPwd: function() {
    if ($V(this.form.new_pwd1) === '' || $V(this.form.new_pwd2) === '' || $V(this.form.new_pwd1) !== $V(this.form.new_pwd2)) {
      return this.notOkLabel('same-pwd');
    }
    return this.okLabel('same-pwd');
  },
  /**
   * Mise à jour d'un indicateur et incrémentation du score général
   *
   * @param className Classe de l'indicateur
   *
   * @returns self
   */
  okLabel: function(className) {
    this.power++;
    this.maxPower++;
    this.indicatorContainer.down('.' + className)
      .removeClassName('indicator-not-ok')
      .addClassName('indicator-ok');
    return this;
  },
  /**
   * Mise à jour d'un indicateur et décrémentation du score général
   *
   * @param className Classe de l'indicateur
   *
   * @returns self
   */
  notOkLabel: function(className) {
    this.maxPower++;
    this.indicatorContainer.down('.' + className)
      .removeClassName('indicator-ok')
      .addClassName('indicator-not-ok');
    return this;
  },

  /**
   * Initialise le cercle d'indication
   */
  initInputCircle: function() {
    this.circle = new InputPercentCircle($('change_indicator').down('.indicator-circle'), 100);

    return this;
  },

  /**
   * Mise à jour du cercle d'indication
   */
  updateCircle: function() {
    this.circle.setPercent(this.power / this.maxPower);
  }
};
