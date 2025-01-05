const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, RangeControl, SelectControl } = wp.components;
const { __ } = wp.i18n;

// Vacancy List Block
registerBlockType('recruit-connect-wp/vacancy-list', {
    title: __('Vacancy List', 'recruit-connect-wp'),
    icon: 'list-view',
    category: 'recruit-connect',
    attributes: {
        limit: {
            type: 'number',
            default: 10
        },
        showFilters: {
            type: 'boolean',
            default: true
        },
        layout: {
            type: 'string',
            default: 'grid'
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;

        return [
            <InspectorControls>
                <PanelBody title={__('List Settings', 'recruit-connect-wp')}>
                    <RangeControl
                        label={__('Number of vacancies', 'recruit-connect-wp')}
                        value={attributes.limit}
                        onChange={(limit) => setAttributes({ limit })}
                        min={1}
                        max={50}
                    />
                    <ToggleControl
                        label={__('Show Filters', 'recruit-connect-wp')}
                        checked={attributes.showFilters}
                        onChange={(showFilters) => setAttributes({ showFilters })}
                    />
                    <SelectControl
                        label={__('Layout', 'recruit-connect-wp')}
                        value={attributes.layout}
                        options={[
                            { label: __('Grid', 'recruit-connect-wp'), value: 'grid' },
                            { label: __('List', 'recruit-connect-wp'), value: 'list' }
                        ]}
                        onChange={(layout) => setAttributes({ layout })}
                    />
                </PanelBody>
            </InspectorControls>,
            <div className={`rcwp-vacancy-list-block layout-${attributes.layout}`}>
                <div className="rcwp-block-placeholder">
                    {__('Vacancy List', 'recruit-connect-wp')}
                    <small>
                        {__('Displaying', 'recruit-connect-wp')} {attributes.limit}
                        {__('vacancies in', 'recruit-connect-wp')} {attributes.layout}
                        {__('layout', 'recruit-connect-wp')}
                    </small>
                </div>
            </div>
        ];
    },

    save: function() {
        return null; // Dynamic block, rendered by PHP
    }
});

// Vacancy Detail Block
registerBlockType('recruit-connect-wp/vacancy-detail', {
    title: __('Vacancy Detail', 'recruit-connect-wp'),
    icon: 'welcome-write-blog',
    category: 'recruit-connect',
    attributes: {
        showApplication: {
            type: 'boolean',
            default: true
        },
        layout: {
            type: 'string',
            default: 'standard'
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;

        return [
            <InspectorControls>
                <PanelBody title={__('Detail Settings', 'recruit-connect-wp')}>
                    <ToggleControl
                        label={__('Show Application Form', 'recruit-connect-wp')}
                        checked={attributes.showApplication}
                        onChange={(showApplication) => setAttributes({ showApplication })}
                    />
                    <SelectControl
                        label={__('Layout', 'recruit-connect-wp')}
                        value={attributes.layout}
                        options={[
                            { label: __('Standard', 'recruit-connect-wp'), value: 'standard' },
                            { label: __('Sidebar', 'recruit-connect-wp'), value: 'sidebar' },
                            { label: __('Full Width', 'recruit-connect-wp'), value: 'full' }
                        ]}
                        onChange={(layout) => setAttributes({ layout })}
                    />
                </PanelBody>
            </InspectorControls>,
            <div className="rcwp-vacancy-detail-block">
                <div className="rcwp-block-placeholder">
                    {__('Vacancy Detail', 'recruit-connect-wp')}
                    <small>
                        {__('Layout:', 'recruit-connect-wp')} {attributes.layout}
                    </small>
                </div>
            </div>
        ];
    },

    save: function() {
        return null; // Dynamic block, rendered by PHP
    }
});

// Application Form Block
registerBlockType('recruit-connect-wp/application-form', {
    title: __('Application Form', 'recruit-connect-wp'),
    icon: 'feedback',
    category: 'recruit-connect',

    edit: function() {
        return (
            <div className="rcwp-application-form-block">
                <div className="rcwp-block-placeholder">
                    {__('Application Form', 'recruit-connect-wp')}
                </div>
            </div>
        );
    },

    save: function() {
        return null; // Dynamic block, rendered by PHP
    }
});
