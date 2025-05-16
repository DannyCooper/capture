import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Spinner } from '@wordpress/components';

// Debug logging
const debug = (message, data) => {
    if (process.env.NODE_ENV === 'development') {
        console.log(`[Form Preview] ${message}`, data);
    }
};

export default function FormPreview({ formId }) {
    debug('FormPreview props:', { formId });

    const form = useSelect((select) => {
        const result = select('core').getEntityRecord('postType', 'capture_form', formId);
        debug('Fetched form:', result);
        return result;
    }, [formId]);

    if (!form) {
        debug('Form is loading...');
        return (
            <div className="form-preview-loading">
                <Spinner />
            </div>
        );
    }

    debug('Rendering form:', form);

    return (
        <div className="form-preview">
            <div className="form-preview-content">
                <div dangerouslySetInnerHTML={{ __html: form.content.rendered }} />
            </div>
        </div>
    );
} 