mkdir "./bundles/green-symfony" -p
cd "./bundles/green-symfony"
git clone "https://github.com/green-symfony/command-bundle.git"
git clone "https://github.com/green-symfony/service-bundle.git"
git clone "https://github.com/green-symfony/env-processor-bundle.git"
cd "../.."
composer install
composer dump-autoload -o
php "./bin/film" "c:c"