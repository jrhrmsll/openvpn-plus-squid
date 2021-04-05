# OpenVPN + Squid, an intermediary solution in the way to Zero Trust Networking

This project is a proof of concept for an intermediary solution on the way to Zero Trust Networking; based on OpenVPN
and Squid Proxy. It's conceived to be running in a local environment using VirtualBox and Vagrant.

Is composed by four virtual machines with Ubuntu 18.04 LTS, as follows:

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

## Running It

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

This basic scenario aims to illustrate a common one, where inbound traffic to internal networks is protected with a
VPN; but enhanced with access control rules based on the user's identity.
