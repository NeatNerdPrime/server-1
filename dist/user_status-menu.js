/*! For license information please see user_status-menu.js.license?v=d40836abcbe66c3d4e58 */
(()=>{"use strict";var e,s,a,r={19725:(e,s,a)=>{var r=a(85471),n=a(21777),o=a(61338),u=a(9518),i=a(53611),c=a(17334),d=a.n(c),l=a(26287),m=a(63814),p=a(33114),A=a(96763);const g={name:"UserStatus",components:{NcButton:u.A,NcUserStatusIcon:i.A,SetStatusModal:()=>Promise.all([a.e(4208),a.e(5133)]).then(a.bind(a,70233))},mixins:[p.A],props:{inline:{type:Boolean,default:!1}},data:()=>({heartbeatInterval:null,isAway:!1,isModalOpen:!1,mouseMoveListener:null,setAwayTimeout:null}),mounted(){this.$store.dispatch("loadStatusFromInitialState"),OC.config.session_keepalive&&(this.heartbeatInterval=setInterval(this._backgroundHeartbeat.bind(this),3e5),this.setAwayTimeout=()=>{this.isAway=!0},this.mouseMoveListener=d()((()=>{const t=this.isAway;this.isAway=!1,clearTimeout(this.setAwayTimeout),setTimeout(this.setAwayTimeout,12e4),t&&this._backgroundHeartbeat()}),2e3,!0),window.addEventListener("mousemove",this.mouseMoveListener,{capture:!0,passive:!0}),this._backgroundHeartbeat()),(0,o.B1)("user_status:status.updated",this.handleUserStatusUpdated)},beforeDestroy(){window.removeEventListener("mouseMove",this.mouseMoveListener),clearInterval(this.heartbeatInterval),(0,o.al)("user_status:status.updated",this.handleUserStatusUpdated)},methods:{openModal(){this.isModalOpen=!0},closeModal(){this.isModalOpen=!1},async _backgroundHeartbeat(){try{const t=await(async t=>{const e=(0,m.KT)("apps/user_status/api/v1/heartbeat?format=json");return(await l.A.put(e,{status:t?"away":"online"})).data.ocs.data})(this.isAway);null!=t&&t.userId?this.$store.dispatch("setStatusFromHeartbeat",t):await this.$store.dispatch("reFetchStatusFromServer")}catch(e){var t;A.debug("Failed sending heartbeat, got: "+(null===(t=e.response)||void 0===t?void 0:t.status))}},handleUserStatusUpdated(t){OC.getCurrentUser().uid===t.userId&&this.$store.dispatch("setStatusFromObject",{status:t.status,icon:t.icon,message:t.message})}}};var v=a(85072),h=a.n(v),f=a(97825),b=a.n(f),y=a(77659),S=a.n(y),w=a(55056),I=a.n(w),C=a(10540),k=a.n(C),_=a(41113),x=a.n(_),M=a(5821),T={};T.styleTagTransform=x(),T.setAttributes=I(),T.insert=S().bind(null,"head"),T.domAPI=b(),T.insertStyleElement=k(),h()(M.A,T),M.A&&M.A.locals&&M.A.locals;const O=(0,a(14486).A)(g,(function(){var t=this,e=t._self._c;return e(t.inline?"div":"li",{tag:"component"},[t.inline?e("NcButton",{on:{click:function(e){return e.stopPropagation(),t.openModal.apply(null,arguments)}},scopedSlots:t._u([{key:"icon",fn:function(){return[e("NcUserStatusIcon",{staticClass:"user-status-icon",attrs:{status:t.statusType,"aria-hidden":"true"}})]},proxy:!0}])},[t._v("\n\t\t"+t._s(t.visibleMessage)+"\n\t")]):e("button",{staticClass:"user-status-menu-item",on:{click:function(e){return e.stopPropagation(),t.openModal.apply(null,arguments)}}},[e("NcUserStatusIcon",{staticClass:"user-status-icon",attrs:{status:t.statusType,"aria-hidden":"true"}}),t._v("\n\t\t"+t._s(t.visibleMessage)+"\n\t")],1),t._v(" "),t.isModalOpen?e("SetStatusModal",{attrs:{inline:t.inline},on:{close:t.closeModal}}):t._e()],1)}),[],!1,null,"056751be",null).exports;var P=a(95353);const F={state:{predefinedStatuses:[]},mutations:{addPredefinedStatus(t,e){t.predefinedStatuses=[...t.predefinedStatuses,e]}},getters:{statusesHaveLoaded:t=>t.predefinedStatuses.length>0},actions:{async loadAllPredefinedStatuses(t){let{state:e,commit:s}=t;if(e.predefinedStatuses.length>0)return;const a=await(async()=>{const t=(0,m.KT)("apps/user_status/api/v1/predefined_statuses?format=json");return(await l.A.get(t)).data.ocs.data})();for(const t of a)s("addPredefinedStatus",t)}}};var U=a(38613),D=a(61913),B=a(51651);const E=t=>{if(null===t)return null;const e=(0,D.R)();if("period"===t.type)return e.setSeconds(e.getSeconds()+t.time),Math.floor(e.getTime()/1e3);if("end-of"===t.type)switch(t.time){case"day":case"week":return Number((0,B.A)(e).endOf(t.time).format("X"))}return"_time"===t.type?t.time:null},j={state:{status:null,statusIsUserDefined:null,message:null,icon:null,clearAt:null,messageIsPredefined:null,messageId:null},mutations:{setStatus(t,e){let{statusType:s}=e;t.status=s,t.statusIsUserDefined=!0},setPredefinedMessage(t,e){let{messageId:s,clearAt:a,message:r,icon:n}=e;t.messageId=s,t.messageIsPredefined=!0,t.message=r,t.icon=n,t.clearAt=a},setCustomMessage(t,e){let{message:s,icon:a,clearAt:r}=e;t.messageId=null,t.messageIsPredefined=!1,t.message=s,t.icon=a,t.clearAt=r},clearMessage(t){t.messageId=null,t.messageIsPredefined=!1,t.message=null,t.icon=null,t.clearAt=null},loadStatusFromServer(t,e){let{status:s,statusIsUserDefined:a,message:r,icon:n,clearAt:o,messageIsPredefined:u,messageId:i}=e;t.status=s,t.message=r,t.icon=n,void 0!==a&&(t.statusIsUserDefined=a),void 0!==o&&(t.clearAt=o),void 0!==u&&(t.messageIsPredefined=u),void 0!==i&&(t.messageId=i)}},getters:{},actions:{async setStatus(t,e){var s;let{commit:a,state:r}=t,{statusType:u}=e;await(async t=>{const e=(0,m.KT)("apps/user_status/api/v1/user_status/status");await l.A.put(e,{statusType:t})})(u),a("setStatus",{statusType:u}),(0,o.Ic)("user_status:status.updated",{status:r.status,message:r.message,icon:r.icon,clearAt:r.clearAt,userId:null===(s=(0,n.HW)())||void 0===s?void 0:s.uid})},async setStatusFromObject(t,e){let{commit:s,state:a}=t;s("loadStatusFromServer",e)},async setPredefinedMessage(t,e){var s;let{commit:a,rootState:r,state:u}=t,{messageId:i,clearAt:c}=e;const d=E(c);await async function(t){let e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;const s=(0,m.KT)("apps/user_status/api/v1/user_status/message/predefined?format=json");await l.A.put(s,{messageId:t,clearAt:e})}(i,d);const p=r.predefinedStatuses.predefinedStatuses.find((t=>t.id===i)),{message:A,icon:g}=p;a("setPredefinedMessage",{messageId:i,clearAt:d,message:A,icon:g}),(0,o.Ic)("user_status:status.updated",{status:u.status,message:u.message,icon:u.icon,clearAt:u.clearAt,userId:null===(s=(0,n.HW)())||void 0===s?void 0:s.uid})},async setCustomMessage(t,e){var s;let{commit:a,state:r}=t,{message:u,icon:i,clearAt:c}=e;const d=E(c);await async function(t){let e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;const a=(0,m.KT)("apps/user_status/api/v1/user_status/message/custom?format=json");await l.A.put(a,{message:t,statusIcon:e,clearAt:s})}(u,i,d),a("setCustomMessage",{message:u,icon:i,clearAt:d}),(0,o.Ic)("user_status:status.updated",{status:r.status,message:r.message,icon:r.icon,clearAt:r.clearAt,userId:null===(s=(0,n.HW)())||void 0===s?void 0:s.uid})},async clearMessage(t){var e;let{commit:s,state:a}=t;await(async()=>{const t=(0,m.KT)("apps/user_status/api/v1/user_status/message?format=json");await l.A.delete(t)})(),s("clearMessage"),(0,o.Ic)("user_status:status.updated",{status:a.status,message:a.message,icon:a.icon,clearAt:a.clearAt,userId:null===(e=(0,n.HW)())||void 0===e?void 0:e.uid})},async reFetchStatusFromServer(t){let{commit:e}=t;e("loadStatusFromServer",await(async()=>{const t=(0,m.KT)("apps/user_status/api/v1/user_status");return(await l.A.get(t)).data.ocs.data})())},async setStatusFromHeartbeat(t,e){let{commit:s}=t;s("loadStatusFromServer",e)},loadStatusFromInitialState(t){let{commit:e}=t;e("loadStatusFromServer",(0,U.C)("user_status","status"))}}},$={state:{status:null,statusIsUserDefined:null,message:null,icon:null,clearAt:null,messageIsPredefined:null,messageId:null},mutations:{loadBackupStatusFromServer(t,e){let{status:s,statusIsUserDefined:a,message:r,icon:n,clearAt:o,messageIsPredefined:u,messageId:i}=e;t.status=s,t.message=r,t.icon=n,void 0!==a&&(t.statusIsUserDefined=a),void 0!==o&&(t.clearAt=o),void 0!==u&&(t.messageIsPredefined=u),void 0!==i&&(t.messageId=i)}},getters:{},actions:{async fetchBackupFromServer(t){let{commit:e}=t;try{var s;e("loadBackupStatusFromServer",await(async t=>{const e=(0,m.KT)("apps/user_status/api/v1/statuses/{userId}",{userId:"_"+t});return(await l.A.get(e)).data.ocs.data})(null===(s=(0,n.HW)())||void 0===s?void 0:s.uid))}catch(t){}},async revertBackupFromServer(t,e){let{commit:s}=t,{messageId:a}=e;const r=await(async t=>{const e=(0,m.KT)("apps/user_status/api/v1/user_status/revert/{messageId}",{messageId:t});return(await l.A.delete(e)).data.ocs.data})(a);var u;r&&(s("loadBackupStatusFromServer",{}),s("loadStatusFromServer",r),(0,o.Ic)("user_status:status.updated",{status:r.status,message:r.message,icon:r.icon,clearAt:r.clearAt,userId:null===(u=(0,n.HW)())||void 0===u?void 0:u.uid}))}}};r.Ay.use(P.Ay);const H=new P.il({modules:{predefinedStatuses:F,userStatus:j,userBackupStatus:$},strict:!0});a.nc=btoa((0,n.do)()),r.Ay.prototype.t=t,r.Ay.prototype.$t=t;const L=()=>{const t=document.getElementById("user_status-menu-entry");new r.Ay({el:t,render:t=>t(O),store:H})};document.getElementById("user_status-menu-entry")?L():(0,o.B1)("core:user-menu:mounted",L),document.addEventListener("DOMContentLoaded",(function(){OCA.Dashboard&&OCA.Dashboard.registerStatus("status",(t=>new(r.Ay.extend(O))({propsData:{inline:!0},store:H}).$mount(t)))}))},33114:(t,e,s)=>{s.d(e,{A:()=>o});var a=s(95353),r=s(85168),n=s(96763);const o={computed:{...(0,a.aH)({statusType:t=>t.userStatus.status,statusIsUserDefined:t=>t.userStatus.statusIsUserDefined,customIcon:t=>t.userStatus.icon,customMessage:t=>t.userStatus.message}),visibleMessage(){if(this.customIcon&&this.customMessage)return"".concat(this.customIcon," ").concat(this.customMessage);if(this.customMessage)return this.customMessage;if(this.statusIsUserDefined)switch(this.statusType){case"online":return this.$t("user_status","Online");case"away":case"busy":return this.$t("user_status","Away");case"dnd":return this.$t("user_status","Do not disturb");case"invisible":return this.$t("user_status","Invisible");case"offline":return this.$t("user_status","Offline")}return this.$t("user_status","Set status")}},methods:{async changeStatus(t){try{await this.$store.dispatch("setStatus",{statusType:t})}catch(t){(0,r.Qg)(this.$t("user_status","There was an error saving the new status")),n.debug(t)}}}}},61913:(t,e,s)=>{s.d(e,{R:()=>a});const a=()=>new Date},5821:(t,e,s)=>{s.d(e,{A:()=>u});var a=s(71354),r=s.n(a),n=s(76314),o=s.n(n)()(r());o.push([t.id,".user-status-menu-item[data-v-056751be]{--color-text-maxcontrast: var(--color-text-maxcontrast-background-blur, var(--color-main-text));width:auto;min-width:44px;height:44px;margin:0;border:0;border-radius:var(--border-radius-pill);background-color:var(--color-main-background-blur);font-size:inherit;font-weight:normal;-webkit-backdrop-filter:var(--background-blur);backdrop-filter:var(--background-blur)}.user-status-menu-item[data-v-056751be]:active,.user-status-menu-item[data-v-056751be]:hover,.user-status-menu-item[data-v-056751be]:focus-visible{background-color:var(--color-background-hover)}.user-status-menu-item[data-v-056751be]:focus-visible{box-shadow:0 0 0 4px var(--color-main-background) !important;outline:2px solid var(--color-main-text) !important}.user-status-icon[data-v-056751be]{width:16px;height:16px;margin-right:10px;opacity:1 !important;background-size:16px;vertical-align:middle !important}","",{version:3,sources:["webpack://./apps/user_status/src/UserStatus.vue"],names:[],mappings:"AACA,wCAEC,+FAAA,CAEA,UAAA,CACA,cAAA,CACA,WAAA,CACA,QAAA,CACA,QAAA,CACA,uCAAA,CACA,kDAAA,CACA,iBAAA,CACA,kBAAA,CAEA,8CAAA,CACA,sCAAA,CAEA,mJAGC,8CAAA,CAED,sDACC,4DAAA,CACA,mDAAA,CAIF,mCACC,UAAA,CACA,WAAA,CACA,iBAAA,CACA,oBAAA,CACA,oBAAA,CACA,gCAAA",sourcesContent:["\n.user-status-menu-item {\n\t// Ensure the maxcontrast color is set for the background\n\t--color-text-maxcontrast: var(--color-text-maxcontrast-background-blur, var(--color-main-text));\n\n\twidth: auto;\n\tmin-width: 44px;\n\theight: 44px;\n\tmargin: 0;\n\tborder: 0;\n\tborder-radius: var(--border-radius-pill);\n\tbackground-color: var(--color-main-background-blur);\n\tfont-size: inherit;\n\tfont-weight: normal;\n\n\t-webkit-backdrop-filter: var(--background-blur);\n\tbackdrop-filter: var(--background-blur);\n\n\t&:active,\n\t&:hover,\n\t&:focus-visible {\n\t\tbackground-color: var(--color-background-hover);\n\t}\n\t&:focus-visible {\n\t\tbox-shadow: 0 0 0 4px var(--color-main-background) !important;\n\t\toutline: 2px solid var(--color-main-text) !important;\n\t}\n}\n\n.user-status-icon {\n\twidth: 16px;\n\theight: 16px;\n\tmargin-right: 10px;\n\topacity: 1 !important;\n\tbackground-size: 16px;\n\tvertical-align: middle !important;\n}\n"],sourceRoot:""}]);const u=o},53611:(t,e,s)=>{s.d(e,{A:()=>a.N});var a=s(8259)}},n={};function o(t){var e=n[t];if(void 0!==e)return e.exports;var s=n[t]={id:t,loaded:!1,exports:{}};return r[t].call(s.exports,s,s.exports,o),s.loaded=!0,s.exports}o.m=r,e=[],o.O=(t,s,a,r)=>{if(!s){var n=1/0;for(d=0;d<e.length;d++){s=e[d][0],a=e[d][1],r=e[d][2];for(var u=!0,i=0;i<s.length;i++)(!1&r||n>=r)&&Object.keys(o.O).every((t=>o.O[t](s[i])))?s.splice(i--,1):(u=!1,r<n&&(n=r));if(u){e.splice(d--,1);var c=a();void 0!==c&&(t=c)}}return t}r=r||0;for(var d=e.length;d>0&&e[d-1][2]>r;d--)e[d]=e[d-1];e[d]=[s,a,r]},o.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return o.d(e,{a:e}),e},o.d=(t,e)=>{for(var s in e)o.o(e,s)&&!o.o(t,s)&&Object.defineProperty(t,s,{enumerable:!0,get:e[s]})},o.f={},o.e=t=>Promise.all(Object.keys(o.f).reduce(((e,s)=>(o.f[s](t,e),e)),[])),o.u=t=>(5133===t?"user-status-modal":t)+"-"+t+".js?v="+{1110:"a5d6e6f59aa058840a1e",5133:"b8a98b12d44fe5e17b21",5455:"9cd46fa2e0b6ad206e92"}[t],o.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),o.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),s={},a="nextcloud:",o.l=(t,e,r,n)=>{if(s[t])s[t].push(e);else{var u,i;if(void 0!==r)for(var c=document.getElementsByTagName("script"),d=0;d<c.length;d++){var l=c[d];if(l.getAttribute("src")==t||l.getAttribute("data-webpack")==a+r){u=l;break}}u||(i=!0,(u=document.createElement("script")).charset="utf-8",u.timeout=120,o.nc&&u.setAttribute("nonce",o.nc),u.setAttribute("data-webpack",a+r),u.src=t),s[t]=[e];var m=(e,a)=>{u.onerror=u.onload=null,clearTimeout(p);var r=s[t];if(delete s[t],u.parentNode&&u.parentNode.removeChild(u),r&&r.forEach((t=>t(a))),e)return e(a)},p=setTimeout(m.bind(null,void 0,{type:"timeout",target:u}),12e4);u.onerror=m.bind(null,u.onerror),u.onload=m.bind(null,u.onload),i&&document.head.appendChild(u)}},o.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},o.nmd=t=>(t.paths=[],t.children||(t.children=[]),t),o.j=9953,(()=>{var t;o.g.importScripts&&(t=o.g.location+"");var e=o.g.document;if(!t&&e&&(e.currentScript&&(t=e.currentScript.src),!t)){var s=e.getElementsByTagName("script");if(s.length)for(var a=s.length-1;a>-1&&(!t||!/^http(s?):/.test(t));)t=s[a--].src}if(!t)throw new Error("Automatic publicPath is not supported in this browser");t=t.replace(/#.*$/,"").replace(/\?.*$/,"").replace(/\/[^\/]+$/,"/"),o.p=t})(),(()=>{o.b=document.baseURI||self.location.href;var t={9953:0};o.f.j=(e,s)=>{var a=o.o(t,e)?t[e]:void 0;if(0!==a)if(a)s.push(a[2]);else{var r=new Promise(((s,r)=>a=t[e]=[s,r]));s.push(a[2]=r);var n=o.p+o.u(e),u=new Error;o.l(n,(s=>{if(o.o(t,e)&&(0!==(a=t[e])&&(t[e]=void 0),a)){var r=s&&("load"===s.type?"missing":s.type),n=s&&s.target&&s.target.src;u.message="Loading chunk "+e+" failed.\n("+r+": "+n+")",u.name="ChunkLoadError",u.type=r,u.request=n,a[1](u)}}),"chunk-"+e,e)}},o.O.j=e=>0===t[e];var e=(e,s)=>{var a,r,n=s[0],u=s[1],i=s[2],c=0;if(n.some((e=>0!==t[e]))){for(a in u)o.o(u,a)&&(o.m[a]=u[a]);if(i)var d=i(o)}for(e&&e(s);c<n.length;c++)r=n[c],o.o(t,r)&&t[r]&&t[r][0](),t[r]=0;return o.O(d)},s=self.webpackChunknextcloud=self.webpackChunknextcloud||[];s.forEach(e.bind(null,0)),s.push=e.bind(null,s.push.bind(s))})(),o.nc=void 0;var u=o.O(void 0,[4208],(()=>o(19725)));u=o.O(u)})();
//# sourceMappingURL=user_status-menu.js.map?v=d40836abcbe66c3d4e58