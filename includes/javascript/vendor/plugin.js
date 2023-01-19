/*
 *jQuery browser plugin detection 1.0.2, ported to PrototypeJS
 * http://plugins.jquery.com/project/jqplugin
 * Checks for plugins / mimetypes supported in the browser extending the jQuery.browser object
 * Copyright (c) 2008 Leonardo Rossetti motw.leo@gmail.com
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */
App.detectPlugin = function(plugin) {
  var pluginList = {
    flash : {
      activex : "ShockwaveFlash.ShockwaveFlash",
      plugin : /flash/gim
    },
    sl : {
      activex : "AgControl.AgControl",
      plugin : /silverlight/gim
    },
    pdf : {
      activex : "AcroPDF.PDF",
      plugin : /adobe\s?acrobat|pdf/gim
    },
    qtime : {
      activex : "QuickTime.QuickTime",
      plugin : /quicktime/gim
    },
    wmp : {
      activex : "WMPlayer.OCX",
      plugin : /(windows\smedia)|(Microsoft)/gim
    },
    shk : {
      activex : "SWCtl.SWCtl",
      plugin : /shockwave/gim
    },
    rp : {
      activex : "RealPlayer",
      plugin : /realplayer/gim
    },
    java : {
      activex : navigator.javaEnabled(),
      plugin : /java/gim
    }
  };
  
  if (!pluginList[plugin]) {
    return false;
  }
  
  if (window.ActiveXObject) {
    var ax = pluginList[plugin].activex;
    if (Object.isString(ax)) {
      try {
        new ActiveXObject(ax);
        return true;
      } catch(e) {
        return false;
      }
    }
    
    return ax;
  }
  else {
    var has = false;
    $A(navigator.plugins).each(function(p) {
      if (!has && pluginList[plugin].plugin.test(p.name)) {
        return has = true;
      }
    });
    
    return has;
  }
};