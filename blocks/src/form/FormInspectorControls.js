import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { SelectControl, Placeholder, Spinner, TextControl, TextareaControl, CheckboxControl, BorderBoxControl, BorderControl, Panel, PanelBody, PanelRow} from '@wordpress/components';
import { __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown, PanelColorSettings } from '@wordpress/block-editor';

export default function FormInspectorControls({
	attributes,
	setAttributes,
	emsProviders,
	isLoadingProviders,
	providersError,
	emsLists,
	isLoadingLists,
	listsError,
	optionsFromAPI,
	clientId
}) {
	const {
		emsConnectionId,	
		selectedListId,
		formId,
		successMessage,
		disableCoreStyles,
		inputTextColor,
		inputBackgroundColor,
		inputBorderColor,
		inputBorder
	} = attributes;

	// Handle provider change - clear both connection and selected list.
	const handleProviderChange = (newConnectionId) => {
		setAttributes({ emsConnectionId: newConnectionId, selectedListId: '' });
	};

	const colorSettings = [
		{
			colorLabel: __('Input Text Color', 'capture'),
			colorValue: inputTextColor,
			onChange: (colorValue) => setAttributes({ inputTextColor: colorValue }),
			resetAllFilter: () => setAttributes({ inputTextColor: undefined }),
		},
		{
			colorLabel: __('Input Background Color', 'capture'),
			colorValue: inputBackgroundColor,
			onChange: (colorValue) => setAttributes({ inputBackgroundColor: colorValue }),
			resetAllFilter: () => setAttributes({ inputBackgroundColor: undefined }),
		},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'capture')}>	
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
			<InspectorControls group="styles">
				<PanelBody title="Input Styles" initialOpen={ true } >
					<BorderControl
						__next40pxDefaultSize
						label={ __( 'Input Borders', 'capture' ) }
						value={ inputBorder }
						onChange={(value) => setAttributes({ inputBorder: value })}
					/>
						<PanelColorSettings
							showTitle={ true }
							__experimentalIsRenderedInSidebar
							title={ __( 'Input Colors', 'capture' ) }
							className="capture-form-color-settings"
							initialOpen={ false }
							colorSettings={ [
								{
									value: inputTextColor,
									onChange: ( value ) => setAttributes( { inputTextColor: value } ),
									label: __( 'Text Color', 'capture' ),
								},
								{
									value: inputBackgroundColor,
									onChange: (value) => setAttributes( { inputBackgroundColor: value } ),
									label: __( 'Background Color', 'capture' ),
								},
							] }
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
		</>
	);
} 