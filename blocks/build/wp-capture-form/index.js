/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/wp-capture-form/FormInspectorControls.js":
/*!******************************************************!*\
  !*** ./src/wp-capture-form/FormInspectorControls.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormInspectorControls)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




function FormInspectorControls({
  attributes,
  setAttributes,
  emsProviders,
  isLoadingProviders,
  providersError,
  emsLists,
  isLoadingLists,
  listsError,
  optionsFromAPI
}) {
  const {
    emsConnectionId,
    selectedListId,
    formId,
    formLayout,
    successMessage,
    fieldGap,
    showNameField,
    showPrivacyPolicy,
    buttonColor,
    buttonTextColor,
    buttonHoverColor,
    disableCoreStyles
  } = attributes;

  // Handle provider change - clear both connection and selected list
  const handleProviderChange = newConnectionId => {
    setAttributes({
      emsConnectionId: newConnectionId,
      selectedListId: ''
    });
  };

  // Define the color settings for the panel
  const colorSettings = [{
    value: buttonTextColor,
    onChange: value => setAttributes({
      buttonTextColor: value
    }),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Text Color', 'capture')
  }, {
    value: buttonColor,
    onChange: value => setAttributes({
      buttonColor: value
    }),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Background Color', 'capture')
  }, {
    value: buttonHoverColor,
    onChange: value => setAttributes({
      buttonHoverColor: value
    }),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Background Hover Color', 'capture')
  }];
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Settings', 'capture'),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Name Field', 'capture'),
          checked: showNameField,
          onChange: newShowNameField => setAttributes({
            showNameField: newShowNameField
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Privacy Policy', 'capture'),
          checked: showPrivacyPolicy,
          onChange: newShowPrivacyPolicy => setAttributes({
            showPrivacyPolicy: newShowPrivacyPolicy
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form ID', 'capture'),
          value: formId || '',
          onChange: newFormId => setAttributes({
            formId: newFormId
          }),
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Changing this allows you to track the success of this form.', 'capture')
        }), isLoadingProviders ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {}) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select EMS Provider', 'capture'),
          value: emsConnectionId,
          options: emsProviders,
          onChange: handleProviderChange
        }), !emsConnectionId && (emsProviders.length <= 1 || !providersError) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("div", {
          className: "capture-local-notice",
          style: {
            padding: '10px',
            backgroundColor: '#f0f0f0',
            border: '1px solid #ddd',
            borderRadius: '4px',
            marginTop: '-8px',
            marginBottom: '16px'
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("p", {
            style: {
              margin: 0
            },
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('ℹ️ No EMS provider selected. Subscribers will be stored locally in your WordPress database.', 'capture')
          })
        }), emsConnectionId && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
          children: isLoadingLists ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {}) : listsError ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Placeholder, {
            icon: "warning",
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('EMS Lists', 'capture'),
            children: listsError
          }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select List', 'capture'),
            value: selectedListId,
            options: emsLists,
            onChange: newListId => setAttributes({
              selectedListId: newListId
            }),
            disabled: emsLists.length <= 1 && !listsError && !isLoadingLists,
            help: emsLists.length <= 1 && !listsError && !isLoadingLists ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No lists available for this provider or select a provider.', 'capture') : ''
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextareaControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Success Message', 'capture'),
          value: successMessage || optionsFromAPI?.default_success_message,
          onChange: newSuccessMessage => setAttributes({
            successMessage: newSuccessMessage
          })
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      group: "advanced",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Disable Core Styles', 'capture'),
        checked: disableCoreStyles,
        onChange: newDisableCoreStyles => setAttributes({
          disableCoreStyles: newDisableCoreStyles
        })
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      group: "styles",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Layout', 'capture'),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Style', 'capture'),
          value: formLayout,
          options: [{
            label: 'Stacked',
            value: 'stacked'
          }, {
            label: 'Inline',
            value: 'inline'
          }],
          onChange: newStyle => setAttributes({
            formLayout: newStyle
          }),
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Stack the form fields or display them inline.', 'capture')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RangeControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Field Gap (rem)', 'capture'),
          value: fieldGap,
          onChange: newFieldGap => setAttributes({
            fieldGap: newFieldGap
          }),
          min: 0,
          max: 5,
          step: 0.2,
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Set the gap between form fields', 'capture')
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.PanelColorSettings, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Button Color Settings', 'capture'),
        initialOpen: true,
        colorSettings: colorSettings
      })]
    })]
  });
}

/***/ }),

