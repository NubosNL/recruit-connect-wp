import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import './style.scss';

registerBlockType('recruit-connect/vacancy-field', {
    title: __('Vacancy Field', 'recruit-connect-wp'),
    icon: 'businessman',
    category: 'recruit-connect',
    attributes: {
        fieldKey: { type: 'string', default: '' },
        label: { type: 'string', default: '' },
        showLabel: { type: 'boolean', default: true },
    },
    edit: Edit,
    save: Save,
});
