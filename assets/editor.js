(function(wp){
'use strict';const el=wp.element.createElement,{useState,useEffect}=wp.element,{__}=wp.i18n;
wp.blocks.registerBlockType('qentrah/language-switcher',{apiVersion:3,title:__('Language switcher','qentrah-languages'),icon:'translation',category:'widgets',edit:()=>el('div',wp.blockEditor.useBlockProps(),__('Qentrah language switcher — published translations appear here on the site.','qentrah-languages')),save:()=>null});
})(window.wp);
