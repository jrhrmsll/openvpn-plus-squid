#!/usr/bin/env bash

apt-get update
apt-get upgrade -y

# enable ipv4 forwarding
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

# install packages
apt-get install -y tree htop traceroute
