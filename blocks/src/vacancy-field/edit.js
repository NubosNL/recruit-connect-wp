import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import {
    SelectControl,
    PanelBody,
    TextControl,
    ToggleControl
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

const Edit = ({ attributes, setAttributes }) => {
    const { fieldKey, label, showLabel } = attributes;

    // Define available vacancy fields
    const fields = {
        '_vacancy_company': __('Company', 'recruit-connect-wp'),
        '_vacancy_city': __('City', 'recruit-connect-wp'),
        '_vacancy_salary': __('Salary', 'recruit-connect-wp'),
        '_vacancy_education': __('Education', 'recruit-connect-wp'),
        '_vacancy_jobtype': __('Job Type', 'recruit-connect-wp'),
        '_vacancy_experience': __('Experience', 'recruit-connect-wp'),
        '_vacancy_recruitername': __('Recruiter Name', 'recruit-connect-wp'),
        '_vacancy_recruiteremail': __('Recruiter Email', 'recruit-connect-wp'),
        '_vacancy_recruiterimage': __('Recruiter Image', 'recruit-connect-wp'),
        '_vacancy_streetaddress': __('Street Address', 'recruit-connect-wp'),
        '_vacancy_postalcode': __('Postal Code', 'recruit-connect-wp'),
        '_vacancy_state': __('State', 'recruit-connect-wp'),
        '_vacancy_country': __('Country', 'recruit-connect-wp'),
        '_vacancy_remotetype': __('Remote Type', 'recruit-connect-wp')
    };

    // Get the current post meta value
    const metaValue = useSelect((select) => {
        const postId = select('core/editor').getCurrentPostId();
        const meta = select('core/editor').getEditedPostAttribute('meta');
        return meta?.[fieldKey] || '';
    }, [fieldKey]);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Field Settings', 'recruit-connect-wp')}>
                    <SelectControl
                        label={__('Select Field', 'recruit-connect-wp')}
                        value={fieldKey}
                        options={Object.entries(fields).map(([value, label]) => ({
                            value,
                            label
                        }))}
                        onChange={(value) => {
                            setAttributes({
                                fieldKey: value,
                                label: fields[value]
                            });
                        }}
                    />
                    <TextControl
                        label={__('Custom Label', 'recruit-connect-wp')}
                        value={label}
                        onChange={(value) => setAttributes({ label: value })}
                    />
                    <ToggleControl
                        label={__('Show Label', 'recruit-connect-wp')}
                        checked={showLabel}
                        onChange={(value) => setAttributes({ showLabel: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div className="wp-block-recruit-connect-vacancy-field">
                {fieldKey ? (
                    <>
                        {showLabel && <span className="field-label">{label}: </span>}
                        <span className="field-value">
                            {fieldKey === '_vacancy_recruiterimage' ? (
                                metaValue ? (
                                    <img src={metaValue} alt={label} className="recruiter-image" />
                                ) : (
                                    __('(No image)', 'recruit-connect-wp')
                                )
                            ) : (
                                metaValue || __('(No value)', 'recruit-connect-wp')
                            )}
                        </span>
                    </>
                ) : (
                    <p>{__('Select a vacancy field', 'recruit-connect-wp')}</p>
                )}
            </div>
        </>
    );
};

export default Edit;
