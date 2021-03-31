#!/usr/bin/env bash

apt-get update
apt-get upgrade -y

# enable ipv4 forwarding
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

# install packages
apt-get install -y tree htop nginx

route add -net 192.168.10.0/24 gw 172.16.0.100 enp0s8

ufw default deny incoming
ufw default allow outgoing

ufw allow in on enp0s3 to any port 22
ufw allow in on enp0s8 to any port 80
ufw allow in on enp0s8 to any port 443

ufw --force enable
