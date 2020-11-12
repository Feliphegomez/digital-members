/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const {
    PanelBody,
    CheckboxControl,
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
        const { attributes: { membership, profile, invoices, links }, setAttributes } = this.props;

        return (
          <InspectorControls>
              <PanelBody>
                <CheckboxControl
                    label={ __( "Show 'My Memberships' Section", 'digital-members-rfid' ) }
                    checked={ membership }
                    onChange={ membership => setAttributes( {membership} ) }
                />
              </PanelBody>
              <PanelBody>
                <CheckboxControl
                  label={ __( "Show 'Profile' Section", 'digital-members-rfid' ) }
                  checked={ profile }
                  onChange={ profile => setAttributes( {profile} ) }
                  />
              </PanelBody>
              <PanelBody>
                <CheckboxControl
                  label={ __( "Show 'Invoices' Section", 'digital-members-rfid' ) }
                  checked={ invoices }
                  onChange={ invoices => setAttributes( {invoices} ) }
                  />
              </PanelBody>
              <PanelBody>
                <CheckboxControl
                  label={ __( "Show 'Member Links' Section", 'digital-members-rfid' ) }
                  checked={ links }
                  onChange={ links => setAttributes( {links} ) }
                  />
              </PanelBody>
          </InspectorControls>
        );
    }
}
