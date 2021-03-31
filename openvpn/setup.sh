#!/usr/bin/env bash

apt-get update
apt-get upgrade -y

# enable ipv4 forwarding
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

# install packages
apt-get install -y tree htop openvpn easy-rsa sqlite3 squid3 apache2 php7.2 php7.2-sqlite redis

# copy ovpnscripts
if [ ! -d "/opt/ovpnscripts" ]; then
  cp -r /vagrant/ovpnscripts /opt/ovpnscripts
  chown -R www-data:www-data /opt/ovpnscripts
fi

# copy ovpnphp, create db and update /etc/sudoers
if [ ! -d "/var/www/html/ovpnphp" ]; then
  cp -r /vagrant/ovpnphp /var/www/html/ovpnphp

  cat /var/www/html/ovpnphp/visudo >> /etc/sudoers
  sqlite3 /var/www/html/ovpnphp/db/ovpn.db < /var/www/html/ovpnphp/db/schema.sql

  chown -R www-data:www-data /var/www/html/ovpnphp
fi

# enable ovpnphp site and rewrite module
if [ ! -e "/etc/apache2/sites-available/ovpnphp.conf" ]; then
  cp /var/www/html/ovpnphp/ovpnphp.conf /etc/apache2/sites-available/ovpnphp.conf

  a2ensite ovpnphp.conf
  a2enmod rewrite

  systemctl restart apache2
fi

# copy OpenVPN event handlers
if [ ! -e "/etc/openvpn/server/client-connect.sh" ]; then
  cp /vagrant/handlers/client-connect.sh /etc/openvpn/server/client-connect.sh
  chmod +x /etc/openvpn/server/client-connect.sh
fi

# setup OpenVPN server
if [ ! -d "/opt/zt/easy-rsa" ]; then
  make-cadir /opt/zt/easy-rsa
  cp /vagrant/openssl.cnf /opt/zt/easy-rsa/openssl.cnf

  bash /opt/ovpnscripts/initialize
  bash /opt/ovpnscripts/gen-server-cert zero-trust
  bash /opt/ovpnscripts/gen-server-config zero-trust

  cp -r /opt/zt/server/* /etc/openvpn/

  echo 'push "route 172.16.0.0 255.240.0.0"' >> /etc/openvpn/zero-trust.conf

  systemctl -f enable openvpn@zero-trust
  systemctl start openvpn@zero-trust
fi

# copy squid helpers
if [ ! -e "/usr/local/bin/identity.sh" ]; then
  cp /vagrant/squid/identity.sh /usr/local/bin/identity.sh
  chown proxy:proxy /usr/local/bin/identity.sh
  chmod +x /usr/local/bin/identity.sh
fi

# setup squid
cp /vagrant/squid/squid.conf /etc/squid/squid.conf
systemctl restart squid

# iptables and routes
iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o enp0s8 -j MASQUERADE
route add -net 172.16.0.0/24 gw 192.168.10.100 enp0s8

ufw default deny incoming
ufw default allow outgoing

ufw allow in on enp0s3 to any port 22
ufw allow in on enp0s8 to any port 80
ufw allow in on enp0s8 to any port 1194
ufw allow in on tun0 to any port 3128
ufw allow in on enp0s9 to any port 3128

ufw --force enable
