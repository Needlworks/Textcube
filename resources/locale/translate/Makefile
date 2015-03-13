LANGUAGES=ko ja en de zh-CN

.PHONY: check_xgettext filelist.txt

all: tattertools_ko.pot tattertools_en.pot

tattertools_ko.pot: check_xgettext filelist.txt
	@echo -n "* Extracting i18n strings : "
	@xgettext -s -k_t -k_f -k_text -k_textf -k_t_noop --from-code=utf-8 -f filelist.txt -o $@
	@echo `grep msgid tattertools_ko.pot | wc -l` strings extracted.

tattertools_en.pot:
	@echo -n "* Convert en.po to" $@
	@./msgstr2pot.pl en.po $@ 

check_xgettext:
	@echo -n "* Check xgettext version to support php"
	@if test -z "`xgettext --help | grep -i php`"; then echo " : You need php enabled xgettext, update gettext package!"; \
	 else echo ": Yes, you have right version."; fi
		
filelist.txt:
	@echo -n "* Listing up php files ($@) : "
	@if test -f $@; then echo -n "$@ found, skip listing up : "; else find ../../../{framework,library,plugins,resources,skin} -name "*.php" > $@; fi
	@echo "`cat $@ | wc -l` files are found."
	
language_po:
	@for l in $(LANGUAGES); do ./po2php.pl $$l.po $$l.php || exit 1; done

check:
	@for l in $(LANGUAGES); do echo "* Check po format: $$l.po"; msgfmt -c --check-accelerators $$l.po || exit 1; done
