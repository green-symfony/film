mkdir "./bundles/green-symfony" -p
cd "./bundles/green-symfony"

git clone "https://github.com/green-symfony/command-bundle.git"
git clone "https://github.com/green-symfony/service-bundle.git"
git clone "https://github.com/green-symfony/env-processor-bundle.git"

cd "./command-bundle"
git checkout -b v1
git checkout v1 -f && git pull origin v1 -Xtheirs
cd ".."

cd "./service-bundle"
git checkout -b v1
git checkout v1 -f && git pull origin v1 -Xtheirs
cd ".."

cd "./env-processor-bundle"
git checkout -b v1
git checkout v1 -f && git pull origin v1 -Xtheirs
cd ".."

cd "../.."
composer install
composer dump-autoload -o
php "./bin/film" "a:i"
php "./bin/film" "c:c"