/***/ "./src/wp-capture-form/FormPreview.js":
/*!********************************************!*\
  !*** ./src/wp-capture-form/FormPreview.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FormPreview)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);



function FormPreview({
  attributes,
  optionsFromAPI
}) {
  const {
    emsConnectionId,
    selectedListId,
    formLayout,
    fieldGap,
    showNameField,
    showPrivacyPolicy,
    buttonColor,
    buttonTextColor,
    disableCoreStyles
  } = attributes;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)({
    style: {
      gap: `${fieldGap}rem`,
      display: 'flex',
      flexDirection: formLayout === 'inline' ? 'row' : 'column'
    },
    className: `capture-form capture-form--${formLayout} ${disableCoreStyles ? 'capture-form--no-core-styles' : ''}`
  });
  const buttonStyles = {
    backgroundColor: buttonColor,
    color: buttonTextColor,
    border: 'none',
    cursor: 'pointer'
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    ...blockProps,
    children: [emsConnectionId && !selectedListId && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "capture-form__error",
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Please select a list for the selected EMS connection.', 'capture')
    }), showNameField && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
      type: "text",
      id: blockProps.id + '-name',
      className: "capture-form__input",
      placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('First name', 'capture'),
      readOnly: true
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
      type: "email",
      id: blockProps.id + '-email',
      className: "capture-form__input",
      placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Email address', 'capture'),
      readOnly: true
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
      type: "button",
      className: "capture-form__button",
      style: buttonStyles,
      onClick: event => event.preventDefault(),
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Subscribe', 'capture')
    }), showPrivacyPolicy && optionsFromAPI?.privacy_policy_text && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
      dangerouslySetInnerHTML: {
        __html: optionsFromAPI.privacy_policy_text
      },
      className: "capture-form__privacy-policy"
    })]
  });
}

/***/ }),

/***/ "./src/wp-capture-form/block.json":
/*!****************************************!*\
  !*** ./src/wp-capture-form/block.json ***!
  \****************************************/
/***/ ((module) => {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"capture/form","version":"0.1.0","title":"Capture Form","category":"widgets","icon":"capture","description":"A simple email capture form block with a button.","attributes":{"emsConnectionId":{"type":"string","default":""},"selectedListId":{"type":"string","default":""},"formId":{"type":"string","default":""},"formLayout":{"type":"string","default":"stacked"},"successMessage":{"type":"string","default":""},"fieldGap":{"type":"number","default":"1"},"showNameField":{"type":"boolean","default":false},"showPrivacyPolicy":{"type":"boolean","default":false},"disableCoreStyles":{"type":"boolean","default":false},"buttonText":{"type":"string","default":"Subscribe"},"buttonColor":{"type":"string","default":""},"buttonTextColor":{"type":"string","default":""},"buttonHoverColor":{"type":"string","default":""}},"supports":{"html":true,"multiple":true,"background":{"backgroundImage":true,"backgroundSize":true},"spacing":{"margin":true,"padding":true}},"editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"capture-form-frontend","render":"file:./render.php"}');

/***/ }),

/***/ "./src/wp-capture-form/edit.js":
/*!*************************************!*\
  !*** ./src/wp-capture-form/edit.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _FormInspectorControls__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FormInspectorControls */ "./src/wp-capture-form/FormInspectorControls.js");
/* harmony import */ var _FormPreview__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./FormPreview */ "./src/wp-capture-form/FormPreview.js");
/* harmony import */ var _hooks__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./hooks */ "./src/wp-capture-form/hooks/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






