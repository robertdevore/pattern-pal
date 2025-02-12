(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var n in r)e.o(r,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:r[n]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.blocks,r=window.wp.element,n=window.wp.components,o=window.wp.blockEditor,a=window.wp.apiFetch;var s=e.n(a);const i=window.wp.data,c=window.ReactJSXRuntime;(0,t.registerBlockType)("pattern-pal/pattern-generator",{edit:function({attributes:e,setAttributes:t,clientId:a}){const[l,p]=(0,r.useState)(!1),d=(0,o.useBlockProps)(),{createNotice:u}=(0,i.useDispatch)("core/notices");return(0,c.jsxs)("div",{...d,children:[(0,c.jsx)(n.TextControl,{label:"Describe your block pattern",value:e.prompt,onChange:e=>t({prompt:e})}),(0,c.jsx)(n.Button,{variant:"primary",onClick:function(){p(!0),s()({url:patternpalNonce.ajaxurl,method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams({action:"pattern_pal_generate_pattern",security:patternpalNonce.nonce,prompt:e.prompt})}).then((e=>{p(!1),e.success?function(e){const{removeBlock:t,insertBlocks:r}=(0,i.dispatch)("core/block-editor");t(a),r(wp.blocks.parse(e))}(e.data.pattern):e.data.message&&e.data.message.includes("No API key")?u("error","No API key found. Please add your OpenAI API key in the Pattern Pal settings.",{isDismissible:!0,actions:[{label:"Go to Settings",onClick:()=>{window.location.href=patternpalNonce.settingsUrl}}]}):u("error","Error: "+e.data.message,{isDismissible:!0})})).catch((e=>{p(!1),console.error("AJAX Error:",e),u("error","The request failed. It appears your OpenAI API key may be missing or invalid. Please add your key in the Pattern Pal settings.",{isDismissible:!0,actions:[{label:"Go to Settings",onClick:()=>{window.location.href=patternpalNonce.settingsUrl}}]})}))},disabled:l,children:l?"Generating...":"Generate"})]})},save:function({attributes:e}){return(0,c.jsx)("div",{...o.useBlockProps.save(),dangerouslySetInnerHTML:{__html:e.generatedPattern}})}})})();