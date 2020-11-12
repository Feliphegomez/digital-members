/**
 * Block: DmRFID Member Profile Edit
 *
 *
 */

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Register block
 */
export default registerBlockType("dmrfid/member-profile-edit", {
	title: __("Member Profile Edit", "digital-members-rfid"),
	description: __("Allow member profile editing.", "digital-members-rfid"),
	category: "dmrfid",
	icon: {
		background: "#2997c8",
		foreground: "#ffffff",
		src: "admin-users",
	},
	keywords: [
		__("dmrfid", "digital-members-rfid"),
		__("member", "digital-members-rfid"),
		__("profile", "digital-members-rfid"),
	],
	edit: (props) => {
		return (
			<div className="dmrfid-block-element">
				<span className="dmrfid-block-title">{__("Digital Members RFID", "digital-members-rfid")}</span>
				<span className="dmrfid-block-subtitle">
					{__("Member Profile Edit", "digital-members-rfid")}
				</span>
			</div>
		);
	},
	save() {
		return null;
	},
});
