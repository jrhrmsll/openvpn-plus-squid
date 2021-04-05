#!/usr/bin/env bash

# iptables and routes
route add -net 172.31.0.0/24 gw 172.16.0.100 enp0s8
