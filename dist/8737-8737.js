"use strict";(self.webpackChunknextcloud=self.webpackChunknextcloud||[]).push([[8737],{83418:(t,e,a)=>{a.d(e,{A:()=>i});var n=a(71354),s=a.n(n),p=a(76314),o=a.n(p)()(s());o.push([t.id,".app-discover-app[data-v-61ce59e2]{width:100% !important}.app-discover-app[data-v-61ce59e2]:hover{background:var(--color-background-hover);border-radius:var(--border-radius-rounded)}.app-discover-app__skeleton[data-v-61ce59e2]{display:flex;flex-direction:column;gap:8px;padding:30px}.app-discover-app__skeleton[data-v-61ce59e2]>:first-child{height:50%;min-height:130px}.app-discover-app__skeleton[data-v-61ce59e2]>:nth-child(2){width:50px}.app-discover-app__skeleton[data-v-61ce59e2]>:nth-child(5){height:20px;width:100px}.app-discover-app__skeleton[data-v-61ce59e2]>:not(:first-child){border-radius:4px}.skeleton-element[data-v-61ce59e2]{min-height:var(--default-font-size, 15px);background:linear-gradient(90deg, var(--color-background-dark), var(--color-background-darker), var(--color-background-dark));background-size:400% 400%;animation:gradient-61ce59e2 6s ease infinite}@keyframes gradient-61ce59e2{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}","",{version:3,sources:["webpack://./apps/settings/src/components/AppStoreDiscover/AppType.vue"],names:[],mappings:"AACA,mCACC,qBAAA,CAEA,yCACC,wCAAA,CACA,0CAAA,CAGD,6CACC,YAAA,CACA,qBAAA,CACA,OAAA,CAEA,YAAA,CAEA,0DACC,UAAA,CACA,gBAAA,CAGD,2DACC,UAAA,CAGD,2DACC,WAAA,CACA,WAAA,CAGD,gEACC,iBAAA,CAKH,mCACC,yCAAA,CAEA,6HAAA,CACA,yBAAA,CACA,4CAAA,CAGD,6BACC,GACC,0BAAA,CAED,IACC,4BAAA,CAED,KACC,0BAAA,CAAA",sourcesContent:["\n.app-discover-app {\n\twidth: 100% !important; // full with of the showcase item\n\n\t&:hover {\n\t\tbackground: var(--color-background-hover);\n\t\tborder-radius: var(--border-radius-rounded);\n\t}\n\n\t&__skeleton {\n\t\tdisplay: flex;\n\t\tflex-direction: column;\n\t\tgap: 8px;\n\n\t\tpadding: 30px; // Same as AppItem\n\n\t\t> :first-child {\n\t\t\theight: 50%;\n\t\t\tmin-height: 130px;\n\t\t}\n\n\t\t> :nth-child(2) {\n\t\t\twidth: 50px;\n\t\t}\n\n\t\t> :nth-child(5) {\n\t\t\theight: 20px;\n\t\t\twidth: 100px;\n\t\t}\n\n\t\t> :not(:first-child) {\n\t\t\tborder-radius: 4px;\n\t\t}\n\t}\n}\n\n.skeleton-element {\n\tmin-height: var(--default-font-size, 15px);\n\n\tbackground: linear-gradient(90deg, var(--color-background-dark), var(--color-background-darker), var(--color-background-dark));\n\tbackground-size: 400% 400%;\n\tanimation: gradient 6s ease infinite;\n}\n\n@keyframes gradient {\n\t0% {\n\t\tbackground-position: 0% 50%;\n\t}\n\t50% {\n\t\tbackground-position: 100% 50%;\n\t}\n\t100% {\n\t\tbackground-position: 0% 50%;\n\t}\n}\n"],sourceRoot:""}]);const i=o},81189:(t,e,a)=>{a.d(e,{A:()=>i});var n=a(71354),s=a.n(n),p=a(76314),o=a.n(p)()(s());o.push([t.id,"h3[data-v-4fd06dba]{font-size:24px;font-weight:600;margin-block:0 1em}.app-discover-showcase__list[data-v-4fd06dba]{list-style:none;display:flex;flex-wrap:wrap;gap:calc(var(--default-clickable-area, 44px)/2)}.app-discover-showcase__item[data-v-4fd06dba]{display:flex;align-items:stretch;position:relative;width:calc(33% - var(--default-clickable-area, 44px)/2)}.app-discover-showcase--small .app-discover-showcase__item[data-v-4fd06dba]{width:calc(50% - var(--default-clickable-area, 44px)/2)}.app-discover-showcase--extra-small .app-discover-showcase__item[data-v-4fd06dba]{width:100%}","",{version:3,sources:["webpack://./apps/settings/src/components/AppStoreDiscover/ShowcaseType.vue"],names:[],mappings:"AAGA,oBACC,cAAA,CACA,eAAA,CACA,kBAAA,CAIA,8CACC,eAAA,CAEA,YAAA,CACA,cAAA,CACA,+CAdS,CAiBV,8CACC,YAAA,CACA,mBAAA,CAEA,iBAAA,CACA,uDAAA,CAKD,4EACC,uDAAA,CAKD,kFACC,UAAA",sourcesContent:["\n$item-gap: calc(var(--default-clickable-area, 44px) / 2);\n\nh3 {\n\tfont-size: 24px;\n\tfont-weight: 600;\n\tmargin-block: 0 1em;\n}\n\n.app-discover-showcase {\n\t&__list {\n\t\tlist-style: none;\n\n\t\tdisplay: flex;\n\t\tflex-wrap: wrap;\n\t\tgap: $item-gap;\n\t}\n\n\t&__item {\n\t\tdisplay: flex;\n\t\talign-items: stretch;\n\n\t\tposition: relative;\n\t\twidth: calc(33% - $item-gap);\n\t}\n}\n\n.app-discover-showcase--small {\n\t.app-discover-showcase__item {\n\t\twidth: calc(50% - $item-gap);\n\t}\n}\n\n.app-discover-showcase--extra-small {\n\t.app-discover-showcase__item {\n\t\twidth: 100%;\n\t}\n}\n"],sourceRoot:""}]);const i=o},98737:(t,e,a)=>{a.r(e),a.d(e,{default:()=>I});var n=a(53334),s=a(13073),p=a(85471),o=a(77796),i=a(74640),r=a(846),A=a(14213);const l=(0,p.pM)({__name:"AppType",props:{modelValue:null},setup(t){const e=t,a=(0,r.T)(),n=(0,p.EW)((()=>a.getAppById(e.modelValue.appId))),s=(0,p.EW)((()=>e.modelValue.appId?"https://apps.nextcloud.com/apps/".concat(e.modelValue.appId):"#"));return{__sfc:!0,props:e,store:a,app:n,appStoreLink:s,AppItem:A.A}}});var d=a(85072),c=a.n(d),C=a(97825),h=a.n(C),u=a(77659),v=a.n(u),m=a(55056),g=a.n(m),k=a(10540),_=a.n(k),f=a(41113),b=a.n(f),w=a(83418),x={};x.styleTagTransform=b(),x.setAttributes=g(),x.insert=v().bind(null,"head"),x.domAPI=h(),x.insertStyleElement=_(),c()(w.A,x),w.A&&w.A.locals&&w.A.locals;var y=a(14486);const E=(0,y.A)(l,(function(){var t=this,e=t._self._c,a=t._self._setupProxy;return a.app?e(a.AppItem,{staticClass:"app-discover-app",attrs:{app:a.app,category:"discover",inline:"","list-view":!1}}):e("a",{staticClass:"app-discover-app app-discover-app__skeleton",attrs:{href:a.appStoreLink,target:"_blank",title:t.modelValue.appId,rel:"noopener noreferrer"}},[e("span",{staticClass:"skeleton-element"}),t._v(" "),e("span",{staticClass:"skeleton-element"}),t._v(" "),e("span",{staticClass:"skeleton-element"}),t._v(" "),e("span",{staticClass:"skeleton-element"}),t._v(" "),e("span",{staticClass:"skeleton-element"})])}),[],!1,null,"61ce59e2",null).exports;var D=a(75390);const B=(0,p.pM)({name:"ShowcaseType",components:{AppType:E,PostType:D.default},props:{...o.K,content:{type:Array,required:!0}},setup(t){const e=(0,i.O)((0,p.EW)((()=>t.headline))),a=(0,p.KR)(),{width:o}=(0,s.Lhy)(a),r=(0,p.EW)((()=>o.value<768)),A=(0,p.EW)((()=>o.value<512));return{t:n.Tl,container:a,isSmallWidth:r,isExtraSmallWidth:A,translatedHeadline:e}}});var T=a(81189),S={};S.styleTagTransform=b(),S.setAttributes=g(),S.insert=v().bind(null,"head"),S.domAPI=h(),S.insertStyleElement=_(),c()(T.A,S),T.A&&T.A.locals&&T.A.locals;const I=(0,y.A)(B,(function(){var t=this,e=t._self._c;return t._self._setupProxy,e("section",{ref:"container",staticClass:"app-discover-showcase",class:{"app-discover-showcase--small":t.isSmallWidth,"app-discover-showcase--extra-small":t.isExtraSmallWidth}},[t.translatedHeadline?e("h3",[t._v("\n\t\t"+t._s(t.translatedHeadline)+"\n\t")]):t._e(),t._v(" "),e("ul",{staticClass:"app-discover-showcase__list"},t._l(t.content,(function(a,n){var s;return e("li",{key:null!==(s=a.id)&&void 0!==s?s:n,staticClass:"app-discover-showcase__item"},["post"===a.type?e("PostType",t._b({attrs:{inline:""}},"PostType",a,!1)):"app"===a.type?e("AppType",{attrs:{"model-value":a}}):t._e()],1)})),0)])}),[],!1,null,"4fd06dba",null).exports}}]);
//# sourceMappingURL=8737-8737.js.map?v=41f38430e0c0380c362b