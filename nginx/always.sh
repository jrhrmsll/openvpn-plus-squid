#!/usr/bin/env bash

# iptables and routes
route add -net 192.168.10.0/24 gw 172.16.0.100 enp0s8
