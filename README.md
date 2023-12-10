<p align="center">
  <a href="" rel="noopener">
 <img height=200px src="https://raw.githubusercontent.com/HaschekSolutions/opendyndns/main/web/imgs/logo-200.png" alt="Open DynDNS"></a>
</p>

<h1 align="center">Open DynDNS</h1>



<div align="center">
  
![](https://img.shields.io/badge/php-8.3%2B-brightgreen.svg)
![](https://img.shields.io/badge/made%20with-htmx-brightgreen.svg)
![](https://img.shields.io/docker/image-size/hascheksolutions/opendyndns/latest?logo=Docker&color=brightgreen)
[![](https://img.shields.io/docker/pulls/hascheksolutions/opendyndns?color=brightgreen)](https://hub.docker.com/r/hascheksolutions/opendyndns)
[![](https://github.com/hascheksolutions/opendyndns/actions/workflows/build-docker.yml/badge.svg?color=brightgreen)](https://github.com/HaschekSolutions/opendyndns/actions)
[![Apache License](https://img.shields.io/badge/license-Apache-blue.svg?style=flat)](https://github.com/HaschekSolutions/opendyndns/blob/main/LICENSE)
[![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=https%3A%2F%2Fgithub.com%2FHaschekSolutions%2Fopendyndns&count_bg=%2379C83D&title_bg=%23555555&icon=&icon_color=%23E7E7E7&title=hits&edge_flat=false)](https://hits.seeyoufarm.com)
[![](https://img.shields.io/github/stars/HaschekSolutions/opendyndns.svg?label=Stars&style=social)](https://github.com/HaschekSolutions/opendyndns)

#### Selfhosted `dyn DNS` solution with a simple `REST API`
  
</div>


# [Changelog](/CHANGELOG.md)

# Features
- Web UI to manage hosts and add notes to hosts
- API manages `dnsmasq` configs and updates server
- Update your hosts via simple REST API (eg via `curl`)
- Autodetect IPs or supply via POST parameters
- IPv4 and IPv6 supported
- Can be configured to be a open resolver (eg so OpenDynDNS can be your LANs DNS with dynamic hostname updates)
- 100% File based, no database needed
- Settings per hostname are stored in comments of dnsmasq config files
- Very small image size (<20MB)


# Setup

## DNS

For OpenDynDNS to work you need to have a domain you can configure.

For example if you want to use the subdomain `sub.example.com` (so users can register `[anything].sub.example.com`) as root for your dyn DNS clients, and your OpenDynDNS instance is running on the IP `1.2.3.4` (which you want to access through the domain `dyndns.example.com`) you need to configure two DNS entries:

```zonefile
# zonefile for example.com
dyndns IN  A   1.2.3.4              # This is where the web interface will be (dyndns.example.com)
sub IN  NS  dyndns.example.com.  # the nameserver pointing to the machine running the docker container
```

# API

All API calls need the secret in form of a HTTP header called `SECRET`. The secret can be found in the web interface (unless you set the `NO_SECRET` config variable)

| Endpoint                         | Explanation                                                                                                                                                                                                                                    | API answer                                           |
|----------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------|
| `/setip/yourhost.example.com`    | Updates the IP of the hostname. If no POST vars are supplied, automatically detecting IP from request, if POST var `ipv4` or `ipv6` is provided, updates the host with these IPs | `OK updated [ip]` or the corresponding error message |
| `/clearips/yourhost.example.com` | Clears all IPs associated with this hostname                                                                                                                                                                                                   | `OK` or the corresponding error message              |


## Examples

**Autodetecting IP and updating hostname `my.example.com`**

```curl
curl http://localhost:8080/api/setip/my.example.com \
-H "secret:5dc2eff026f11b1e2ec537abba60c079b50fc495212622e6a76f05bcbed11794"
```

**Supplying the IPv4 and IPv6 IPs in the request**

```curl
curl http://localhost:8080/api/setip/my.example.com \
-H "secret:5dc2eff026f11b1e2ec537abba60c079b50fc495212622e6a76f05bcbed11794" \
--data "ipv4=1.1.1.1" \
--data "ipv6=2001:4860:4860::8888"
```

**Clear IPs of `my.example.com`**

```curl
curl http://localhost:8080/api/clearips/yes.example.com \
-H "secret:5dc2eff026f11b1e2ec537abba60c079b50fc495212622e6a76f05bcbed11794"
```

# Configuration

| Config                 | Explanation                                                                                                                                                        | Default                       |
|------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------|
| URL                    | URL to the web UI. No tailing slash!                                                                                                                               | `http://localhost:8080`       |
| DOMAINS                | Comma separated list of domains which can be registered. Must have DNS records pointing to the containers DNS port                                                 | `example.com,sub.example.org` |
| ALLOW_PRIVATE_IP       | Whether or not IPs in private ranges can be set (useful if you want to use OpenDynDNS as your LAN DNS)                                                             | `false`                       |
| ALLOW_DYNAMIC_CREATION | If set to `true` (default), the API will allow you to create new hostnames on the fly by calling the `/setip` endpoint. All further calls need the `secret` though | `true`                        |
| NO_SECRET              | If set to `true`, all API calls can be used without the generated secret. Please only use in trusted LAN settings                                                  | `false`                       |

# Docker environment vars

All config settings can be supplied to docker containers with the same name. Just with one addition:

| env                | Explanation                                                                                                                                        | Default   |
|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------|-----------|
| DNS_OPENRESOLVE    | If set to `true`, dnsmasq will forward all unknown requests to cloudflare DNS servers. ONLY FOR LAN, NEVER EXPOSE AN OPEN RESOLVER TO THE INTERNET | `false`   |

