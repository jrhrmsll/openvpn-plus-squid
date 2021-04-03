#!/usr/bin/env bash

# iptables and routes
iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o enp0s8 -j MASQUERADE
route add -net 172.16.0.0/24 gw 192.168.10.100 enp0s8

if ufw status | grep "inactive"; then
  ufw default deny incoming
  ufw default allow outgoing

  ufw allow in on enp0s3 to any port 22
  ufw allow in on enp0s8 to any port 80
  ufw allow in on enp0s8 to any port 1194
  ufw allow in on tun0 to any port 3128
  ufw allow in on enp0s9 to any port 3128

  ufw --force enable
fi
