/*! grapesjs-style-bg - 1.0.1 */
!function(e,t){'object'==typeof exports&&'object'==typeof module?module.exports=t():'function'==typeof define&&define.amd?define([],t):'object'==typeof exports?exports["grapesjs-style-bg"]=t():e["grapesjs-style-bg"]=t()}(window,(function(){return function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){'undefined'!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:'Module'}),Object.defineProperty(e,'__esModule',{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&'object'==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,'default',{enumerable:!0,value:e}),2&t&&'string'!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,'a',t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=6)}([function(e,t,n){var r=n(3),o=n(4),i=n(5);e.exports=function(e){return r(e)||o(e)||i()}},function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}},function(e,t,n){window,e.exports=function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){'undefined'!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:'Module'}),Object.defineProperty(e,'__esModule',{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&'object'==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,'default',{enumerable:!0,value:e}),2&t&&'string'!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,'a',t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=3)}([function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}},function(e,t){function n(t){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?e.exports=n=function(e){return typeof e}:e.exports=n=function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n(t)}e.exports=n},function(e,t,n){e.exports=function(e){function t(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,t),o.l=!0,o.exports}var n={};return t.m=e,t.c=n,t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=1)}([function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.on=function(e,t,n){t=t.split(/\s+/);for(var r=0;r<t.length;++r)e.addEventListener(t[r],n)},t.off=function(e,t,n){t=t.split(/\s+/);for(var r=0;r<t.length;++r)e.removeEventListener(t[r],n)}},function(e,t,n){"use strict";var r=function(e){return e&&e.__esModule?e:{default:e}}(n(2));e.exports=function(e){return new r.default(e)}},function(e,t,n){"use strict";function r(e){return e&&e.__esModule?e:{default:e}}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function i(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}Object.defineProperty(t,"__esModule",{value:!0});var a=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),l=r(n(3)),u=r(n(4)),c=n(0),s=function(e,t){return e.position-t.position},p=function(e){return e+"-gradient("},f=function(e){function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};o(this,t);var n=i(this,(t.__proto__||Object.getPrototypeOf(t)).call(this));e=Object.assign({},e);var r={pfx:"grp",el:".grp",colorEl:"",min:0,max:100,direction:"90deg",type:"linear",height:"30px",width:"100%"};for(var a in r)a in e||(e[a]=r[a]);var l=e.el;if(!((l="string"==typeof l?document.querySelector(l):l)instanceof HTMLElement))throw"Element not found, given "+l;return n.el=l,n.handlers=[],n.options=e,n.on("handler:color:change",(function(e,t){return n.change(t)})),n.on("handler:position:change",(function(e,t){return n.change(t)})),n.on("handler:remove",(function(e){return n.change(1)})),n.on("handler:add",(function(e){return n.change(1)})),n.render(),n}return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}(t,e),a(t,[{key:"setColorPicker",value:function(e){this.colorPicker=e}},{key:"getValue",value:function(e,t){var n=this.getColorValue(),r=e||this.getType(),o=t||this.getDirection();return n?r+"-gradient("+o+", "+n+")":""}},{key:"getSafeValue",value:function(e,t){var n=this.previewEl,r=this.getValue(e,t);if(!this.sandEl&&(this.sandEl=document.createElement("div")),!n||!r)return"";for(var o=this.sandEl.style,i=[r].concat(function(e){if(Array.isArray(e)){for(var t=0,n=Array(e.length);t<e.length;t++)n[t]=e[t];return n}return Array.from(e)}(this.getPrefixedValues(e,t))),a=void 0,l=0;l<i.length&&(a=i[l],o.backgroundImage=a,o.backgroundImage!=a);l++);return o.backgroundImage}},{key:"setValue",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=this.type,o=this.direction,i=t.indexOf("(")+1,a=t.lastIndexOf(")"),l=t.substring(i,a),u=l.split(/,(?![^(]*\)) /);if(this.clear(n),l){u.length>2&&(o=u.shift());var c=void 0;["repeating-linear","repeating-radial","linear","radial"].forEach((function(e){t.indexOf(p(e))>-1&&!c&&(c=1,r=e)})),this.setDirection(o,n),this.setType(r,n),u.forEach((function(t){var r=t.split(" "),o=parseFloat(r.pop()),i=r.join("");e.addHandler(o,i,0,n)})),this.updatePreview()}else this.updatePreview()}},{key:"getColorValue",value:function(){var e=this.handlers;return e.sort(s),(e=1==e.length?[e[0],e[0]]:e).map((function(e){return e.getValue()})).join(", ")}},{key:"getPrefixedValues",value:function(e,t){var n=this.getValue(e,t);return["-moz-","-webkit-","-o-","-ms-"].map((function(e){return""+e+n}))}},{key:"change",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};this.updatePreview(),!t.silent&&this.emit("change",e)}},{key:"setDirection",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};this.options.direction=e,this.change(1,t)}},{key:"getDirection",value:function(){return this.options.direction}},{key:"setType",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};this.options.type=e,this.change(1,t)}},{key:"getType",value:function(){return this.options.type}},{key:"addHandler",value:function(e,t){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:1,r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{},o=new u.default(this,e,t,n);return!r.silent&&this.emit("handler:add",o),o}},{key:"getHandler",value:function(e){return this.handlers[e]}},{key:"getHandlers",value:function(){return this.handlers}},{key:"clear",value:function(){for(var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=this.handlers,n=t.length-1;n>=0;n--)t[n].remove(e)}},{key:"getSelected",value:function(){for(var e=this.getHandlers(),t=0;t<e.length;t++){var n=e[t];if(n.isSelected())return n}return null}},{key:"updatePreview",value:function(){var e=this.previewEl;e&&(e.style.backgroundImage=this.getSafeValue("linear","to right"))}},{key:"initEvents",value:function(){var e=this,t=this.options,n=t.min,r=t.max,o=this.previewEl,i=0,a={};o&&(0,c.on)(o,"click",(function(t){a.w=o.clientWidth,a.h=o.clientHeight;var l=t.offsetX-o.clientLeft,u=t.offsetY-o.clientTop;if(!((i=l/a.w*100)>r||i<n)){var c=document.createElement("canvas"),s=c.getContext("2d");c.width=a.w,c.height=a.h;var p=s.createLinearGradient(0,0,a.w,a.h);e.getHandlers().forEach((function(e){return p.addColorStop(e.position/100,e.color)})),s.fillStyle=p,s.fillRect(0,0,c.width,c.height),c.style.background="black";var f=c.getContext("2d").getImageData(l,u,1,1).data,d="rgba("+f[0]+", "+f[1]+", "+f[2]+", "+f[3]+")";e.addHandler(i,d)}}))}},{key:"render",value:function(){var e=this.options,t=this.el,n=e.pfx,r=e.height,o=e.width;if(t){var i=n+"-wrapper",a=n+"-preview";t.innerHTML='\n      <div class="'+i+'">\n        <div class="'+a+'"></div>\n      </div>\n    ';var l=t.querySelector("."+i),u=t.querySelector("."+a),c=l.style;c.position="relative",this.wrapperEl=l,this.previewEl=u,r&&(c.height=r),o&&(c.width=o),this.initEvents(),this.updatePreview()}}}]),t}(l.default);t.default=f},function(e,t){function n(){}n.prototype={on:function(e,t,n){var r=this.e||(this.e={});return(r[e]||(r[e]=[])).push({fn:t,ctx:n}),this},once:function(e,t,n){function r(){o.off(e,r),t.apply(n,arguments)}var o=this;return r._=t,this.on(e,r,n)},emit:function(e){for(var t=[].slice.call(arguments,1),n=((this.e||(this.e={}))[e]||[]).slice(),r=0,o=n.length;r<o;r++)n[r].fn.apply(n[r].ctx,t);return this},off:function(e,t){var n=this.e||(this.e={}),r=n[e],o=[];if(r&&t)for(var i=0,a=r.length;i<a;i++)r[i].fn!==t&&r[i].fn._!==t&&o.push(r[i]);return o.length?n[e]=o:delete n[e],this}},e.exports=n},function(e,t,n){"use strict";function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),i=n(0),a=function(){function e(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0,o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"black",i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:1;r(this,e),t.getHandlers().push(this),this.gp=t,this.position=n,this.color=o,this.selected=0,this.render(),i&&this.select()}return o(e,[{key:"toJSON",value:function(){return{position:this.position,selected:this.selected,color:this.color}}},{key:"setColor",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1;this.color=e,this.emit("handler:color:change",this,t)}},{key:"setPosition",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1,n=this.getEl();this.position=e,n&&(n.style.left=e+"%"),this.emit("handler:position:change",this,t)}},{key:"getColor",value:function(){return this.color}},{key:"getPosition",value:function(){return this.position}},{key:"isSelected",value:function(){return!!this.selected}},{key:"getValue",value:function(){return this.getColor()+" "+this.getPosition()+"%"}},{key:"select",value:function(){var e=this.getEl();this.gp.getHandlers().forEach((function(e){return e.deselect()})),this.selected=1;var t=this.getSelectedCls();e&&(e.className+=" "+t),this.emit("handler:select",this)}},{key:"deselect",value:function(){var e=this.getEl();this.selected=0;var t=this.getSelectedCls();e&&(e.className=e.className.replace(t,"").trim()),this.emit("handler:deselect",this)}},{key:"getSelectedCls",value:function(){return this.gp.options.pfx+"-handler-selected"}},{key:"remove",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=this.getEl(),n=this.gp.getHandlers(),r=n.splice(n.indexOf(this),1)[0];return t&&t.parentNode.removeChild(t),!e.silent&&this.emit("handler:remove",r),r}},{key:"getEl",value:function(){return this.el}},{key:"initEvents",value:function(){var e=this,t=this.getEl(),n=this.gp.previewEl,r=this.gp.options,o=r.min,a=r.max,l=t.querySelector("[data-toggle=handler-close]"),u=t.querySelector("[data-toggle=handler-color-c]"),c=t.querySelector("[data-toggle=handler-color-wrap]"),s=t.querySelector("[data-toggle=handler-color]"),p=t.querySelector("[data-toggle=handler-drag]");if(u&&(0,i.on)(u,"click",(function(e){return e.stopPropagation()})),l&&(0,i.on)(l,"click",(function(t){t.stopPropagation(),e.remove()})),s&&(0,i.on)(s,"change",(function(t){var n=t.target.value;e.setColor(n),c&&(c.style.backgroundColor=n)})),p){var f=0,d=0,h=0,v={},g={},y={},m=function(t){h=1,y.x=t.clientX-g.x,y.y=t.clientY-g.y,f=100*y.x,f/=v.w,f=(f=(f=d+f)<o?o:f)>a?a:f,e.setPosition(f,0),e.emit("handler:drag",e,f),0===t.which&&b(t)},b=function t(n){h&&(h=0,e.setPosition(f),(0,i.off)(document,"touchmove mousemove",m),(0,i.off)(document,"touchend mouseup",t),e.emit("handler:drag:end",e,f))};(0,i.on)(p,"touchstart mousedown",(function(t){0===t.button&&(e.select(),d=e.position,v.w=n.clientWidth,v.h=n.clientHeight,g.x=t.clientX,g.y=t.clientY,(0,i.on)(document,"touchmove mousemove",m),(0,i.on)(document,"touchend mouseup",b),e.emit("handler:drag:start",e))})),(0,i.on)(p,"click",(function(e){return e.stopPropagation()}))}}},{key:"emit",value:function(){var e;(e=this.gp).emit.apply(e,arguments)}},{key:"render",value:function(){var e=this.gp,t=e.options,n=e.previewEl,r=e.colorPicker,o=t.pfx,i=t.colorEl,a=this.getColor();if(n){var l=document.createElement("div"),u=l.style,c=o+"-handler";return l.className=c,l.innerHTML='\n      <div class="'+c+'-close-c">\n        <div class="'+c+'-close" data-toggle="handler-close">&Cross;</div>\n      </div>\n      <div class="'+c+'-drag" data-toggle="handler-drag"></div>\n      <div class="'+c+'-cp-c" data-toggle="handler-color-c">\n        '+(i||'\n          <div class="'+c+'-cp-wrap" data-toggle="handler-color-wrap" style="background-color: '+a+'">\n            <input type="color" data-toggle="handler-color" value="'+a+'">\n          </div>')+"\n      </div>\n    ",u.position="absolute",u.top=0,u.left=this.position+"%",n.appendChild(l),this.el=l,this.initEvents(),r&&r(this),l}}}]),e}();t.default=a}])},function(e,t,n){"use strict";n.r(t);var r=n(0),o=n.n(r),i=n(1),a=n.n(i),l=n(2),u=n.n(l);function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function s(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){o()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var p,f,d=function(e){return(1==e.getAlpha()?e.toHexString():e.toRgbString()).replace(/ /g,'')},h=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=e.StyleManager,r=t.colorPicker;n.addType('gradient',{view:{events:{},templateInput:function(){return''},setValue:function(e){var t=this.gp,n=this.model.getDefaultValue();e=e||n,t&&t.setValue(e,{silent:1}),f&&f.setValue(t.getType()),p&&p.setValue(t.getDirection())},onRender:function(){var o=this,i=this.ppfx,l=this.em,c=this.model,h=s({},t,{},c.get('gradientConfig')||{}),v=h.onCustomInputChange,g=document.createElement('div'),y=r&&"<div class=\"grp-handler-cp-wrap\">\n          <div class=\"".concat(i,"field-colorp-c\">\n            <div class=\"").concat(i,"checker-bg\"></div>\n            <div class=\"").concat(i,"field-color-picker\" ").concat("data-cp","></div>\n          </div>\n        </div>"),m=new u.a(s({el:g,colorEl:y},h.grapickOpts)),b=this.el.querySelector(".".concat(i,"fields"));b.style.flexWrap='wrap',b.appendChild(g.children[0]),this.gp=m,m.on('change',(function(e){var t=m.getSafeValue();c.setValueFromInput(t,e)})),[['inputDirection','integer','setDirection',{name:'Direction',units:['deg'],defaults:90,fixedValues:['top','right','bottom','left']}],['inputType','select','setType',{name:'Type',defaults:'linear',options:[{value:'radial'},{value:'linear'},{value:'repeating-radial'},{value:'repeating-linear'}]}]].forEach((function(e){var t=e[0],r=h[e[0]];if(r){var i=c.parent,l=e[1],u='object'==a()(r)?r:{},d=n.createType(u.type||l,{model:s({},e[3],{},u),view:{propTarget:o.propTarget}});i&&(d.model.parent=i),d.render(),d.model.on('change:value',(function(t){m[e[2]](t.getFullValue()),v({model:t,input:e,inputDirection:p,inputType:f})})),b.appendChild(d.el),'inputDirection'==t&&(p=d),'inputType'==t&&(f=d)}})),'default'==r&&(r=function(t){var n=t.getEl().querySelector("[".concat("data-cp","]")),r=n.style;r.backgroundColor=t.getColor();var o=l&&l.getConfig()||{},a=o.colorPicker||{},u=o.el,c=function(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1,o=d(e);r.backgroundColor=o,t.setColor(o,n)},p={color:t.getColor(),change:function(e){c(e)},move:function(e){c(e,0)}},f=l&&l.initBaseColorPicker;f?f(n,p):e.$(n).spectrum(s({containerClassName:"".concat(i,"one-bg ").concat(i,"two-color"),appendTo:u||'body',maxSelectionSize:8,showPalette:!0,palette:[],showAlpha:!0,chooseText:'Ok',cancelText:'⨯'},p,{},a))}),r&&m.setColorPicker(r)}}})};function v(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function g(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?v(Object(n),!0).forEach((function(t){o()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):v(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}t.default=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n={grapickOpts:{},colorPicker:'',inputDirection:1,inputType:1,onCustomInputChange:function(){return 0}},r=g({},n,{},t);h(e,r)}}])},function(e,t){e.exports=function(e){if(Array.isArray(e)){for(var t=0,n=new Array(e.length);t<e.length;t++)n[t]=e[t];return n}}},function(e,t){e.exports=function(e){if(Symbol.iterator in Object(e)||"[object Arguments]"===Object.prototype.toString.call(e))return Array.from(e)}},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance")}},function(e,t,n){"use strict";n.r(t);var r={};n.r(r),n.d(r,"typeBg",(function(){return p})),n.d(r,"typeImage",(function(){return f})),n.d(r,"typeBgRepeat",(function(){return d})),n.d(r,"typeBgPos",(function(){return h})),n.d(r,"typeBgAttach",(function(){return v})),n.d(r,"typeBgSize",(function(){return g})),n.d(r,"typeColorLin",(function(){return y})),n.d(r,"typeGradient",(function(){return m}));var o=n(0),i=n.n(o),a=n(1),l=n.n(a),u=n(2),c=n.n(u),s='style="max-height: 16px; display: block" viewBox="0 0 24 24"',p={name:' ',property:"__bg-type",type:'radio',defaults:'img',options:[{value:'img',name:"<svg ".concat(s,"><path fill=\"currentColor\" d=\"M8.5 13.5l2.5 3 3.5-4.5 4.5 6H5m16 1V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2z\"/></svg>")},{value:'color',name:"<svg ".concat(s,"><path fill=\"currentColor\" d=\"M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z\"/></svg>")},{value:'grad',name:"<svg ".concat(s,"><path fill=\"currentColor\" d=\"M11 9h2v2h-2V9m-2 2h2v2H9v-2m4 0h2v2h-2v-2m2-2h2v2h-2V9M7 9h2v2H7V9m12-6H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2M9 18H7v-2h2v2m4 0h-2v-2h2v2m4 0h-2v-2h2v2m2-7h-2v2h2v2h-2v-2h-2v2h-2v-2h-2v2H9v-2H7v2H5v-2h2v-2H5V5h14v6z\"/></svg>")}]},f={name:' ',property:'background-image',type:'file',functionName:'url',defaults:'none'},d={property:'background-repeat',type:'select',defaults:'repeat',options:[{value:'repeat'},{value:'repeat-x'},{value:'repeat-y'},{value:'no-repeat'}]},h={property:'background-position',type:'select',defaults:'left top',options:[{value:'left top'},{value:'left center'},{value:'left bottom'},{value:'right top'},{value:'right center'},{value:'right bottom'},{value:'center top'},{value:'center center'},{value:'center bottom'}]},v={property:'background-attachment',type:'select',defaults:'scroll',options:[{value:'scroll'},{value:'fixed'},{value:'local'}]},g={property:'background-size',type:'select',defaults:'auto',options:[{value:'auto'},{value:'cover'},{value:'contain'}]},y={name:' ',property:'background-image',type:'color-linear',defaults:'none',full:1},m={name:'&nbsp;',property:'background-image',type:'gradient',value:'linear-gradient(90deg, #d983a6 0%, #713873 100%)',defaults:'none',full:1},b=function(e,t){var n=t.getType('color'),r=n.model;t.addType('color-linear',{model:r.extend({getFullValue:function(){var e=this.get('value'),t=this.get('defaults');return e?e===t?t:"linear-gradient(".concat(e,",").concat(e,")"):''}}),view:n.view})};function O(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function w(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?O(Object(n),!0).forEach((function(t){l()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):O(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}t.default=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=w({},{styleGradientOpts:{},propExtender:function(e){return e},typeProps:function(e){return e}},{},t),o=w({},r),a=e.StyleManager,l=a.getType('stack'),u=l.model;o=Object.keys(o).reduce((function(e,t){var r=o[t];return e[t]=n.propExtender(r)||r,e}),{});var s=function(e){var t=[o.typeImage,o.typeBgRepeat,o.typeBgPos,o.typeBgAttach,o.typeBgSize];switch(e){case'color':t=[o.typeColorLin];break;case'grad':t=[o.typeGradient]}return n.typeProps(t,e)||t};c()(e,w({colorPicker:'default',inputDirection:{property:'__gradient-direction'},inputType:{property:'__gradient-type'}},n.styleGradientOpts)),b(0,a),a.addType('bg',{model:u.extend({defaults:function(){return w({},u.prototype.defaults,{detached:1,preview:1,full:1,prepend:1,properties:[o.typeBg].concat(i()(s()))})},init:function(){this.handleTypeChange=this.handleTypeChange.bind(this),this.listenTo(this.getLayers(),'add',this.onNewLayerAdd)},_updateLayerProps:function(e,t){var n=e.get('properties');n.remove(n.filter((function(e,t){return 0!==t}))),s(t).forEach((function(e){return n.push(e)}))},onNewLayerAdd:function(e){var t=e.getPropertyAt(0);e.listenTo(t,'change:value',this.handleTypeChange)},handleTypeChange:function(e,t,n){var r=this.getCurrentLayer();r&&this._updateLayerProps(r,t),n.fromInput&&this.trigger('updateValue')},getLayersFromTarget:function(e){var t=this,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=n.resultValue,a=[],l=r||e.getStyle()[this.get('property')],u=l["__bg-type"];return u&&this.splitValues(u).forEach((function(e,n){var r=s(e);a.push({properties:[w({},o.typeBg,{value:e})].concat(i()(r.map((function(e){var r=t.splitValues(l[e.property])[n];if('color-linear'==e.type){var o=t.parseValue(r,{complete:1});r=t.splitValues(o.value)[0]}else'file'==e.type&&(r=r&&t.parseValue(r,{complete:1}).value);return w({},e,{},r&&{value:r})}))))})})),a}}),view:l.view})}}])}));
//# sourceMappingURL=grapesjs-style-bg.min.js.map