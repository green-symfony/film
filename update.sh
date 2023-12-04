cd "./bundles/green-symfony"
cd "./command-bundle"
git fetch --all
git checkout -b v1 -f -q
git checkout v1 -f
git merge origin/v1 --ff
cd ".."
cd "./service-bundle"
git fetch --all
git checkout -b v1 -f -q
git checkout v1 -f
git merge origin/v1 --ff
cd ".."
cd "./env-processor-bundle"
git fetch --all
git checkout -b v1 -f -q
git checkout v1 -f
git merge origin/v1 --ff
cd "../../.."