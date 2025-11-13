import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Notice } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
	const { text }   = attributes;
	const blockProps = useBlockProps({
		className: 'capture-form__privacy-policy',
	});

	// Fetch default privacy policy text from plugin options.
	useEffect(() => {
		if (!text) {
			apiFetch({ path: '/capture/v1/get-options/' })
				.then((response) => {
					if (response.success && response.options && response.options.privacy_policy_text) {
						setAttributes({ text: response.options.privacy_policy_text });
					}
				})
				.catch((err) => {
					console.error('Error fetching privacy policy text:', err);
				});
		}
	}, []); // Only run once on mount.

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Privacy Policy Settings', 'capture')}>
					<Notice status="info" isDismissible={false}>
						{__('The default text is loaded from plugin settings. You can customize it here for this specific form.', 'capture')}
					</Notice>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<RichText
					tagName="span"
					value={text}
					onChange={(value) => setAttributes({ text: value })}
					placeholder={__('Privacy policy text will load from plugin settings...', 'capture')}
					allowedFormats={['core/bold', 'core/italic', 'core/link']}
				/>
			</div>
		</>
	);
}

