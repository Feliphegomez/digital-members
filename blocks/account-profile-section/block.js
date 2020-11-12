/**
 * Block: DmRFID Checkout Button
 *
 * Add a styled link to the DmRFID checkout page for a
 * specific level.
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
     'dmrfid/account-profile-section',
     {
         title: __( 'Membership Account: Profile', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s profile information.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'admin-users',
         },
         keywords: [ __( 'dmrfid', 'paid-memberships-pro' ) ],
         supports: {
         },
         attributes: {
         },
         edit() {
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'paid-memberships-pro' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Account: Profile', 'paid-memberships-pro' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
