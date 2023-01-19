/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * ObjectTooltip Class
 *   Handle object tooltip creation, associated with a MbObject and a target HTML element
 */
var ObjectTooltip = Class.create({
  initialize: function(trigger, options) {
    trigger = $(trigger);

    if (!trigger) {
      return;
    }

    this.sTrigger = trigger.identify();
    this.sTooltip = null;
    this.idTimeout = null;
    this.modalScrollTop = null;

    var appearenceTimeout = {
      "short": 0.4,
      "medium": 0.8,
      "long": 1.2
    };

    this.oOptions = Object.extend( {
      mode: "objectView",
      duration: appearenceTimeout[Preferences.tooltipAppearenceTimeout] || 0.6,
      durationHide: 0.2,
      addContainer: true,
      offsetLeft: true,
      moveContainer: true,
      borderLess: false,
      newHideSystem: false,
      params: {}
    }, options);

    trigger
        .observe("mouseout", this.launchHide.bindAsEventListener(this))
        .observe("mouseover", this.cancelHide.bind(this))
        .observe("mousedown", this.cancelShow.bind(this))
        .observe("mousemove", this.resetShow.bind(this));

    // TODO: Use only one hide system. Check if these two event observer are usefull
    if (!this.oOptions.newHideSystem) {
      trigger
        .observe("mouseout", this.cancelShow.bind(this))
        .observe("mouseleave", this.cancelShow.bind(this))
    }

    if (App.touchDevice) {
      var that = this;
      document.observe(Event.pointerEvents.start, function(event){
        var element = Event.element(event);
        var eTooltip = $(that.sTooltip);

        if (!eTooltip || element == eTooltip || element.descendantOf(eTooltip)) {
          return;
        }

        that.launchHide(that);
      });
    }

    this.mode = ObjectTooltip.modes[this.oOptions.mode];
  },

  launchShow: function() {
    this.idTimeout = this.show.bind(this).delay(this.oOptions.duration);
    this.dontShow = false;
  },

  launchHide: function(event) {
    // To handle mouseout when hovering options in select elements (Firefox and IE)
    if (event && !event.relatedTarget && event.originalTarget && event.originalTarget.tagName === "SELECT") {
      return;
    }
    
    this.idTimeoutHide = this.hide.bind(this).delay(this.oOptions.durationHide);
  },

  cancelHide: function() {
    window.clearTimeout(this.idTimeoutHide);
  },

  cancelShow: function() {
    window.clearTimeout(this.idTimeout);
    this.dontShow = true;
  },

  resetShow: function(){
    window.clearTimeout(this.idTimeout);
    this.idTimeout = this.show.bind(this).delay(this.oOptions.duration);
  },

  getScrollTop: function(){
    if (!document.documentMode) return;

    var modal = $(this.sTrigger).up(".modal");
    if (!modal) return;

    this.modalScrollTop = modal.scrollTop;
  },

  setScrollTop: function(){
    if (!document.documentMode) return;

    var modal = $(this.sTrigger).up(".modal");
    if (!modal) return;

    modal.scrollTop = this.modalScrollTop;
  },

  show: function() {
    if (!this.sTooltip) {
      this.createContainer();
    }

    this.getScrollTop();

    var eTooltip = $(this.sTooltip);

    if (!eTooltip) {
      this.createContainer();
      eTooltip = $(this.sTooltip);
    }

    if (!document.documentMode) {
      $$("div.tooltip").each(function(other) {
        if (!eTooltip.descendantOf(other)) {
          other.hide();
        }
      });
    }

    if (!eTooltip) return;

    if (eTooltip.empty()) {
      this.load();
    }

    var siblings = document.getElementsByClassName("modal-wrapper modal popup");
    var max_index = 800;

    for (var i = 0; i < siblings.length; i++) {
      if (siblings[i].style["z-index"] > max_index) {
        max_index = siblings[i].style["z-index"];
      }
    }

    eTooltip.style["z-index"] = max_index;

    this.reposition();
  },

  hide: function() {
    var eTooltip = $(this.sTooltip);
    if (eTooltip) eTooltip.hide();
  },

  reposition: function() {
    var eTrigger = $(this.sTrigger),
        eTooltip = $(this.sTooltip);

    if (!eTrigger || this.dontShow) return; // necessary, unless it throws an error some times (why => ?)

    var dim = eTrigger.getDimensions();

    var offset = eTrigger.cumulativeOffset();
    var vpScroll = ViewPort.getScrollOffset();
    var delta = eTrigger.cumulativeScrollOffset();
    var parent = eTooltip.getOffsetParent();

    if (parent != document.body) {
      var parentOffset = parent.cumulativeOffset();
      delta.top += parentOffset.top;
      delta.left += parentOffset.left;
    }

    offset.top  += vpScroll.top  - delta.top  + (App.touchDevice ? -eTooltip.getHeight() : dim.height);
    offset.left += vpScroll.left - delta.left + (this.oOptions.offsetLeft ? Math.min(dim.width, 20) : 0);

    eTooltip.show()
      .setStyle({
        marginTop: 0,
        marginLeft: 0,
        top: offset.top+"px",
        left: offset.left+"px"
      })
      .unoverflow(20);

    this.setScrollTop();
  },

  load: function() {
    var eTooltip = $(this.sTooltip);

    if (this.oOptions.mode != 'dom') {
      var url = new Url;
      url.setModuleAction(this.mode.module,this.mode.action); // needed here as it makes a bug with httrack in offline mode when in the constructor (???)
      $H(this.oOptions.params).each( function(pair) { url.addParam(pair.key,pair.value); } );

      url.requestUpdate(eTooltip, {
        waitingText: $T("Loading tooltip"),
        onComplete: this.reposition.bind(this)
      });
    }
    else {
      eTooltip.update($(this.oOptions.params.element).show());
      this.reposition();
    }
  },

  createContainer: function() {
    var eTrigger = $(this.sTrigger);

    if (!eTrigger) return;

    var eTooltip = (this.oOptions.addContainer ? DOM.div({className: this.mode.sClass}) : this.oOptions.params.element).hide();

    /* Mode borderless */
    if (this.oOptions.borderless) {
      eTooltip.addClassName('borderless');
    }

    /* Ajout d'une classe custom */
    if (this.oOptions.class) {
      eTooltip.addClassName(this.oOptions.class);
    }

    // document.documentMode instead of Prototype.Browser.IE because of bad IE recognition
    if (this.oOptions.moveContainer) {
      $((document.documentMode ? document.body : eTrigger.up("div.tooltip")) || document.body).insert(eTooltip);
    }

    if (!document.documentMode) {
      eTooltip.setStyle({
        minWidth : this.mode.width+"px",
        minHeight: this.mode.height+"px"
      });
    }

    eTooltip
        .observe("mouseout", this.cancelShow.bind(this))
        .observe("mouseleave", this.cancelShow.bind(this))
        .observe("mouseout", this.launchHide.bind(this))
        .observe("mouseover", this.cancelHide.bind(this));

    this.sTooltip = eTooltip.identify();
  }
} );

