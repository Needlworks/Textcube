#!/bin/sh
rm -f textcube_ko.pot
touch textcube_ko.pot
#svn co http://dev.textcube.org/svn/trunk/language l
for p in ko ja en zh-CN
do
echo "========= $p ==========="
./php2po.pl l/$p.php $p.po
msgmerge --no-wrap -N -v $p.po textcube_ko.pot -o $p.po
echo "# vim: enc=utf-8" >> $p.po
./po2php.pl $p.po $p.php
grep -v "^#" $p.php | sort > output/$p.php
grep -v "^#" ../$p.php | sort > origin/$p.php
done