function Edit({
  attributes,
  setAttributes,
  clientId
}) {
  const {
    emsConnectionId,
    formId
  } = attributes;

  // Effect to set formId from clientId
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    if (clientId && !formId) {
      setAttributes({
        formId: clientId
      });
    }
  }, [clientId, formId, setAttributes]);

  // Use custom hooks for data fetching
  const {
    optionsFromAPI,
    isLoadingOptions
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_4__.usePluginOptions)();
  const {
    emsProviders,
    isLoadingProviders,
    providersError,
    validateConnectionExists
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_4__.useEmsProviders)();

  // Determine if we should fetch lists: either no connection (empty string) or valid connection
  const shouldFetchLists = !emsConnectionId || validateConnectionExists(emsConnectionId);
  const {
    emsLists,
    isLoadingLists,
    listsError
  } = (0,_hooks__WEBPACK_IMPORTED_MODULE_4__.useEmsLists)(emsConnectionId, shouldFetchLists);

  // Effect to validate selected connection ID when providers have finished loading
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    // Only validate after providers have loaded
    if (!isLoadingProviders && emsConnectionId && !validateConnectionExists(emsConnectionId)) {
      // Clear the connection ID and selected list if the connection no longer exists
      setAttributes({
        emsConnectionId: '',
        selectedListId: ''
      });
    }
  }, [emsConnectionId, isLoadingProviders, validateConnectionExists, setAttributes]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_FormInspectorControls__WEBPACK_IMPORTED_MODULE_2__["default"], {
      attributes: attributes,
      setAttributes: setAttributes,
      emsProviders: emsProviders,
      isLoadingProviders: isLoadingProviders,
      providersError: providersError,
      emsLists: emsLists,
      isLoadingLists: isLoadingLists,
      listsError: listsError,
      optionsFromAPI: optionsFromAPI
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_FormPreview__WEBPACK_IMPORTED_MODULE_3__["default"], {
      attributes: attributes,
      optionsFromAPI: optionsFromAPI
    })]
  });
}

/***/ }),

/***/ "./src/wp-capture-form/editor.scss":
/*!*****************************************!*\
  !*** ./src/wp-capture-form/editor.scss ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/wp-capture-form/hooks/index.js":
/*!********************************************!*\
  !*** ./src/wp-capture-form/hooks/index.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useEmsLists: () => (/* reexport safe */ _useEmsLists__WEBPACK_IMPORTED_MODULE_2__.useEmsLists),
/* harmony export */   useEmsProviders: () => (/* reexport safe */ _useEmsProviders__WEBPACK_IMPORTED_MODULE_1__.useEmsProviders),
/* harmony export */   usePluginOptions: () => (/* reexport safe */ _usePluginOptions__WEBPACK_IMPORTED_MODULE_0__.usePluginOptions)
/* harmony export */ });
/* harmony import */ var _usePluginOptions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./usePluginOptions */ "./src/wp-capture-form/hooks/usePluginOptions.js");
/* harmony import */ var _useEmsProviders__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./useEmsProviders */ "./src/wp-capture-form/hooks/useEmsProviders.js");
/* harmony import */ var _useEmsLists__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./useEmsLists */ "./src/wp-capture-form/hooks/useEmsLists.js");




/***/ }),

/***/ "./src/wp-capture-form/hooks/useEmsLists.js":
/*!**************************************************!*\
  !*** ./src/wp-capture-form/hooks/useEmsLists.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useEmsLists: () => (/* binding */ useEmsLists)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



