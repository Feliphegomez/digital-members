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
         title: __( 'Membership Account: Memberships', 'digital-members-rfid' ),
         description: __( 'Displays the member\'s membership information.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'groups',
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
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Account: My Memberships', 'digital-members-rfid' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
