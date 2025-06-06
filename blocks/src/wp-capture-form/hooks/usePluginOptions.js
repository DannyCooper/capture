import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export function usePluginOptions() {
	const [optionsFromAPI, setOptionsFromAPI] = useState(null);
	const [isLoadingOptions, setIsLoadingOptions] = useState(false);

	useEffect(() => {
		if (!isLoadingOptions && !optionsFromAPI) {
			setIsLoadingOptions(true);
			apiFetch({ path: '/capture/v1/get-options/' })
				.then((response) => {
					if (response.success && response.options) {
						setOptionsFromAPI(response.options);
					}
					setIsLoadingOptions(false);
				})
				.catch((err) => {
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