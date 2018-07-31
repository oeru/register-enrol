#!/usr/bin/env bash
#
# resizing images for use in the BFF app.

SIZE="20x20"

SUFF=png
IMGS=`ls *enrol*.$SUFF | grep -v ".&&x&&."`
CVT=`which convert`

for IMG in $IMGS
do
    BASE=`basename -s $SUFF $IMG`
    NEW=$BASE$SIZE.$SUFF
    echo "resizing $IMG to $SIZE as $NEW"
    $CVT -scale $SIZE $IMG $NEW
done
