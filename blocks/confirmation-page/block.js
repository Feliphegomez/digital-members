/**
 * Block: DmRFID Membership Confirmation
 *
 * Displays the Membership Confirmation template.
 *
 */

 /**
  * Internal block libraries
  */
 const { __ } = wp.i18n;
 const {
    registerBlockType
} = wp.blocks;

 /**
  * Register block
  */
 export default registerBlockType(
     'dmrfid/confirmation-page',
     {
         title: __( 'Membership Confirmation Page', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s Membership Confirmation after Membership Checkout.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'yes',
         },
         keywords: [ __( 'dmrfid', 'paid-memberships-pro' ) ],
         supports: {
         },
         attributes: {
         },
         edit(){
             return [
                <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'paid-memberships-pro' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Confirmation Page', 'paid-memberships-pro' ) }</span>
                </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
