workflow "testing" {
    on = "push"
    resolves = "Build Docker Image"
}

action "Build Docker Image" {
    uses = "./build-php"
    args = "-t xxxcoltxxx/request-logger/php:$GITHUB_SHA ."
    needs = "Run Unit Tests"
}

action "Run Unit Tests" {
    uses "xxxcoltxxx/request-logger/php:$GITHUB_SHA"
    runs "vendor/bin/phpunit tests"
}
