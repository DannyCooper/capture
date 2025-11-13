import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { Icon } from '@wordpress/components';

export default function FormBlocksPanel({ clientId }) {
	const { insertBlock } = useDispatch('core/block-editor');

	const commonBlocks = [
        {
			name:        'capture/form-input',
			title:       __('Name Input', 'capture'),
			icon:        'admin-users',
			description: __('Add a name input field', 'capture'),
			attributes:  {
				fieldType:   'name',
				fieldName:   'name',
				placeholder: __('Enter your name', 'capture'),
				required:    false,
			},
		},
		{
			name:        'capture/form-input',
			title:       __('Email Input', 'capture'),
			icon:        'email',
			description: __('Add an email input field', 'capture'),
			attributes:  {
				fieldType:   'email',
				fieldName:   'email',
				placeholder: __('Enter your email address', 'capture'),
				required:    true,
			},
		},
		{
			name:        'capture/form-input',
			title:       __('Custom Input', 'capture'),
			icon:        'editor-textcolor',
			description: __('Add a custom input field', 'capture'),
			attributes:  {
				fieldType:   'custom',
				fieldName:   'custom_field',
				placeholder: __('Enter value', 'capture'),
				required:    false,
			},
		},
		{
			name:        'capture/form-privacy-policy',
			title:       __('Privacy Policy', 'capture'),
			icon:        'privacy',
			description: __('Add privacy policy text', 'capture'),
			attributes:  {},
		},
		{
			name:        'capture/form-submit',
			title:       __('Submit Button', 'capture'),
			icon:        'button',
			description: __('Add a submit button', 'capture'),
			attributes:  {
				text: __('Subscribe', 'capture'),
			},
		},
	];

	const handleAddBlock = (blockConfig) => {
		const newBlock = createBlock(blockConfig.name, blockConfig.attributes);
		
		// Insert block as last child of the form.
		insertBlock(newBlock, undefined, clientId);
	};

	return (
		<InspectorControls>
			<PanelBody 
				title={__('Add Form Fields', 'capture')} 
				initialOpen={true}
			>
				<div className="capture-form-blocks-panel">
					{commonBlocks.map((block, index) => (
						<div
							key={index}
							className="capture-form-blocks-panel__item"
							onClick={() => handleAddBlock(block)}
						>
							<div className="capture-form-blocks-panel__item-content">
								<Icon 
									icon={block.icon} 
									className="capture-form-blocks-panel__item-icon"
								/>
								<div className="capture-form-blocks-panel__item-text">
									<div className="capture-form-blocks-panel__item-title">
										{block.title}
									</div>
								</div>
							</div>
						</div>
					))}
				</div>
				<p 
					className="description"
					style={{
						marginTop:  'calc(8px)',
						fontSize:   '12px',
						color:      'rgb(117, 117, 117)',
					}}
				>
					{__('Click a field to add it to your form.', 'capture')}
				</p>
			</PanelBody>
		</InspectorControls>
	);
}

