workflow "Testing" {
  on = "push"
  resolves = ["Build Docker Image"]
}

action "Build Docker Image" {
  uses = "./build-php"
  args = "-t xxxcoltxxx/request-logger/php:$GITHUB_SHA ."
}
