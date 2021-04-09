# OpenVPN + Squid, an intermediary solution in the way to Zero Trust Networking

This project is a proof of concept for an intermediary solution on the way to Zero Trust Networking; based on OpenVPN
and Squid Proxy.

## The Problem

Today, many enterprises employ a VPN to secure users access from external networks; but when inside, no other controls 
are enforced and trust is based on the perimeter only. In this model, a compromise device could be used to gain access 
to internal resources, directly or by lateral movement; with disastrous consequences for the business.

The Zero Trust model try to mitigate these problems replacing the perimeter security by access controls based on the
user's identity, devices state and context (e.g. geolocation).

There are multiple ways to implement a Zero Trust Networking Architecture, as cited in
[NIST SP 800-207](https://www.nist.gov/publications/zero-trust-architecture). One approach is the Google model, known as
[Beyond Corp](https://beyondcorp.com/).

In some cases, depending on the number of users and assets to protect, the transition to this new form cannot be done 
at once. Therefore, a compromise solution would be useful; providing some benefits while the migration is taking place.

What follows, attempt to be this **intermediary solution**, taking as advantage the VPN presence.

## Interlude

The core of this solution is the use of OpenVPN and Squid Proxy together, allowing to combine the perimeter based model
with and Identity Aware Access Proxy, but keeping the majority of infrastructure resources _as is_.

### Components

- OpenVPN: listening in the external network interface.

- Squid Proxy: configured to listen in the internal network.

- Redis: use as key value database.

### Flow

A user with a valid certificate connect to the OpenVPN server. After the user connection the server store the pair
(ip, user) in Redis.

e.g.
```
#!/usr/bin/env bash

redis-cli set ${ifconfig_pool_remote_ip} ${common_name}
```

The user SHOULD configure the internal VPN interface as the HTTP(S) proxy, only accessible after receiving the
proper _route_ from the VPN server. When the user requests an internal HTTP(S) resource, the Squid use the VPN user's IP
to get the corresponding identity (_common_name_) from Redis. Then, the identity is combine with some basic access 
control list allow/deny the access.

e.g.
```
external_acl_type identity_program children-startup=10 children-max=100 ttl=0 %SRC /usr/local/bin/identity.sh
acl identity external identity_program

acl block_users ext_user guess-01
acl block_ips dst 172.16.0.5
http_access deny identity block_users block_ips
```

The above fragments correspond to the next testing scenario, but for a production deployment another external acl could
be used as **decision engine**; allowing more dynamic configurations based on "user groups", "url categories", etc.

## Running It

This testing scenario is conceived to run in a local environment, using VirtualBox and Vagrant. It's composed by four
virtual machines with Ubuntu 18.04 LTS, as follows:

- **openvpn**: the VPN server with OpenVPN 2.4.4 and squid 3.5, with three networks interfaces:
  * Host-only, with IP address `192.168.10.5`
  * Internal Network, with IP address `172.31.0.5`

- **gateway**: a router between the networks `172.31.0.0/24` and `172.16.0.0/24`, with two networks interfaces:
  * Internal Network, with IP address `172.31.0.100`
  * Internal Network, with IP address `172.16.0.100`

- **nginx**: a http server with nginx
  * Internal Network, with IP address `172.16.0.5`

- **apache2**: a http server with apache2
  * Internal Network, with IP address `172.16.0.10`

After cloning the repository go to each directory and run:

```
vagrant up
```

When ready, access to th local [OpenVPN Management Console](http://192.168.10.5/ovpnphp) and logging with:

```
username: ovpnphp
password: ovpnphp
```

Go to [Clients](http://192.168.10.5/ovpnphp/clients) menu and add two users:
- _guess-01_
- _guess-02_

Download the corresponding clients configurations files (`<user>.tar.gz`) and add them to the VPN client.

Connect to the VPN with _guess-01_ and check it in [Connections](http://192.168.10.5/ovpnphp/connections) menu.

Before test the solution, configure the browser to use the proxy `172.31.0.5:3128` for HTTP access.

Then, go to the _nginx_ http server on IP address `172.16.0.5`, the request will be **denied**; but **allowed** if the
_apache2_ http server on `172.16.0.10` is accessed.

By connecting with user _guess-02_ the access to both http servers will be allowed.


