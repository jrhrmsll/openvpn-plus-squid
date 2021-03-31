#!/usr/bin/env bash

redis-cli set ${ifconfig_pool_remote_ip} ${common_name}
