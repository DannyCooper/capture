import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Spinner } from '@wordpress/components';

export default function FormPreview({ formId }) {

    const form = useSelect((select) => {
        const result = select('core').getEntityRecord('postType', 'capture_form', formId);
        return result;
    }, [formId]);

    if (!form) {
        return (
            <div className="form-preview-loading">
                <Spinner />
            </div>
        );
    }

    return (
        <div className="form-preview">
            <div className="form-preview-content">
                <div dangerouslySetInnerHTML={{ __html: form.content.rendered }} />
            </div>
        </div>
    );
} 