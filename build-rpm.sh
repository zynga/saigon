#!/bin/sh

FILES="api \
composer.json \
composer.lock \
conf \
consumer \
controllers \
include \
lib \
modules \
misc \
renderers \
routes \
views \
www \
LICENSE"

specfile=rpm/saigon.spec
package=saigon

version="$(sed -n 's/^Version:[ \t]*//p' $specfile)"
package_name="$package-$version"

topdir=`pwd`/_rpmbuild

rm -rf $topdir 2>/dev/null

mkdir -p $topdir/{SRPMS,RPMS,BUILD,SOURCES,SPECS}
mkdir -p $topdir/$package_name
mkdir -p rpms/srcs

rsync -aR $FILES $topdir/$package_name && \
touch package.xml && \
cp package.xml $topdir && \
cp $specfile $topdir/SPECS && \
echo "Creating source tar.gz..." && \
tar czvf $topdir/SOURCES/$package_name.tar.gz -C $topdir package.xml $package_name && \
echo "Building rpm ..." && \
rpmbuild --define="_topdir $topdir" -ba $specfile && \
cp $topdir/SRPMS/*.rpm rpms/srcs/ && \
cp $topdir/RPMS/*/*.rpm rpms/ && \
rm -rf $topdir 
rm -f package.xml
