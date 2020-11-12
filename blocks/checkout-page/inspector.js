/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const {
    PanelBody,
    PanelRow,
    SelectControl,
} = wp.components;
const {
    InspectorControls,
} = wp.blockEditor;

/**
 * Create an Inspector Controls wrapper Component
 */ 
export default class Inspector extends Component {

    constructor() {
        super( ...arguments );
    }

    render() {
        const { attributes: { dmrfid_default_level }, setAttributes } = this.props;

        return (
          <InspectorControls>
          <PanelBody>
             <SelectControl
                 label={ __( 'Membership Level', 'paid-memberships-pro' ) }
                 help={ __( 'Choose a default level for Membership Checkout.', 'paid-memberships-pro' ) }
                 value={ dmrfid_default_level }
                 onChange={ dmrfid_default_level => setAttributes( { dmrfid_default_level } ) }
                 options={ [''].concat( window.dmrfid.all_level_values_and_labels ) }
             />
          </PanelBody>
          </InspectorControls>
        );
    }
}
