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
         title: __( 'Membership Account: Profile', 'digital-members-rfid' ),
         description: __( 'Displays the member\'s profile information.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'admin-users',
         },
         keywords: [ __( 'dmrfid', 'digital-members-rfid' ) ],
         supports: {
         },
         attributes: {
         },
         edit() {
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'digital-members-rfid' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Account: Profile', 'digital-members-rfid' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
