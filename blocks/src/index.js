import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    SelectControl,
    PanelBody,
    TextControl,
    ToggleControl
} from '@wordpress/components';
import {
    useBlockProps,
    InspectorControls
} from '@wordpress/block-editor';

registerBlockType('recruit-connect/vacancy-field', {
    title: __('Vacancy Field', 'recruit-connect-wp'),
    icon: 'businessman',
    category: 'recruit-connect',
    attributes: {
        fieldKey: {
            type: 'string',
            default: ''
        },
        label: {
            type: 'string',
            default: ''
        },
        showLabel: {
            type: 'boolean',
            default: true
        }
    },

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const { fieldKey, label, showLabel } = attributes;

        // Field options
        const fieldOptions = [
            { value: '', label: __('Select a field...', 'recruit-connect-wp') },
            { value: '_vacancy_company', label: __('Company', 'recruit-connect-wp') },
            { value: '_vacancy_city', label: __('City', 'recruit-connect-wp') },
            { value: '_vacancy_salary', label: __('Salary', 'recruit-connect-wp') },
            { value: '_vacancy_education', label: __('Education', 'recruit-connect-wp') },
            { value: '_vacancy_jobtype', label: __('Job Type', 'recruit-connect-wp') },
            { value: '_vacancy_experience', label: __('Experience', 'recruit-connect-wp') }
        ];

        // Main block content
        const blockContent = (
            <div {...blockProps}>
                <div style={{ padding: '20px', border: '1px dashed #ccc', borderRadius: '4px' }}>
                    <SelectControl
                        value={fieldKey}
                        options={fieldOptions}
                        onChange={(newValue) => {
                            const selectedField = fieldOptions.find(field => field.value === newValue);
                            setAttributes({
                                fieldKey: newValue,
                                label: selectedField ? selectedField.label : ''
                            });
                        }}
                    />
                    {fieldKey && (
                        <div style={{ marginTop: '10px' }}>
                            <strong>{label}: </strong>
                            <span>{__('Field value will appear here', 'recruit-connect-wp')}</span>
                        </div>
                    )}
                </div>
            </div>
        );

        // Sidebar controls
        const inspectorControls = (
            <InspectorControls>
                <PanelBody title={__('Field Settings', 'recruit-connect-wp')}>
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
        );

        return (
            <>
                {inspectorControls}
                {blockContent}
            </>
        );
    },

    save: () => null
});
