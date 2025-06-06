import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export function useEmsProviders() {
	const [emsProviders, setEmsProviders] = useState([]);
	const [isLoadingProviders, setIsLoadingProviders] = useState(true);
	const [providersError, setProvidersError] = useState(null);

	// Effect to fetch EMS Providers on mount
	useEffect(() => {
		setIsLoadingProviders(true);
		setProvidersError(null);
		apiFetch({ path: '/capture/v1/get-ems-providers/' })
			.then((response) => {
				if (response.success && response.providers) {
					const selectOptions = response.providers.map((provider) => ({
						label: provider.label,
						value: provider.value,
					}));
					setEmsProviders([
						{ label: __('Select an EMS Provider', 'capture'), value: '' },
						...selectOptions,
					]);
					
					if (response.providers.length === 0 && response.message) {
						setProvidersError(response.message);
					}
				} else {
					setProvidersError(response.message || __('Failed to load EMS providers.', 'capture'));
					setEmsProviders([{ label: __('Select an EMS Provider', 'capture'), value: '' }]);
				}
				setIsLoadingProviders(false);
			})
			.catch((err) => {
				console.error('Error fetching EMS providers:', err);
				setProvidersError(__('An error occurred while fetching EMS providers.', 'capture'));
				setEmsProviders([{ label: __('Select an EMS Provider', 'capture'), value: '' }]);
				setIsLoadingProviders(false);
			});
	}, []);

	// Function to validate if a connection ID exists in the providers
	const validateConnectionExists = (connectionId) => {
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