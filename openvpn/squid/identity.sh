#!/bin/bash

while read -r line
do
  printf 'OK user=%s\n' $(redis-cli get "${line}")
done
