import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes, clientId }) {
	const { blockId, fieldType, fieldName, label, placeholder, required, autocomplete, showLabel } = attributes;
	
	// Set blockId to clientId on mount if not already set.
	useEffect(() => {
		if (!blockId && clientId) {
			setAttributes({ blockId: clientId });
		}
	}, [blockId, clientId, setAttributes]);
	
	const uniqueFieldId = 'capture-form__field-' + (blockId || clientId);
	
	// Field type configuration.
	const fieldTypeConfig = {
		name: {
			fieldName: 'name',
			autocomplete: 'given-name',
			placeholder: __('Enter your first name', 'capture'),
			label: __('First name', 'capture'),
		},
		email: {
			fieldName: 'email',
			autocomplete: 'email',
			placeholder: __('Enter your email address', 'capture'),
			label: __('Email address', 'capture'),
		},
		custom: {
			fieldName: 'custom_field',
			autocomplete: '',
			placeholder: '',
			label: __('Custom field', 'capture'),
		},
		'': {
			fieldName: '',
			autocomplete: '',
			placeholder: 'Placeholder text',
			label: '',
		},
	};
	
	const blockProps = useBlockProps({
		className: 'capture-form__field',
	});

	// Extract style from blockProps to apply to input, keep other props on wrapper.
	const { style, className, ...wrapperProps } = blockProps;
	const inputClassName = `capture-form__input ${className || ''}`.trim();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Field Settings', 'capture')}>
					<SelectControl
						label={__('Field Type', 'capture')}
						value={fieldType}
						options={[
							{ label: __('- Select a field type -', 'capture'), value: '' },
							{ label: __('First Name', 'capture'), value: 'name' },
							{ label: __('Email', 'capture'), value: 'email' },
							{ label: __('Custom', 'capture'), value: 'custom' },
						]}
					onChange={(value) => {
						// Auto-set field name and autocomplete based on type.
						const config = fieldTypeConfig[value];
						if (config) {
							setAttributes({ 
								fieldType: value,
								...config,
							});
						}
					}}
					/>
					{ fieldType === 'custom' && (
						<TextControl
							label={__('Field Name', 'capture')}
							value={fieldName}
							onChange={(value) => setAttributes({ fieldName: value })}
							help={__('The name attribute for the input field.', 'capture')}
						/>
					)}
					<TextControl
						label={__('Label', 'capture')}
						value={label}
						onChange={(value) => setAttributes({ label: value })}
						help={__('Optional label text. Leave empty to hide.', 'capture')}
					/>
					<TextControl
						label={__('Placeholder', 'capture')}
						value={placeholder}
						onChange={(value) => setAttributes({ placeholder: value })}
					/>
					<TextControl
						label={__('Autocomplete', 'capture')}
						value={autocomplete}
						onChange={(value) => setAttributes({ autocomplete: value })}
						help={__('HTML autocomplete attribute (e.g., "email", "given-name").', 'capture')}
					/>
					<ToggleControl
						label={__('Required', 'capture')}
						checked={required}
						onChange={(value) => setAttributes({ required: value })}
					/>
					<ToggleControl
						label={__('Show Label', 'capture')}
						checked={showLabel}
						onChange={(value) => setAttributes({ showLabel: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...wrapperProps} className={className}>
				{label && showLabel && <label className="capture-form__label" htmlFor={uniqueFieldId}>{label}</label>}
				<input
					className={inputClassName}
					style={style}
					type={fieldType}
					name={fieldName}
					id={uniqueFieldId}
					placeholder={placeholder}
					required={required}
					disabled
				/>
			</div>
		</>
	);
}

