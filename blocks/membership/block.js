/**
 * Block: DmRFID Membership
 *
 *
 */

 /**
  * Internal block libraries
  */
 const { __ } = wp.i18n;
 const {
    registerBlockType,
} = wp.blocks;
const {
    PanelBody,
    SelectControl,
} = wp.components;

const {
    InspectorControls,
    InnerBlocks,
} = wp.blockEditor;

const all_levels = [{ value: 0, label: "Non-Members" }].concat( dmrfid.all_level_values_and_labels );

 /**
  * Register block
  */
 export default registerBlockType(
     'dmrfid/membership',
     {
         title: __( 'Require Membership Block', 'digital-members-rfid' ),
         description: __( 'Control the visibility of nested blocks for members or non-members.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'visibility',
         },
         keywords: [ __( 'dmrfid', 'digital-members-rfid' ) ],
         attributes: {
             levels: {
                 type: 'array',
                 default:[]
             },
             uid: {
                 type: 'string',
                 default:'',
             },
         },
         edit: props => {
             const { attributes: {levels, uid}, setAttributes, isSelected } = props;
             if( uid=='' ) {
               var rand = Math.random()+"";
               setAttributes( { uid:rand } );
             }
             return [
                isSelected && <InspectorControls>
                    <PanelBody>
                        <SelectControl
                            multiple
                            label={ __( 'Select levels to show content to:', 'digital-members-rfid' ) }
                            value={ levels }
                            onChange={ levels => { setAttributes( { levels } ) } }
                            options={ all_levels }
                        />
                    </PanelBody>
                </InspectorControls>,
                isSelected && <div className="dmrfid-block-require-membership-element" >
                  <span className="dmrfid-block-title">{ __( 'Require Membership', 'digital-members-rfid' ) }</span>
                  <PanelBody>
                      <SelectControl
                          multiple
                          label={ __( 'Select levels to show content to:', 'digital-members-rfid' ) }
                          value={ levels }
                          onChange={ levels => { setAttributes( { levels } ) } }
                          options={ all_levels }
                      />
                  </PanelBody>
                  <InnerBlocks
                      renderAppender={ () => (
                        <InnerBlocks.ButtonBlockAppender />
                      ) }
                      templateLock={ false }
                  />
                </div>,
                ! isSelected && <div className="dmrfid-block-require-membership-element" >
                  <span className="dmrfid-block-title">{ __( 'Require Membership', 'digital-members-rfid' ) }</span>
                  <InnerBlocks
                      renderAppender={ () => (
                        <InnerBlocks.ButtonBlockAppender />
                      ) }
                      templateLock={ false }
                  />
                </div>,
            ];
         },
         save: props => {
           const {  className } = props;
        		return (
        			<div className={ className }>
        				<InnerBlocks.Content />
        			</div>
        		);
        	},
       }
 );
