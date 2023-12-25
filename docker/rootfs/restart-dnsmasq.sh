#!/bin/ash

# if there is a running dnsmasq process, kill it and start a new one
ps | grep dnsmasq | grep -v grep | grep -v 'restart' | awk '{print $1}' | xargs kill -9
dnsmasq
