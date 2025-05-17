import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { 
    Placeholder,
    Spinner,
    SelectControl,
    PanelBody,
    PanelRow
} from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import FormPreview from './components/FormPreview';

// Debug logging
const debug = (message, data) => {
    if (process.env.NODE_ENV === 'development') {
        console.log(`[Form Embed Block] ${message}`, data);
    }
};

registerBlockType('capture/form-embed', {
    edit: function Edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        const { formId } = attributes;

        debug('Block attributes:', attributes);

        const forms = useSelect((select) => {
            const result = select('core').getEntityRecords('postType', 'capture_form', {
                per_page: -1,
                _embed: true,
                orderby: 'title',
                order: 'asc'
            });
            debug('Fetched forms:', result);
            return result;
        }, []);

        if (!forms) {
            debug('Forms are loading...');
            return (
                <div {...blockProps}>
                    <Placeholder>
                        <Spinner />
                    </Placeholder>
                </div>
            );
        }

        const formOptions = forms.map(form => ({
            label: form.title.rendered,
            value: form.id
        }));

        debug('Form options:', formOptions);

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Form Settings', 'capture')}>
                        <PanelRow>
                            <SelectControl
                                label={__('Select Form', 'capture')}
                                value={formId}
                                options={[
                                    { label: __('Select a form...', 'capture'), value: '' },
                                    ...formOptions
                                ]}
                                onChange={(value) => {
                                    debug('Form selected:', value);
                                    setAttributes({ formId: value ? parseInt(value) : null });
                                }}
                            />
                        </PanelRow>
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    {!formId ? (
                        <Placeholder
                            label={__('Form Embed', 'capture')}
                            instructions={__('Select a form to embed.', 'capture')}
                        >
                            <SelectControl
                                value={formId}
                                options={[
                                    { label: __('Select a form...', 'capture'), value: '' },
                                    ...formOptions
                                ]}
                                onChange={(value) => {
                                    debug('Form selected:', value);
                                    setAttributes({ formId: value ? parseInt(value) : null });
                                }}
                            />
                        </Placeholder>
                    ) : (
                        <FormPreview formId={formId} />
                    )}
                </div>
            </>
        );
    }
}); 