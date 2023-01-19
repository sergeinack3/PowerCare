/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Composant qui permet de saisir une heure via un slider
 */
var Reglette = Class.create({
  /** Element racine de la réglette */
  reglette_elt: null,
  /** Vignette */
  handle: null,
  /** Label de la borne minimum */
  range_min_label: null,
  /** Label de la borne maximum */
  range_max_label: null,
  /** Position X de la vignette */
  handle_posX: null,
  /** Position X du curseur */
  cursor_posX: null,
  /** Rail de déplacement de la vignette */
  rail: null,
  /** Limite gauche du rail */
  handle_limit_left: null,
  /** Limite droite du rail */
  handle_limit_right: null,
  /** Coordonnées du rail */
  rail_block: null,
  /** Largeur du rail */
  rail_width: null,
  /** Evenement appui sur la vignette */
  handler_mousedown: null,
  /** Evenement relâchement de la vignette */
  handler_mouseup: null,
  /** Evenement déplacemeent de la vignette */
  handler_mousemove: null,
  /** Evenement du clique sur la rail  */
  handler_click_rail: null,
  /** Evenement début de la saisie manuelle */
  handler_start_edit: null,
  /** Delai gestion appui long */
  timeout_touchlong: null,
  /** Evenement arrêt de la saisie manuelle (via un clique sur la page) */
  handler_end_edit_click: null,
  /** Evenement arrêt de la saisie manuelle (via la perte du focus de l'input des minutes) */
  handler_end_blur_minute_input: null,
  /** Evenement arrêt de la saisie manuelle (via appui sur "enter") */
  handler_end_edit_enter: null,
  /** Temps par défaut */
  default_time: null,
  /** Temps minimum sur la réglette */
  min_time: null,
  /** Temps maximum sur la réglette */
  max_time: null,
  /** Temps minimum sur la réglette en minutes */
  min_time_minutes: null,
  /** Temps maximum sur la réglette en minutes */
  max_time_minutes: null,
  /** Nombre total de minutes sur la réglette */
  total_minutes: null,
  /** Porté totale de la réglette (Nombre d'heures) */
  total_range: null,
  /** Input contenant la valeur */
  input: null,
  /** Valeur l'input */
  input_value: null,
  /** Reglette sur deux jours ? */
  two_day: false,
  /** Format de la date (date ou datetime) */
  format: null,
  /** Ne pas afficher la date du jour lorsqu'elle correspond à cette variable */
  hide_date_when: null,

  /**
   * Constructeur de la réglette
   *
   * @param {Element} reglette       La div racine de la reglette
   * @param {string}  format         Format "datetime" ou "time"
   * @param {string}  default_time   Temps par défaut
   * @param {int}     before_range   Portée de la réglette, nombre d'heure avant default_time
   * @param {int}     after_range    Portée de la réglette, nombre d'heure après default_time
   * @param {string}  width          Largeur du rail de la reglette en px
   * @param {string}  hide_date_when Date pour laquelle son jour n'est pas affiché dans les bornes
   */
  initialize: function (
    reglette,
    format,
    default_time,
    before_range,
    after_range,
    width,
    hide_date_when
  ) {
    // Récupération de la dom
    this.reglette_elt = reglette;
    this.handle = reglette.down('.reglette_handle');
    this.rail = reglette.down('.reglette_slider-rail');
    this.total_range = before_range + after_range;
    this.input = this.reglette_elt.down('input');
    this.range_min_label = reglette.down('.reglette_range-min');
    this.range_max_label = reglette.down('.reglette_range-max');
    this.format = format;
    this.rail_width = parseInt(width);

    // Calcul des temps
    this.default_time = this.strToDateTime(default_time);
    var begin = new Date(this.default_time.getTime());
    begin.setHours(begin.getHours() - before_range);
    begin.setSeconds(0);
    begin.setMilliseconds(0);
    this.min_time = begin;
    var end = new Date(this.default_time.getTime());
    end.setHours(end.getHours() + after_range);
    end.setSeconds(0);
    end.setMilliseconds(0);
    this.max_time = end;
    this.min_time_minutes = this.dateToMinutes(begin);
    this.max_time_minutes = this.dateToMinutes(end);
    // Vérification si les bornes de la réglette sont sur deux jours différents
    if (this.max_time_minutes < this.min_time_minutes) {
      this.two_day = true;
      this.max_time_minutes += (60*24);
    }
    this.total_minutes = 60*this.total_range;
    this.handle_posX = 0;
    this.cursor_posX = 0;
    this.handle_limit_left = 0;
    this.hide_date_when = hide_date_when === "" ? null : this.strToDateTime(hide_date_when);
    this.computeSliderSize();

    // Prise en compte de la value de l'input
    var current_time = this.input.value;
    if(current_time) {
      var parse_current_time = this.strToDateTime(current_time);
      this.updateRegletteByDatetime(parse_current_time, false)
          .updateRangLabels(parse_current_time, true)
          .displayTime(parse_current_time, false);
    } else {
      this.updateRegletteByDatetime(this.default_time, false)
          .updateRangLabels(null, true);
    }

    this.initDefaultBehaviour();

    // Détection du changement de valeur de l'input
    var default_onchange = this.input.onchange ? this.input.onchange : Prototype.emptyFunction;
    this.input.onchange = function() {
      default_onchange.bind(this.input)();
      this.updateRegletteByDatetime(this.strToDateTime(this.input.value), false);
    }.bind(this);
  },

  /**
   * Active les evenements du comportement de base de la réglette
   */
  initDefaultBehaviour: function () {
    // Détection de l'appui long sur la vignette
    if (Preferences.touchscreen === '1') {
      this.handler_mousedown = this.handle.on('touchstart', function (e) {
        this.beginDrag(e);
      }.bind(this));
    }
    else {
      // Détection du click maintenu enfoncé sur la vignette
      this.handler_mousedown = this.handle.on('mousedown', function (e) {
        this.beginDrag(e);
      }.bind(this));
    }

    // Détection du clic sur le rail
    this.handler_click_rail = this.rail.on('click', function (e) {
      this.jump(e);
    }.bind(this));
    this.handle.on('click', function(e) {
      e.stopPropagation();
    });

    if (Preferences.touchscreen === '1') {
      // Détection de l'appui long sur la vignette
      this.handler_start_edit = this.handle.on('touchstart', function () {
        this.timeout_touchlong = setTimeout(function () {
          this.enabledEdit();
        }.bind(this), 600);
      }.bind(this));
    }
    else {
      // Détection du double clic sur la vignette
      this.handler_start_edit = this.handle.on('dblclick', function () {
        this.enabledEdit();
      }.bind(this));
    }
  },

  /**
   * Désactive les evenements du comportement de base de la réglette
   */
  stopDefaultBehaviour: function () {
    this.handler_mousedown.stop();
    this.handler_click_rail.stop();
    this.handler_start_edit.stop();
    this.handler_mousemove.stop();
    this.handler_mouseup.stop();
  },

  /**
   * Initialisation des variables et fonctions pour le déplacement du handler
   *
   * @param {Event} e Evenement mousedown
   */
  beginDrag: function(e) {
    e.preventDefault();
    this.computeSliderSize();
    this.handle.addClassName('is-active');
    this.cursor_posX = this.getClientX(e);
    if (Preferences.touchscreen === '1') {
      this.handler_mousemove = document.on('touchmove', function (e) {
        this.drag(e);
      }.bind(this));
      this.handler_mouseup = document.on('touchend', function(e) {
        this.endDrag();
      }.bind(this));
    }
    else {
      this.handler_mousemove = document.on('mousemove', function (e) {
        this.drag(e);
      }.bind(this));
      this.handler_mouseup = document.on('mouseup', function(e) {
        this.endDrag();
      }.bind(this));
    }
    this.updateReglette(Math.round(this.getClientX(e) - this.rail_block.left));
    this.handle_posX = parseInt(this.handle.getStyle("left"));
  },

  /**
   * Gestion du déplacement du handle en fonction du curseur
   *
   * @param {Event} e Evenement mousemouve
   */
  drag: function(e) {
    e.preventDefault();
    if (Preferences.touchscreen === '1') {
      clearTimeout(this.timeout_touchlong);
    }
    if (this.getClientX(e) < this.rail_block.left - 18) {
      this.updateReglette(this.handle_limit_left)
          .endDrag();
      return;
    }
    if (this.getClientX(e) > this.rail_block.right + 18) {
      this.updateReglette(this.handle_limit_right)
          .endDrag();
      return;
    }

    var delta_posX = this.getClientX(e) - this.cursor_posX;
    var handle_new_posX = this.handle_posX + delta_posX;
    this.updateReglette(handle_new_posX);
  },

  /**
   * Remise à zéro de l'état de la reglette
   */
  endDrag: function () {
    if (Preferences.touchscreen === '1') {
      clearTimeout(this.timeout_touchlong);
    }
    this.handle.removeClassName('is-active');
    this.handler_mousemove.stop();
    this.handler_mouseup.stop();
  },

  /**
   * Déplace la vignette pour se retrouver à la position correspondant à datetime
   *
   * @param {Date} datetime La nouvelle datetime
   * @return Reglette
   */
  updatePosX: function(datetime) {
    this.handle.setStyle({left: this.computePosX(datetime)+"px"});

    return this;
  },

  /**
   * Affiche le nouveau temps dans l'input
   *
   * @param {Date}    date       La Date à afficher
   * @param {boolean} fire_event Déclenche l'évènement onchange de l'input
   * @return Reglette
   */
  displayTime: function(date, fire_event) {
    fire_event = (typeof fire_event === 'undefined') ? true : fire_event;
    $V(this.input, this.formatDate(date), fire_event);
    this.reglette_elt.down('.reglette_pin-value-hour').update(this.formatTimeHour(date));
    this.reglette_elt.down('.reglette_pin-value-min').update(this.formatTimeMinutes(date));
    this.reglette_elt.down('.reglette_dot_hour').update(this.formatTime(date));
    return this;
  },

  /**
   * Met à jour le label des bornes minimum et maximum de la réglette
   *
   * @param {Date}    date         Date sélectionnée
   * @param {boolean} force_update Force la mise à jour des bornes
   */
  updateRangLabels: function (date, force_update) {
    if (this.hide_date_when === null) {
      if (force_update) {
        this.range_min_label.update(this.beautifyDate(this.min_time, false));
        this.range_max_label.update(this.beautifyDate(this.max_time, false));
      }
      return this;
    }
    if (date === null) {
      return this;
    }
    var hideDate = this.hide_date_when.getDate() === date.getDate();
    this.range_min_label.update(this.beautifyDate(this.min_time, hideDate));
    this.range_max_label.update(this.beautifyDate(this.max_time, hideDate));

    return this;
  },

  /**
   * Formate une datetime au bon format
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  formatDate: function(date) {
    if (this.format === 'time') {
      return this.formatTime(date);
    }
    if (this.format === 'datetime') {
      return this.formatDatetime(date);
    }
  },

  /**
   * Formate une datetime au format hh:mm
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  formatTime: function(date) {
    var date_split = date.toLocaleTimeString().split(':');
    return date_split[0] + ':' + date_split[1];
  },

  /**
   * Formate une datetime au format datetime
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  formatDatetime: function(date) {
    return date.getFullYear()+'-'+("0" + (date.getMonth() + 1)).slice(-2)+'-'+("0" + date.getDate()).slice(-2)+' '+this.formatTime(date)+':00';
  },

  /**
   * Formate la date dans un format lisible
   *
   * @param {Date} date         la date à formater
   * @param {boolean} hide_date Cacher la date
   * @return {string} La date formatée
   */
  beautifyDate: function(date, hide_date) {
    if (this.format === 'time' || hide_date) {
      return this.formatTime(date);
    }
    if (this.format === 'datetime') {
      return this.beautifyDatetime(date);
    }
  },

  /**
   * Formate une datetime au format datetime
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  beautifyDatetime: function(date) {
    return this.formatTime(date)+'<br>'+date.toLocaleString('FR-fr', {month: "short", day: "2-digit"});
  },

  /**
   * Formate une datetime au format hh
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  formatTimeHour: function(date) {
    var date_split = date.toLocaleTimeString().split(':');
    return date_split[0];
  },

  /**
   * Formate une datetime au format mm
   *
   * @param {Date} date La date à formater
   *
   * @return {string} la date formatée
   */
  formatTimeMinutes: function(date) {
    var date_split = date.toLocaleTimeString().split(':');
    return date_split[1];
  },

  /**
   * Met à jour l'état de la réglette à partir d'une position sur la réglette
   *
   * @param {int}     posX       Position de la vignette
   * @param {boolean} fire_event Déclenche l'évènement onchange de l'input
   * @return Reglette
   */
  updateReglette: function (posX,  fire_event) {
    var ratio = Math.ceil((posX / this.rail_width) * 10000) / 10000;
    var minutes_select = Math.round(this.total_minutes * ratio);
    var new_time = new Date(this.min_time.getTime());
    new_time.setMinutes(this.min_time.getMinutes() + minutes_select);

    if (this.dateToMinutes(new_time) < this.min_time_minutes) {
      return this;
    }
    if (this.dateToMinutes(new_time) > this.max_time_minutes) {
      return this;
    }

    this.displayTime(new_time, fire_event)
        .updateRangLabels(new_time, false)
        .updatePosX(new_time);
    this.handle.removeClassName('is-invalid');
    this.handle.removeAttribute('title');

    return this;
  },

  /**
   * Met à jour l'état de la réglette à partir d'une date
   *
   * @param {Date}    date       la nouvelle datetime
   * @param {boolean} fire_event Déclenche l'évènement onchange de l'input
   * @return Reglette
   */
  updateRegletteByDatetime: function(date, fire_event) {
    if (date < this.min_time) {
      this.displayTime(date)
          .updateRangLabels(date, false)
          .updatePosX(this.min_time);
      this.handle.addClassName('is-invalid');
      this.handle.setAttribute('title', date.toLocaleString());
      return this;
    }
    if (date > this.max_time) {
      this.displayTime(date, fire_event)
          .updateRangLabels(date, false)
          .updatePosX(this.max_time);
      this.handle.addClassName('is-invalid');
      this.handle.setAttribute('title', date.toLocaleString());
      return this;
    }

    this.updateReglette(this.computePosX(date), fire_event);
    return this;
  },

  /**
   * Transforme une datetime en minutes
   *
   * @param  {Date} date La datetime à transformer
   * @return {number} Le nombre de minutes
   */
  dateToMinutes: function(date) {
    if (this.two_day && (date.getDay() === this.max_time.getDay())) {
      return (date.getHours() * 60) + date.getMinutes() + (60*24);
    }
    return (date.getHours() * 60) + date.getMinutes();
  },

  /**
   * Transforme une chaîne de caractère en datetime
   *
   * @param {string} str     La chaîne de caractère à transformer
   * @return {Date} datetime La datetime
   */
  strToDateTime: function(str) {
    if (this.format === 'time') {
      return this.strTimeToDateTime(str);
    }
    if (this.format === 'datetime') {
      return this.strDatetimeToDateTime(str);
    }
  },

  /**
   * Transfome une chaîne de caractère au format hh:mm[:ss] en datetime
   *
   * @param {string} str     La chaîne de caractère à transformer
   * @return {Date} datetime La datetime
   */
  strTimeToDateTime: function (str) {
    var hour_split = str.split(":");
    var datetime = new Date();
    if (this.two_day && (this.parseIntIE(hour_split[0]) > this.min_time.getHours()-12)) {
      datetime.setDate(this.min_time.getDate());
    }
    else if(this.two_day && (this.parseIntIE(hour_split[0]) < this.max_time.getHours()+12)) {
      datetime.setDate(this.max_time.getDate());
    }
    datetime.setHours(this.parseIntIE(hour_split[0]));
    datetime.setMinutes(this.parseIntIE(hour_split[1]));
    datetime.setSeconds(0);
    datetime.setMilliseconds(0);

    return datetime;
  },

  /**
   * Transforme une chaîne de caractère au format datetime en datetime
   *
   * @param {string} str     La chaîne de caractère à transformer
   * @return {Date} datetime La datetime
   */
  strDatetimeToDateTime: function (str) {
    var hour_split = str.split(/-|:|\s/);
    var datetime = new Date();
    datetime.setFullYear(this.parseIntIE(hour_split[0]));
    datetime.setMonth(this.parseIntIE(hour_split[1]) - 1);
    datetime.setDate(this.parseIntIE(hour_split[2]));
    datetime.setHours(this.parseIntIE(hour_split[3]));
    datetime.setMinutes(this.parseIntIE(hour_split[4]));
    datetime.setSeconds(0);
    datetime.setMilliseconds(0);
    return datetime;
  },


  /**
   * Fonction qui aide IE à faire un parseInt (le pauvre)
   *
   * @param {string} str La chaine à convertir
   */
  parseIntIE: function (str) {
    if (str == null) {
      return ""
    }
    return parseInt(str.match(/[0-9]+/));
  },

  /**
   * Calcul la position sur la réglette correspondant à la datetime
   *
   * @param {Date} datetime
   * @return {int} posX     Position en px correspondant à la datetime
   */
  computePosX: function(datetime) {
    var ratio = (this.dateToMinutes(datetime) - this.min_time_minutes) / this.total_minutes;
    return this.rail_width * ratio;
  },

  /**
   * Déplacement de la vignette lors du clique directement sur le rail
   *
   * @param {Event} e Evenement clique sur le rail
   */
  jump: function (e) {
    this.computeSliderSize();
    var posX = Math.round(this.getClientX(e) - this.rail_block.left);
    this.updateReglette(posX);
  },

  /**
   * Calcul la taille du rail de la reglette
   */
  computeSliderSize: function() {
    this.rail_block = this.rail.getBoundingClientRect();
    this.handle_limit_right = this.rail_width;
  },

  /**
   * Retourne la position X du curseur d'un event
   *
   * @param {Event} event l'évènement dont on veut récupérer le clientX
   *
   * @return {int} La position X du curseur
   */
  getClientX: function(event) {
    if (['touchmove', 'touchstart'].include(event.type)) {
      return event.touches[0].clientX;
    }
    else if (['mousemove', 'mousedown', 'click'].include(event.type)) {
      return event.pointerX();
    }
  },

  /**
   * Active la saisie au clavier de la date
   */
  enabledEdit: function() {
    this.handle.addClassName('is-editable');
    this.handle.removeClassName('is-active');
    this.createInput();
    this.stopDefaultBehaviour();
    this.input_value = this.input.value;

    if (Preferences.touchscreen === '1') {
      this.handler_end_edit_click = document.on('touchstart', function() {
        this.disabledEdit();
      }.bind(this));
    }
    else {
      this.handler_end_edit_click = document.on('click', function() {
        this.disabledEdit();
      }.bind(this));
    }

    var minute_input = this.reglette_elt.down('input.reglette_input_minute');

    this.handler_end_blur_minute_input = minute_input.on('blur', function() {
      this.disabledEdit();
    }.bind(this));

    this.handler_end_edit_enter = document.on('keypress', function (e) {
      if (e.key === "Enter") {
        this.disabledEdit();
      }
    }.bind(this));
  },

  /**
   * Désactive la saisie au clavier de la date
   */
  disabledEdit: function () {
    this.handle.removeClassName('is-editable');
    this.fillInput();

    this.handler_end_edit_click.stop();
    this.handler_end_blur_minute_input.stop();
    this.handler_end_edit_enter.stop();
    this.createHandler(true);

    this.initDefaultBehaviour();
  },

  /**
   * Remplace les valeurs vides de l'input par les anciennes
   */
  fillInput: function() {
    var hour_input = this.reglette_elt.down('input.reglette_input_hour');
    var minute_input = this.reglette_elt.down('input.reglette_input_minute');

    if (!hour_input.value) {
      $V(hour_input, this.strToDateTime(this.input_value).getHours().toString());
    }
    if (!minute_input.value) {
      $V(minute_input, this.strToDateTime(this.input_value).getMinutes().toString());
    }
  },

  /**
   * Génère l'input pour la saisie manuelle
   */
  createInput: function () {
    var display_value = this.reglette_elt.down('.reglette_dot_hour');
    var hour = display_value.innerHTML.split(':')[0];
    var minute = display_value.innerHTML.split(':')[1];
    var input_hour = DOM.input(
      {
        type: 'tel',
        class: 'reglette_input_hour',
        name: 'reglette_input_hour',
        placeholder: hour
      }
    );
    var input_minute = DOM.input(
      {
        type: 'tel',
        class: 'reglette_input_minute',
        name: 'reglette_input_minute',
        placeholder: minute
      }
    );
    var input_wrapper = DOM.div(
      {class: 'reglette_input_wrapper'},
      input_hour,
      ' : ',
      input_minute
    );
    display_value.up().insert(input_wrapper);
    this.prepareInput(input_hour, input_minute);
    display_value.hide();
  },

  /**
   * Ajoute différents comportements pour une saisie au clavier plus simple
   *
   * @param {HTMLInputElement} input_hour   l'input pour la saisie de l'heure
   * @param {HTMLInputElement} input_minute l'input pour la saisie des minutes
   *
   */
  prepareInput: function(input_hour, input_minute) {
    // Focus automatique sur le premier input
    if (Preferences.touchscreen === '1') {
      this.handle.on('touchend', function () {
        input_hour.focus();
      });
      input_hour.on('touchstart', function (e) {
        e.stopPropagation();
      });
      input_minute.on('touchstart', function (e) {
        e.stopPropagation();
      });
    }
    else {
      input_hour.focus();
    }

    // Obligation de saisir que des chiffres
    [input_hour, input_minute].each(function (input) {
      input.on('keypress', function (e) {
        var regex = /[0-9]/;
        if (!regex.test(e.key) || this.value.length >= 2) {
          e.preventDefault();
        }
      });
    });

    // Changement de focus automatique lors que les heures sont renseignées
    input_hour.on('keyup', function (e) {
      var regex = /[0-9]/;
      if (regex.test(e.key) && input_hour.value.length >= 2) {
        input_minute.focus();
      }
    });
  },

  /**
   * Génère le handler en fonction de la valeur de l'input
   *
   * @param {boolean} need_compute Nécessité de mettre à jour la réglette avec la valeur de l'input
   */
  createHandler: function (need_compute) {
    if (need_compute) {
      var hour_value = this.reglette_elt.down('input.reglette_input_hour').value;
      var minute_value = this.reglette_elt.down('input.reglette_input_minute').value;

      if (this.format === 'time') {
        this.updateRegletteByDatetime(this.strTimeToDateTime(hour_value + ':' + minute_value));
      }
      else if(this.format === 'datetime') {
        var datetime = this.strDatetimeToDateTime(this.input.value);
        if (this.two_day && (parseInt(hour_value) > this.min_time.getHours()-12)) {
          datetime.setDate(this.min_time.getDate());
        }
        else if(this.two_day && (parseInt(hour_value) < this.max_time.getHours()+12)) {
          datetime.setDate(this.max_time.getDate());
        }
        datetime.setHours(parseInt(hour_value));
        datetime.setMinutes(parseInt(minute_value));
        this.updateRegletteByDatetime(datetime);
      }
    }
    this.reglette_elt.down('.reglette_input_wrapper').remove();
    this.reglette_elt.down('.reglette_dot_hour').show();
  }
});
