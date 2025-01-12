import { registerBlockType } from '@wordpress/blocks';
import { useSelect, useDispatch } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('recruit-connect/vacancy-field', {
    title: __('Vacancy Field', 'recruit-connect-wp'),
    icon: 'businessman',
    category: 'recruit-connect',
    attributes: {
        fieldKey: {
            type: 'string',
            default: ''
        }
    },
    edit: function Edit({ attributes, setAttributes }) {
        const { fieldKey } = attributes;
        const blockProps = useBlockProps();

        const postMeta = useSelect(select => {
            const { getCurrentPostType, getEditedPostAttribute } = select('core/editor');
            const postType = getCurrentPostType();
            console.log('Post Type:', postType); // Debug log

            const meta = getEditedPostAttribute('meta');
            console.log('Full meta:', meta); // Debug log

            return meta || {};
        }, []);

        console.log('Selected field:', fieldKey); // Debug log
        console.log('Field value:', postMeta[fieldKey]); // Debug log

        const vacancyFields = [
            { key: '_vacancy_id', label: __('Vacancy ID', 'recruit-connect-wp') },
            { key: '_vacancy_company', label: __('Company', 'recruit-connect-wp') },
            // ... rest of your fields
        ];

        return (
            <div {...blockProps}>
                <SelectControl
                    label={__('Select Vacancy Field', 'recruit-connect-wp')}
                    value={fieldKey}
                    options={[
                        { label: __('Select a field...', 'recruit-connect-wp'), value: '' },
                        ...vacancyFields.map(({ key, label }) => ({
                            label,
                            value: key
                        }))
                    ]}
                    onChange={(value) => setAttributes({ fieldKey: value })}
                />
                {fieldKey && (
                    <div className="vacancy-field-preview">
                        <span className="field-label">
                            {vacancyFields.find(f => f.key === fieldKey)?.label}:
                        </span>
                        {' '}
                        <span className="field-value">
                            {fieldKey === '_vacancy_recruiterimage' && postMeta[fieldKey] ? (
                                <img src={postMeta[fieldKey]} alt="" className="recruiter-image" />
                            ) : (
                                postMeta[fieldKey] || __('(No value)', 'recruit-connect-wp')
                            )}
                        </span>
                    </div>
                )}
            </div>
        );
    },
    save: () => null
});
