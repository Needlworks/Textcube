#!/bin/sh

rm -f tattertools_ko.pot
make tattertools_ko.pot
svn co -r 2653 http://dev.tattertools.com/svn/sandbox/language l
for p in ko ja en de zh-CN
do
echo "========= $p ==========="
./php2po.pl l/$p.php $p.po
msgmerge --no-wrap -N -v $p.po tattertools_ko.pot -o $p.po
echo "# vim: enc=utf-8" >> $p.po
./po2php.pl $p.po $p.php
grep -v "^#" $p.php | sort > output/$p.php
grep -v "^#" ../$p.php | sort > origin/$p.php
done
