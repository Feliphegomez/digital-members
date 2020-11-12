/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const {
    PanelBody,
    TextControl,
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
        const { attributes: { text, level, css_class }, setAttributes } = this.props;

        return (
          <InspectorControls>
              <PanelBody>
                 <TextControl
                     label={ __( 'Button Text', 'digital-members-rfid' ) }
                     help={ __( 'Text for checkout button', 'digital-members-rfid' ) }
                     value={ text }
                     onChange={ text => setAttributes( { text } ) }
                 />
              </PanelBody>
              <PanelBody>
                  <SelectControl
                      label={ __( 'Level', 'digital-members-rfid' ) }
                      help={ __( 'The level to link to for checkout button', 'digital-members-rfid' ) }
                      value={ level }
                      onChange={ level => setAttributes( { level } ) }
                      options={ window.dmrfid.all_level_values_and_labels }
                  />
              </PanelBody>
              <PanelBody>
                 <TextControl
                     label={ __( 'CSS Class', 'digital-members-rfid' ) }
                     help={ __( 'Additional styling for checkout button', 'digital-members-rfid' ) }
                     value={ css_class }
                     onChange={ css_class => setAttributes( { css_class } ) }
                 />
              </PanelBody>
          </InspectorControls>
        );
    }
}
