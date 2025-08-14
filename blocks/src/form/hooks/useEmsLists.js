import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export function useEmsLists(emsConnectionId, shouldFetch = true) {
	const [emsLists, setEmsLists] = useState([]);
	const [isLoadingLists, setIsLoadingLists] = useState(false);
	const [listsError, setListsError] = useState(null);

	// Effect to fetch EMS Lists when emsConnectionId changes
	useEffect(() => {
		if (emsConnectionId && shouldFetch) {
			setIsLoadingLists(true);
			setListsError(null);
			setEmsLists([{ label: __('Loading lists...', 'capture'), value: '' }]);

			apiFetch({ path: `/capture/v1/get-ems-lists/?ems_id=${emsConnectionId}` })
				.then((response) => {
					if (response.success && response.lists) {
						const selectOptions = response.lists.map((list) => ({
							label: list.label,
							value: list.value,
						}));
						setEmsLists([
							{ label: __('Select a List', 'capture'), value: '' },
							...selectOptions,
						]);
						if (response.lists.length === 0 && response.message) {
							setListsError(response.message);
						}
					} else {
						setListsError(response.message || __('Failed to load lists for the selected provider.', 'capture'));
						setEmsLists([{ label: __('Select a List', 'capture'), value: '' }]);
					}
					setIsLoadingLists(false);
				})
				.catch((err) => {
					console.error('Error fetching EMS lists:', err);
					setListsError(__('An error occurred while fetching lists.', 'capture'));
					setEmsLists([{ label: __('Select a List', 'capture'), value: '' }]);
					setIsLoadingLists(false);
				});
		} else {
			setEmsLists([{ label: __('Select a provider first', 'capture'), value: '' }]);
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