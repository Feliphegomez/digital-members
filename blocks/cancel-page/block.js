/**
 * Block: DmRFID Membership Cancel
 *
 * Displays the Membership Cancel page.
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
     'dmrfid/cancel-page',
     {
         title: __( 'Membership Cancel Page', 'paid-memberships-pro' ),
         description: __( 'Generates the Membership Cancel page.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'no',
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
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Cancel Page', 'paid-memberships-pro' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
