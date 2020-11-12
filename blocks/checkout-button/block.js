/**
 * Block: DmRFID Checkout Button
 *
 * Add a styled link to the DmRFID checkout page for a specific level.
 *
 */

/**
 * Block dependencies
 */
import Inspector from './inspector';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const {
    registerBlockType,
} = wp.blocks;
const {
    TextControl,
    SelectControl,
} = wp.components;

/**
 * Register block
 */
export default registerBlockType(
     'dmrfid/checkout-button',
     {
         title: __( 'Membership Checkout Button', 'digital-members-rfid' ),
         description: __( 'Displays a button-styled link to Membership Checkout for the specified level.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'migrate',
         },
         keywords: [ 
             __( 'dmrfid', 'digital-members-rfid' ), 
             __( 'buy', 'digital-members-rfid' ),
             __( 'level', 'digital-members-rfid' ),
         ],
         supports: {
         },
         attributes: {
             text: {
                 type: 'string',
                 default: 'Buy Now',
             },
             css_class: {
                 type: 'string',
                 default: 'dmrfid_btn',
             },
             level: {
                  type: 'string'
             }
         },
         edit: props => {
             const { attributes: { text, level, css_class}, className, setAttributes, isSelected } = props;
             return [
                isSelected && <Inspector { ...{ setAttributes, ...props} } />,
                <div className={ className }>
                  <a class={css_class} >{text}</a>
                </div>,
                isSelected && <div className="dmrfid-block-element">
                   <TextControl
                       label={ __( 'Button Text', 'digital-members-rfid' ) }
                       value={ text }
                       onChange={ text => setAttributes( { text } ) }
                   />
                   <SelectControl
                       label={ __( 'Membership Level', 'digital-members-rfid' ) }
                       value={ level }
                       onChange={ level => setAttributes( { level } ) }
                       options={ window.dmrfid.all_level_values_and_labels }
                   />
                   <TextControl
                       label={ __( 'CSS Class', 'digital-members-rfid' ) }
                       value={ css_class }
                       onChange={ css_class => setAttributes( { css_class } ) }
                   />
                   </div>,
            ];
         },
         save() {
           return null;
         },
       }
);