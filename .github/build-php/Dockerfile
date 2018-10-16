FROM php:7.1-alpine

LABEL "com.github.actions.name"="Testing"
LABEL "com.github.actions.description"="Run unit tests"
LABEL "com.github.actions.icon"="mic"
LABEL "com.github.actions.color"="purple"

LABEL "repository"="https://github.com/xxxcoltxxx/request-logger"
LABEL "maintainer"="Aleksandr Paramonov <xxxcoltxxx@gmail.com>"

COPY . /app

RUN .github/action-build-php/install-composer.sh && rm .github/action-build-php/install-composer.sh

RUN php composer.phar install
