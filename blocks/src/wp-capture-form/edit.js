import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import FormInspectorControls from './FormInspectorControls';
import FormPreview from './FormPreview';
import { usePluginOptions, useEmsProviders, useEmsLists } from './hooks';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { emsConnectionId, formId } = attributes;

	// Effect to set formId from clientId
	useEffect(() => {
		if (clientId && ! formId ) {
			setAttributes({ formId: clientId });
		}
	}, [clientId, formId, setAttributes]);

	// Use custom hooks for data fetching
	const { optionsFromAPI, isLoadingOptions } = usePluginOptions();
	const { emsProviders, isLoadingProviders, providersError, validateConnectionExists } = useEmsProviders();
	
	// Determine if we should fetch lists: either no connection (empty string) or valid connection
	const shouldFetchLists = !emsConnectionId || validateConnectionExists(emsConnectionId);
	const { emsLists, isLoadingLists, listsError } = useEmsLists(emsConnectionId, shouldFetchLists);

	// Effect to validate selected connection ID when providers have finished loading
	useEffect(() => {
		// Only validate after providers have loaded
		if (!isLoadingProviders && emsConnectionId && !validateConnectionExists(emsConnectionId)) {
			// Clear the connection ID and selected list if the connection no longer exists
			setAttributes({ emsConnectionId: '', selectedListId: '' });
		}
	}, [emsConnectionId, isLoadingProviders, validateConnectionExists, setAttributes]);

	return (
		<>
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
			/>
			<FormPreview 
				attributes={attributes}
				optionsFromAPI={optionsFromAPI}
			/>
		</>
	);
} 