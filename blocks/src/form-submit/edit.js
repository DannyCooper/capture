import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText, __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { text, hoverBackgroundColor, hoverTextColor } = attributes;
	
	const blockProps = useBlockProps({
		className: 'capture-form__button',
		style: {
			'--hover-bg-color': hoverBackgroundColor,
			'--hover-text-color': hoverTextColor,
		},
	});

	const colorSettings = [
		{
			colorLabel: __('Hover Text Color', 'capture'),
			colorValue: hoverTextColor,
			onChange: (colorValue) => setAttributes({ hoverTextColor: colorValue }),
			resetAllFilter: () => setAttributes({ hoverTextColor: undefined }),
		},
		{
			colorLabel: __('Hover Background Color', 'capture'),
			colorValue: hoverBackgroundColor,
			onChange: (colorValue) => setAttributes({ hoverBackgroundColor: colorValue }),
			resetAllFilter: () => setAttributes({ hoverBackgroundColor: undefined }),
		},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Button Settings', 'capture')}>
					<TextControl
						label={__('Button Text', 'capture')}
						value={text}
						onChange={(value) => setAttributes({ text: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<InspectorControls group="color">
				{colorSettings.map(
					({
						colorLabel,
						colorValue,
						onChange,
						resetAllFilter,
					}) => (
						<ColorGradientSettingsDropdown
							key={`button-hover-color-${colorLabel}`}
							__experimentalIsRenderedInSidebar
							settings={[
								{
									label: colorLabel,
									colorValue,
									onColorChange: onChange,
									isShownByDefault: true,
									resetAllFilter,
									enableAlpha: true,
								},
							]}
							panelId={clientId}
						/>
					)
				)}
			</InspectorControls>

			<button type="submit" {...blockProps}>
				<RichText
					tagName="span"
					value={text}
					onChange={(value) => setAttributes({ text: value })}
					placeholder={__('Subscribe', 'capture')}
					allowedFormats={[]}
				/>
			</button>
		</>
	);
}

