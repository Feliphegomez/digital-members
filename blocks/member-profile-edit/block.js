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
	title: __("Member Profile Edit", "paid-memberships-pro"),
	description: __("Allow member profile editing.", "paid-memberships-pro"),
	category: "dmrfid",
	icon: {
		background: "#2997c8",
		foreground: "#ffffff",
		src: "admin-users",
	},
	keywords: [
		__("dmrfid", "paid-memberships-pro"),
		__("member", "paid-memberships-pro"),
		__("profile", "paid-memberships-pro"),
	],
	edit: (props) => {
		return (
			<div className="dmrfid-block-element">
				<span className="dmrfid-block-title">{__("Digital Members RFID", "paid-memberships-pro")}</span>
				<span className="dmrfid-block-subtitle">
					{__("Member Profile Edit", "paid-memberships-pro")}
				</span>
			</div>
		);
	},
	save() {
		return null;
	},
});
