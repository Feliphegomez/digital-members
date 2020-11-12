/**
 * Block: DmRFID Membership Checkout
 *
 * Displays the Membership Checkout form.
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
    registerBlockType
} = wp.blocks;
const {
    SelectControl,
} = wp.components;

 /**
  * Register block
  */
 export default registerBlockType(
     'dmrfid/checkout-page',
     {
         title: __( 'Membership Checkout Form', 'digital-members-rfid' ),
         description: __( 'Displays the Membership Checkout form.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'list-view',
         },
         keywords: [ __( 'dmrfid', 'digital-members-rfid' ) ],
         supports: {
         },
         attributes: {
             dmrfid_default_level: {
                 type: 'string',
                 source: 'meta',
                 meta: 'dmrfid_default_level',
             },
         },
         edit: props => {
             const { attributes: { dmrfid_default_level }, className, setAttributes, isSelected } = props;
             return [
                isSelected && <Inspector { ...{ setAttributes, ...props} } />,
                <div className="dmrfid-block-element">
                  <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'digital-members-rfid' ) }</span>
                  <span className="dmrfid-block-subtitle">{ __( 'Membership Checkout Form', 'digital-members-rfid' ) }</span>
                  <hr />
                  <SelectControl
                      label={ __( 'Membership Level', 'digital-members-rfid' ) }
                      value={ dmrfid_default_level }
                      onChange={ dmrfid_default_level => setAttributes( { dmrfid_default_level } ) }
                      options={ window.dmrfid.all_level_values_and_labels }
                  />
                </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
