FROM almalinux:9 AS builder
RUN curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | tee /etc/yum.repos.d/yarn.repo && \
    dnf install -y yarn

FROM ccarney16/pterodactyl-panel:v1.11.3

COPY --from=builder /usr/bin/yarn /usr/bin/yarn

ARG APP_DIR="/var/www/html"

ENV APP_DIR_ENV=${APP_DIR}
WORKDIR ${APP_DIR_ENV}
ENV APP_KEY=SomeRandomStringSomeRandomString

COPY module/BillingModule.php ${APP_DIR_ENV}/app/Console/Commands/BillingModule.php
        
CMD ["php", "artisan", "billing:latest"]

ARG INSTALL_STRIPE=false
RUN if [ "$INSTALL_STRIPE" = "true" ]; then composer require stripe/stripe-php; fi

EXPOSE 80
EXPOSE 443
EXPOSE 9000
#Not actually sure if port 9000 is required; exposed in official container