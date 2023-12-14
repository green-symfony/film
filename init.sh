mkdir "./bundles/green-symfony" -p
cd "./bundles/green-symfony"
git clone "https://github.com/green-symfony/command-bundle.git"
git checkout -b v1
git pull origin v1
git clone "https://github.com/green-symfony/service-bundle.git"
git checkout -b v1
git pull origin v1
git clone "https://github.com/green-symfony/env-processor-bundle.git"
git checkout -b v1
git pull origin v1
cd "../.."
composer install
composer dump-autoload -o
php "./bin/film" "c:c"