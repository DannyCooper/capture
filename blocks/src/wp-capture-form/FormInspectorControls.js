import { __ } from '@wordpress/i18n';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner, TextControl, TextareaControl, RangeControl, CheckboxControl } from '@wordpress/components';

export default function FormInspectorControls({
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
	const handleProviderChange = (newConnectionId) => {
		setAttributes({ emsConnectionId: newConnectionId, selectedListId: '' });
	};

	// Define the color settings for the panel
	const colorSettings = [
		{
			value: buttonTextColor,
			onChange: (value) => setAttributes({ buttonTextColor: value }),
			label: __('Text Color', 'capture'),
		},
		{
			value: buttonColor,
			onChange: (value) => setAttributes({ buttonColor: value }),
			label: __('Background Color', 'capture'),
		},
		{
			value: buttonHoverColor,
			onChange: (value) => setAttributes({ buttonHoverColor: value }),
			label: __('Background Hover Color', 'capture'),
		},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'capture')}>
					<CheckboxControl
						label={__('Show Name Field', 'capture')}
						checked={showNameField}
						onChange={(newShowNameField) => setAttributes({ showNameField: newShowNameField })}
					/>
					<CheckboxControl
						label={__('Show Privacy Policy', 'capture')}
						checked={showPrivacyPolicy}
						onChange={(newShowPrivacyPolicy) => setAttributes({ showPrivacyPolicy: newShowPrivacyPolicy })}
					/>
					<TextControl
						label={__('Form ID', 'capture')}
						value={formId || ''}
						onChange={(newFormId) => setAttributes({ formId: newFormId })}
						help={__('Changing this allows you to track the success of this form.', 'capture')}
					/>
					{isLoadingProviders ? (
						<Spinner />
					) : (
						<SelectControl
							label={__('Select EMS Provider', 'capture')}
							value={emsConnectionId}
							options={emsProviders}
							onChange={handleProviderChange}
						/>
					)}

					{!emsConnectionId && (emsProviders.length <= 1 || !providersError) && (
						<div className="capture-local-notice" style={{ 
							padding: '10px', 
							backgroundColor: '#f0f0f0', 
							border: '1px solid #ddd', 
							borderRadius: '4px',
							marginTop: '-8px',
							marginBottom: '16px'
						}}>
							<p style={{ margin: 0 }}>
								{__('ℹ️ No EMS provider selected. Subscribers will be stored locally in your WordPress database.', 'capture')}
							</p>
						</div>
					)}

					{emsConnectionId && (
						<>
							{isLoadingLists ? (
								<Spinner />
							) : listsError ? (
								<Placeholder icon="warning" label={__('EMS Lists', 'capture')}>
									{listsError}
								</Placeholder>
							) : (
								<SelectControl
									label={__('Select List', 'capture')}
									value={selectedListId}
									options={emsLists}
									onChange={(newListId) => setAttributes({ selectedListId: newListId })}
									disabled={emsLists.length <= 1 && !listsError && !isLoadingLists}
									help={emsLists.length <= 1 && !listsError && !isLoadingLists ? __('No lists available for this provider or select a provider.', 'capture') : ''}
								/>
							)}
						</>
					)}
					<TextareaControl
						label={__('Success Message', 'capture')}
						value={successMessage || optionsFromAPI?.default_success_message}
						onChange={(newSuccessMessage) => setAttributes({ successMessage: newSuccessMessage })}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls group="advanced">
				<CheckboxControl
					label={__('Disable Core Styles', 'capture')}
					checked={disableCoreStyles}
					onChange={(newDisableCoreStyles) => setAttributes({ disableCoreStyles: newDisableCoreStyles })}
				/>
			</InspectorControls>
			<InspectorControls group="styles">
				<PanelBody title={__('Form Layout', 'capture')}>
					<SelectControl
						label={__('Style', 'capture')}
						value={formLayout}
						options={[
							{ label: 'Stacked', value: 'stacked' },
							{ label: 'Inline', value: 'inline' },
						]}
						onChange={(newStyle) => setAttributes({ formLayout: newStyle })}
						help={__('Stack the form fields or display them inline.', 'capture')}
					/>
					<RangeControl
						label={__('Field Gap (rem)', 'capture')}
						value={fieldGap}
						onChange={(newFieldGap) => setAttributes({ fieldGap: newFieldGap })}
						min={0}
						max={5}
						step={0.2}
						help={__('Set the gap between form fields', 'capture')}
					/>
				</PanelBody>
				<PanelColorSettings
					title={__('Button Color Settings', 'capture')}
					initialOpen={true}
					colorSettings={colorSettings}
				/>
			</InspectorControls>
		</>
	);
} 