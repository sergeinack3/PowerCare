/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Class utility object
 */
Class.extend = function (oClass, oExtension) {
  Object.extend(oClass.prototype, oExtension);
};

/**
 * Function class
 */
Class.extend(Function, {
  getSignature: function() {
    var re = /function ([^\{]*)/;
    return this.toString().match(re)[1];
  }
});

/**
 * Recursively merges two objects.
 * @param {Object} src - source object (likely the object with the least properties)
 * @param {Object} dest - destination object (optional, object with the most properties)
 * @return {Object} recursively merged Object
 */
Object.merge = function(src, dest){
  var v, result = dest || {};
  for(var i in src){
    v = src[i];
    result[i] = (v && typeof(v) === 'object' && !(v.constructor === Array || v.constructor === RegExp) && !Object.isElement(v)) ? Object.merge(v, dest[i]) : result[i] = v;
  }
  return result;
};

// In order IE8 to work just a little bit ...
if (!window.CanvasRenderingContext2D) {
  window.CanvasRenderingContext2D = function(){};
}

/** TODO: Remove theses fixes */
//fixes getDimensions bug which does not work with Android
Object.extend(document.viewport,{
  getDimensions: function() {
    var dimensions = { }, B = Prototype.Browser;
    $w('width height').each(function(d) {
      var D = d.capitalize();
      if (B.WebKit && !document.evaluate) {
        // Safari <3.0 needs self.innerWidth/Height
        dimensions[d] = self['inner' + D];
      } else if (B.Opera && parseFloat(window.opera.version()) < 9.5) {
        // Opera <9.5 needs document.body.clientWidth/Height
        dimensions[d] = document.body['client' + D]
      } else {
        dimensions[d] = document.documentElement['client' + D];
      }
    });
    return dimensions;
  }
});
// Fixes a bug that scrolls the page when in an autocomplete
Class.extend(Autocompleter.Base, {
  markPrevious: function() {
   if(this.index > 0) {this.index--;}
   else {
    this.index = this.entryCount-1;
    this.update.scrollTop = this.update.scrollHeight;
   }
   var selection = this.getEntry(this.index);
   if(selection.offsetTop < this.update.scrollTop){
    this.update.scrollTop = this.update.scrollTop-selection.offsetHeight;
   }
  },
  markNext: function() {
   if(this.index < this.entryCount-1) {this.index++;}
   else {
    this.index = 0;
    this.update.scrollTop = 0;
   }
   var selection = this.getEntry(this.index);
   if((selection.offsetTop+selection.offsetHeight) > this.update.scrollTop+this.update.offsetHeight){
    this.update.scrollTop = this.update.scrollTop+selection.offsetHeight;
   }
  },
  updateChoices: function(choices) {
    if(!this.changed && this.hasFocus) {
      this.update.innerHTML = choices;
      Element.cleanWhitespace(this.update);
      Element.cleanWhitespace(this.update.down());

      if(this.update.firstChild && this.update.down().childNodes) {
        this.entryCount =
          this.update.down().childNodes.length;
        for (var i = 0; i < this.entryCount; i++) {
          var entry = this.getEntry(i);
          entry.autocompleteIndex = i;
          this.addObservers(entry);
        }
      } else {
        this.entryCount = 0;
      }

      this.stopIndicator();
      this.update.scrollTop = 0;

      // was "this.index = 0;"
      this.index = this.options.dontSelectFirst ? -1 : 0;

      if(this.entryCount==1 && this.options.autoSelect) {
        this.selectEntry();
        this.hide();
      } else {
        this.render();
      }
    }
  },
  onKeyPress: function(event) {
    if(this.active)
      switch(event.keyCode) {
       // Hide list while typing letters
        default:
         if (!this.options.localStorage) {
           this.update.update();
           this.options.onHide(this.element, this.update);
         }
         this.index = -1;
         break;

       case Event.KEY_TAB:
         // Tab key should not select an element if this is a STR autocomplete
         if (this.element.hasClassName("str")) {
           this.hide();
           this.active = false;
           this.changed = false;
           this.hasFocus = false;
           return;
         }
       case Event.KEY_RETURN:
         if (this.index < 0) return;
         this.selectEntry();
         Event.stop(event);
       case Event.KEY_ESC:
         this.hide();
         this.active = false;
         Event.stop(event);
         return;
       case Event.KEY_LEFT:
       case Event.KEY_RIGHT:
         return;
       case Event.KEY_UP:
         this.markPrevious();
         this.render();
         Event.stop(event);
         return;
       case Event.KEY_DOWN:
         this.markNext();
         this.render();
         Event.stop(event);
         return;
      }
     else
       if(event.keyCode==Event.KEY_TAB || event.keyCode==Event.KEY_RETURN ||
         (Prototype.Browser.WebKit > 0 && event.keyCode == 0)) return;

    this.changed = true;
    this.hasFocus = true;

    if(this.observer) clearTimeout(this.observer);
    this.observer =
      setTimeout(this.onObserverEvent.bind(this), this.options.frequency*1000);
  },
  onBlur: function(event) {
    if (this.updateHasFocus) return;

    if (Prototype.Browser.IE && this.update.visible()) {
      // fix for IE: don't blur when clicking the vertical scrollbar (if there is one)
      var verticalScrollbarWidth = this.update.offsetWidth - this.update.clientWidth -
        this.update.clientLeft - (parseInt(this.update.currentStyle['borderRightWidth']) || 0);

      if (verticalScrollbarWidth) {
        var x = event.clientX,
            y = event.clientY,
            parent = this.update.offsetParent,
            sbLeft = this.update.offsetLeft + this.update.clientLeft + this.update.clientWidth,
            sbTop = this.update.offsetTop + this.update.clientTop,
            sbRight = sbLeft + verticalScrollbarWidth,
            sbBottom = sbTop + this.update.clientHeight;

        while (parent) {
          var offs = parent.offsetLeft + parent.clientLeft, scrollOffs = offs - parent.scrollLeft;
          sbLeft = (sbLeft += scrollOffs) < offs ? offs : sbLeft;
          sbRight = (sbRight += scrollOffs) < offs ? offs : sbRight;
          offs = parent.offsetTop + parent.clientTop; scrollOffs = offs - parent.scrollTop;
          sbTop = (sbTop += scrollOffs) < offs ? offs : sbTop;
          sbBottom = (sbBottom += scrollOffs) < offs ? offs : sbBottom;
          parent = parent.offsetParent;
        }

        if (x >= sbLeft && x < sbRight && y >= sbTop && y < sbBottom) {
          this.element.setActive();
          return;
        }
      }
    }

    setTimeout(this.hide.bind(this), 250);
    this.hasFocus = false;
    this.active = false;
  },

  getTokenBounds: function() {
    if (!this.options.caretBounds && (null != this.tokenBounds)) return this.tokenBounds;
    var value = this.element.value;
    if (value.strip().empty()) return [-1, 0];

    // This has been added so that the token bounds are relative to the current cert position
    if (this.options.caretBounds) {
      var caret = this.element.getInputSelection(true).start;
      var start = value.substr(0, caret).lastIndexOf("\n")+1;
      var end = value.substr(caret).indexOf("\n")+caret+1;
      return (this.tokenBounds = [start, end]);
    }

    // This needs to be declared here as the arguments.callee is not the same
    var firstDiff = function(newS, oldS) {
      var boundary = Math.min(newS.length, oldS.length);
      for (var index = 0; index < boundary; ++index)
        if (newS[index] != oldS[index])
          return index;
      return boundary;
    };
    /////////////

    var diff = firstDiff(value, this.oldElementValue);
    var offset = (diff == this.oldElementValue.length ? 1 : 0);
    var prevTokenPos = -1, nextTokenPos = value.length;
    var tp;
    for (var index = 0, l = this.options.tokens.length; index < l; ++index) {
      tp = value.lastIndexOf(this.options.tokens[index], diff + offset - 1);
      if (tp > prevTokenPos) prevTokenPos = tp;
      tp = value.indexOf(this.options.tokens[index], diff + offset);
      if (-1 != tp && tp < nextTokenPos) nextTokenPos = tp;
    }
    return (this.tokenBounds = [prevTokenPos + 1, nextTokenPos]);
  },
  selectEntry: function() {
    this.active = false;
    if(this.index > -1){
      this.updateElement(this.getCurrentEntry());
    }
  },

  // Reimplemented for IE10
  show: function() {
    if(Element.getStyle(this.update, 'display')=='none') this.options.onShow(this.element, this.update);
    if(!this.iefix &&
      (Prototype.Browser.IE && document.documentMode <= 9) && // added " && document.documentMode <= 9"
      (Element.getStyle(this.update, 'position')=='absolute')) {
      new Insertion.After(this.update,
        '<iframe id="' + this.update.id + '_iefix" '+
          'style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" ' +
          'src="javascript:false;" frameborder="0" scrolling="no"></iframe>');
      this.iefix = $(this.update.id+'_iefix');
    }
    if(this.iefix) setTimeout(this.fixIEOverlapping.bind(this), 50);
  }
});

// Fix a bug in IE9 where whitespace between cells adds empty cells *sometimes*
if (Prototype.Browser.IE && document.documentMode && document.documentMode == 9) {
  Object.toHTML = function(object) {
    return (object && object.toHTML ? object.toHTML() : String.interpret(object)).replace(/>\s+<(t[dh])/gi, "><$1").replace(/\s+<\/tr>/gi, "</tr>");
  }
}

// FIX in Scriptaculous
Droppables.isAffected = function(point, element, drop) {
  Position.prepare();
  return (
    (drop.element!=element) &&
    ((!drop._containers) ||
      this.isContained(element, drop)) &&
    ((!drop.accept) ||
      (Element.classNames(element).detect(
        function(v) { return drop.accept.include(v) } ) )) &&
    Position.withinIncludingScrolloffsets(drop.element, point[0], point[1]) ); // differs from original code
};

Class.extend(Ajax.Request, {
  abort: function() {
    if (this._complete) {
      return;
    }

    this._complete = true;

    var transport = this.transport;

    // prevent and state change callbacks from being issued
    transport.onreadystatechange = Prototype.emptyFunction;

    // abort the XHR
    transport.abort();

    var response = new Ajax.Response(this);

    ['Abort', 'Complete'].each(function(state) {
      try {
        (this.options['on' + state] || Prototype.emptyFunction)(response, response.headerJSON);
        Ajax.Responders.dispatch('on' + state, this, response, response.headerJSON);
      } catch (e) {
        this.dispatchException(e);
      }
    }, this);
  }
});

Class.extend(Array, {
  notMatch: function(css) {
    return this.reject(function(e){
      return e.match(css);
    });
  },
  average: function () {
    var sum = 0, j = 0, l = this.length;
    for (var i = 0; i < l, isFinite(this[i]); i++) {
      sum += parseFloat(this[i]); ++j;
    }
    return j ? sum / j : 0;
  }
});

// Fix to get better window size ( document.documentElement instead of document.body )
// Needs to be done after everything
(function(){
  try {
  Object.extend(Control.Overlay, {
    positionOverlay: function(){
      Control.Overlay.container.setStyle({
        width: document.documentElement.clientWidth + 'px',
        height: document.documentElement.clientHeight + 'px'
      });
    }
  });
  } catch (e) {}
}).defer();

Element.addMethods({
  absolutize: function (element) {
    element = $(element);

    if (Element.getStyle(element, 'position') === 'absolute') {
      return element;
    }

    var offsetParent = element.getOffsetParent();
    var eOffset = element.viewportOffset(),
        pOffset = offsetParent.viewportOffset();

    var offset = eOffset.relativeTo(pOffset);
    var layout = element.getLayout();

    element.store('prototype_absolutize_original_styles', {
      left:   element.getStyle('left'),
      top:    element.getStyle('top'),
      width:  element.getStyle('width'),
      height: element.getStyle('height'),
      position: element.getStyle('position')
    });

    element.setStyle({
      position: 'absolute',
      top:    offset.top + 'px',
      left:   offset.left + 'px',
      width:  layout.get('width') + 'px',
      height: layout.get('height') + 'px'
    });

    return element;
  }
});

/** END HACKS */

/**
 * Element.ClassNames class
 */
Class.extend(Element.ClassNames, {
  load: function (sCookieName, nDuration) {
    var oCookie = new CookieJar({expires: nDuration});
    var sValue = oCookie.getValue(sCookieName, this.element.id);
    if (sValue) {
      this.set(sValue);
    }
  },

  save: function (sCookieName, nDuration) {
    new CookieJar({expires: nDuration}).setValue(sCookieName, this.element.id, this.toString());
  },

  toggle: function(sClassName) {
    this[this.include(sClassName) ? 'remove' : 'add'](sClassName);
  },

  flip: function(sClassName1, sClassName2) {
    if (this.include(sClassName1)) {
      this.remove(sClassName1);
      this.add(sClassName2);
      return;
    }

    if (this.include(sClassName2)) {
      this.remove(sClassName2);
      this.add(sClassName1);
      return;
    }
  }
});

function NoClickDelay(el) {
  this.element = typeof el == 'object' ? el : document.getElementById(el);
  if( window.Touch ) this.element.addEventListener('touchstart', this, false);
}

NoClickDelay.prototype = {
  handleEvent: function(e) {
    var callback = {
      touchstart: this.onTouchStart,
      touchmove:  this.onTouchMove,
      touchend:   this.onTouchEnd
    }[e.type];

    if (callback) {
      callback(e);
    }
  },

  onTouchStart: function(e) {
    e.preventDefault();
    this.moved = false;

    this.theTarget = document.elementFromPoint(e.targetTouches[0].clientX, e.targetTouches[0].clientY);
    if(this.theTarget.nodeType == 3) this.theTarget = theTarget.parentNode;
    this.theTarget.className+= ' pressed';

    this.element.addEventListener('touchmove', this, false);
    this.element.addEventListener('touchend', this, false);
  },

  onTouchMove: function(e) {
    this.moved = true;
    this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
  },

  onTouchEnd: function(e) {
    this.element.removeEventListener('touchmove', this, false);
    this.element.removeEventListener('touchend', this, false);

    if( !this.moved && this.theTarget ) {
      this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
      var theEvent = document.createEvent('MouseEvents');
      theEvent.initEvent('click', true, true);
      this.theTarget.dispatchEvent(theEvent);
    }

    this.theTarget = undefined;
  }
};

// Makes an element to be in the viewport instead of overflow
Element.addMethods({
  unoverflow: function(element, offset) {
    var dim = element.getDimensions(); // Element dimensions
    var pos = element.cumulativeOffset(); // Element position
    var scroll = document.viewport.getScrollOffsets(); // Viewport offset
    var viewport = document.viewport.getDimensions(); // Viewport size
    offset = offset || 0;

    pos.left -= scroll.left;
    pos.top -= scroll.top;

    pos.right  = pos[2] = pos.left + dim.width; // Element right position
    pos.bottom = pos[3] = pos.top + dim.height; // Element bottom position

    // If the element exceeds the viewport on the right
    if (pos.right > (viewport.width - offset)) {
      element.style.left = parseInt(element.style.left) - (pos.right - viewport.width) - offset + 'px';
    }

    // If the element exceeds the viewport on the top
    if (pos.top < 0) {
      element.style.top = '0px';
    }
    // If the element exceeds the viewport on the bottom
    else if (pos.bottom > (viewport.height - offset)) {
      element.style.top = Math.max(0, parseInt(element.style.top) - (pos.bottom - viewport.height) - offset) + 'px';
    }

    return element;
  },

  centerHV : function(element, pos) {
    element.setStyle({
      left: 0
    });

    var viewport = document.viewport.getDimensions(); // Viewport size
    var dim = element.getDimensions(); // Element dimensions

    pos = parseInt(pos || 0)-(dim.height/2);

    element.setStyle({
      top: Math.max(pos, 100) + "px",
      left: (viewport.width - dim.width) / 2 + "px",
      width: dim.width - 10 + "px"
    });
    return element;
  },

  isVisible: function(element, parent) {
    var element = $(element);
    var parent = parent ? $(parent) : element.getOffsetParent();

    var offset_element = element.cumulativeOffset();
    var offset_parent = parent.cumulativeOffset();
    var scroll = element.cumulativeScrollOffset();

    var top_top = offset_parent.top;
    var top_bottom = top_top + parent.getHeight();
    var left_left = offset_parent.left;
    var left_right = left_left + parent.getWidth();

    var scroll_top_a = offset_element.top - scroll.top;
    var scroll_top_b = scroll_top_a + element.getHeight();
    var scroll_left_a = offset_element.left - scroll.left;
    var scroll_left_b = scroll_left_a + element.getWidth();

    return ((scroll_top_a >= top_top && scroll_top_a <= top_bottom) ||
            (scroll_top_b >= top_top && scroll_top_b <= top_bottom))
        && ((scroll_left_a >= left_left && scroll_left_a <= left_right) ||
            (scroll_left_b >= left_left && scroll_left_b <= left_right));
  },

  /**
   * Switch the display status of the element with a condition
   *
   * @param {HTMLElement} element   The element
   * @param {Boolean}     condition The condition
   *
   * @return {Boolean}
   */
  setVisible: function(element, condition) {
    return element[condition ? "show" : "hide"]();
  },

  /**
   * Switch the visibility of the element with a condition
   *
   * @param {HTMLElement} element   The element
   * @param {Boolean}     condition The condition
   *
   * @return {Boolean}
   */
  setVisibility: function(element, condition) {
    return element.setStyle( {
      visibility: condition ? "visible" : "hidden"
    } );
  },

  setClassName: function(element, className, condition) {
    if (condition ) element.addClassName(className);
    if (!condition) element.removeClassName(className);
    return element;
  },

  /** Gets the elements properties (specs) thanks to its className */
  getProperties: function (element) {
    var props = {};

    $w(element.className).each(function (value) {
      var params = value.split("|");
      props[params.shift()] = (params.length == 0) ? true : (params.length > 1 ? params : params[0]);
    });

    if (props.pattern) {
      props.pattern = props.pattern.replace(/\\x7C/g, "|").replace(/\\x20/g, " ");
    }

    return props;
  },

  /** Add a class name to an element, and removing this class name to all of it's siblings */
  addUniqueClassName: function(element, className, parentSelector) {
    element = $(element);

    if (parentSelector) {
      element.up(parentSelector).select("."+className).invoke('removeClassName', className);
    }
    else {
      $(element).siblings().invoke('removeClassName', className);
    }

    return element.addClassName(className);
  },

  clone: function(element, deep) {
    return $($(element).cloneNode(deep)).writeAttribute("id", "");
  },

  /** Get the surrounding form of the element  */
  getSurroundingForm: function(element) {
    if (element.form) return $(element.form);
    return $(element).up('form');
  },

  enableInputs: function(element) {
    var inputs = element.select("input,select,textarea");
    inputs.invoke("enable");
    return element.show();
  },
  disableInputs: function(element, reset) {
    var inputs = element.select("input,select,textarea");
    inputs.invoke("disable");
    if (reset) {
      inputs.each(function(i) { $V(i, ""); });
    }
    return element.hide();
  },
  getText: function(element) {
    // using || may not work
    return ("textContent" in element ? element.textContent : element.innerText)+"";
  },

  /**
   * @param {HTMLElement=} root The root element to init the touch events on
   */
  prepareTouchEvents: function(root){
    if (!App.touchDevice) return;

    /*root.select("label").each(function(label){
      label.observe("touchstart", Event.stop);
    });*/

    /*
    root.select("*[onclick], .control_tabs a, .control_tabs_vertical a").each(function(element) {
      new NoClickDelay(element);
    });
    */

    root.select("label").each(function(label){
      if (label.hasAttribute("onclick")) {
        return;
      }

      label.setAttribute("onclick", "");
    });

    if (App.mouseEventsPrepared) {
      return;
    }

    var eventsHandled = $H({
      onmouseover: 300,
      ondblclick:  500
    });

    document.observe(Event.pointerEvents.start, function(event){
      var element = Event.element(event);

      eventsHandled.each(function(pair){
        var eventName = pair.key;

        if (element[eventName]) {
          var timeout = pair.value;
          Event.stop(event);
          element["triggered"+eventName] = false;

          element["timer"+eventName] = setTimeout(function(){
            element[eventName](event);
            element["triggered"+eventName] = true;
          }, timeout);
        }
      });
    });

    document.observe(Event.pointerEvents.move, function(event){
      var element = Event.element(event);

      eventsHandled.each(function(pair){
        var eventName = pair.key;

        if (element["timer"+eventName]) {
          clearTimeout(element["timer"+eventName]);
        }
      });
    });

    document.observe(Event.pointerEvents.end, function(event){
      var element = Event.element(event);

      eventsHandled.each(function(pair){
        var eventName = pair.key;

        if (element[eventName]) {
          Event.stop(event);
          clearTimeout(element["timer"+eventName]);

          if (!element["triggered"+eventName]) {
            // event bubbling
            var bubble = (element.onclick || element.href) ? element : element.up("[onclick], a[href]");

            // simulate event firing
            if (bubble.href && (!bubble.onclick || bubble.onclick() !== false)) {
              location.href = bubble.href;
              return;
            }
          }
        }
      });
    });

    App.mouseEventsPrepared = true;
  },

  checkAllCheckboxes: function(input, parent, check_class) {
    parent.select('.' + check_class).each(function(e) {
      if (!e.disabled) {
        e.checked = input.checked;
      }
    });
  },

  makeIntuitiveCheck: function(parent, line_class, check_class) {
    var line_selector = '.' + line_class;
    var check_selector = '.' + check_class;

    parent.on('click', check_selector, function(e) {



      var element = e.element();

      if (!e.shiftKey || (e.shiftKey && Object.isUndefined(parent.last_checked_line))) {
        var previous_siblings = element.up(line_selector).previousSiblings();

        // In order to remove additional lines
        previous_siblings.each(function(elt, key) {
          if (!elt.hasClassName(line_class) && !elt.disabled) {
            previous_siblings.splice(key, 1);
          }
        });

        parent.last_checked_line = previous_siblings.length;
      }
      else {
        if (e.shiftKey && !Object.isUndefined(parent.last_checked_line)) {
          var previous_siblings = element.up(line_selector).previousSiblings();

          // In order to remove additional lines
          previous_siblings.each(function(elt, key) {
            if (!elt.hasClassName(line_class) && !elt.disabled) {
              previous_siblings.splice(key, 1);
            }
          });

          var next_checked_line = previous_siblings.length;
          var range = [parent.last_checked_line, next_checked_line].sort(function(a, b) {
            return parseInt(a) - parseInt(b);
          });

          parent.select(line_selector).slice(range[0], range[1]).each(function(line) {
            var checkbox = line.down(check_selector);
            if (!checkbox.disabled) {
              checkbox.checked = true;
            }
          });
        }
      }
    });
  }
});

/** Get the element's "data-" attributes value */
Element.addMethods({
  "get": function(element, data) {
    return element.getAttribute("data-"+data);
  },
  "set": function(element, key, data) {
    return element.writeAttribute("data-"+key, data);
  }
});

Element.addMethods(['input', 'textarea'], {
  emptyValue: function (element) {
    var notWhiteSpace = /\S/;
    return Object.isUndefined(element.value) ?
      element.empty() :
      !notWhiteSpace.test(element.value);
  },
  switchMultiline: function (element, button) {
    var newElement;

    if (/^textarea$/i.test(element.tagName)) {
      newElement = new Element("input", {type: "text", value: $V(element)});
      if (button) $(button).removeClassName("singleline").addClassName("multiline");
    }
    else {
      newElement = new Element("textarea", {style: "width: auto;"}).update($V(element));

      if (element.maxLength) {
        newElement.observe("keypress", function(e){
          var txtarea = Event.element(e),
              value = $V(txtarea);
          if (value.length >= element.maxLength) {
            $V(txtarea, value.substr(0, element.maxLength-1));
          }
        });
      }

      if (button) $(button).removeClassName("multiline").addClassName("singleline");
    }

    var exclude = ["type", "value"];
    var map = {
      readonly: "readOnly",
      maxlength: "maxLength",
      size: "cols",
      cols: "size"
    };

    $A(element.attributes).each(function(a){
      if (exclude.indexOf(a.name) != -1) return;
      newElement.setAttribute(map[a.name] || a.name, a.value);
    });

    element.insert({after: newElement});
    element.remove();
    return newElement;
  }
});

Element.addMethods(['input', 'textarea', 'select', 'button'], {
  tryFocus: function (element) {
    try {
      element.focus();
    } catch (e) {}
    return element;
  }
});

Element.addMethods('select', {
  sortByLabel: function(element){
    var selected = $V(element),
        sortedOptions = element.childElements().sortBy(function(o){
      return o.text;
    });
    element.update();
    sortedOptions.each(function(o){
      element.insert(o);
    });
    $V(element, selected, false);
  }
});

Element.addMethods('input', {
  /**
   * Transforms the input into a color picker
   * @param {HTMLElement=} element
   * @param {Object=} options
   */
  colorPicker: function(element, options){
    options = Object.extend({
      afterInit: null, // Custom event
      allowEmpty: true,
      showInput: true,
      showPalette: true,
      showSelectionPalette: true,
      preferredFormat: "hex",
      palette: [
        ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
        ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
        ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
        ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
        ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
      ]
    }, options);

    App.loadCSS('lib/spectrum/spectrum.css', true);

    require(['lib/spectrum/spectrum.js'], function() {
      require(['lib/spectrum/i18n/jquery.spectrum-'+Preferences.LOCALE+'.js'], function() {
        jQuery(element).spectrum(options);

        if (options.afterInit) {
          options.afterInit(element);
        }
      });
    });
  }
});

Element.addMethods('form', {
  clear: function(form, fire){
    $A(form.elements).each(function(e){
      if (e.type != "hidden" || /(autocomplete|date|time)/i.test(e.className)) {
        $V(e, '', fire);
      }
    });
  },

  /**
   * Tells if the app is in readonly mode
   *
   * @param {HTMLFormElement} form
   *
   * @return {Boolean}
   */
  isReadonly: function(form) {
    return App.readonly && User.id && form.method === "post" && (!form.elements.dosql || App.notReadonlyForms.indexOf(form.elements.dosql.value) == -1);
  }
});

Element.addMethods('div', {
  fixedTableHeaders: function(element, options) {

    if(options && isNaN(options)) {
      ViewPort.SetAvlHeightEx(element.id, options);
    } else {
      ViewPort.SetAvlHeight(element.id, options || 1.0);
    }

    // Pas d'entête fixe si IE
    if (document.documentMode) {
      return;
    }

    element.addClassName('tablefixedheaders_wrap');
    element.observe("scroll",function(e) {
      var translateCell = "translate(" + this.scrollLeft + "px,0)";
      // I translate every cell from left column one by one
      Event.element(e).select(".leftcell").invoke("setStyle", {transform: translateCell});

      var translateHead = "translate(0," + Math.max((this.scrollTop-1), 0) + "px)";
      // the same for each header cell
      Event.element(e).select("thead").invoke("setStyle", {transform: translateHead});

      element[(this.scrollTop != 0 ? "add" : "remove") + "ClassName"]("tablefixedheaders_wrap_shadowed");
    })
  }
});

Form.getInputsArray = function(element) {
  if (element instanceof NodeList || element instanceof HTMLCollection) {
    return $A(element);
  }

  return [element];
};

Object.extend(Event, {
  key: function(e){
    return (window.event && (window.event.keyCode || window.event.which)) || e.which || e.keyCode || false;
  },
  isCapsLock: function(e){
    var charCode = Event.key(e);
    var shiftOn = false;

    if (e.shiftKey) {
      shiftOn = e.shiftKey;
    } else if (e.modifiers) {
      shiftOn = !!(e.modifiers & 4);
    }

    if ((charCode >= 97 && charCode <= 122 && shiftOn) ||
        (charCode >= 65 && charCode <= 90 && !shiftOn)) {
      return true;
    }

    // Keys from the top of a French keyboard
    /*var keys = {
      "0": "à",
      "1": "&",
      "2": "é",
      "3": "\"",
      "4": "'",
      "5": "(",
      "6": "-",
      "7": "è",
      "8": "_",
      "9": "ç",
      "°": ")",
      "+": "=",
      "¨": "^",
      "£": "$",
      "%": "ù",
      "µ": "*",
      "?": ",",
      ".": ";",
      "/": ":",
      "§": "!",
      ">": "<"
    };

    var c = String.fromCharCode(charCode);

    if ( shiftOn && Object.values(keys).indexOf(c) != -1 ||
        !shiftOn && keys[c]) return true;*/

    return false;
  },
  wheel: function (event){
    var delta = 0;

    if (!event) event = window.event;

    if (event.wheelDelta) {
      delta = event.wheelDelta/120;
      if (window.opera) delta = -delta;
    }
    else if (event.detail) {
      delta = -event.detail/3;
    }

    return Math.round(delta); //Safari Round
  }
});

if (window.navigator.msPointerEnabled) {
  Event.pointerEvents = {
    start: "MSPointerDown",
    move:  "MSPointerMove",
    end:   "MSPointerUp"
  };
}
else {
  Event.pointerEvents = {
    start: "touchstart",
    move:  "touchmove",
    end:   "touchend"
  };
}

Object.extend(String, {
  allographs: {
    withDiacritics   : "àáâãäåòóôõöøèéêëçìíîïùúûüÿñ",
    withoutDiacritics: "aaaaaaooooooeeeeciiiiuuuuyn"
  },
  glyphs: {
    "a": "àáâãäå",
    "c": "ç",
    "e": "èéêë",
    "i": "ìíîï",
    "o": "òóôõöø",
    "u": "ùúûü",
    "y": "ÿ",
    "n": "ñ"
  },
  dec2frac: function (dec, sep) {
    sep = sep || "/";

    var df = 1,
        top = 1,
        bot = 1;

    while (df != dec) {
      if (df < dec) {
        top++;
      }
      else {
        bot++;
        top = parseInt(dec * bot);
      }

      df = top / bot;
    }

    return top + sep + bot;
  },

  /**
   * Convert a number or a percentage to a CSS length
   *
   * @param {String,Number} string The string to convert to a CSS length, may be a Number, will become NNpx
   *
   * @returns {String} A CSS length
   */
  getCSSLength: function(string) {
    if (/%/.test(string)) {
      return string;
    }

    return parseInt(string)+"px";
  }
});

Class.extend(String, {
  trim: function() {
    return this.strip();
  },
  pad: function(ch, length, right) {
    length = length || 30;
    ch = ch || ' ';
    var t = this;
    while(t.length < length) t = (right ? t+ch : ch+t);
    return t;
  },
  unslash: function() {
    return this
      .replace(/\\n/g, "\n")
      .replace(/\\t/g, "\t");
  },
  stripAll: function() {
    return this.strip().gsub(/\s+/, " ");
  },
  removeDiacritics: function(){
    var str = this;
    var from, to;

    from = String.allographs.withDiacritics.split("");
    to   = String.allographs.withoutDiacritics.split("");

    from.each(function(c, i){
      str = str.gsub(c, to[i]);
    });

    from = String.allographs.withDiacritics.toUpperCase().split("");
    to   = String.allographs.withoutDiacritics.toUpperCase().split("");

    from.each(function(c, i){
      str = str.gsub(c, to[i]);
    });

    return str;
  },
  // @todo: should extend RegExp instead of String
  allowDiacriticsInRegexp: function() {
    var re = this.removeDiacritics();

    var translation = {};
    $H(String.glyphs).each(function(g){
      translation[g.key] = "["+g.key+g.value+"]";
    });

    $H(translation).each(function(t){
      re = re.replace(new RegExp(t.key, "gi"), t.value);
    });

    return re;
  },
  like: function(term) {
    return !!this.match(
      new RegExp(
        RegExp.escape(term).trim().allowDiacriticsInRegexp(),
        "i"
      )
    );
  },
  beginsWith: function(term) {
    return !!this.match(
      new RegExp(
        '^' + RegExp.escape(term).trim(),
        "i"
      )
    );
  },
  htmlDecode: function() {
    return DOM.div({}, this).getText();
  },
  htmlSanitize: function() {
    return this
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  },
  truncate: function(n){
    return this.length > n ? this.substr(0, n-1)+'&hellip;' : this;
  }
});

/**
 *
 * @param {String}         string
 * @param {String,Integer} defaultValue
 *
 * @return {*}
 */
Number.getInt = function(string, defaultValue) {
  var number = parseInt(string, 10);
  if (isNaN(number)) {
    return defaultValue;
  }

  return number;
};

/**
 * Utility function to convert file size values in bytes to kB, MB, ...
 *
 * @param {Number} value The value to convert
 * @param {Number} precision The number of digits after the comma (default: 2)
 * @param {Number} base The base (default: 1000)
 *
 * @return {String}
 */
Number.toDecaBinary = function (value, precision, base) {
  var sizes = ['Y', 'Z', 'E', 'P', 'T', 'G', 'M', 'k', ''],
    fractionSizes = ['y', 'z', 'a', 'f', 'p', 'n', 'µ', 'm', ''],
    total = sizes.length;

  base = base || 1000;
  precision = Math.pow(10, precision || 2);

  if (value == 0) return 0;

  if (value > 1) {
    while (total-- && (value >= base)) value /= base;
  }
  else {
    sizes = fractionSizes;
    total = sizes.length;
    while (total-- && (value < 1)) value *= base;
  }

  return (Math.round(value * precision) / precision) + sizes[total];
};

RegExp.escape = function(text) {
  return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
};

Ajax.PeriodicalUpdater.addMethods({
  resume: function() {
    this.updateComplete();
  }
});

if (Prototype.Browser.IE) {
  Object.extend(Function.prototype, {
    delay: function(timeout){
      var __method = this, args = Array.prototype.slice.call(arguments, 1);
      timeout = timeout * 1000;

      return window.setTimeout(function(){
        try {
          return __method.apply(__method, args);
        }
        catch (e) {
          var msg = (e.extMessage || e.message || e.description || e) + "\n -- " + __method;
          errorHandler(msg, e.fileName, e.lineNumber, e);
        }
      }, timeout);
    }
  });
}

PeriodicalExecuter.addMethods({
  resume: function() {
    if (!this.timer) this.registerCallback();
  }
});

document.observeOnce = function(event_name, outer_callback){
  $(document.documentElement).observeOnce(event_name, outer_callback);
};

Function.getEvent = function(){
  var caller = arguments.callee.caller;

  while(caller = caller.caller) {
    if(caller.arguments[0] instanceof Event) {
      return caller.arguments[0];
    }
  }
};

Element.findDuplicates = function(attr, tag) {
  var ids = $$((tag || "*")+"["+attr+"]").sort(function(e){return e[attr]});
  var results = [],
      len = ids.length - 1;

  for (var i = 0; i < len; i++) {
    if (ids[i][attr] === "") continue;

    if (ids[i + 1][attr] == ids[i][attr]) {
      if (results.indexOf(ids[i]) == -1) {
        results.push(ids[i]);
      }
      results.push(ids[i + 1]);
    }
  }

  return results;
};

Element._duplicates = [];
Element._idConflicts = [];

Element.warnDuplicates = function(){
  if (Prototype.Browser.IE || Prototype.Browser.IPad || !(console.firebug || (Preferences.INFOSYSTEM == 1))) return; // if("0") => true

  var elements;

  /*elements = Element.findDuplicates("id");
  if (elements.length && !Element._duplicates.intersect(elements).length) {
    Element._duplicates = Element._duplicates.concat(elements);
    console.warn("Duplicates *[id]: ", elements);
  }*/

  elements = Element.findDuplicates("name", "form");
  if (elements.length && !Element._duplicates.intersect(elements).length) {
    Element._duplicates = Element._duplicates.concat(elements);
    console.warn("Duplicates form[name]: ", elements);
  }

  elements = $$("form form");
  if (elements.length && !Element._duplicates.intersect(elements).length) {
    Element._duplicates = Element._duplicates.concat(elements);
    console.error("Nested form: ", elements);
  }

  elements = $$("form:not([method]), form[method='']");
  if (elements.length && !Element._duplicates.intersect(elements).length) {
    Element._duplicates = Element._duplicates.concat(elements);
    console.error("Method-less forms: ", elements);
  }

  /*
  // Disabled because of https://stackoverflow.com/questions/3434278/ie-chrome-are-dom-tree-elements-global-variables-here
  elements = $$("*[id]").pluck("id").intersect($H(window).keys().without("console", "main", "menubar", "performance")); // FIXME
  if (elements.length && !Element._idConflicts.intersect(elements).length) {
    Element._idConflicts = Element._idConflicts.concat(elements);
    console.error("ID conflicts (element ID and global variable have the same name): ", elements);
  }*/
};

Event.initKeyboardEvents = function() {
  document.observe("keydown", function(e){
    var key = Event.key(e);
    var element = Event.element(e);
    var tagName = element.tagName;

    // Prevent backspace to go back in history
    if(key == Event.KEY_BACKSPACE && !(/input|textarea/i.test(tagName) || element.contentEditable)) {
      Event.stop(e);
    }

    // Ctrl+Return in a textera to submit the form
    if(key == Event.KEY_RETURN && element.form && e.ctrlKey && tagName == "TEXTAREA") {
      element.form.onsubmit();
      Event.stop(e);
    }

    // Escape in a modal window containing a visible "close" button
    if(key == Event.KEY_ESC && Control.Modal.stack.length) {
      var closeButton = Control.Modal.stack.last().container.down("button.close");
      if (closeButton && closeButton.visible()) {
        closeButton.click();
        Event.stop(e);
      }
    }
  });
};

/**
 * Number.prototype.format(n, x)
 *
 * @param integer n: length of decimal
 */
Number.prototype.format = function(n) {
  var re = '\\d(?=(\\d{' + (3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
  return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$& ');
};
