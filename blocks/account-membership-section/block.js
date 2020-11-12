/**
 * Block: DmRFID Membership Account: Memberships
 *
 * Displays the Membership Account > My Memberships page section.
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
     'dmrfid/account-membership-section',
     {
         title: __( 'Membership Account: Memberships', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s membership information.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'groups',
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
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Account: My Memberships', 'paid-memberships-pro' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
