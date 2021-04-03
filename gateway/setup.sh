#!/usr/bin/env bash

apt-get update
apt-get upgrade -y

# enable ipv4 forwarding
if sysctl net.ipv4.ip_forward | grep "net.ipv4.ip_forward = 0"; then
  echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
  sysctl -p
fi

# install packages
apt-get install -y tree htop traceroute
