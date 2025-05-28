import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner, TextControl, TextareaControl, RangeControl, CheckboxControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { emsConnectionId, selectedListId, formId, formLayout, successMessage, fieldGap, showNameField, buttonText, buttonColor, buttonTextColor, buttonHoverColor } = attributes;

	// Effect to set formId from clientId
	useEffect(() => {
		if (clientId && ! formId ) {
			setAttributes({ formId: clientId });
		}
	}, [clientId, formId, setAttributes]);

	// State for EMS Providers
	const [emsProviders, setEmsProviders] = useState([]);
	const [isLoadingProviders, setIsLoadingProviders] = useState(true);
	const [providersError, setProvidersError] = useState(null);

	// State for EMS Lists
	const [emsLists, setEmsLists] = useState([]);
	const [isLoadingLists, setIsLoadingLists] = useState(false);
	const [listsError, setListsError] = useState(null);

	// Effect to fetch EMS Providers on mount
	useEffect(() => {
		setIsLoadingProviders(true);
		setProvidersError(null);
		apiFetch({ path: '/wp-capture/v1/get-ems-providers/' })
			.then((response) => {
				if (response.success && response.providers) {
					const selectOptions = response.providers.map((provider) => ({
						label: provider.label,
						value: provider.value,
					}));
					setEmsProviders([
						{ label: __('Select an EMS Provider', 'wp-capture'), value: '' },
						...selectOptions,
					]);
					if (response.providers.length === 0 && response.message) {
						setProvidersError(response.message);
					}
				} else {
					setProvidersError(response.message || __('Failed to load EMS providers.', 'wp-capture'));
					setEmsProviders([{ label: __('Select an EMS Provider', 'wp-capture'), value: '' }]);
				}
				setIsLoadingProviders(false);
			})
			.catch((err) => {
				console.error('Error fetching EMS providers:', err);
				setProvidersError(__('An error occurred while fetching EMS providers.', 'wp-capture'));
				setEmsProviders([{ label: __('Select an EMS Provider', 'wp-capture'), value: '' }]);
				setIsLoadingProviders(false);
			});
	}, []);

	// Effect to fetch EMS Lists when emsConnectionId changes
	useEffect(() => {
		if (emsConnectionId) {
			setIsLoadingLists(true);
			setListsError(null);
			setEmsLists([{ label: __('Loading lists...', 'wp-capture'), value: '' }]);

			apiFetch({ path: `/wp-capture/v1/get-ems-lists/?ems_id=${emsConnectionId}` })
				.then((response) => {
					if (response.success && response.lists) {
						const selectOptions = response.lists.map((list) => ({
							label: list.label,
							value: list.value,
						}));
						setEmsLists([
							{ label: __('Select a List', 'wp-capture'), value: '' },
							...selectOptions,
						]);
						if (response.lists.length === 0 && response.message) {
							setListsError(response.message);
						}
					} else {
						setListsError(response.message || __('Failed to load lists for the selected provider.', 'wp-capture'));
						setEmsLists([{ label: __('Select a List', 'wp-capture'), value: '' }]);
					}
					setIsLoadingLists(false);
				})
				.catch((err) => {
					console.error('Error fetching EMS lists:', err);
					setListsError(__('An error occurred while fetching lists.', 'wp-capture'));
					setEmsLists([{ label: __('Select a List', 'wp-capture'), value: '' }]);
					setIsLoadingLists(false);
				});
		} else {
			setEmsLists([{ label: __('Select a provider first', 'wp-capture'), value: '' }]);
			setAttributes({ selectedListId: '' });
			setIsLoadingLists(false);
			setListsError(null);
		}
	}, [emsConnectionId, setAttributes]);

	const handleProviderChange = (newConnectionId) => {
		setAttributes({ emsConnectionId: newConnectionId, selectedListId: '' });
	};

	const blockProps = useBlockProps(
		{
			style: { 
				gap: `${fieldGap}rem`, 
				display: 'flex', 
				flexDirection: formLayout === 'inline' ? 'row' : 'column' 
		    },
			className: `capture-form capture-form--${formLayout}`
		}
	);

	// Styles for the button, can be applied directly or via CSS variables
	const buttonStyles = {
		backgroundColor: buttonColor,
		color: buttonTextColor,
		border: 'none',
		cursor: 'pointer',
	};

	// Define the color settings for the panel
	const colorSettings = [
		{
			value: buttonTextColor,
			onChange: (value) => setAttributes({ buttonTextColor: value }),
			label: __('Text Color', 'wp-capture'),
		},
		{
			value: buttonColor,
			onChange: (value) => setAttributes({ buttonColor: value }),
			label: __('Background Color', 'wp-capture'),
		},
		{
			value: buttonHoverColor,
			onChange: (value) => setAttributes({ buttonHoverColor: value }),
			label: __('Background Hover Color', 'wp-capture'),
		},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'wp-capture')}>
					<CheckboxControl
						label={__('Show Name Field', 'wp-capture')}
						checked={showNameField}
						onChange={(newShowNameField) => setAttributes({ showNameField: newShowNameField })}
					/>
					<TextControl
						label={__('Form ID', 'wp-capture')}
						value={formId || ''}
						onChange={(newFormId) => setAttributes({ formId: newFormId })}
						help={__('Changing this allows you to track the success of this form.', 'wp-capture')}
					/>
					{isLoadingProviders ? (
						<Spinner />
					) : providersError ? (
						<Placeholder icon="warning" label={__('EMS Providers', 'wp-capture')}>
							{providersError}
						</Placeholder>
					) : (
						<SelectControl
							label={__('Select EMS Provider', 'wp-capture')}
							value={emsConnectionId}
							options={emsProviders}
							onChange={handleProviderChange}
							help={emsProviders.length <= 1 && !providersError ? __('No EMS providers configured. Please add one in plugin settings.', 'wp-capture') : ''}
						/>
					)}

					{/* Show local storage notice when no EMS is connected */}
					{!emsConnectionId && (emsProviders.length <= 1 || !providersError) && (
						<div className="wp-capture-local-notice" style={{ 
							padding: '10px', 
							backgroundColor: '#f0f0f0', 
							border: '1px solid #ddd', 
							borderRadius: '4px',
							marginTop: '10px'
						}}>
							<p style={{ margin: 0, fontSize: '14px', color: '#666' }}>
								{__('ℹ️ No EMS provider selected. Subscribers will be stored locally in your WordPress database.', 'wp-capture')}
							</p>
						</div>
					)}

					{emsConnectionId && (
						<>
							{isLoadingLists ? (
								<Spinner />
							) : listsError ? (
								<Placeholder icon="warning" label={__('EMS Lists', 'wp-capture')}>
									{listsError}
								</Placeholder>
							) : (
								<SelectControl
									label={__('Select List', 'wp-capture')}
									value={selectedListId}
									options={emsLists}
									onChange={(newListId) => setAttributes({ selectedListId: newListId })}
									disabled={emsLists.length <= 1 && !listsError && !isLoadingLists}
									help={emsLists.length <= 1 && !listsError && !isLoadingLists ? __('No lists available for this provider or select a provider.', 'wp-capture') : ''}
								/>
							)}
						</>
					)}
					<TextareaControl
						label={__('Success Message', 'wp-capture')}
						value={successMessage}
						onChange={(newSuccessMessage) => setAttributes({ successMessage: newSuccessMessage })}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls group="styles">
				<PanelBody title={__('Form Layout', 'wp-capture')}>
					<SelectControl
						label={__('Style', 'wp-capture')}
						value={formLayout}
						options={[
							{ label: 'Stacked', value: 'stacked' },
							{ label: 'Inline', value: 'inline' },
						]}
						onChange={(newStyle) => setAttributes({ formLayout: newStyle })}
						help={__('Stack the form fields or display them inline.', 'wp-capture')}
					/>
					<RangeControl
						label={__('Field Gap (rem)', 'wp-capture')}
						value={fieldGap}
						onChange={(newFieldGap) => setAttributes({ fieldGap: newFieldGap })}
						min={0}
						max={5}
						step={0.2}
						help={__('Set the gap between form fields', 'wp-capture')}
					/>
				</PanelBody>
				<PanelColorSettings
					title={__('Button Color Settings', 'wp-capture')}
					initialOpen={true}
					colorSettings={colorSettings}
				/>
			</InspectorControls>
			
			<div {...blockProps}>
				{showNameField && (
					<input
						type="text"
						id={blockProps.id + '-name'}
						className="capture-form__input"
						placeholder={__('First name', 'wp-capture')}
						readOnly
					/>
				)}
				<input
					type="email"
					id={blockProps.id + '-email'}
					className="capture-form__input"
					placeholder={__('Email address', 'wp-capture')}
					readOnly
					/>
				
				<button
					type="button"
					className="capture-form__button"
					style={buttonStyles}
					onClick={(event) => event.preventDefault()}
				>
					{__('Subscribe', 'wp-capture')}
				</button>
			</div>
		</>
	);
} 