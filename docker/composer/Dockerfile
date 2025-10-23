FROM composer:latest


ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} -S www && \
    adduser -u ${USER_ID} -S www-user -G www


WORKDIR /var/www/myapp


RUN chown -R www-user:www /var/www/myapp


USER www-user

ENTRYPOINT ["composer", "--ignore-platform-reqs"]
