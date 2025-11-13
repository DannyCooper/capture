import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import FormInspectorControls from './FormInspectorControls';
import FormBlocksPanel from './FormBlocksPanel';
import { usePluginOptions, useEmsProviders, useEmsLists } from './hooks';
import { Icon } from '@wordpress/components';
const TEMPLATE = [
	['capture/form-input', { fieldType: 'email', fieldName: 'email', placeholder: 'Enter your email address' }],
	['capture/form-submit', { text: 'Subscribe' }],
];

export default function Edit({ attributes, setAttributes, clientId }) {
	const { emsConnectionId, formId, disableCoreStyles, inputTextColor, inputBackgroundColor, inputBorder } = attributes;

	// Effect to set formId from clientId.
	useEffect(() => {
		if (clientId && ! formId ) {
			setAttributes({ formId: clientId });
		}
	}, [clientId, formId, setAttributes]);

	// Use custom hooks for data fetching.
	const { optionsFromAPI, isLoadingOptions } = usePluginOptions();
	const { emsProviders, isLoadingProviders, providersError, validateConnectionExists } = useEmsProviders();
	
	// Determine if we should fetch lists: either no connection (empty string) or valid connection.
	const shouldFetchLists = !emsConnectionId || validateConnectionExists(emsConnectionId);
	const { emsLists, isLoadingLists, listsError } = useEmsLists(emsConnectionId, shouldFetchLists);

	// Effect to validate selected connection ID when providers have finished loading.
	useEffect(() => {
		// Only validate after providers have loaded.
		if (!isLoadingProviders && emsConnectionId && !validateConnectionExists(emsConnectionId)) {
			// Clear the connection ID and selected list if the connection no longer exists.
			setAttributes({ emsConnectionId: '', selectedListId: '' });
		}
	}, [emsConnectionId, isLoadingProviders, validateConnectionExists, setAttributes]);

	// Generate scoped CSS for input colors in editor.
	useEffect(() => {
		const styleId = `capture-form-colors-${clientId}`;
		let styleEl = document.getElementById(styleId);
		
		// Remove existing style element.
		if (styleEl) {
			styleEl.remove();
		}
		
		// Generate CSS if colors or borders are set.
		if (inputTextColor || inputBackgroundColor || inputBorder) {
			let css = `.editor-styles-wrapper [data-block="${clientId}"] .capture-form__input {`;
			if (inputTextColor) {
				css += `color:${inputTextColor};`;
			}
			if (inputBackgroundColor) {
				css += `background-color:${inputBackgroundColor};`;
			}
			if (inputBorder) {
				if (inputBorder.color) {
					css += `border-color:${inputBorder.color};`;
				}
				if (inputBorder.style) {
					css += `border-style:${inputBorder.style};`;
				}
				if (inputBorder.width) {
					css += `border-width:${inputBorder.width};`;
				}
			}
			css += '}';
			
			// Inject style into editor.
			styleEl = document.createElement('style');
			styleEl.id = styleId;
			styleEl.innerHTML = css;
			document.head.appendChild(styleEl);
		}
		
		// Cleanup on unmount.
		return () => {
			const el = document.getElementById(styleId);
			if (el) {
				el.remove();
			}
		};
	}, [clientId, inputTextColor, inputBackgroundColor, inputBorder]);

	const blockProps = useBlockProps({
		className: `capture-form-editor-container ${disableCoreStyles ? 'capture-form--no-core-styles' : ''}`,
	});

	const innerBlocksProps = useInnerBlocksProps({}, {
		template: TEMPLATE,
		templateLock: false,
	});

	return (
		<>
			<FormBlocksPanel clientId={clientId} />
			<FormInspectorControls
				attributes={attributes}
				setAttributes={setAttributes}
				emsProviders={emsProviders}
				isLoadingProviders={isLoadingProviders}
				providersError={providersError}
				emsLists={emsLists}
				isLoadingLists={isLoadingLists}
				listsError={listsError}
				optionsFromAPI={optionsFromAPI}
				clientId={clientId}
			/>
			{emsConnectionId && !attributes.selectedListId && (
				<div className="capture-form__error">
					{__('Please select a list for the selected EMS connection.', 'capture')}
				</div>
			)}
			<div {...blockProps}>
				<div className="capture-form-edit-tab">
					<Icon icon="admin-generic" size={16} /> {__('Form Settings', 'capture')}
				</div>
				<div {...innerBlocksProps} />
			</div>
		</>
	);
} 