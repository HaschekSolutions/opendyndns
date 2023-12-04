FROM alpine:3.18

LABEL org.opencontainers.image.source = "https://github.com/HaschekSolutions/opendyndns"

RUN apk add --no-cache php nginx php-openssl php-mbstring php-session php-json php-fpm dnsmasq sudo
RUN mkdir -p /var/www/opendyndns
WORKDIR /var/www/opendyndns

ADD . /var/www/opendyndns/.

ADD docker/rootfs/start.sh /etc/start.sh
RUN chmod +x /etc/start.sh

# nginx stuff
ADD docker/rootfs/nginx.conf /etc/nginx/http.d/default.conf
RUN mkdir -p /run/nginx
RUN mkdir -p /var/log/nginx
RUN sed -i 's/nobody/nginx/g' /etc/php81/php-fpm.d/www.conf
RUN sed -i 's/E_ALL \& ~E_DEPRECATED \& ~E_STRICT/E_ALL \& ~E_DEPRECATED \& ~E_STRICT \& ~E_NOTICE \& ~E_WARNING/g' /etc/php81/php.ini

# dnsmasq stuff
ADD docker/rootfs/dnsmasq.conf /etc/dnsmasq.conf
ADD docker/rootfs/sudoers /etc/sudoers
ADD docker/rootfs/restart-dnsmasq.sh /etc/restart-dnsmasq.sh

WORKDIR /var/www/opendyndns

# Volumes to mount
VOLUME /var/www/opendyndns/data
VOLUME /var/www/opendyndns/logs

EXPOSE 80 25 465

#CMD ["/bin/ash"]
ENTRYPOINT ["/etc/start.sh"]