/**
 * ObjectTooltip utility fonctions
 * Helpers for ObjectTooltip instanciations
 */
Object.extend(ObjectTooltip, {
  modes: {
    objectCompleteView: {
      module: "system",
      action: "httpreq_vw_complete_object",
      sClass: "tooltip",
      width: 600,
      height: 500
    },
    objectViewHistory: {
      module: "system",
      action: "httpreq_vw_object_history",
      sClass: "tooltip",
      width: 200,
      height: 0
    },
    objectView: {
      module: "system",
      action: "httpreq_vw_object",
      sClass: "tooltip",
      width: 300,
      height: 50
    },
    identifiers: {
      module: "dPsante400",
      action: "ajax_tooltip_identifiers",
      sClass: "tooltip",
      width: 150,
      height: 0
    },
    objectNotes: {
      module: "system",
      action: "httpreq_vw_object_notes",
      sClass: "tooltip postit"
    },
    oxContract: {
      module: 'oxContract',
      action: 'ajax_tooltip_catalog',
      sClass: 'tooltip',
      width: 150,
      height: 0
    },
    import_tools: {
      module: "importTools",
      action: "ajax_vw_table_data",
      sClass: "tooltip",
      width: 150,
      height: 0
    },
    allergies: {
      module: "patients",
      action: "ajax_vw_allergies",
      sClass: "tooltip"
    },
    antecedents: {
      module: "patients",
      action: "ajax_tooltip_atcd",
      sClass: "tooltip"
    },
    ox_fac_docs: {
      module: "oxFAC",
      action: "ajax_tooltip_docs",
      sClass: "tooltip",
      width: 150,
      height: 0
    },
    ox_fac_occupation: {
      module: 'oxFAC',
      action: "ajax_tooltip_occupation",
      sClass: 'tooltip',
      width: 150,
      height: 0
    },
    hyperlinks: {
      module: 'dPsante400',
      action:'ajax_tooltip_hyperlinks',
      sClass: 'tooltip',
      width: 150,
      height: 0
    },
    objectUFs: {
      module: "dPhospi",
      action: "httpreq_vw_object_ufs",
      sClass: "tooltip"
    },
    macroSejour: {
      module: "dPhospi",
      action: "vw_macrocibles",
      sClass: "tooltip"
    },
    regimeSejour: {
      module: "soins",
      action: "vw_elts_regime_sejour",
      sClass: "tooltip"
    },
    dom: {
      sClass: "tooltip"
    },
    fieldHistory: {
      module: 'dPpatients',
      action: 'ajax_tooltip_field_history',
      sClass: 'tooltip',
      width: 200,
      height: 0
    },
    ox_erp_subscriptions: {
      module: 'oxERP',
      action: "getSubscribersTooltip",
      sClass: 'tooltip',
    },
    pipeline_due_jobs: {
      module: 'oxPipeline',
      action: 'showDueJobsWidget',
      sClass: 'tooltip',
    }
  },

  init: function(){
    // Init object tooltips on elements with the "data-object_guid" attribute
    // Do not use yet because slows down IE8
    /*var selector = "span[data-object_guid],div[data-object_guid],label[data-object_guid],strong[data-object_guid]";
    document.on("mouseover", selector, function(event, element){
      ObjectTooltip.createEx(element, element.get("object_guid"));
    });*/
  },

  /**
   * Create a generic tooltip
   *
   * @param {HTMLElement} trigger
   * @param {Object=}     options
   *
   * @return {ObjectTooltip,null}
   */
  create: function(trigger, options) {
    if (!trigger) {
      return null;
    }

    if (!trigger.oTooltip) {
      trigger.oTooltip = new ObjectTooltip(trigger, options);
    }

    trigger.oTooltip.launchShow();

    return trigger.oTooltip;
  },

  /**
   * Create a tooltip based on predefined ones
   *
   * @param {HTMLElement} trigger
   * @param {String}      guid
   * @param {String=}     mode
   * @param {Object=}     params
   * @param {Object=}     options
   *
   * @return {ObjectTooltip,null}
   */
  createEx: function(trigger, guid, mode, params, options) {
    mode = mode || 'objectView';
    params = params || {};

    params.object_guid = guid;

    options = Object.extend({
      mode:   mode,
      params: params
    }, options);

    return this.create(trigger, options);
  },

  createDOM: function(trigger, tooltip, options) {
    options = Object.extend( {
      params: {}
    }, options);

    options.params.element = tooltip;
    options.mode = "dom";

    return this.create(trigger, options);
  }
});
