#---------------------------
# This script generates a new dmrfid.pot file for use in translations.
# To generate a new dmrfid.pot, cd to the main /digital-members-rfid/ directory,
# then execute `languages/gettext.sh` from the command line.
# then fix the header info (helps to have the old dmrfid.pot open before running script above)
# then execute `cp languages/digital-members-rfid.pot languages/digital-members-rfid.po` to copy the .pot to .po
# then execute `msgfmt languages/digital-members-rfid.po --output-file languages/digital-members-rfid.mo` to generate the .mo
#---------------------------
echo "Updating digital-members-rfid.pot... "
xgettext -j -o languages/digital-members-rfid.pot \
--default-domain=digital-members-rfid \
--language=PHP \
--keyword=_ \
--keyword=__ \
--keyword=_e \
--keyword=_ex \
--keyword=_n \
--keyword=_x \
--sort-by-file \
--package-version=1.0 \
--msgid-bugs-address="jason@strangerstudios.com" \
$(find . -name "*.php")
echo "Done!"