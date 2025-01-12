import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
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

        const metaValue = useSelect((select) => {
            const meta = select('core/editor').getEditedPostAttribute('meta') || {};
            return meta[fieldKey] || '';
        }, [fieldKey]);

        const vacancyFields = [
            { key: '_vacancy_id', label: __('Vacancy ID', 'recruit-connect-wp') },
            { key: '_vacancy_company', label: __('Company', 'recruit-connect-wp') },
            { key: '_vacancy_city', label: __('City', 'recruit-connect-wp') },
            { key: '_vacancy_createdat', label: __('Created At', 'recruit-connect-wp') },
            { key: '_vacancy_streetaddress', label: __('Street Address', 'recruit-connect-wp') },
            { key: '_vacancy_postalcode', label: __('Postal Code', 'recruit-connect-wp') },
            { key: '_vacancy_state', label: __('State', 'recruit-connect-wp') },
            { key: '_vacancy_country', label: __('Country', 'recruit-connect-wp') },
            { key: '_vacancy_salary', label: __('Salary', 'recruit-connect-wp') },
            { key: '_vacancy_education', label: __('Education', 'recruit-connect-wp') },
            { key: '_vacancy_jobtype', label: __('Job Type', 'recruit-connect-wp') },
            { key: '_vacancy_experience', label: __('Experience', 'recruit-connect-wp') },
            { key: '_vacancy_remotetype', label: __('Remote Type', 'recruit-connect-wp') },
            { key: '_vacancy_recruitername', label: __('Recruiter Name', 'recruit-connect-wp') },
            { key: '_vacancy_recruiteremail', label: __('Recruiter Email', 'recruit-connect-wp') },
            { key: '_vacancy_recruiterimage', label: __('Recruiter Image', 'recruit-connect-wp') }
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
                    __nextHasNoMarginBottom={true}
                />
                {/*{fieldKey && (*/}
                {/*    <div className="vacancy-field-preview">*/}
                {/*        <span className="field-label">*/}
                {/*            {vacancyFields.find(f => f.key === fieldKey)?.label}:*/}
                {/*        </span>*/}
                {/*        {' '}*/}
                {/*        <span className="field-value">*/}
                {/*            {fieldKey === '_vacancy_recruiterimage' && metaValue ? (*/}
                {/*                <img src={metaValue} alt="" className="recruiter-image" />*/}
                {/*            ) : (*/}
                {/*                metaValue || __('(No value)', 'recruit-connect-wp')*/}
                {/*            )}*/}
                {/*        </span>*/}
                {/*    </div>*/}
                {/*)}*/}
            </div>
        );
    },
    save: () => null
});
