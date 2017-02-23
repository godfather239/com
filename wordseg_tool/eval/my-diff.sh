#!/bin/sh

FILE1=$1
FILE2=$2

awk '
{
	if (ARGIND == 1) {
		elem_file_1[$0];
	}else {
		elem_file_2[$0];
	}
}
END{
	for (item in elem_file_1) {
		if (!(item in elem_file_2)) {
			print item >"/dev/stdout";
		}
	}
	for (item in elem_file_2) {
		if (!(item in elem_file_1)) {
			print item >"/dev/stderr";
		}
	}
}' $FILE1 $FILE2
