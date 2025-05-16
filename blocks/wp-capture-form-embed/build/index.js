/******/ (() => { // webpackBootstrap
/*!*****************************************************!*\
  !*** ./blocks/wp-capture-form-embed/build/index.js ***!
  \*****************************************************/
/******/(() => {
  // webpackBootstrap
  /*!*****************************************************!*\
    !*** ./blocks/wp-capture-form-embed/build/index.js ***!
    \*****************************************************/
  /******/
  (() => {
    // webpackBootstrap
    /******/
    "use strict";

    /******/
    var __webpack_modules__ = {
      /***/"./src/components/FormPreview.js": (
      /*!***************************************!*\
        !*** ./src/components/FormPreview.js ***!
        \***************************************/
      /***/
      (__unused_webpack_module, __nested_webpack_exports__, __nested_webpack_require_361__) => {
        __nested_webpack_require_361__.r(__nested_webpack_exports__);
        /* harmony export */
        __nested_webpack_require_361__.d(__nested_webpack_exports__, {
          /* harmony export */"default": () => (/* binding */FormPreview)
          /* harmony export */
        });
        /* harmony import */
        var react__WEBPACK_IMPORTED_MODULE_0__ = __nested_webpack_require_361__(/*! react */"react");
        /* harmony import */
        var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__nested_webpack_require_361__.n(react__WEBPACK_IMPORTED_MODULE_0__);
        /* harmony import */
        var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __nested_webpack_require_361__(/*! @wordpress/i18n */"@wordpress/i18n");
        /* harmony import */
        var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__nested_webpack_require_361__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
        /* harmony import */
        var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __nested_webpack_require_361__(/*! @wordpress/data */"@wordpress/data");
        /* harmony import */
        var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__nested_webpack_require_361__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
        /* harmony import */
        var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __nested_webpack_require_361__(/*! @wordpress/components */"@wordpress/components");
        /* harmony import */
        var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__nested_webpack_require_361__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);

        // Debug logging
        const debug = (message, data) => {
          if (true) {
            console.log(`[Form Preview] ${message}`, data);
          }
        };
        function FormPreview({
          formId
        }) {
          debug('FormPreview props:', {
            formId
          });
          const form = (0, _wordpress_data__WEBPACK_IMPORTED_MODULE_2__.useSelect)(select => {
            const result = select('core').getEntityRecord('postType', 'capture_form', formId);
            debug('Fetched form:', result);
            return result;
          }, [formId]);
          if (!form) {
            debug('Form is loading...');
            return (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
              className: "form-preview-loading"
            }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Spinner, null));
          }
          debug('Rendering form:', form);
          return (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
            className: "form-preview"
          }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
            className: "form-preview-content"
          }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
            dangerouslySetInnerHTML: {
              __html: form.content.rendered
            }
          })));
        }

        /***/
      }),
      /***/"@wordpress/block-editor": (
      /*!*************************************!*\
        !*** external ["wp","blockEditor"] ***!
        \*************************************/
      /***/
      module => {
        module.exports = window["wp"]["blockEditor"];

        /***/
      }),
      /***/"@wordpress/blocks": (
      /*!********************************!*\
        !*** external ["wp","blocks"] ***!
        \********************************/
      /***/
      module => {
        module.exports = window["wp"]["blocks"];

        /***/
      }),
      /***/"@wordpress/components": (
      /*!************************************!*\
        !*** external ["wp","components"] ***!
        \************************************/
      /***/
      module => {
        module.exports = window["wp"]["components"];

        /***/
      }),
      /***/"@wordpress/data": (
      /*!******************************!*\
        !*** external ["wp","data"] ***!
        \******************************/
      /***/
      module => {
        module.exports = window["wp"]["data"];

        /***/
      }),
      /***/"@wordpress/i18n": (
      /*!******************************!*\
        !*** external ["wp","i18n"] ***!
        \******************************/
      /***/
      module => {
        module.exports = window["wp"]["i18n"];

        /***/
      }),
      /***/"react": (
      /*!************************!*\
        !*** external "React" ***!
        \************************/
      /***/
      module => {
        module.exports = window["React"];

        /***/
      })

      /******/
    };
    /************************************************************************/
    /******/ // The module cache
    /******/
    var __webpack_module_cache__ = {};
    /******/
    /******/ // The require function
    /******/
    function __nested_webpack_require_4978__(moduleId) {
      /******/ // Check if module is in cache
      /******/var cachedModule = __webpack_module_cache__[moduleId];
      /******/
      if (cachedModule !== undefined) {
        /******/return cachedModule.exports;
        /******/
      }
      /******/ // Create a new module (and put it into the cache)
      /******/
      var module = __webpack_module_cache__[moduleId] = {
        /******/ // no module.id needed
        /******/ // no module.loaded needed
        /******/exports: {}
        /******/
      };
      /******/
      /******/ // Execute the module function
      /******/
      __webpack_modules__[moduleId](module, module.exports, __nested_webpack_require_4978__);
      /******/
      /******/ // Return the exports of the module
      /******/
      return module.exports;
      /******/
    }
    /******/
    /************************************************************************/
    /******/ /* webpack/runtime/compat get default export */
    /******/
    (() => {
      /******/ // getDefaultExport function for compatibility with non-harmony modules
      /******/__nested_webpack_require_4978__.n = module => {
        /******/var getter = module && module.__esModule ? /******/() => module['default'] : /******/() => module;
        /******/
        __nested_webpack_require_4978__.d(getter, {
          a: getter
        });
        /******/
        return getter;
        /******/
      };
      /******/
    })();
    /******/
    /******/ /* webpack/runtime/define property getters */
    /******/
    (() => {
      /******/ // define getter functions for harmony exports
      /******/__nested_webpack_require_4978__.d = (exports, definition) => {
        /******/for (var key in definition) {
          /******/if (__nested_webpack_require_4978__.o(definition, key) && !__nested_webpack_require_4978__.o(exports, key)) {
            /******/Object.defineProperty(exports, key, {
              enumerable: true,
              get: definition[key]
            });
            /******/
          }
          /******/
        }
        /******/
      };
      /******/
    })();
    /******/
    /******/ /* webpack/runtime/hasOwnProperty shorthand */
    /******/
    (() => {
      /******/__nested_webpack_require_4978__.o = (obj, prop) => Object.prototype.hasOwnProperty.call(obj, prop);
      /******/
    })();
    /******/
    /******/ /* webpack/runtime/make namespace object */
    /******/
    (() => {
      /******/ // define __esModule on exports
      /******/__nested_webpack_require_4978__.r = exports => {
        /******/if (typeof Symbol !== 'undefined' && Symbol.toStringTag) {
          /******/Object.defineProperty(exports, Symbol.toStringTag, {
            value: 'Module'
          });
          /******/
        }
        /******/
        Object.defineProperty(exports, '__esModule', {
          value: true
        });
        /******/
      };
      /******/
    })();
    /******/
    /************************************************************************/
    var __nested_webpack_exports__ = {};
    // This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
    (() => {
      /*!**********************!*\
        !*** ./src/index.js ***!
        \**********************/
      __nested_webpack_require_4978__.r(__nested_webpack_exports__);
      /* harmony import */
      var react__WEBPACK_IMPORTED_MODULE_0__ = __nested_webpack_require_4978__(/*! react */"react");
      /* harmony import */
      var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__nested_webpack_require_4978__.n(react__WEBPACK_IMPORTED_MODULE_0__);
      /* harmony import */
      var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __nested_webpack_require_4978__(/*! @wordpress/blocks */"@wordpress/blocks");
      /* harmony import */
      var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__nested_webpack_require_4978__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
      /* harmony import */
      var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __nested_webpack_require_4978__(/*! @wordpress/data */"@wordpress/data");
      /* harmony import */
      var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__nested_webpack_require_4978__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
      /* harmony import */
      var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __nested_webpack_require_4978__(/*! @wordpress/i18n */"@wordpress/i18n");
      /* harmony import */
      var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__nested_webpack_require_4978__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
      /* harmony import */
      var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __nested_webpack_require_4978__(/*! @wordpress/components */"@wordpress/components");
      /* harmony import */
      var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__nested_webpack_require_4978__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
      /* harmony import */
      var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_5__ = __nested_webpack_require_4978__(/*! @wordpress/block-editor */"@wordpress/block-editor");
      /* harmony import */
      var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__nested_webpack_require_4978__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_5__);
      /* harmony import */
      var _components_FormPreview__WEBPACK_IMPORTED_MODULE_6__ = __nested_webpack_require_4978__(/*! ./components/FormPreview */"./src/components/FormPreview.js");

      // Debug logging
      const debug = (message, data) => {
        if (true) {
          console.log(`[Form Embed Block] ${message}`, data);
        }
      };
      (0, _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('capture/form-embed', {
        edit: function Edit({
          attributes,
          setAttributes
        }) {
          const blockProps = (0, _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_5__.useBlockProps)();
          const {
            formId
          } = attributes;
          debug('Block attributes:', attributes);
          const forms = (0, _wordpress_data__WEBPACK_IMPORTED_MODULE_2__.useSelect)(select => {
            const result = select('core').getEntityRecords('postType', 'capture_form', {
              per_page: -1,
              _embed: true,
              orderby: 'title',
              order: 'asc'
            });
            debug('Fetched forms:', result);
            return result;
          }, []);
          if (!forms) {
            debug('Forms are loading...');
            return (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
              ...blockProps
            }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Placeholder, null, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Spinner, null)));
          }
          const formOptions = forms.map(form => ({
            label: form.title.rendered,
            value: form.id
          }));
          debug('Form options:', formOptions);
          return (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_5__.InspectorControls, null, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
            title: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Form Settings', 'capture')
          }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelRow, null, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
            label: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select Form', 'capture'),
            value: formId,
            options: [{
              label: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select a form...', 'capture'),
              value: ''
            }, ...formOptions],
            onChange: value => {
              debug('Form selected:', value);
              setAttributes({
                formId: value ? parseInt(value) : null
              });
            }
          })))), (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
            ...blockProps
          }, !formId ? (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Placeholder, {
            label: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Form Embed', 'capture'),
            instructions: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select a form to embed.', 'capture')
          }, (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
            value: formId,
            options: [{
              label: (0, _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select a form...', 'capture'),
              value: ''
            }, ...formOptions],
            onChange: value => {
              debug('Form selected:', value);
              setAttributes({
                formId: value ? parseInt(value) : null
              });
            }
          })) : (0, react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_FormPreview__WEBPACK_IMPORTED_MODULE_6__["default"], {
            formId: formId
          })));
        }
      });
    })();

    /******/
  })();
  /******/
})();
/******/ })()
;
//# sourceMappingURL=index.js.map