#!/usr/bin/env bash

#set -x


# sample fixed length of lines by rand
# using reservoir sampling
# @param sample size
# @param filepath
# @return
#        0    succeed
#        1    failed
function rand() {
    size=$1
    filepath=$2
    awk 'BEGIN{
        srand();
        size = "'${size}'";
    }
    {
        if (NR-0 <= size-0) {
            res[NR] = $0;
        } else {
            num = 1 + int(rand() * 10^8) % NR;
            #print num;
            if (num-0 <= size-0) res[num] = $0;
        }
    }
    END{
        for (k=1; k<=size; ++k) {
            print res[k];
        }
    }' $filepath
}

rand $@
