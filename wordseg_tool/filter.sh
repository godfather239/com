#!/bin/sh

awk '
{
    for (i=1; i<=NF; ++i) {
        split($i, a, "/");
        tag[a[2]];
    }
    for (s in tag) {
        print s;
    }
}'
