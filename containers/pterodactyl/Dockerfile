FROM almalinux:9 AS builder
RUN curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | tee /etc/yum.repos.d/yarn.repo && \
    dnf install -y yarn

FROM ccarney16/pterodactyl-panel:v1.11.3

COPY --from=builder /usr/bin/yarn /usr/bin/yarn

ARG APP_DIR="/var/www/html"

ENV APP_DIR_ENV=${APP_DIR}
ENV APP_KEY=SomeRandomStringSomeRandomString

COPY module/BillingModule.php ${APP_DIR_ENV}/app/Console/Commands/BillingModule.php
COPY stripe-install-check.sh /stripe-install-check.sh
        
RUN php artisan ${APP_DIR_ENV}/app/Console/Commands/BillingModule.php
CMD ["/stripe-install-check.sh"]