function useEmsLists(emsConnectionId, shouldFetch = true) {
  const [emsLists, setEmsLists] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [isLoadingLists, setIsLoadingLists] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [listsError, setListsError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);

  // Effect to fetch EMS Lists when emsConnectionId changes
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    if (emsConnectionId && shouldFetch) {
      setIsLoadingLists(true);
      setListsError(null);
      setEmsLists([{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Loading lists...', 'capture'),
        value: ''
      }]);
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/capture/v1/get-ems-lists/?ems_id=${emsConnectionId}`
      }).then(response => {
        if (response.success && response.lists) {
          const selectOptions = response.lists.map(list => ({
            label: list.label,
            value: list.value
          }));
          setEmsLists([{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a List', 'capture'),
            value: ''
          }, ...selectOptions]);
          if (response.lists.length === 0 && response.message) {
            setListsError(response.message);
          }
        } else {
          setListsError(response.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Failed to load lists for the selected provider.', 'capture'));
          setEmsLists([{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a List', 'capture'),
            value: ''
          }]);
        }
        setIsLoadingLists(false);
      }).catch(err => {
        console.error('Error fetching EMS lists:', err);
        setListsError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('An error occurred while fetching lists.', 'capture'));
        setEmsLists([{
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a List', 'capture'),
          value: ''
        }]);
        setIsLoadingLists(false);
      });
    } else {
      setEmsLists([{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a provider first', 'capture'),
        value: ''
      }]);
      setIsLoadingLists(false);
      setListsError(null);
    }
  }, [emsConnectionId, shouldFetch]);
  return {
    emsLists,
    isLoadingLists,
    listsError
  };
}

/***/ }),

/***/ "./src/wp-capture-form/hooks/useEmsProviders.js":
/*!******************************************************!*\
  !*** ./src/wp-capture-form/hooks/useEmsProviders.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useEmsProviders: () => (/* binding */ useEmsProviders)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



function useEmsProviders() {
  const [emsProviders, setEmsProviders] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [isLoadingProviders, setIsLoadingProviders] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [providersError, setProvidersError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);

  // Effect to fetch EMS Providers on mount
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    setIsLoadingProviders(true);
    setProvidersError(null);
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/capture/v1/get-ems-providers/'
    }).then(response => {
      if (response.success && response.providers) {
        const selectOptions = response.providers.map(provider => ({
          label: provider.label,
          value: provider.value
        }));
        setEmsProviders([{
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an EMS Provider', 'capture'),
          value: ''
        }, ...selectOptions]);
        if (response.providers.length === 0 && response.message) {
          setProvidersError(response.message);
        }
      } else {
        setProvidersError(response.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Failed to load EMS providers.', 'capture'));
        setEmsProviders([{
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an EMS Provider', 'capture'),
          value: ''
        }]);
      }
      setIsLoadingProviders(false);
    }).catch(err => {
      console.error('Error fetching EMS providers:', err);
      setProvidersError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('An error occurred while fetching EMS providers.', 'capture'));
      setEmsProviders([{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an EMS Provider', 'capture'),
        value: ''
      }]);
      setIsLoadingProviders(false);
    });
  }, []);

  // Function to validate if a connection ID exists in the providers
  const validateConnectionExists = connectionId => {
    // Return false for empty connections
    if (!connectionId) {
      return false;
    }
    // Check if the connection exists in the providers list
    return emsProviders.some(provider => provider.value === connectionId);
  };
  return {
    emsProviders,
    isLoadingProviders,
    providersError,
    validateConnectionExists
  };
}

/***/ }),

/***/ "./src/wp-capture-form/hooks/usePluginOptions.js":
/*!*******************************************************!*\
  !*** ./src/wp-capture-form/hooks/usePluginOptions.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   usePluginOptions: () => (/* binding */ usePluginOptions)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__);


function usePluginOptions() {
  const [optionsFromAPI, setOptionsFromAPI] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [isLoadingOptions, setIsLoadingOptions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!isLoadingOptions && !optionsFromAPI) {
      setIsLoadingOptions(true);
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
        path: '/capture/v1/get-options/'
      }).then(response => {
        if (response.success && response.options) {
          setOptionsFromAPI(response.options);
        }
        setIsLoadingOptions(false);
      }).catch(err => {
        console.error('Error fetching options:', err);
        setIsLoadingOptions(false);
      });
    }
  }, [isLoadingOptions, optionsFromAPI]);
  console.log('Options from API:', optionsFromAPI);
  return {
    optionsFromAPI,
    isLoadingOptions
  };
}

/***/ }),

/***/ "./src/wp-capture-form/index.js":
/*!**************************************!*\
  !*** ./src/wp-capture-form/index.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/wp-capture-form/edit.js");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./editor.scss */ "./src/wp-capture-form/editor.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./style.scss */ "./src/wp-capture-form/style.scss");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./block.json */ "./src/wp-capture-form/block.json");






(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_5__.name, {
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"]
});

/***/ }),

/***/ "./src/wp-capture-form/style.scss":
/*!****************************************!*\
  !*** ./src/wp-capture-form/style.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"wp-capture-form/index": 0,
/******/ 			"wp-capture-form/style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkcapture_plugin_blocks"] = globalThis["webpackChunkcapture_plugin_blocks"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["wp-capture-form/style-index"], () => (__webpack_require__("./src/wp-capture-form/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map